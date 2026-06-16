<?php

namespace App\Services\Documents;

use App\Enums\RequestSignoffStatus;
use App\Enums\RequestStatus;
use App\Models\Request as UltRequest;
use App\Models\RequestSignoff;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\RequestWorkflowService;
use App\Services\Uploads\UniqueUploadNamer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class DocumentSignerService
{
    private const CERT_SIGNATURE_TYPES = ['image/png', 'image/jpeg', 'image/webp'];
    private const CERT_SIGNATURE_MAX_KB = 1024;

    public function __construct(
        private readonly RequestWorkflowService $workflow,
        private readonly AuditLogger $audit,
        private readonly UniqueUploadNamer $uploadNamer,
        private readonly CertificateDocumentService $certificateDocs,
    ) {}

    public function decide(UltRequest $request, User $actor, string $decision, ?string $note = null, ?UploadedFile $signatureFile = null): UltRequest
    {
        $decision = strtoupper(trim($decision));
        if (!in_array($decision, ['APPROVE','REVISION','REJECT'], true)) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Invalid decision.');
        }

        return DB::transaction(function () use ($request, $actor, $decision, $note, $signatureFile) {
            /** @var UltRequest $request */
            $request = UltRequest::query()->whereKey($request->id)->lockForUpdate()->firstOrFail();
            $request->loadMissing(['service.signers', 'signoffs', 'currentUnit']);
            $isCertificateRequest = $this->certificateDocs->isCertificateRequest($request);

            if ($request->current_status !== RequestStatus::IN_SIGNING) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Request belum dalam status IN_SIGNING.');
            }

            $currentIndex = (int) ($request->current_signer_order_index ?: 0);
            if ($currentIndex < 1) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'current_signer_order_index tidak valid.');
            }

            $currentSigner = $request->service->signers->firstWhere('order_index', $currentIndex);
            if (!$currentSigner && !$isCertificateRequest) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Signer step tidak ditemukan.');
            }

            /** @var RequestSignoff|null $signoff */
            $signoff = $request->signoffs->firstWhere('order_index', $currentIndex);
            if (!$signoff || $signoff->status !== RequestSignoffStatus::PENDING) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Signoff tidak dalam status PENDING.');
            }

            if ($signoff->signer_user_id) {
                if ((int) $signoff->signer_user_id !== (int) $actor->id) {
                    throw new \Symfony\Component\HttpKernel\Exception\HttpException(403, 'Bukan signer untuk step aktif.');
                }
            } else {
                if ($isCertificateRequest) {
                    throw new \Symfony\Component\HttpKernel\Exception\HttpException(403, 'Bukan signer untuk step aktif.');
                }
                if (!$actor->matchesSignerRole((string) $currentSigner->role)) {
                    throw new \Symfony\Component\HttpKernel\Exception\HttpException(403, 'Bukan signer untuk step aktif.');
                }
            }

            $signaturePath = null;
            $requiresSignatureUpload = $isCertificateRequest
                ? $this->requiresSignatureForCertificateSignoff($signoff)
                : (bool) ($currentSigner?->requires_signature_upload ?? false);
            if ($decision === 'APPROVE' && $requiresSignatureUpload) {
                if (!$signatureFile) {
                    throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'signature_file wajib untuk APPROVE.');
                }

                $types = $isCertificateRequest
                    ? self::CERT_SIGNATURE_TYPES
                    : (is_array($currentSigner?->signature_file_types) ? $currentSigner->signature_file_types : []);
                $maxKb = $isCertificateRequest
                    ? self::CERT_SIGNATURE_MAX_KB
                    : (int) ($currentSigner?->signature_max_size_kb ?: 0);
                if ($maxKb <= 0 || empty($types)) {
                    throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Konfigurasi signature signer tidak valid.');
                }

                $mime = $signatureFile->getMimeType() ?: '';
                if (!in_array($mime, $types, true)) {
                    throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'MIME signature_file tidak diizinkan.');
                }
                if (($signatureFile->getSize() ?: 0) > ($maxKb * 1024)) {
                    throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Ukuran signature_file melebihi batas.');
                }

                $disk = config('ult.private_disk');
                $ext = match ($mime) {
                    'image/png' => 'png',
                    'image/jpeg' => 'jpg',
                    'image/webp' => 'webp',
                    default => strtolower($signatureFile->getClientOriginalExtension() ?: 'bin'),
                };
                $signaturePath = $this->uploadNamer->makePath(
                    $disk,
                    "requests/{$request->id}/signatures",
                    "signature_".($currentSigner?->role ?: $signoff->signer_role),
                    $ext,
                );

                $stream = fopen($signatureFile->getRealPath(), 'rb');
                Storage::disk($disk)->put($signaturePath, $stream);
                if (is_resource($stream)) fclose($stream);
            }

            $toSignoffStatus = match ($decision) {
                'APPROVE' => RequestSignoffStatus::APPROVED,
                'REVISION' => RequestSignoffStatus::REVISION_REQUESTED,
                'REJECT' => RequestSignoffStatus::REJECTED,
            };

            $signoff->status = $toSignoffStatus;
            $signoff->decided_by = $actor->id;
            $signoff->decided_at = now();
            $signoff->note = $note;
            if ($signaturePath) $signoff->signature_file_path = $signaturePath;
            $signoff->save();

            $this->audit->log('doc.signoff.decided', 'request_signoffs', (string) $signoff->id, [
                'request_id' => $request->id,
                'decision' => $decision,
                'order_index' => $currentIndex,
                'signer_role' => $currentSigner?->role ?: $signoff->signer_role,
                'signature_file_path' => $signaturePath,
            ]);

            if ($decision === 'REVISION') {
                $request->resume_signer_order_index = $currentIndex;
                $request->current_signer_order_index = null;
                $request->save();

                return $this->workflow->transition($request, RequestStatus::PERLU_PERBAIKAN, $actor, $note ?: 'Perlu perbaikan', 'doc_signing', $request->currentUnit);
            }

            if ($decision === 'REJECT') {
                $request->current_signer_order_index = null;
                $request->save();
                return $this->workflow->transition($request, RequestStatus::REJECTED_IN_SIGNING, $actor, $note ?: 'Ditolak di signing', 'doc_signing', $request->currentUnit);
            }

            // APPROVE: advance or finish required chain.
            $lastRequiredIndex = $isCertificateRequest
                ? (int) ($request->signoffs->where('is_required', true)->max('order_index') ?: 0)
                : (int) ($request->service->signers->where('is_required', true)->max('order_index') ?: 0);
            if ($lastRequiredIndex > 0 && $currentIndex === $lastRequiredIndex) {
                // Auto-fill tanggal_surat exactly at the moment required signer terakhir approved
                $request->last_required_approved_at = $signoff->decided_at;
                $request->tanggal_surat = $signoff->decided_at->toDateString();
                $request->current_signer_order_index = null;
                $request->save();

                // Auto-skip any remaining OPTIONAL signers after last required signer (must not require signature upload)
                $optionalAfter = $request->signoffs()
                    ->where('order_index', '>', $currentIndex)
                    ->where('is_required', false)
                    ->where('status', RequestSignoffStatus::PENDING)
                    ->get();
                foreach ($optionalAfter as $row) {
                    $row->status = RequestSignoffStatus::APPROVED;
                    $row->note = 'Skipped (optional) after last required signer approved.';
                    $row->decided_at = now();
                    $row->save();
                }

                $this->audit->log('doc.tanggal_surat.autofilled', 'requests', (string) $request->id, [
                    'tanggal_surat' => $request->tanggal_surat,
                    'last_required_approved_at' => (string) $request->last_required_approved_at,
                ]);

                return $this->workflow->transition($request, RequestStatus::READY_FOR_FINAL, $actor, 'Siap untuk final', 'doc_final', $request->currentUnit);
            }

            // Advance to next pending signer step (skip signers already approved, e.g. PEMOHON prefilled on submit).
            $nextSignoff = $request->signoffs
                ->filter(fn ($row) => (int) $row->order_index > $currentIndex)
                ->first(fn ($row) => $row->status === RequestSignoffStatus::PENDING);

            if (!$nextSignoff) {
                // No pending signer left in config: treat current as last effective signer.
                $request->last_required_approved_at = $signoff->decided_at;
                $request->tanggal_surat = $signoff->decided_at->toDateString();
                $request->current_signer_order_index = null;
                $request->save();
                return $this->workflow->transition($request, RequestStatus::READY_FOR_FINAL, $actor, 'Siap untuk final', 'doc_final', $request->currentUnit);
            }

            $nextIndex = (int) $nextSignoff->order_index;
            $next = $request->service->signers->firstWhere('order_index', $nextIndex);
            if (!$next && !$isCertificateRequest) {
                $request->last_required_approved_at = $signoff->decided_at;
                $request->tanggal_surat = $signoff->decided_at->toDateString();
                $request->current_signer_order_index = null;
                $request->save();
                return $this->workflow->transition($request, RequestStatus::READY_FOR_FINAL, $actor, 'Siap untuk final', 'doc_final', $request->currentUnit);
            }

            $request->current_signer_order_index = $nextIndex;
            $request->save();

            // Notify next signer (either specific user or role-based)
            if ($nextSignoff->signer_user_id) {
                $target = \App\Models\User::query()->whereKey((int) $nextSignoff->signer_user_id)->get();
                Notification::send($target, new \App\Notifications\DocumentSignerStepAssigned(
                    $request,
                    (string) ($next?->role ?: $nextSignoff->signer_role),
                    $nextIndex
                ));
            } else {
                if ($isCertificateRequest) {
                    return $request->fresh(['service', 'student', 'currentUnit', 'signoffs']);
                }

                $targets = collect();
                try {
                    $targets = \App\Models\User::query()->role($next->role)->get();
                } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
                    // Skip to loose fallback below.
                }

                if ($targets->isEmpty()) {
                    $targets = \App\Models\User::query()
                        ->with('roles')
                        ->get()
                        ->filter(fn (\App\Models\User $u) => $u->matchesSignerRole((string) $next->role))
                        ->values();
                }

                if ($targets->isNotEmpty()) {
                    Notification::send($targets, new \App\Notifications\DocumentSignerStepAssigned($request, $next->role, $nextIndex));
                }
            }

            return $request->fresh(['service', 'student', 'currentUnit', 'signoffs']);
        });
    }

    private function requiresSignatureForCertificateSignoff(RequestSignoff $signoff): bool
    {
        $role = strtoupper(trim((string) ($signoff->signer_role ?? '')));
        return $role === 'CERT_INTERNAL';
    }
}
