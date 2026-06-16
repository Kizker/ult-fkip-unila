<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RequestStatus;
use App\Http\Controllers\Controller;
use App\Models\LetterNumberFormat;
use App\Models\Request as UltRequest;
use App\Models\Unit;
use App\Services\Documents\DocumentGateService;
use App\Enums\UnitType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class DocumentRequestGateController extends Controller
{
    public function __construct(private readonly DocumentGateService $gate) {}

    public function verify(UltRequest $request, Request $http)
    {
        $status = $request->current_status instanceof RequestStatus
            ? $request->current_status->value
            : (string) $request->current_status;

        // REVIEW_ULT decisions are ULT-only (can be delegated by granting requests.review_ult).
        if ($status === RequestStatus::REVIEW_ULT->value) {
            Gate::authorize('reviewUlt', $request);
        } else {
            $this->authorizeInitialGateActions($request, $http);
        }

        $data = $http->validate([
            'decision' => ['required','in:PASS,REVISION,REJECT'],
            'note' => ['nullable','string','max:2000','required_if:decision,REVISION'],
            'letter_format_id' => ['nullable','integer'],
        ]);

        $note = isset($data['note']) ? trim((string) $data['note']) : null;

        if ($data['decision'] === 'PASS' && blank((string) ($request->nomor_surat ?? ''))) {
            try {
                [$format, $unit] = $this->resolveLetterFormatForApproval(
                    $request,
                    isset($data['letter_format_id']) ? (int) $data['letter_format_id'] : null,
                    $http
                );
                $this->gate->fillNomorSuratFromTemplate($request, $http->user(), $format, $unit);
                $request->refresh();
            } catch (HttpExceptionInterface $e) {
                $msg = trim((string) $e->getMessage());
                if ($msg === '') {
                    $msg = 'Template nomor surat wajib dipilih sebelum disetujui.';
                }

                return back()
                    ->withErrors(['letter_format_id' => $msg])
                    ->withInput();
            }
        }

        try {
            $this->gate->verifyInitial($request, $http->user(), $data['decision'], $note !== '' ? $note : null);
        } catch (HttpExceptionInterface $e) {
            if ((int) $e->getStatusCode() !== 422) {
                throw $e;
            }

            $msg = trim((string) $e->getMessage());
            if ($msg === '') {
                $msg = 'Aksi gate gagal diproses.';
            }

            return back()
                ->withErrors(['gate' => $msg])
                ->withInput();
        }

        return back()->with('status', 'Gate updated.');
    }

    public function startSigning(UltRequest $request, Request $http)
    {
        $status = $request->current_status instanceof RequestStatus
            ? $request->current_status->value
            : (string) $request->current_status;
        if ($status === RequestStatus::REVIEW_ULT->value) {
            Gate::authorize('reviewUlt', $request);
        } else {
            Gate::authorize('process', $request);
        }

        $result = $this->gate->startSigning($request, $http->user());
        $resultStatus = $result->current_status instanceof RequestStatus
            ? $result->current_status->value
            : (string) $result->current_status;

        if ($resultStatus === RequestStatus::PERLU_PERBAIKAN->value) {
            return back()->with('status', 'Template sertifikat/piagam belum valid. Permohonan dikembalikan untuk perbaikan.');
        }

        return back()->with('status', 'Signing dimulai.');
    }

    /**
     * Resolve applicable numbering scope for applicant hierarchy.
     *
     * Priority order: prodi -> jurusan -> fakultas.
     *
     * @return array<int,Unit>
     */
    private function resolveLetterFormatScopeUnits(?Unit $studentUnit): array
    {
        if (!$studentUnit) {
            return [];
        }

        $units = [
            $studentUnit->ancestorOfType(UnitType::prodi),
            $studentUnit->ancestorOfType(UnitType::jurusan),
            $studentUnit->ancestorOfType(UnitType::fakultas),
        ];

        $out = [];
        foreach ($units as $unit) {
            if (!$unit) {
                continue;
            }

            $exists = false;
            foreach ($out as $item) {
                if ((int) $item->id === (int) $unit->id) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $out[] = $unit;
            }
        }

        return $out;
    }

    /**
     * @return array{0:LetterNumberFormat,1:Unit}
     */
    private function resolveLetterFormatForApproval(UltRequest $request, ?int $formatId, Request $http): array
    {
        if (!$formatId) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Template nomor surat wajib dipilih sebelum disetujui.');
        }

        if (!Schema::hasTable('letter_number_formats')) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(500, 'Tabel template nomor surat belum ada. Jalankan: php artisan migrate');
        }

        $http->validate([
            'letter_format_id' => ['required', 'integer', 'exists:letter_number_formats,id'],
        ]);

        $request->loadMissing('student.unit.parent.parent');
        $format = LetterNumberFormat::query()->active()->with('unit')->findOrFail($formatId);

        $applicableUnits = $this->resolveLetterFormatScopeUnits($request->student?->unit);
        if (empty($applicableUnits)) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Unit pemohon tidak ditemukan.');
        }

        $applicableUnitIds = array_map(fn (Unit $u) => (int) $u->id, $applicableUnits);
        if (!in_array((int) $format->unit_id, $applicableUnitIds, true)) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Template tidak sesuai dengan cakupan unit pemohon (Prodi/Jurusan/Fakultas).');
        }

        if (!$format->unit) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Unit template nomor surat tidak valid.');
        }

        return [$format, $format->unit];
    }

    private function authorizeInitialGateActions(UltRequest $request, Request $http): void
    {
        Gate::authorize('process', $request);

        $actor = $http->user();
        if (!$actor) {
            abort(403);
        }

        // Superadmin can still override for troubleshooting.
        if ($actor->hasRole('Superadmin')) {
            return;
        }

        $request->loadMissing('service.workflow');
        $requiredRole = $this->normalizeGateRole((string) ($request->service?->workflow?->gate_role ?? ''));
        if ($requiredRole === '') {
            return;
        }

        if (!$this->actorMatchesGateRole($actor, $requiredRole)) {
            abort(403, "Tahap gate awal (verifikasi + nomor surat) hanya untuk role {$requiredRole}.");
        }
    }

    private function normalizeGateRole(string $raw): string
    {
        $normalized = strtoupper(str_replace(' ', '_', trim($raw)));
        return match ($normalized) {
            'ADMIN_JURUSAN',
            'ADMIN_JURUSAN_PER_PRODI',
            'ADMIN_PRODI' => 'Admin Jurusan',
            'STAF_ULT',
            'STAFF_ULT' => 'Staf ULT',
            default => trim($raw),
        };
    }

    private function actorMatchesGateRole(\App\Models\User $actor, string $requiredRole): bool
    {
        $normalized = strtoupper(str_replace(' ', '_', trim($requiredRole)));

        return match ($normalized) {
            'ADMIN_JURUSAN' => $actor->hasAnyRole([
                'Admin Jurusan',
                'Admin Jurusan per Prodi',
                'Admin Prodi',
            ]),
            'STAF_ULT' => $actor->hasAnyRole([
                'Staf ULT',
                'Staff ULT',
            ]),
            default => $actor->hasRole($requiredRole),
        };
    }
}
