<?php

namespace App\Services\Documents;

use App\Enums\AttachmentKind;
use App\Enums\AttachmentVerifiedStatus;
use App\Enums\RequestSignoffStatus;
use App\Models\Attachment;
use App\Models\Request as UltRequest;
use App\Models\RequestData;
use App\Models\RequestSignoff;
use App\Models\Service;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\Uploads\UniqueUploadNamer;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class CertificateDocumentService
{
    private const SIGNER_TYPE_INTERNAL = 'INTERNAL';
    private const SIGNER_TYPE_PEMOHON = 'PEMOHON';
    private const SIGNER_TYPE_CUSTOM = 'CUSTOM';

    private const SIGNER_ROLE_INTERNAL = 'CERT_INTERNAL';
    private const SIGNER_ROLE_PEMOHON = 'CERT_PEMOHON';
    private const SIGNER_ROLE_CUSTOM = 'CERT_CUSTOM';

    private const TOKEN_REQUIRED_GLOBAL = [
        'nomor_surat',
        'tanggal_ttd',
    ];

    private const TOKEN_REQUIRED_PER_SIGNER_PREFIX = [
        'ttd',
        'nama_penandatangan',
        'id_penandatangan',
    ];

    private const TOKEN_OPTIONAL_PER_SIGNER_PREFIX = [
        'jabatan_penandatangan',
    ];

    private const TOKEN_OPTIONAL_GLOBAL = [
        'nama_penerima',
    ];

    private const TOKEN_DISALLOWED = [
        'kota_ttd',
        'tanggal_surat',
    ];

    /**
     * Role pool untuk dropdown "User dosen" pada editor sertifikat/piagam.
     *
     * @var array<int,string>
     */
    private const INTERNAL_SIGNER_SELECTABLE_ROLES = [
        'DEKAN',
        'WD_AKADEMIK',
        'WD_UMUM',
        'WD_KEMAHASISWAAN',
        'KAJUR',
        'KAPRODI',
        'SEKJUR',
        'Dosen',
        'DOSEN',
    ];

    public function __construct(
        private readonly UniqueUploadNamer $uploadNamer,
        private readonly AuditLogger $audit,
    ) {}

    public function isCertificateService(?Service $service): bool
    {
        return $service?->usesRequestPptxSource() ?? false;
    }

    public function isCertificateRequest(UltRequest $request): bool
    {
        $request->loadMissing('service');
        return $this->isCertificateService($request->service);
    }

    /**
     * @return \Illuminate\Support\Collection<int,\App\Models\User>
     */
    public function internalSignerOptions(): \Illuminate\Support\Collection
    {
        return User::query()
            ->with(['unit:id,name', 'roles:id,name'])
            ->whereHas('roles', fn ($r) => $r->whereIn('name', self::INTERNAL_SIGNER_SELECTABLE_ROLES))
            ->get()
            ->sortBy(fn (User $user) => $user->signerHierarchySortKey())
            ->values()
            ->map(function (User $user) {
                $user->setAttribute('identity_number', $this->resolveUserIdentityNumber($user));
                return $user;
            });
    }

    /**
     * Persist certificate/piagam source + signer chain.
     *
     * @param array<string,mixed> $dataJson
     * @param array<string,mixed> $attachmentsJson
     */
    public function persistSubmissionData(
        UltRequest $request,
        User $actor,
        HttpRequest $http,
        array $dataJson = [],
        array $attachmentsJson = [],
        bool $isRevision = false
    ): void {
        $request->loadMissing(['service', 'data', 'signoffs', 'attachments']);
        if (!$this->isCertificateService($request->service)) {
            return;
        }

        $existingData = is_array($request->data?->data_json) ? $request->data->data_json : [];
        $existingCertificate = is_array(data_get($existingData, 'certificate')) ? data_get($existingData, 'certificate') : [];
        $existingSourcePath = trim((string) data_get($existingCertificate, 'source_stored_path', ''));
        $hasExistingSource = $existingSourcePath !== '';

        $sourceRequired = !$isRevision || !$hasExistingSource;

        $validator = Validator::make($http->all(), [
            'certificate_activity_title' => ['required', 'string', 'max:190'],
            'certificate_source_pptx' => [$sourceRequired ? 'required' : 'nullable', 'file', 'mimes:pptx', 'max:20480'],
            'certificate_signers' => ['required', 'array', 'min:1'],
            'certificate_signers.*.type' => ['required', 'in:internal,pemohon,custom'],
            'certificate_signers.*.internal_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'certificate_signers.*.name' => ['nullable', 'string', 'max:190'],
            'certificate_signers.*.id_number' => ['nullable', 'string', 'max:120'],
            'certificate_signers.*.jabatan' => ['nullable', 'string', 'max:190'],
            'certificate_signatures' => ['nullable', 'array'],
            'certificate_signatures.*' => ['nullable', 'file', 'mimetypes:image/png,image/jpeg,image/webp', 'max:1024'],
        ]);

        $validator->after(function ($v) use ($http, $request, $actor, $isRevision) {
            $activityTitle = trim((string) $http->input('certificate_activity_title', ''));
            if ($activityTitle === '') {
                $v->errors()->add('certificate_activity_title', 'Judul kegiatan wajib diisi.');
            }

            $rawSigners = $http->input('certificate_signers', []);
            if (!is_array($rawSigners) || empty($rawSigners)) {
                return;
            }

            foreach ($rawSigners as $idx => $row) {
                if (!is_array($row)) {
                    $v->errors()->add("certificate_signers.$idx", 'Format signer tidak valid.');
                    continue;
                }

                $type = strtoupper(trim((string) ($row['type'] ?? '')));
                if (!in_array($type, [self::SIGNER_TYPE_INTERNAL, self::SIGNER_TYPE_PEMOHON, self::SIGNER_TYPE_CUSTOM], true)) {
                    $v->errors()->add("certificate_signers.$idx.type", 'Tipe signer tidak valid.');
                    continue;
                }

                if ($type === self::SIGNER_TYPE_INTERNAL) {
                    $uid = (int) ($row['internal_user_id'] ?? 0);
                    $candidate = null;
                    if ($uid < 1) {
                        $v->errors()->add("certificate_signers.$idx.internal_user_id", 'Pilih user internal untuk signer ini.');
                    } else {
                        $candidate = User::query()
                            ->whereKey($uid)
                            ->whereHas('roles', fn ($r) => $r->whereIn('name', self::INTERNAL_SIGNER_SELECTABLE_ROLES))
                            ->first();
                        if (!$candidate) {
                            $v->errors()->add("certificate_signers.$idx.internal_user_id", 'User tidak termasuk lingkup dosen signer (Dekan/Wakil Dekan/Kajur/Sekjur/Kaprodi/Dosen).');
                        }
                    }

                    $idNumber = trim((string) ($row['id_number'] ?? ''));
                    if ($idNumber === '' && $this->resolveUserIdentityNumber($candidate) === '') {
                        $v->errors()->add("certificate_signers.$idx.id_number", 'ID penandatangan internal belum tersedia. Isi manual pada kolom ID.');
                    }
                }

                if ($type === self::SIGNER_TYPE_PEMOHON) {
                    $idNumber = trim((string) ($row['id_number'] ?? ''));
                    if ($idNumber === '' && $this->resolveUserIdentityNumber($actor) === '') {
                        $v->errors()->add("certificate_signers.$idx.id_number", 'ID pemohon belum tersedia. Isi manual pada kolom ID.');
                    }
                }

                if ($type === self::SIGNER_TYPE_CUSTOM) {
                    if (trim((string) ($row['name'] ?? '')) === '') {
                        $v->errors()->add("certificate_signers.$idx.name", 'Nama penandatangan custom wajib diisi.');
                    }
                    if (trim((string) ($row['id_number'] ?? '')) === '') {
                        $v->errors()->add("certificate_signers.$idx.id_number", 'ID penandatangan custom wajib diisi.');
                    }
                }

                if (in_array($type, [self::SIGNER_TYPE_PEMOHON, self::SIGNER_TYPE_CUSTOM], true)) {
                    $uploaded = $http->file("certificate_signatures.$idx");
                    $existing = null;
                    if ($isRevision) {
                        $existing = $request->signoffs
                            ->firstWhere('order_index', (int) $idx + 1);
                    }

                    $existingPath = trim((string) ($existing?->signature_file_path ?? ''));
                    if (!$uploaded instanceof UploadedFile && $existingPath === '') {
                        $v->errors()->add("certificate_signatures.$idx", 'File tanda tangan wajib untuk signer ini.');
                    }
                }
            }
        });

        $payload = $validator->validate();

        DB::transaction(function () use ($request, $actor, $http, $payload, $dataJson, $attachmentsJson, $existingCertificate) {
            $request->loadMissing(['signoffs', 'data']);
            $activityTitle = trim((string) ($payload['certificate_activity_title'] ?? ''));

            $disk = (string) config('ult.private_disk');

            $sourceFile = $http->file('certificate_source_pptx');
            $sourcePath = trim((string) data_get($existingCertificate, 'source_stored_path', ''));
            $sourceAttachmentId = (int) data_get($existingCertificate, 'source_attachment_id', 0);
            $sourceOriginalName = trim((string) data_get($existingCertificate, 'source_original_name', ''));

            if ($sourceFile instanceof UploadedFile) {
                $sourcePath = $this->uploadNamer->makePathForUploadedFile(
                    $disk,
                    "requests/{$request->id}/input",
                    'certificate_source_pptx',
                    $sourceFile,
                    'pptx'
                );
                $stream = fopen($sourceFile->getRealPath(), 'rb');
                Storage::disk($disk)->put($sourcePath, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }

                $sha = hash_file('sha256', $sourceFile->getRealPath());
                $attachment = $request->attachments()->create([
                    'uploaded_by' => $actor->id,
                    'kind' => AttachmentKind::input,
                    'service_field_id' => null,
                    'original_name' => $sourceFile->getClientOriginalName(),
                    'stored_path' => $sourcePath,
                    'mime' => (string) ($sourceFile->getMimeType() ?: 'application/vnd.openxmlformats-officedocument.presentationml.presentation'),
                    'size' => (int) ($sourceFile->getSize() ?: 0),
                    'sha256' => $sha,
                    'verified_status' => AttachmentVerifiedStatus::pending,
                ]);

                $sourceAttachmentId = (int) $attachment->id;
                $sourceOriginalName = (string) $attachment->original_name;
            }

            if ($sourcePath === '') {
                throw ValidationException::withMessages([
                    'certificate_source_pptx' => ['Dokumen sumber .pptx belum tersedia.'],
                ]);
            }

            $normalizedSigners = [];
            $signoffRows = [];
            $rawSigners = is_array($payload['certificate_signers'] ?? null) ? array_values($payload['certificate_signers']) : [];

            foreach ($rawSigners as $idx => $row) {
                if (!is_array($row)) {
                    continue;
                }

                $order = $idx + 1;
                $type = strtoupper(trim((string) ($row['type'] ?? '')));
                if ($type === '') {
                    continue;
                }

                $signerUserId = null;
                $name = '';
                $idNumber = trim((string) ($row['id_number'] ?? ''));
                $jabatan = trim((string) ($row['jabatan'] ?? ''));
                $signaturePath = null;
                $role = match ($type) {
                    self::SIGNER_TYPE_INTERNAL => self::SIGNER_ROLE_INTERNAL,
                    self::SIGNER_TYPE_PEMOHON => self::SIGNER_ROLE_PEMOHON,
                    self::SIGNER_TYPE_CUSTOM => self::SIGNER_ROLE_CUSTOM,
                    default => self::SIGNER_ROLE_INTERNAL,
                };

                if ($type === self::SIGNER_TYPE_INTERNAL) {
                    $signerUserId = (int) ($row['internal_user_id'] ?? 0);
                    $internalUser = User::query()
                        ->whereKey($signerUserId)
                        ->whereHas('roles', fn ($r) => $r->whereIn('name', self::INTERNAL_SIGNER_SELECTABLE_ROLES))
                        ->first();
                    if (!$internalUser) {
                        throw ValidationException::withMessages([
                            "certificate_signers.$idx.internal_user_id" => ['User tidak termasuk lingkup dosen signer (Dekan/Wakil Dekan/Kajur/Sekjur/Kaprodi/Dosen).'],
                        ]);
                    }
                    $name = trim((string) ($row['name'] ?? ($internalUser?->name ?? '')));
                    if ($name === '') {
                        $name = trim((string) ($internalUser?->name ?? ''));
                    }
                    if ($idNumber === '') {
                        $idNumber = $this->resolveUserIdentityNumber($internalUser);
                    }
                    if ($jabatan === '') {
                        $jabatan = trim((string) ($internalUser?->jabatan ?? ''));
                    }
                } elseif ($type === self::SIGNER_TYPE_PEMOHON) {
                    $signerUserId = (int) $actor->id;
                    $name = trim((string) ($row['name'] ?? $actor->name));
                    if ($name === '') {
                        $name = trim((string) $actor->name);
                    }
                    if ($idNumber === '') {
                        $idNumber = $this->resolveUserIdentityNumber($actor);
                    }
                    if ($jabatan === '') {
                        $jabatan = trim((string) ($actor->jabatan ?? ''));
                    }
                } else {
                    $name = trim((string) ($row['name'] ?? ''));
                }

                if (in_array($type, [self::SIGNER_TYPE_PEMOHON, self::SIGNER_TYPE_CUSTOM], true)) {
                    $uploadedSignature = $http->file("certificate_signatures.$idx");
                    if ($uploadedSignature instanceof UploadedFile) {
                        $signaturePath = $this->storeCertificateSignatureFile(
                            $request,
                            $uploadedSignature,
                            $type === self::SIGNER_TYPE_CUSTOM ? "custom_{$order}" : "pemohon_{$order}"
                        );
                    } else {
                        $existingSignoff = $request->signoffs->firstWhere('order_index', $order);
                        $existingPath = trim((string) ($existingSignoff?->signature_file_path ?? ''));
                        if ($existingPath !== '') {
                            $signaturePath = $existingPath;
                        }
                    }
                }

                if (trim($name) === '' || trim($idNumber) === '') {
                    throw ValidationException::withMessages([
                        "certificate_signers.$idx.id_number" => ['Nama/ID penandatangan belum lengkap.'],
                    ]);
                }

                $normalizedSigners[] = [
                    'order_index' => $order,
                    'signer_type' => $type,
                    'signer_user_id' => $signerUserId > 0 ? $signerUserId : null,
                    'name' => $name,
                    'id_number' => $idNumber,
                    'jabatan' => $jabatan !== '' ? $jabatan : null,
                ];

                $isPrefilled = in_array($type, [self::SIGNER_TYPE_PEMOHON, self::SIGNER_TYPE_CUSTOM], true);
                $signoffRows[] = [
                    'request_id' => $request->id,
                    'signer_role' => $role,
                    'signer_user_id' => $signerUserId > 0 ? $signerUserId : null,
                    'order_index' => $order,
                    'is_required' => true,
                    'status' => $isPrefilled ? RequestSignoffStatus::APPROVED : RequestSignoffStatus::PENDING,
                    'decided_by' => $isPrefilled ? $actor->id : null,
                    'decided_at' => $isPrefilled ? now() : null,
                    'note' => $this->buildCertificateSignoffNote($type, $name, $idNumber, $jabatan),
                    'signature_file_path' => $signaturePath,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (empty($signoffRows)) {
                throw ValidationException::withMessages([
                    'certificate_signers' => ['Daftar penandatangan belum valid.'],
                ]);
            }

            RequestSignoff::query()->where('request_id', $request->id)->delete();
            RequestSignoff::insert($signoffRows);

            $request->forceFill([
                'activity_title' => $activityTitle !== '' ? $activityTitle : null,
            ])->save();

            $dataJson['certificate'] = [
                'activity_title' => $activityTitle,
                'source_attachment_id' => $sourceAttachmentId > 0 ? $sourceAttachmentId : null,
                'source_stored_path' => $sourcePath,
                'source_original_name' => $sourceOriginalName,
                'source_uploaded_at' => now()->toIso8601String(),
                'signers' => $normalizedSigners,
            ];
            $attachmentsJson['certificate_source_pptx'] = $sourceAttachmentId > 0 ? $sourceAttachmentId : null;

            RequestData::updateOrCreate(
                ['request_id' => $request->id],
                [
                    'data_json' => $dataJson,
                    'attachments_json' => $attachmentsJson,
                ]
            );

            $this->audit->log('doc.certificate.request_payload_saved', 'requests', (string) $request->id, [
                'request_id' => $request->id,
                'activity_title' => $activityTitle,
                'source_path' => $sourcePath,
                'signer_count' => count($normalizedSigners),
                'prefilled_signer_count' => count(array_filter($normalizedSigners, fn ($s) => in_array($s['signer_type'], [self::SIGNER_TYPE_CUSTOM, self::SIGNER_TYPE_PEMOHON], true))),
            ]);
        });
    }

    /**
     * @return array{
     *   is_certificate:bool,
     *   source_attachment:?Attachment,
     *   source_original_name:?string,
     *   signers:array<int,array<string,mixed>>
     * }
     */
    public function editorState(UltRequest $request): array
    {
        $request->loadMissing(['service', 'data', 'attachments', 'signoffs']);

        if (!$this->isCertificateRequest($request)) {
            return [
                'is_certificate' => false,
                'source_attachment' => null,
                'source_original_name' => null,
                'signers' => [],
            ];
        }

        $certificate = $this->certificateData($request);
        $sourceAttachmentId = (int) data_get($certificate, 'source_attachment_id', 0);
        $sourceAttachment = $sourceAttachmentId > 0 ? $request->attachments->firstWhere('id', $sourceAttachmentId) : null;

        $signers = $this->certificateSigners($request);
        $signaturePreviewByOrder = [];
        foreach ($request->signoffs as $signoff) {
            $order = (int) ($signoff->order_index ?? 0);
            if ($order < 1) {
                continue;
            }

            $role = strtoupper(trim((string) ($signoff->signer_role ?? '')));
            if (!in_array($role, [self::SIGNER_ROLE_PEMOHON, self::SIGNER_ROLE_CUSTOM], true)) {
                continue;
            }

            $path = trim((string) ($signoff->signature_file_path ?? ''));
            if ($path === '') {
                continue;
            }

            $signaturePreviewByOrder[$order] = route('student.requests.signature.preview', [
                'request' => $request,
                'signoff' => $signoff,
            ]);
        }

        $signers = array_map(function (array $row) use ($signaturePreviewByOrder): array {
            $type = strtoupper(trim((string) ($row['signer_type'] ?? '')));
            $order = (int) ($row['order_index'] ?? 0);
            $previewUrl = '';
            if ($order > 0 && in_array($type, [self::SIGNER_TYPE_PEMOHON, self::SIGNER_TYPE_CUSTOM], true)) {
                $previewUrl = (string) ($signaturePreviewByOrder[$order] ?? '');
            }

            $row['signature_preview_url'] = $previewUrl;
            return $row;
        }, $signers);

        return [
            'is_certificate' => true,
            'activity_title' => data_get($certificate, 'activity_title', $request->activity_title),
            'source_attachment' => $sourceAttachment,
            'source_original_name' => data_get($certificate, 'source_original_name'),
            'signers' => $signers,
        ];
    }

    /**
     * Validate mandatory placeholder contract for certificate/piagam source.
     *
     * @return array<int,string> list of validation messages
     */
    public function validateRequiredPlaceholders(UltRequest $request): array
    {
        $request->loadMissing(['service', 'data', 'signoffs']);
        if (!$this->isCertificateRequest($request)) {
            return [];
        }

        $source = $this->certificateSourcePath($request);
        if ($source === '') {
            return ['Dokumen sumber .pptx belum tersedia.'];
        }

        $tokens = $this->extractPlaceholderTokensFromStoredPptx((string) config('ult.private_disk'), $source);
        $set = array_fill_keys($tokens, true);
        $errors = [];

        foreach (self::TOKEN_REQUIRED_GLOBAL as $token) {
            if (!isset($set[$token])) {
                $errors[] = 'Token wajib {{'.$token.'}} belum ada di template.';
            }
        }

        foreach (self::TOKEN_DISALLOWED as $token) {
            if (isset($set[$token])) {
                $errors[] = 'Token {{'.$token.'}} tidak dipakai untuk layanan Sertifikat/Piagam.';
            }
        }

        $signerCount = count($this->certificateSigners($request));
        if ($signerCount < 1) {
            $signerCount = (int) $request->signoffs->count();
        }

        $expected = $signerCount > 0 ? range(1, $signerCount) : [];
        foreach (self::TOKEN_REQUIRED_PER_SIGNER_PREFIX as $prefix) {
            $actual = $this->collectTokenIndexes($tokens, $prefix);
            $missing = array_values(array_diff($expected, $actual));
            $extra = array_values(array_diff($actual, $expected));
            if (!empty($missing)) {
                $errors[] = 'Token wajib {{'.$prefix.'_i}} belum lengkap. Kurang indeks: '.implode(', ', $missing).'.';
            }
            if (!empty($extra)) {
                $errors[] = 'Token {{'.$prefix.'_i}} melebihi jumlah signer yang dipilih. Indeks berlebih: '.implode(', ', $extra).'.';
            }
        }

        return $errors;
    }

    /**
     * Build rendered PDF for certificate request.
     *
     * @return array{pdf_tmp_path:string,unresolved:array<int,string>}
     */
    public function buildRenderedPdf(UltRequest $request, bool $strict = true): array
    {
        $request->loadMissing(['service', 'data', 'signoffs', 'signoffs.signerUser']);
        if (!$this->isCertificateRequest($request)) {
            throw new \RuntimeException('Request ini bukan mode Sertifikat/Piagam.');
        }

        $sourcePath = $this->certificateSourcePath($request);
        if ($sourcePath === '') {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Dokumen sumber .pptx belum tersedia.');
        }

        $disk = (string) config('ult.private_disk');
        if (!Storage::disk($disk)->exists($sourcePath)) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Dokumen sumber .pptx tidak ditemukan di storage.');
        }

        $values = $this->buildTextValueMap($request);
        $signatureMap = $this->buildSignatureTokenMap($request);

        $renderedPptx = null;
        try {
            $renderedPptx = $this->renderPptxTemplate($disk, $sourcePath, $values, $signatureMap);
            $unresolved = $this->scanUnresolvedPlaceholdersFromPptx($renderedPptx);

            if ($strict && !empty($unresolved)) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(
                    422,
                    'Masih ada placeholder belum terisi: '.implode(', ', $unresolved)
                );
            }

            $pdfTmp = $this->convertPptxToPdf($renderedPptx);
            if (!$pdfTmp) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(
                    422,
                    'Konversi PPTX ke PDF gagal. Pastikan LibreOffice (soffice) atau Microsoft PowerPoint tersedia di server.'
                );
            }

            return [
                'pdf_tmp_path' => $pdfTmp,
                'unresolved' => $unresolved,
            ];
        } finally {
            if ($renderedPptx) {
                @unlink($renderedPptx);
            }
        }
    }

    /**
     * Build rendered preview artifact for certificate request.
     * Returns PDF when converter is available; otherwise returns rendered PPTX.
     *
     * @return array{
     *   path:string,
     *   mime:string,
     *   filename:string,
     *   unresolved:array<int,string>
     * }
     */
    public function buildRenderedPreviewArtifact(UltRequest $request): array
    {
        $request->loadMissing(['service', 'data', 'signoffs', 'signoffs.signerUser']);
        if (!$this->isCertificateRequest($request)) {
            throw new \RuntimeException('Request ini bukan mode Sertifikat/Piagam.');
        }

        $sourcePath = $this->certificateSourcePath($request);
        if ($sourcePath === '') {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Dokumen sumber .pptx belum tersedia.');
        }

        $disk = (string) config('ult.private_disk');
        if (!Storage::disk($disk)->exists($sourcePath)) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Dokumen sumber .pptx tidak ditemukan di storage.');
        }

        $values = $this->buildTextValueMap($request);
        $signatureMap = $this->buildSignatureTokenMap($request);

        $renderedPptx = null;
        try {
            $renderedPptx = $this->renderPptxTemplate($disk, $sourcePath, $values, $signatureMap);
            $unresolved = $this->scanUnresolvedPlaceholdersFromPptx($renderedPptx);

            $pdfTmp = $this->convertPptxToPdf($renderedPptx);
            if (is_string($pdfTmp) && $pdfTmp !== '' && is_file($pdfTmp)) {
                @unlink($renderedPptx);
                return [
                    'path' => $pdfTmp,
                    'mime' => 'application/pdf',
                    'filename' => "preview-request-{$request->id}.pdf",
                    'unresolved' => $unresolved,
                ];
            }

            return [
                'path' => $renderedPptx,
                'mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'filename' => "preview-request-{$request->id}.pptx",
                'unresolved' => $unresolved,
            ];
        } catch (\Throwable $e) {
            if ($renderedPptx && is_file($renderedPptx)) {
                @unlink($renderedPptx);
            }
            throw $e;
        }
    }

    /**
     * Build preview artifact directly from uploaded source .pptx (pre-submit).
     * Preview wajib PDF; jika konversi gagal akan lempar error 422.
     *
     * @return array{
     *   path:string,
     *   mime:string,
     *   filename:string
     * }
     */
    public function buildUploadedSourcePreviewArtifact(UploadedFile $sourceFile): array
    {
        $tmpDir = storage_path('app/tmp/pptx');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }

        $ext = strtolower((string) ($sourceFile->getClientOriginalExtension() ?: 'pptx'));
        if ($ext !== 'pptx') {
            $ext = 'pptx';
        }

        $tmpSource = $tmpDir.'/'.uniqid('cert_src_preview_', true).'.'.$ext;
        $stream = fopen($sourceFile->getRealPath(), 'rb');
        if (!is_resource($stream)) {
            throw new \RuntimeException('File sumber .pptx tidak dapat dibaca.');
        }
        file_put_contents($tmpSource, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        $pdfTmp = $this->convertPptxToPdf($tmpSource);
        if (is_string($pdfTmp) && $pdfTmp !== '' && is_file($pdfTmp)) {
            @unlink($tmpSource);
            return [
                'path' => $pdfTmp,
                'mime' => 'application/pdf',
                'filename' => 'preview-sertifikat.pdf',
            ];
        }

        @unlink($tmpSource);
        throw new \Symfony\Component\HttpKernel\Exception\HttpException(
            422,
            'Preview PDF belum tersedia. Konversi PPTX ke PDF gagal di server. Pastikan LibreOffice (soffice) aktif untuk user web.'
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function certificateSigners(UltRequest $request): array
    {
        $request->loadMissing(['data']);
        $certificate = $this->certificateData($request);
        $rows = data_get($certificate, 'signers');
        if (!is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $out[] = [
                'order_index' => (int) ($row['order_index'] ?? 0),
                'signer_type' => strtoupper(trim((string) ($row['signer_type'] ?? ''))),
                'signer_user_id' => isset($row['signer_user_id']) && is_numeric($row['signer_user_id']) ? (int) $row['signer_user_id'] : null,
                'name' => trim((string) ($row['name'] ?? '')),
                'id_number' => trim((string) ($row['id_number'] ?? '')),
                'jabatan' => trim((string) ($row['jabatan'] ?? '')),
            ];
        }

        usort($out, fn ($a, $b) => ((int) $a['order_index']) <=> ((int) $b['order_index']));
        return $out;
    }

    private function certificateData(UltRequest $request): array
    {
        $data = is_array($request->data?->data_json) ? $request->data->data_json : [];
        $certificate = data_get($data, 'certificate');
        return is_array($certificate) ? $certificate : [];
    }

    private function certificateSourcePath(UltRequest $request): string
    {
        $certificate = $this->certificateData($request);
        $path = trim((string) data_get($certificate, 'source_stored_path', ''));
        if ($path !== '') {
            return $path;
        }

        $sourceAttachmentId = (int) data_get($certificate, 'source_attachment_id', 0);
        if ($sourceAttachmentId > 0) {
            $request->loadMissing('attachments');
            $attachment = $request->attachments->firstWhere('id', $sourceAttachmentId);
            $storedPath = trim((string) ($attachment?->stored_path ?? ''));
            if ($storedPath !== '') {
                return $storedPath;
            }
        }

        return '';
    }

    /**
     * @return array<string,string>
     */
    private function buildTextValueMap(UltRequest $request): array
    {
        $request->loadMissing(['signoffs', 'signoffs.signerUser', 'data']);
        $signers = $this->certificateSigners($request);

        $requiredSignoffs = $request->signoffs
            ->filter(fn ($s) => (bool) $s->is_required)
            ->values();

        $hasAllRequiredApproved = $requiredSignoffs->isNotEmpty()
            && $requiredSignoffs->every(
                fn ($s) => $s->status === RequestSignoffStatus::APPROVED && $s->decided_at
            );

        $lastSignerAt = null;
        if ($hasAllRequiredApproved) {
            // Use the most recent approval timestamp (chronological), not signer order.
            $lastSignerAt = $requiredSignoffs
                ->sortByDesc(function ($s) {
                    $at = $s->decided_at;
                    if ($at instanceof Carbon) {
                        return $at->getTimestamp();
                    }

                    $ts = strtotime((string) $at);
                    return $ts === false ? 0 : $ts;
                })
                ->first()
                ?->decided_at;
        }

        $values = [
            'nomor_surat' => (string) ($request->nomor_surat ?? ''),
            'tanggal_ttd' => $lastSignerAt instanceof Carbon
                ? DateFormatter::formatDateToDoc($lastSignerAt->toDateString(), 'id')
                : '',
            'nama_penerima' => trim((string) data_get($request->data?->data_json, 'nama_penerima', '')),
        ];

        foreach ($signers as $row) {
            $i = (int) ($row['order_index'] ?? 0);
            if ($i < 1) {
                continue;
            }

            $values["ttd_{$i}"] = '';
            $values["nama_penandatangan_{$i}"] = (string) ($row['name'] ?? '');
            $values["id_penandatangan_{$i}"] = (string) ($row['id_number'] ?? '');
            $values["jabatan_penandatangan_{$i}"] = (string) ($row['jabatan'] ?? '');
        }

        return $values;
    }

    /**
     * @return array<string,array{stored_path:string,name:string}>
     */
    private function buildSignatureTokenMap(UltRequest $request): array
    {
        $request->loadMissing(['signoffs']);
        $out = [];
        foreach ($request->signoffs as $signoff) {
            $idx = (int) ($signoff->order_index ?? 0);
            $path = trim((string) ($signoff->signature_file_path ?? ''));
            if ($idx < 1 || $path === '') {
                continue;
            }

            $token = "ttd_{$idx}";
            $out[$token] = [
                'stored_path' => $path,
                'name' => "signature_{$token}",
            ];
        }

        return $out;
    }

    /**
     * @param array<string,string> $values
     * @param array<string,array{stored_path:string,name:string}> $signatureMap
     */
    private function renderPptxTemplate(string $disk, string $storedSourcePath, array $values, array $signatureMap): string
    {
        $tmpDir = storage_path('app/tmp/pptx');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }

        $src = $tmpDir.'/'.uniqid('cert_src_', true).'.pptx';
        $out = $tmpDir.'/'.uniqid('cert_out_', true).'.pptx';

        $stream = Storage::disk($disk)->readStream($storedSourcePath);
        if (!is_resource($stream)) {
            throw new \RuntimeException('Dokumen sumber PPTX tidak dapat dibaca.');
        }

        $fp = fopen($src, 'wb');
        if (!is_resource($fp)) {
            if (is_resource($stream)) {
                fclose($stream);
            }
            throw new \RuntimeException('Gagal menyiapkan file sementara PPTX.');
        }
        stream_copy_to_stream($stream, $fp);
        if (is_resource($stream)) {
            fclose($stream);
        }
        if (is_resource($fp)) {
            fclose($fp);
        }

        $workDir = $tmpDir.'/'.uniqid('cert_unz_', true);
        @mkdir($workDir, 0775, true);

        $zip = new ZipArchive();
        if ($zip->open($src) !== true) {
            @unlink($src);
            $this->rrmdir($workDir);
            throw new \RuntimeException('File sumber PPTX tidak valid.');
        }
        $zip->extractTo($workDir);
        $zip->close();
        @unlink($src);

        $slides = glob($workDir.'/ppt/slides/slide*.xml') ?: [];
        sort($slides);

        $mediaDir = $workDir.'/ppt/media';
        if (!is_dir($mediaDir)) {
            @mkdir($mediaDir, 0775, true);
        }

        $mediaCache = [];
        foreach ($slides as $slidePath) {
            $relsPath = preg_replace('#/slides/(slide\d+)\.xml$#', '/slides/_rels/$1.xml.rels', $slidePath);
            if (!is_string($relsPath)) {
                continue;
            }
            $this->processSlideXml($slidePath, $relsPath, $values, $signatureMap, $disk, $mediaDir, $mediaCache);
        }

        $this->syncPptMediaContentTypes($workDir);

        $outZip = new ZipArchive();
        if ($outZip->open($out, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->rrmdir($workDir);
            throw new \RuntimeException('Gagal membuat output PPTX.');
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($workDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($files as $f) {
            /** @var \SplFileInfo $f */
            if (!$f->isFile()) {
                continue;
            }
            $path = $f->getRealPath();
            if (!$path) {
                continue;
            }
            $workDirReal = realpath($workDir) ?: $workDir;
            $local = str_replace('\\', '/', substr($path, strlen($workDirReal) + 1));
            $outZip->addFile($path, $local);
        }
        $outZip->close();

        $this->rrmdir($workDir);

        return $out;
    }

    /**
     * @param array<string,string> $textValues
     * @param array<string,array{stored_path:string,name:string}> $signatureMap
     * @param array<string,string> $mediaCache
     */
    private function processSlideXml(
        string $slidePath,
        string $relsPath,
        array $textValues,
        array $signatureMap,
        string $disk,
        string $mediaDir,
        array &$mediaCache
    ): void {
        $xml = file_get_contents($slidePath);
        if (!is_string($xml) || trim($xml) === '') {
            return;
        }

        $slideDom = new \DOMDocument('1.0', 'UTF-8');
        $slideDom->preserveWhiteSpace = false;
        $slideDom->formatOutput = false;
        if (@$slideDom->loadXML($xml) === false) {
            return;
        }

        $slideXpath = new \DOMXPath($slideDom);
        $slideXpath->registerNamespace('p', 'http://schemas.openxmlformats.org/presentationml/2006/main');
        $slideXpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
        $slideXpath->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');

        $relsDom = $this->loadOrCreateRelsDom($relsPath);
        $relsXpath = new \DOMXPath($relsDom);
        $relsXpath->registerNamespace('rel', 'http://schemas.openxmlformats.org/package/2006/relationships');
        $nextRid = $this->nextRelationshipId($relsXpath);
        $nextShapeId = $this->nextSlideShapeId($slideXpath);

        /** @var \DOMNodeList<\DOMElement> $shapes */
        $shapes = $slideXpath->query('//p:sp');
        if ($shapes !== false && !empty($signatureMap)) {
            foreach ($shapes as $shape) {
                $shapeText = $this->extractShapeText($shape, $slideXpath);
                if ($shapeText === '') {
                    continue;
                }

                $matchedToken = null;
                foreach ($signatureMap as $token => $img) {
                    if (stripos($shapeText, '{{'.$token.'}}') !== false) {
                        $matchedToken = $token;
                        break;
                    }
                }
                if ($matchedToken === null) {
                    continue;
                }

                $img = $signatureMap[$matchedToken];
                $storedPath = trim((string) ($img['stored_path'] ?? ''));
                if ($storedPath === '') {
                    continue;
                }

                $mediaName = $this->copySignatureToPptMedia($disk, $storedPath, $mediaDir, $mediaCache, $matchedToken);
                if ($mediaName === null) {
                    continue;
                }

                $relId = 'rId'.$nextRid++;
                $this->appendImageRelationship($relsDom, $relId, $mediaName);
                $picNode = $this->buildSlidePictureNode(
                    $slideDom,
                    $shape,
                    $relId,
                    $nextShapeId++,
                    'Signature '.strtoupper($matchedToken),
                    null
                );

                $parent = $shape->parentNode;
                if ($parent) {
                    $parent->insertBefore($picNode, $shape->nextSibling);
                }

                $this->replaceTokenWithinShapeText($shape, $matchedToken);
            }
        }

        $this->replaceTextPlaceholdersInSlide($slideXpath, $textValues);

        file_put_contents($slidePath, $slideDom->saveXML());
        file_put_contents($relsPath, $relsDom->saveXML());
    }

    /**
     * Replace placeholder tokens on paragraph scope so split runs like
     * "{{" + "nomor_surat" + "}}" are still resolved.
     *
     * @param array<string,string> $textValues
     */
    private function replaceTextPlaceholdersInSlide(\DOMXPath $slideXpath, array $textValues): void
    {
        $paragraphs = $slideXpath->query('//a:p');
        if ($paragraphs === false) {
            return;
        }

        foreach ($paragraphs as $paragraph) {
            if (!($paragraph instanceof \DOMElement)) {
                continue;
            }

            $textNodes = $slideXpath->query('.//a:t', $paragraph);
            if ($textNodes === false || $textNodes->length === 0) {
                continue;
            }

            $joined = '';
            foreach ($textNodes as $textNode) {
                $joined .= (string) $textNode->textContent;
            }

            if ($joined === '' || !str_contains($joined, '{{')) {
                continue;
            }

            $replaced = $this->replacePlaceholderTokens($joined, $textValues);
            if ($replaced === $joined) {
                continue;
            }

            $first = $textNodes->item(0);
            if (!$first) {
                continue;
            }

            $first->textContent = $replaced;
            for ($i = 1; $i < $textNodes->length; $i++) {
                $n = $textNodes->item($i);
                if ($n) {
                    $n->textContent = '';
                }
            }
        }
    }

    private function replaceTokenWithinShapeText(\DOMElement $shape, string $token): void
    {
        $shapeXpath = new \DOMXPath($shape->ownerDocument);
        $shapeXpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
        $texts = $shapeXpath->query('.//a:t', $shape);
        if ($texts === false) {
            return;
        }

        foreach ($texts as $node) {
            $raw = (string) $node->textContent;
            if ($raw === '') {
                continue;
            }
            $node->textContent = preg_replace(
                '/\{\{\s*'.preg_quote($token, '/').'\s*\}\}/i',
                '',
                $raw
            ) ?? $raw;
        }
    }

    private function replacePlaceholderTokens(string $text, array $values): string
    {
        if (!str_contains($text, '{{')) {
            return $text;
        }

        return preg_replace_callback('/\{\{\s*([A-Za-z0-9_]+)\s*\}\}/', function ($m) use ($values) {
            $token = strtolower((string) ($m[1] ?? ''));
            if ($token === '' || !array_key_exists($token, $values)) {
                return $m[0];
            }
            return (string) $values[$token];
        }, $text) ?? $text;
    }

    private function extractShapeText(\DOMElement $shape, \DOMXPath $xpath): string
    {
        $texts = $xpath->query('.//a:t', $shape);
        if ($texts === false) {
            return '';
        }

        $parts = [];
        foreach ($texts as $node) {
            $val = (string) $node->textContent;
            if ($val !== '') {
                $parts[] = $val;
            }
        }

        return implode('', $parts);
    }

    private function appendImageRelationship(\DOMDocument $relsDom, string $relId, string $mediaFileName): void
    {
        $root = $relsDom->documentElement;
        if (!$root) {
            $root = $relsDom->createElementNS('http://schemas.openxmlformats.org/package/2006/relationships', 'Relationships');
            $relsDom->appendChild($root);
        }

        $rel = $relsDom->createElementNS('http://schemas.openxmlformats.org/package/2006/relationships', 'Relationship');
        $rel->setAttribute('Id', $relId);
        $rel->setAttribute('Type', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/image');
        $rel->setAttribute('Target', '../media/'.$mediaFileName);
        $root->appendChild($rel);
    }

    private function buildSlidePictureNode(
        \DOMDocument $dom,
        \DOMElement $shape,
        string $relId,
        int $shapeId,
        string $name,
        ?array $xfrmOverride = null
    ): \DOMElement {
        $pic = $dom->createElementNS('http://schemas.openxmlformats.org/presentationml/2006/main', 'p:pic');

        $nvPicPr = $dom->createElementNS('http://schemas.openxmlformats.org/presentationml/2006/main', 'p:nvPicPr');
        $cNvPr = $dom->createElementNS('http://schemas.openxmlformats.org/presentationml/2006/main', 'p:cNvPr');
        $cNvPr->setAttribute('id', (string) max(1, $shapeId));
        $cNvPr->setAttribute('name', $name);
        $cNvPicPr = $dom->createElementNS('http://schemas.openxmlformats.org/presentationml/2006/main', 'p:cNvPicPr');
        $picLocks = $dom->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'a:picLocks');
        $picLocks->setAttribute('noChangeAspect', '1');
        $cNvPicPr->appendChild($picLocks);
        $nvPr = $dom->createElementNS('http://schemas.openxmlformats.org/presentationml/2006/main', 'p:nvPr');
        $nvPicPr->appendChild($cNvPr);
        $nvPicPr->appendChild($cNvPicPr);
        $nvPicPr->appendChild($nvPr);
        $pic->appendChild($nvPicPr);

        $blipFill = $dom->createElementNS('http://schemas.openxmlformats.org/presentationml/2006/main', 'p:blipFill');
        $blip = $dom->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'a:blip');
        $blip->setAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'r:embed', $relId);
        $stretch = $dom->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'a:stretch');
        $fillRect = $dom->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'a:fillRect');
        $stretch->appendChild($fillRect);
        $blipFill->appendChild($blip);
        $blipFill->appendChild($stretch);
        $pic->appendChild($blipFill);

        $spPr = $dom->createElementNS('http://schemas.openxmlformats.org/presentationml/2006/main', 'p:spPr');
        $shapeXpath = new \DOMXPath($dom);
        $shapeXpath->registerNamespace('p', 'http://schemas.openxmlformats.org/presentationml/2006/main');
        $shapeXpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
        $shapeXfrm = $shapeXpath->query('./p:spPr/a:xfrm', $shape)?->item(0);
        if (is_array($xfrmOverride) && isset($xfrmOverride['x'], $xfrmOverride['y'], $xfrmOverride['cx'], $xfrmOverride['cy'])) {
            $xfrm = $dom->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'a:xfrm');
            $off = $dom->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'a:off');
            $off->setAttribute('x', (string) ((int) $xfrmOverride['x']));
            $off->setAttribute('y', (string) ((int) $xfrmOverride['y']));
            $ext = $dom->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'a:ext');
            $ext->setAttribute('cx', (string) max(1, (int) $xfrmOverride['cx']));
            $ext->setAttribute('cy', (string) max(1, (int) $xfrmOverride['cy']));
            $xfrm->appendChild($off);
            $xfrm->appendChild($ext);
            $spPr->appendChild($xfrm);
        } elseif ($shapeXfrm instanceof \DOMElement) {
            $spPr->appendChild($dom->importNode($shapeXfrm, true));
        } else {
            $xfrm = $dom->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'a:xfrm');
            $off = $dom->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'a:off');
            $off->setAttribute('x', '0');
            $off->setAttribute('y', '0');
            $ext = $dom->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'a:ext');
            $ext->setAttribute('cx', (string) (120 * 12700));
            $ext->setAttribute('cy', (string) (42 * 12700));
            $xfrm->appendChild($off);
            $xfrm->appendChild($ext);
            $spPr->appendChild($xfrm);
        }

        $prst = $dom->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'a:prstGeom');
        $prst->setAttribute('prst', 'rect');
        $prst->appendChild($dom->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'a:avLst'));
        $spPr->appendChild($prst);
        $pic->appendChild($spPr);

        return $pic;
    }

    /**
     * Resolve signature picture transform from shape frame:
     * fixed height from shape, width follows image ratio.
     *
     * @return array{x:int,y:int,cx:int,cy:int}
     */
    private function resolveSignaturePictureTransform(\DOMElement $shape, string $imagePath): array
    {
        [$offX, $offY] = $this->resolveShapeFrameOffset($shape);
        [$frameWEmu, $frameHEmu] = $this->resolveShapeFrameExtent($shape);

        $targetH = max(1, $frameHEmu);
        $targetW = max(1, $frameWEmu);

        $dim = @getimagesize($imagePath);
        $imgW = (int) ($dim[0] ?? 0);
        $imgH = (int) ($dim[1] ?? 0);
        if ($imgW > 0 && $imgH > 0) {
            $ratio = $imgW / $imgH;
            if (is_finite($ratio) && $ratio > 0) {
                $targetW = max(1, (int) round($targetH * $ratio));
            }
        }

        $targetX = $offX;
        if ($targetW !== $frameWEmu) {
            $targetX = (int) round($offX + (($frameWEmu - $targetW) / 2.0));
        }

        return [
            'x' => $targetX,
            'y' => $offY,
            'cx' => $targetW,
            'cy' => $targetH,
        ];
    }

    /**
     * @return array{0:int,1:int} [xEmu, yEmu]
     */
    private function resolveShapeFrameOffset(\DOMElement $shape): array
    {
        $dom = $shape->ownerDocument;
        if (!$dom) {
            return [0, 0];
        }

        $shapeXpath = new \DOMXPath($dom);
        $shapeXpath->registerNamespace('p', 'http://schemas.openxmlformats.org/presentationml/2006/main');
        $shapeXpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');

        $off = $shapeXpath->query('./p:spPr/a:xfrm/a:off', $shape)?->item(0);
        if (!($off instanceof \DOMElement)) {
            return [0, 0];
        }

        return [
            (int) $off->getAttribute('x'),
            (int) $off->getAttribute('y'),
        ];
    }

    /**
     * @return array{0:int,1:int} [wEmu, hEmu]
     */
    private function resolveShapeFrameExtent(\DOMElement $shape): array
    {
        $defaultW = (int) (120 * 12700);
        $defaultH = (int) (42 * 12700);

        $dom = $shape->ownerDocument;
        if (!$dom) {
            return [$defaultW, $defaultH];
        }

        $shapeXpath = new \DOMXPath($dom);
        $shapeXpath->registerNamespace('p', 'http://schemas.openxmlformats.org/presentationml/2006/main');
        $shapeXpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');

        $ext = $shapeXpath->query('./p:spPr/a:xfrm/a:ext', $shape)?->item(0);
        if (!($ext instanceof \DOMElement)) {
            return [$defaultW, $defaultH];
        }

        $cx = (int) $ext->getAttribute('cx');
        $cy = (int) $ext->getAttribute('cy');
        return [
            $cx > 0 ? $cx : $defaultW,
            $cy > 0 ? $cy : $defaultH,
        ];
    }

    private function nextRelationshipId(\DOMXPath $relsXpath): int
    {
        $max = 0;
        $nodes = $relsXpath->query('//rel:Relationship');
        if ($nodes === false) {
            return 1;
        }

        foreach ($nodes as $node) {
            if (!$node instanceof \DOMElement) {
                continue;
            }
            $id = (string) $node->getAttribute('Id');
            if (preg_match('/^rId(\d+)$/i', $id, $m) === 1) {
                $num = (int) ($m[1] ?? 0);
                if ($num > $max) {
                    $max = $num;
                }
            }
        }

        return $max + 1;
    }

    private function nextSlideShapeId(\DOMXPath $slideXpath): int
    {
        $max = 0;
        $nodes = $slideXpath->query('//p:cNvPr');
        if ($nodes === false) {
            return 1;
        }

        foreach ($nodes as $node) {
            if (!$node instanceof \DOMElement) {
                continue;
            }
            $num = (int) $node->getAttribute('id');
            if ($num > $max) {
                $max = $num;
            }
        }

        return $max + 1;
    }

    private function loadOrCreateRelsDom(string $relsPath): \DOMDocument
    {
        $relsDom = new \DOMDocument('1.0', 'UTF-8');
        $relsDom->preserveWhiteSpace = false;
        $relsDom->formatOutput = false;

        if (is_file($relsPath)) {
            $raw = file_get_contents($relsPath);
            if (is_string($raw) && trim($raw) !== '' && @$relsDom->loadXML($raw)) {
                return $relsDom;
            }
        }

        $rootDir = dirname($relsPath);
        if (!is_dir($rootDir)) {
            @mkdir($rootDir, 0775, true);
        }
        $root = $relsDom->createElementNS('http://schemas.openxmlformats.org/package/2006/relationships', 'Relationships');
        $relsDom->appendChild($root);
        return $relsDom;
    }

    /**
     * @param array<string,string> $mediaCache
     */
    private function copySignatureToPptMedia(
        string $disk,
        string $storedPath,
        string $mediaDir,
        array &$mediaCache,
        string $token
    ): ?string {
        if (isset($mediaCache[$storedPath])) {
            return $mediaCache[$storedPath];
        }

        if (!Storage::disk($disk)->exists($storedPath)) {
            return null;
        }

        $ext = strtolower(pathinfo($storedPath, PATHINFO_EXTENSION));
        if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
            $ext = 'png';
        }
        $filename = 'sig_'.preg_replace('/[^a-z0-9_]+/i', '_', strtolower($token)).'_'.uniqid('', true).'.'.$ext;
        $local = rtrim($mediaDir, '/\\').DIRECTORY_SEPARATOR.$filename;

        $stream = Storage::disk($disk)->readStream($storedPath);
        if (!is_resource($stream)) {
            return null;
        }

        $fp = fopen($local, 'wb');
        if (!is_resource($fp)) {
            if (is_resource($stream)) {
                fclose($stream);
            }
            return null;
        }

        stream_copy_to_stream($stream, $fp);
        if (is_resource($stream)) {
            fclose($stream);
        }
        if (is_resource($fp)) {
            fclose($fp);
        }

        $mediaCache[$storedPath] = $filename;
        return $filename;
    }

    private function syncPptMediaContentTypes(string $workDir): void
    {
        $ctPath = $workDir.'/[Content_Types].xml';
        if (!is_file($ctPath)) {
            return;
        }

        $raw = file_get_contents($ctPath);
        if (!is_string($raw) || trim($raw) === '') {
            return;
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        if (@$dom->loadXML($raw) === false) {
            return;
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('ct', 'http://schemas.openxmlformats.org/package/2006/content-types');
        $defaults = [];
        $defaultNodes = $xpath->query('/ct:Types/ct:Default');
        if ($defaultNodes !== false) {
            foreach ($defaultNodes as $node) {
                if (!$node instanceof \DOMElement) {
                    continue;
                }
                $ext = strtolower((string) $node->getAttribute('Extension'));
                if ($ext !== '') {
                    $defaults[$ext] = true;
                }
            }
        }

        $needed = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
        ];

        $root = $dom->documentElement;
        if (!$root) {
            return;
        }

        foreach ($needed as $ext => $mime) {
            if (isset($defaults[$ext])) {
                continue;
            }
            $el = $dom->createElementNS('http://schemas.openxmlformats.org/package/2006/content-types', 'Default');
            $el->setAttribute('Extension', $ext);
            $el->setAttribute('ContentType', $mime);
            $root->appendChild($el);
        }

        file_put_contents($ctPath, $dom->saveXML());
    }

    /**
     * @return array<int,string>
     */
    private function extractPlaceholderTokensFromStoredPptx(string $disk, string $storedPath): array
    {
        $tmpDir = storage_path('app/tmp/pptx');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }

        $tmpFile = $tmpDir.'/'.uniqid('pptx_tokens_', true).'.pptx';
        $stream = Storage::disk($disk)->readStream($storedPath);
        if (!is_resource($stream)) {
            throw new \RuntimeException('File PPTX tidak dapat dibaca.');
        }

        $fp = fopen($tmpFile, 'wb');
        if (!is_resource($fp)) {
            if (is_resource($stream)) {
                fclose($stream);
            }
            throw new \RuntimeException('Gagal membuat file sementara PPTX.');
        }
        stream_copy_to_stream($stream, $fp);
        if (is_resource($stream)) {
            fclose($stream);
        }
        if (is_resource($fp)) {
            fclose($fp);
        }

        try {
            return $this->extractPlaceholderTokensFromPptxFile($tmpFile);
        } finally {
            @unlink($tmpFile);
        }
    }

    /**
     * @return array<int,string>
     */
    private function extractPlaceholderTokensFromPptxFile(string $pptxFile): array
    {
        $zip = new ZipArchive();
        if ($zip->open($pptxFile) !== true) {
            return [];
        }

        $tokens = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!is_string($name)) {
                continue;
            }

            if (!preg_match('#^ppt/(slides/slide\d+|slideMasters/slideMaster\d+|notesSlides/notesSlide\d+)\.xml$#', $name)) {
                continue;
            }

            $xml = $zip->getFromIndex($i);
            if (!is_string($xml) || $xml === '') {
                continue;
            }

            foreach ($this->extractPlaceholderTokensFromSlideXml($xml) as $token) {
                $tokens[$token] = true;
            }
        }

        $zip->close();
        $out = array_keys($tokens);
        sort($out);
        return $out;
    }

    /**
     * Read placeholder tokens from a slide XML body and support split-run
     * placeholders inside the same paragraph (e.g. "{{" + "nomor_surat" + "}}").
     *
     * @return array<int,string>
     */
    private function extractPlaceholderTokensFromSlideXml(string $xml): array
    {
        $tokens = [];
        $this->collectPlaceholderTokensFromText($xml, $tokens);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        if (@$dom->loadXML($xml) === false) {
            $out = array_keys($tokens);
            sort($out);
            return $out;
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');

        $paragraphs = $xpath->query('//a:p');
        if ($paragraphs !== false) {
            foreach ($paragraphs as $paragraph) {
                if (!($paragraph instanceof \DOMElement)) {
                    continue;
                }

                $textNodes = $xpath->query('.//a:t', $paragraph);
                if ($textNodes === false || $textNodes->length < 1) {
                    continue;
                }

                $joined = '';
                foreach ($textNodes as $textNode) {
                    $joined .= (string) $textNode->textContent;
                }

                $this->collectPlaceholderTokensFromText($joined, $tokens);
            }
        }

        $out = array_keys($tokens);
        sort($out);
        return $out;
    }

    /**
     * @param array<string,bool> $tokens
     */
    private function collectPlaceholderTokensFromText(string $text, array &$tokens): void
    {
        if ($text === '' || !str_contains($text, '{{')) {
            return;
        }

        if (!preg_match_all('/\{\{\s*([A-Za-z0-9_]+)\s*\}\}/', $text, $matches)) {
            return;
        }

        foreach (($matches[1] ?? []) as $rawToken) {
            $token = strtolower(trim((string) $rawToken));
            if ($token !== '') {
                $tokens[$token] = true;
            }
        }
    }

    /**
     * @return array<int,string>
     */
    private function scanUnresolvedPlaceholdersFromPptx(string $pptxPath): array
    {
        return $this->extractPlaceholderTokensFromPptxFile($pptxPath);
    }

    /**
     * @param array<int,string> $tokens
     * @return array<int,int>
     */
    private function collectTokenIndexes(array $tokens, string $prefix): array
    {
        $indexes = [];
        foreach ($tokens as $token) {
            if (preg_match('/^'.preg_quote($prefix, '/').'_(\d+)$/', $token, $m) !== 1) {
                continue;
            }
            $idx = (int) ($m[1] ?? 0);
            if ($idx > 0) {
                $indexes[$idx] = true;
            }
        }

        $out = array_keys($indexes);
        sort($out);
        return $out;
    }

    private function resolveUserIdentityNumber(?User $user): string
    {
        if (!$user) {
            return '';
        }

        $candidates = [
            $user->student_number ?? null,
            data_get($user, 'user_number'),
            data_get($user, 'nip'),
            data_get($user, 'employee_number'),
            data_get($user, 'nik'),
        ];

        foreach ($candidates as $value) {
            $str = trim((string) ($value ?? ''));
            if ($str !== '') {
                return $str;
            }
        }

        return '';
    }

    private function buildCertificateSignoffNote(string $type, string $name, string $idNumber, ?string $jabatan): string
    {
        $parts = [
            'Certificate signer',
            'type:'.$type,
            'name:'.$name,
            'id:'.$idNumber,
        ];
        $jabatan = trim((string) ($jabatan ?? ''));
        if ($jabatan !== '') {
            $parts[] = 'jabatan:'.$jabatan;
        }

        return implode(' | ', $parts);
    }

    private function storeCertificateSignatureFile(UltRequest $request, UploadedFile $file, string $suffix): string
    {
        $disk = (string) config('ult.private_disk');
        $mime = (string) ($file->getMimeType() ?? '');
        $ext = match ($mime) {
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            default => strtolower((string) ($file->getClientOriginalExtension() ?: 'png')),
        };

        $path = $this->uploadNamer->makePath(
            $disk,
            "requests/{$request->id}/signatures",
            "signature_certificate_{$suffix}",
            $ext
        );

        $stream = fopen($file->getRealPath(), 'rb');
        Storage::disk($disk)->put($path, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        return $path;
    }

    private function convertPptxToPdf(string $pptxTmpPath): ?string
    {
        $soffice = $this->findSofficeBinary();
        if ($soffice) {
            $pdf = $this->convertPptxToPdfViaSoffice($pptxTmpPath, $soffice);
            if ($pdf) {
                return $pdf;
            }
        }

        return $this->convertPptxToPdfViaPowerPointVbs($pptxTmpPath);
    }

    private function convertPptxToPdfViaSoffice(string $pptxTmpPath, string $soffice): ?string
    {
        $tmpDir = storage_path('app/tmp/pptx');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }

        $expected = $tmpDir.'/'.pathinfo($pptxTmpPath, PATHINFO_FILENAME).'.pdf';
        for ($attempt = 1; $attempt <= 2; $attempt++) {
            @unlink($expected);

            // Isolated LO profile prevents first-run/profile-lock failures on initial request.
            $profileDir = $tmpDir.'/'.uniqid('lo_profile_', true);
            @mkdir($profileDir, 0775, true);

            try {
                $result = Process::timeout(120)->run([
                    $soffice,
                    '--headless',
                    $this->buildLibreOfficeUserInstallationArg($profileDir),
                    '--convert-to',
                    'pdf',
                    '--outdir',
                    $tmpDir,
                    $pptxTmpPath,
                ]);

                if (!$result->successful() || !is_file($expected)) {
                    if ($attempt < 2) {
                        usleep(300000);
                        continue;
                    }
                    return null;
                }

                $tmpPdf = $tmpDir.'/'.uniqid('cert_out_', true).'.pdf';
                if (!@rename($expected, $tmpPdf)) {
                    if (!@copy($expected, $tmpPdf)) {
                        return null;
                    }
                    @unlink($expected);
                }

                return $tmpPdf;
            } finally {
                $this->rrmdir($profileDir);
            }
        }
        return null;
    }

    private function convertPptxToPdfViaPowerPointVbs(string $pptxTmpPath): ?string
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return null;
        }

        $cscript = $this->findCscriptBinary();
        if (!$cscript) {
            return null;
        }

        $tmpDir = storage_path('app/tmp/pptx');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }

        $tmpPdf = $tmpDir.'/'.uniqid('cert_out_', true).'.pdf';
        $tmpVbs = $tmpDir.'/'.uniqid('ppt_export_', true).'.vbs';
        $pptxPathWin = str_replace('/', '\\', $pptxTmpPath);
        $tmpPdfWin = str_replace('/', '\\', $tmpPdf);
        $tmpVbsWin = str_replace('/', '\\', $tmpVbs);
        $vbs = <<<'VBS'
