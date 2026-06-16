<?php

namespace App\Services\Documents;

use App\Enums\RequestSignoffStatus;
use App\Enums\RequestStatus;
use App\Models\LetterNumber;
use App\Models\LetterNumberFormat;
use App\Models\Request as UltRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\RequestWorkflowService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class DocumentGateService
{
    public function __construct(
        private readonly RequestWorkflowService $workflow,
        private readonly AuditLogger $audit,
        private readonly LetterNumberService $letterNumbers,
        private readonly CertificateDocumentService $certificateDocs,
    ) {}

    public function verifyInitial(UltRequest $request, User $actor, string $decision, ?string $note = null): UltRequest
    {
        $decision = strtoupper(trim($decision));
        if (!in_array($decision, ['PASS', 'REVISION', 'REJECT'], true)) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Invalid decision.');
        }

        return DB::transaction(function () use ($request, $actor, $decision, $note) {
            $request->refresh();

            if (!in_array($request->current_status, [RequestStatus::DIAJUKAN, RequestStatus::PERLU_PERBAIKAN, RequestStatus::REVIEW_ULT], true)) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Status tidak valid untuk verifikasi awal.');
            }

            // ULT review stage can only be decided by ULT.
            if ($request->current_status === RequestStatus::REVIEW_ULT && !$actor->can('requests.review_ult')) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(403, 'Hanya Staf ULT yang dapat memproses review ULT.');
            }

            $to = match ($decision) {
                // PASS from admin jurusan: requires nomor_surat already filled, then goes to REVIEW_ULT.
                'PASS' => RequestStatus::REVIEW_ULT,
                'REVISION' => RequestStatus::PERLU_PERBAIKAN,
                'REJECT' => RequestStatus::DITOLAK_ADMIN,
            };

            if ($decision === 'PASS' && empty($request->nomor_surat)) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Nomor surat wajib diisi sebelum disetujui.');
            }

            $this->audit->log('doc.gate.verify_initial', 'requests', (string) $request->id, [
                'decision' => $decision,
            ]);

            return $this->workflow->transition($request, $to, $actor, $note, 'doc_gate_verify', $request->currentUnit);
        });
    }

    public function fillNomorSuratFromTemplate(
        UltRequest $request,
        User $actor,
        LetterNumberFormat $format,
        \App\Models\Unit $unit,
        ?int $seqOverride = null
    ): UltRequest {
        return DB::transaction(function () use ($request, $actor, $format, $unit, $seqOverride) {
            $request->refresh();

            if (!in_array($request->current_status, [RequestStatus::DIAJUKAN, RequestStatus::PERLU_PERBAIKAN, RequestStatus::GATE_VERIFIED, RequestStatus::REVIEW_ULT], true)) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Status harus DIAJUKAN/PERLU_PERBAIKAN/GATE_VERIFIED/REVIEW_ULT untuk input nomor surat.');
            }

            $row = $this->letterNumbers->issue($request, $unit, $format, $actor, $seqOverride);

            $this->audit->log('doc.gate.nomor_surat_generated', 'requests', (string) $request->id, [
                'format_id' => $format->id,
                'format_key' => $format->format_key,
                'unit_id' => $unit->id,
                'year' => $row->year,
                'number_seq' => $row->number_seq,
                'number_text' => $row->number_text,
                'is_manual_override' => (bool) $row->is_manual_override,
            ]);

            return $request->fresh(['service', 'student', 'currentUnit', 'letterNumber']);
        });
    }

    public function startSigning(UltRequest $request, User $actor): UltRequest
    {
        return DB::transaction(function () use ($request, $actor) {
            $request->loadMissing(['service.signers', 'signoffs', 'currentUnit']);
            $request->refresh();

            if (!in_array($request->current_status, [RequestStatus::REVIEW_ULT, RequestStatus::GATE_VERIFIED], true)) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Status harus REVIEW_ULT/GATE_VERIFIED untuk start signing.');
            }

            if (empty($request->nomor_surat)) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'NOMOR_SURAT wajib diisi sebelum signing.');
            }

            if ($request->signoffs()->count() === 0) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Signer chain belum di-inisialisasi pada request ini.');
            }

            if ($this->certificateDocs->isCertificateRequest($request)) {
                $placeholderErrors = $this->certificateDocs->validateRequiredPlaceholders($request);
                if (!empty($placeholderErrors)) {
                    $note = 'Template Sertifikat/Piagam perlu perbaikan: '.implode(' | ', $placeholderErrors);
                    $request->resume_signer_order_index = null;
                    $request->current_signer_order_index = null;
                    $request->save();

                    $this->audit->log('doc.certificate.placeholder.invalid', 'requests', (string) $request->id, [
                        'request_id' => $request->id,
                        'errors' => $placeholderErrors,
                    ]);

                    return $this->workflow->transition(
                        $request,
                        RequestStatus::PERLU_PERBAIKAN,
                        $actor,
                        $note,
                        'doc_signing',
                        $request->currentUnit
                    );
                }
            }

            // Ensure custom signers are assigned before signing starts.
            foreach ($request->signoffs as $s) {
                $role = strtoupper(trim((string) ($s->signer_role ?? '')));
                if (in_array($role, ['CUSTOM', 'PEMOHON'], true) && !$s->signer_user_id) {
                    $hasPrefilledSignature = $s->status === RequestSignoffStatus::APPROVED && !empty($s->signature_file_path);
                    if (!$hasPrefilledSignature) {
                        throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, "Signer {$role} belum dipilih/dipetakan pada request ini.");
                    }
                }

                if (in_array($role, ['CERT_CUSTOM', 'CERT_PEMOHON'], true)) {
                    $hasPrefilledSignature = $s->status === RequestSignoffStatus::APPROVED && !empty($s->signature_file_path);
                    if (!$hasPrefilledSignature) {
                        throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, "Signer {$role} wajib memiliki file tanda tangan gambar.");
                    }
                }

                if (in_array($role, ['DOSEN', 'KAJUR_SCOPE', 'SEKJUR_SCOPE', 'KAPRODI_SCOPE'], true) && !$s->signer_user_id) {
                    throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, "Signer {$role} belum dipilih/dipetakan pada request ini.");
                }

                if ($role === 'CERT_INTERNAL' && !$s->signer_user_id) {
                    throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, "Signer {$role} belum dipilih user internalnya.");
                }
            }

            $pendingSignoff = $request->signoffs
                ->first(fn ($s) => $s->status === RequestSignoffStatus::PENDING);

            if (!$pendingSignoff) {
                $nonApproved = $request->signoffs
                    ->first(fn ($s) => $s->status !== RequestSignoffStatus::APPROVED);
                if ($nonApproved) {
                    throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Masih ada signer yang belum APPROVED.');
                }

                $lastRequiredApproved = $request->signoffs
                    ->where('is_required', true)
                    ->where('status', RequestSignoffStatus::APPROVED)
                    ->sortBy('order_index')
                    ->last();

                if ($lastRequiredApproved && !$request->last_required_approved_at) {
                    $request->last_required_approved_at = $lastRequiredApproved->decided_at ?: now();
                }
                if ($request->last_required_approved_at && !$request->tanggal_surat) {
                    $request->tanggal_surat = $request->last_required_approved_at->toDateString();
                }

                $request->current_signer_order_index = null;
                $request->resume_signer_order_index = null;
                $request->save();

                $this->audit->log('doc.signing.skipped_no_pending', 'requests', (string) $request->id, [
                    'request_id' => $request->id,
                ]);

                return $this->workflow->transition(
                    $request,
                    RequestStatus::READY_FOR_FINAL,
                    $actor,
                    'Semua signer sudah APPROVED.',
                    'doc_final',
                    $request->currentUnit
                );
            }

            $startIndex = (int) $pendingSignoff->order_index;
            $request->current_signer_order_index = $startIndex;
            $request->resume_signer_order_index = null;
            $request->save();

            $this->audit->log('doc.signing.started', 'requests', (string) $request->id, [
                'current_signer_order_index' => $startIndex,
            ]);

            $req = $this->workflow->transition($request, RequestStatus::IN_SIGNING, $actor, 'Signing dimulai', 'doc_signing', $request->currentUnit);

            $firstSigner = $req->service->signers->firstWhere('order_index', $startIndex);
            $firstSignoff = $req->signoffs?->firstWhere('order_index', $startIndex);
            if ($firstSignoff?->signer_user_id) {
                $target = \App\Models\User::query()->whereKey((int) $firstSignoff->signer_user_id)->get();
                Notification::send($target, new \App\Notifications\DocumentSignerStepAssigned(
                    $req,
                    (string) ($firstSigner?->role ?: $firstSignoff->signer_role),
                    $startIndex
                ));
            } elseif ($firstSigner) {
                try {
                    $targets = \App\Models\User::query()->role($firstSigner->role)->get();
                    Notification::send($targets, new \App\Notifications\DocumentSignerStepAssigned($req, $firstSigner->role, $startIndex));
                } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
                    // Role not seeded yet; skip notifying.
                }
            }

            return $req;
        });
    }
}