On Error Resume Next
Dim app, pres, inFile, outFile
inFile = WScript.Arguments(0)
outFile = WScript.Arguments(1)

Set app = CreateObject("PowerPoint.Application")
If Err.Number <> 0 Then
  WScript.Quit 2
End If

app.Visible = True
' Open(FileName, ReadOnly, Untitled, WithWindow)
Set pres = app.Presentations.Open(inFile, True, False, False)
If Err.Number <> 0 Then
  app.Quit
  WScript.Quit 3
End If

' ppSaveAsPDF = 32
Call pres.SaveAs(outFile, 32)
If Err.Number <> 0 Then
  pres.Close
  app.Quit
  WScript.Quit 4
End If

pres.Close
app.Quit
WScript.Quit 0
VBS;

        try {
            file_put_contents($tmpVbs, $vbs);
            $result = Process::timeout(180)->run([
                $cscript,
                '//NoLogo',
                $tmpVbsWin,
                $pptxPathWin,
                $tmpPdfWin,
            ]);

            if (!$result->successful() || !is_file($tmpPdf)) {
                Log::warning('doc.certificate.powerpoint_vbs_failed', [
                    'pptx' => $pptxTmpPath,
                    'pdf' => $tmpPdf,
                    'cscript' => $cscript,
                    'exit_code' => $result->exitCode(),
                    'output' => $result->output(),
                    'error_output' => $result->errorOutput(),
                ]);
                return null;
            }

            return $tmpPdf;
        } catch (\Throwable $e) {
            Log::warning('doc.certificate.powerpoint_vbs_exception', [
                'pptx' => $pptxTmpPath,
                'pdf' => $tmpPdf,
                'cscript' => $cscript,
                'error' => $e->getMessage(),
            ]);
            return null;
        } finally {
            @unlink($tmpVbs);
        }
    }

    private function findSofficeBinary(): ?string
    {
        $configured = (string) config('ult.soffice_path', '');
        $candidates = [
            $configured,
            env('ULT_SOFFICE_PATH', ''),
            'soffice',
            'soffice.com',
            'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
            'C:\\Program Files\\LibreOffice\\program\\soffice.com',
            'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
            'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.com',
        ];

        foreach ($candidates as $bin) {
            if (!is_string($bin) || trim($bin) === '') {
                continue;
            }
            try {
                $probe = Process::timeout(10)->run([$bin, '--version']);
                if ($probe->successful()) {
                    return $bin;
                }
            } catch (\Throwable $e) {
                // Continue searching.
            }
        }

        return null;
    }

    private function findCscriptBinary(): ?string
    {
        $candidates = [
            env('COMSPEC') ? dirname((string) env('COMSPEC')).'\\cscript.exe' : '',
            getenv('SystemRoot') ? rtrim((string) getenv('SystemRoot'), '\\/').'\\System32\\cscript.exe' : '',
            'C:\\Windows\\System32\\cscript.exe',
            'cscript',
        ];

        foreach ($candidates as $bin) {
            if (!is_string($bin) || trim($bin) === '') {
                continue;
            }

            try {
                $probe = Process::timeout(10)->run([$bin, '//NoLogo', '//?']);
                if ($probe->successful()) {
                    return $bin;
                }
            } catch (\Throwable $e) {
                // Continue searching.
            }
        }

        return null;
    }

    private function buildLibreOfficeUserInstallationArg(string $profileDir): string
    {
        $normalized = str_replace('\\', '/', $profileDir);
        if (preg_match('/^[a-zA-Z]:\//', $normalized) === 1) {
            $normalized = '/'.$normalized;
        }

        return '-env:UserInstallation=file://'.$normalized;
    }

    private function rrmdir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $it = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
        $ri = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($ri as $f) {
            /** @var \SplFileInfo $f */
            $path = $f->getRealPath();
            if (!$path) {
                continue;
            }
            if ($f->isDir()) {
                @rmdir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}
