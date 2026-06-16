<?php

namespace App\Services\Documents;

use App\Enums\PlaceholderSourceType;
use App\Enums\RequestSignoffStatus;
use App\Enums\RequestStatus;
use App\Enums\ServiceTemplateType;
use App\Enums\UnitType;
use App\Models\Attachment;
use App\Models\Request as UltRequest;
use App\Models\RequestOutput;
use App\Models\RequestSignaturePlacement;
use App\Models\Service;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\RequestWorkflowService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

use Docx;

class DocumentAssemblerService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly RequestWorkflowService $workflow,
        private readonly CertificateDocumentService $certificateDocs,
    ) {}

    /**
     * @param array<int,array{signer_role:string,page_number:int,x_pt:float,y_pt:float,width_pt:float,height_pt:float}> $placements
     */
    public function preview(UltRequest $request, User $actor, array $placements): array
    {
        // For MVP: preview runs same validation as finalize but does not change request status.
        $result = $this->generate($request, $actor, $placements, finalize: false);
        return $result;
    }

    /**
     * @param array<int,array{signer_role:string,page_number:int,x_pt:float,y_pt:float,width_pt:float,height_pt:float}> $placements
     */
    public function finalize(UltRequest $request, User $actor, array $placements): RequestOutput
    {
        $result = $this->generate($request, $actor, $placements, finalize: true);
        return $result['output'];
    }

    public function ensurePdfOutput(RequestOutput $output): ?RequestOutput
    {
        if (strtoupper((string) $output->output_type) === 'PDF') {
            return $output;
        }

        $disk = config('ult.private_disk');
        $ext = strtolower((string) pathinfo((string) $output->file_path, PATHINFO_EXTENSION));
        if ($ext !== 'docx') {
            return null;
        }

        $tmpDir = storage_path('app/tmp/docx');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }

        $docxTmp = $tmpDir.'/'.uniqid('legacy_docx_', true).'.docx';
        $pdfTmp = null;

        try {
            $stream = Storage::disk($disk)->readStream((string) $output->file_path);
            if (!is_resource($stream)) {
                return null;
            }

            $fp = fopen($docxTmp, 'wb');
            stream_copy_to_stream($stream, $fp);
            if (is_resource($stream)) fclose($stream);
            if (is_resource($fp)) fclose($fp);

            $pdfTmp = $this->convertDocxToPdf($docxTmp);
            if (!$pdfTmp) {
                return null;
            }

            $uuid = (string) Str::uuid();
            $pdfStored = "requests/{$output->request_id}/outputs/{$uuid}.pdf";
            $pdfStream = fopen($pdfTmp, 'rb');
            Storage::disk($disk)->put($pdfStored, $pdfStream);
            if (is_resource($pdfStream)) fclose($pdfStream);

            $pdfOutput = RequestOutput::create([
                'request_id' => $output->request_id,
                'output_type' => 'PDF',
                'file_path' => $pdfStored,
                'original_filename' => $this->buildOutputOriginalFilenameFromOutput($output, 'pdf'),
                'uploaded_by' => $output->uploaded_by,
                'is_private' => true,
                'created_at' => now(),
            ]);

            $this->audit->log('doc.output.pdf_created_from_docx', 'request_outputs', (string) $pdfOutput->id, [
                'request_id' => $output->request_id,
                'source_output_id' => $output->id,
                'path' => $pdfStored,
            ]);

            return $pdfOutput;
        } finally {
            @unlink($docxTmp);
            if ($pdfTmp) @unlink($pdfTmp);
        }
    }

    public function ensurePdfOutputForRequest(UltRequest $request): ?RequestOutput
    {
        $request->loadMissing([
            'service.templates',
            'service.placeholders',
            'service.fields',
            'service.signers',
            'signoffs.decider.unit.parent',
            'signoffs.signerUser.unit.parent',
            'data',
            'currentUnit',
            'student.unit.parent',
            'signaturePlacements',
        ]);

        if ($this->certificateDocs->isCertificateRequest($request)) {
            $latestPdf = $request->outputs()
                ->where('output_type', strtoupper('pdf'))
                ->orderByDesc('id')
                ->first();
            if ($latestPdf) {
                return $latestPdf;
            }

            $rendered = $this->certificateDocs->buildRenderedPdf($request, strict: true);
            $pdfTmp = $rendered['pdf_tmp_path'] ?? null;
            if (!is_string($pdfTmp) || $pdfTmp === '' || !is_file($pdfTmp)) {
                return null;
            }

            $disk = config('ult.private_disk');
            $uuid = (string) Str::uuid();
            $pdfStored = "requests/{$request->id}/outputs/{$uuid}.pdf";
            $stream = fopen($pdfTmp, 'rb');
            Storage::disk($disk)->put($pdfStored, $stream);
            if (is_resource($stream)) fclose($stream);
            @unlink($pdfTmp);

            $output = RequestOutput::create([
                'request_id' => $request->id,
                'output_type' => 'PDF',
                'file_path' => $pdfStored,
                'original_filename' => $this->buildOutputOriginalFilename($request, 'pdf'),
                'uploaded_by' => null,
                'is_private' => true,
                'created_at' => now(),
            ]);

            $this->audit->log('doc.output.pdf_rebuilt_from_certificate_source', 'request_outputs', (string) $output->id, [
                'request_id' => $request->id,
                'path' => $pdfStored,
            ]);

            return $output;
        }

        $latestDocx = $request->outputs()
            ->where('output_type', strtoupper('docx'))
            ->orderByDesc('id')
            ->first();
        if ($latestDocx) {
            return $this->ensurePdfOutput($latestDocx);
        }

        $templatePath = $this->resolveTemplatePathForRequest($request);
        if (!$templatePath) {
            return null;
        }

        $disk = config('ult.private_disk');
        $values = $this->buildPlaceholderValues($request);
        $latestPlacement = $request->signaturePlacements()->orderByDesc('id')->first();
        $placements = is_array($latestPlacement?->placements_json) ? $latestPlacement->placements_json : [];

        $docxTmp = null;
        $pdfTmp = null;
        try {
            $docxTmp = $this->buildDocxFromTemplate($disk, $templatePath, $values, $placements, $request);
            $pdfTmp = $this->convertDocxToPdf($docxTmp);
            if (!$pdfTmp) {
                return null;
            }

            $uuid = (string) Str::uuid();
            $pdfStored = "requests/{$request->id}/outputs/{$uuid}.pdf";
            $stream = fopen($pdfTmp, 'rb');
            Storage::disk($disk)->put($pdfStored, $stream);
            if (is_resource($stream)) fclose($stream);

            $output = RequestOutput::create([
                'request_id' => $request->id,
                'output_type' => 'PDF',
                'file_path' => $pdfStored,
                'original_filename' => $this->buildOutputOriginalFilename($request, 'pdf'),
                'uploaded_by' => null,
                'is_private' => true,
                'created_at' => now(),
            ]);

            $this->audit->log('doc.output.pdf_rebuilt_from_template', 'request_outputs', (string) $output->id, [
                'request_id' => $request->id,
                'path' => $pdfStored,
                'placement_mode' => empty($placements) ? 'auto' : 'manual',
            ]);

            return $output;
        } finally {
            if ($docxTmp) @unlink($docxTmp);
            if ($pdfTmp) @unlink($pdfTmp);
        }
    }

    /**
     * Build a temporary PDF preview from service MAIN_DOCX template.
     *
     * @return array{path:string,mime:string,filename:string}
     */
    public function buildServiceTemplatePreview(Service $service): array
    {
        $service->loadMissing(['templates']);

        $tpl = $service->templates->firstWhere('type', ServiceTemplateType::MAIN_DOCX);
        if (!$tpl) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(404, 'Template dokumen belum tersedia untuk layanan ini.');
        }

        $disk = config('ult.private_disk');
        $tmpDir = storage_path('app/tmp/docx');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }

        $docxTmp = $tmpDir.'/'.uniqid('service_tpl_', true).'.docx';
        $pdfTmp = null;

        try {
            $stream = Storage::disk($disk)->readStream((string) $tpl->file_path);
            if (!is_resource($stream)) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Template dokumen tidak dapat dibaca.');
            }

            $fp = fopen($docxTmp, 'wb');
            if (!is_resource($fp)) {
                if (is_resource($stream)) fclose($stream);
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(500, 'Gagal menyiapkan file preview sementara.');
            }
            stream_copy_to_stream($stream, $fp);
            if (is_resource($stream)) fclose($stream);
            if (is_resource($fp)) fclose($fp);

            $pdfTmp = $this->convertDocxToPdf($docxTmp);
            if (!$pdfTmp) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(
                    422,
                    'Preview PDF gagal dibuat. Pastikan konverter PDF (LibreOffice/soffice) tersedia di server.'
                );
            }

            $slug = Str::slug((string) ($service->slug ?: $service->title_id ?: 'layanan'));

            return [
                'path' => $pdfTmp,
                'mime' => 'application/pdf',
                'filename' => "preview-layanan-{$slug}.pdf",
            ];
        } finally {
            @unlink($docxTmp);
        }
    }

    /**
     * Build a temporary preview file for review in any in-process state.
     *
     * @return array{path:string,mime:string,filename:string,unresolved:array<int,string>}
     */
    public function buildReviewPreview(UltRequest $request, User $actor): array
    {
        $request->loadMissing([
            'service.templates',
            'service.placeholders',
            'service.fields',
            'service.signers',
            'signoffs.decider.unit.parent',
            'signoffs.signerUser.unit.parent',
            'data',
            'currentUnit',
            'student.unit.parent',
        ]);

        if ($this->certificateDocs->isCertificateRequest($request)) {
            $result = $this->certificateDocs->buildRenderedPdf($request, strict: false);
            $path = (string) ($result['pdf_tmp_path'] ?? '');
            $unresolved = is_array($result['unresolved'] ?? null) ? $result['unresolved'] : [];
            if ($path === '' || !is_file($path)) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Preview PDF gagal dibuat.');
            }

            return [
                'path' => $path,
                'mime' => 'application/pdf',
                'filename' => "preview-request-{$request->id}.pdf",
                'unresolved' => $unresolved,
            ];
        }

        $templatePath = $this->resolveTemplatePathForRequest($request);
        if (!$templatePath) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Template dokumen utama belum tersedia untuk layanan ini.');
        }

        $disk = config('ult.private_disk');
        $values = $this->buildPlaceholderValues($request);
        $docxTmp = $this->buildDocxFromTemplate($disk, $templatePath, $values, [], $request);
        $unresolved = $this->scanUnresolvedPlaceholders($docxTmp);

        $mime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        $filename = "preview-request-{$request->id}.docx";
        $outPath = $docxTmp;

        try {
            $pdfTmp = $this->convertDocxToPdf($docxTmp);
            if ($pdfTmp) {
                @unlink($docxTmp);
                $outPath = $pdfTmp;
                $mime = 'application/pdf';
                $filename = "preview-request-{$request->id}.pdf";
            } else {
                $this->audit->log('doc.preview.pdf_fallback_to_docx', 'requests', (string) $request->id, [
                    'actor_id' => $actor->id,
                ]);
            }
        } catch (\Throwable $e) {
            $this->audit->log('doc.preview.pdf_failed', 'requests', (string) $request->id, [
                'error' => $e->getMessage(),
            ]);
        }

        $this->audit->log('doc.preview.generated', 'requests', (string) $request->id, [
            'mime' => $mime,
            'filename' => $filename,
            'unresolved_count' => count($unresolved),
            'actor_id' => $actor->id,
        ]);

        return [
            'path' => $outPath,
            'mime' => $mime,
            'filename' => $filename,
            'unresolved' => $unresolved,
        ];
    }

    /**
     * @return array{output:RequestOutput, unresolved:array<int,string>, docx_path:string, pdf_path:?string}
     */
    private function generate(UltRequest $request, User $actor, array $placements, bool $finalize): array
    {
        return DB::transaction(function () use ($request, $actor, $placements, $finalize) {
            $request->loadMissing([
                'service.templates',
                'service.placeholders',
                'service.fields',
                'service.signers',
                'signoffs.decider.unit.parent',
                'signoffs.signerUser.unit.parent',
                'data',
                'currentUnit',
                'student.unit.parent',
            ]);
            $request->refresh();

            if ($this->certificateDocs->isCertificateRequest($request)) {
                return $this->generateCertificateResult($request, $actor, $finalize);
            }

            if ($request->current_status !== RequestStatus::READY_FOR_FINAL) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Status harus READY_FOR_FINAL untuk assembly.');
            }
            if (empty($request->nomor_surat) || !$request->tanggal_surat) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'NOMOR_SURAT dan TANGGAL_SURAT wajib ada sebelum finalize.');
            }

            $templatePath = $this->resolveTemplatePathForRequest($request);
            if (!$templatePath) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'MAIN_DOCX template tidak ditemukan.');
            }

            // Ensure required signature files exist for configured signers
            foreach ($this->resolveRequestSignerDefinitions($request) as $s) {
                if (!$s->requires_signature_upload) continue;
                $signoff = $request->signoffs->firstWhere('signer_role', $s->role);
                if (!$signoff || $signoff->status !== RequestSignoffStatus::APPROVED || !$signoff->signature_file_path) {
                    throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, "Signature file belum tersedia untuk signer {$s->role}.");
                }
            }

            $placements = $this->validatePlacements($placements, $request);

            // Append-only placement log
            RequestSignaturePlacement::create([
                'request_id' => $request->id,
                'placements_json' => $placements,
                'created_by' => $actor->id,
                'created_at' => now(),
            ]);

            $disk = config('ult.private_disk');

            // Build placeholder values
            $values = $this->buildPlaceholderValues($request);

            // Generate Docx
            $docxTmp = $this->buildDocxFromTemplate($disk, $templatePath, $values, $placements, $request);

            // Ensure no {{...}} left in docx after replacements
            $unresolved = $this->scanUnresolvedPlaceholders($docxTmp);
            if (!empty($unresolved)) {
                @unlink($docxTmp);
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Masih ada placeholder belum terisi: '.implode(', ', $unresolved));
            }

            $uuid = (string) Str::uuid();
            $docxStored = "requests/{$request->id}/outputs/{$uuid}.docx";
            $stream = fopen($docxTmp, 'rb');
            Storage::disk($disk)->put($docxStored, $stream);
            if (is_resource($stream)) fclose($stream);

            $pdfStored = null;
            $pdfTmp = null;

            // Generate PDF; finalize requires PDF output.
            try {
                $pdfTmp = $this->convertDocxToPdf($docxTmp);
                if ($pdfTmp) {
                    $pdfStored = "requests/{$request->id}/outputs/{$uuid}.pdf";
                    $pdfStream = fopen($pdfTmp, 'rb');
                    Storage::disk($disk)->put($pdfStored, $pdfStream);
                    if (is_resource($pdfStream)) fclose($pdfStream);
                }
            } catch (\Throwable $e) {
                // Keep Docx as fallback; audit captures conversion failure.
                $this->audit->log('doc.assemble.pdf_failed', 'requests', (string) $request->id, [
                    'error' => $e->getMessage(),
                ]);
            }

            if ($finalize && !$pdfStored) {
                @unlink($docxTmp);
                if ($pdfTmp) @unlink($pdfTmp);
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(
                    422,
                    'Finalize wajib output PDF. Konversi Docx->PDF gagal. Pastikan LibreOffice (soffice) tersedia di server.'
                );
            }

            @unlink($docxTmp);
            if ($pdfTmp) @unlink($pdfTmp);

            $output = RequestOutput::create([
                'request_id' => $request->id,
                'output_type' => $pdfStored ? 'PDF' : strtoupper('docx'),
                'file_path' => $pdfStored ?: $docxStored,
                'original_filename' => $this->buildOutputOriginalFilename($request, $pdfStored ? 'pdf' : 'docx'),
                'uploaded_by' => $actor->id,
                'is_private' => true,
                'created_at' => now(),
            ]);

            $this->audit->log($finalize ? 'doc.assemble.finalized' : 'doc.assemble.previewed', 'request_outputs', (string) $output->id, [
                'request_id' => $request->id,
                'output_type' => $output->output_type,
                'path' => $output->file_path,
            ]);

            if ($finalize) {
                $this->workflow->transition($request, RequestStatus::SELESAI, $actor, 'Output finalized', 'doc_final', $request->currentUnit);
            }

            return [
                'output' => $output,
                'unresolved' => $unresolved,
                'docx_path' => $docxStored,
                'pdf_path' => $pdfStored,
            ];
        });
    }

    /**
     * @return array{output:RequestOutput, unresolved:array<int,string>, docx_path:string, pdf_path:?string}
     */
    private function generateCertificateResult(UltRequest $request, User $actor, bool $finalize): array
    {
        if ($request->current_status !== RequestStatus::READY_FOR_FINAL) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Status harus READY_FOR_FINAL untuk assembly.');
        }
        if (empty($request->nomor_surat)) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'NOMOR_SURAT wajib ada sebelum finalize.');
        }

        $rendered = $this->certificateDocs->buildRenderedPdf($request, strict: true);
        $unresolved = is_array($rendered['unresolved'] ?? null) ? $rendered['unresolved'] : [];
        $pdfTmp = (string) ($rendered['pdf_tmp_path'] ?? '');
        if ($pdfTmp === '' || !is_file($pdfTmp)) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Gagal menghasilkan PDF Sertifikat/Piagam.');
        }

        $disk = config('ult.private_disk');
        $uuid = (string) Str::uuid();
        $pdfStored = "requests/{$request->id}/outputs/{$uuid}.pdf";
        $stream = fopen($pdfTmp, 'rb');
        Storage::disk($disk)->put($pdfStored, $stream);
        if (is_resource($stream)) fclose($stream);
        @unlink($pdfTmp);

        $output = RequestOutput::create([
            'request_id' => $request->id,
            'output_type' => 'PDF',
            'file_path' => $pdfStored,
            'original_filename' => $this->buildOutputOriginalFilename($request, 'pdf'),
            'uploaded_by' => $actor->id,
            'is_private' => true,
            'created_at' => now(),
        ]);

        $this->audit->log($finalize ? 'doc.assemble.certificate.finalized' : 'doc.assemble.certificate.previewed', 'request_outputs', (string) $output->id, [
            'request_id' => $request->id,
            'output_type' => $output->output_type,
            'path' => $output->file_path,
            'unresolved_count' => count($unresolved),
        ]);

        if ($finalize) {
            $request->loadMissing('signoffs');
            $lastSignerAt = $request->signoffs
                ->where('status', RequestSignoffStatus::APPROVED)
                ->sortBy('order_index')
                ->last()
                ?->decided_at;

            if ($lastSignerAt) {
                $request->last_required_approved_at = $lastSignerAt;
                $request->tanggal_surat = $lastSignerAt->toDateString();
                $request->save();
            }

            $this->workflow->transition($request, RequestStatus::SELESAI, $actor, 'Output finalized', 'doc_final', $request->currentUnit);
        }

        return [
            'output' => $output,
            'unresolved' => $unresolved,
            'docx_path' => '',
            'pdf_path' => $pdfStored,
        ];
    }

    private function validatePlacements(array $placements, UltRequest $request): array
    {
        $out = [];
        foreach ($placements as $p) {
            if (!is_array($p)) continue;
            $role = (string) ($p['signer_role'] ?? '');
            $page = (int) ($p['page_number'] ?? 0);
            $x = (float) ($p['x_pt'] ?? 0);
            $y = (float) ($p['y_pt'] ?? 0);
            $w = (float) ($p['width_pt'] ?? 0);
            $h = (float) ($p['height_pt'] ?? 0);

            if ($role === '' || $page < 1 || $w <= 0 || $h <= 0) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Placement tidak valid.');
            }

            $signoff = $request->signoffs->firstWhere('signer_role', $role);
            if (!$signoff || $signoff->status !== RequestSignoffStatus::APPROVED || !$signoff->signature_file_path) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, "Signature untuk {$role} tidak tersedia.");
            }

            $out[] = [
                'signer_role' => $role,
                'page_number' => $page,
                'x_pt' => $x,
                'y_pt' => $y,
                'width_pt' => $w,
                'height_pt' => $h,
            ];
        }
        return $out;
    }

    private function buildPlaceholderValues(UltRequest $request): array
    {
        $values = [];

        $values['NOMOR_SURAT'] = (string) $request->nomor_surat;
        $values['TANGGAL_SURAT'] = $request->tanggal_surat
            ? DateFormatter::formatDateToDoc($request->tanggal_surat->toDateString(), 'id')
            : DateFormatter::formatDateToDoc(now()->toDateString(), 'id');

        $data = is_array($request->data?->data_json) ? $request->data->data_json : [];
        $fields = $this->resolveRequestFieldDefinitions($request);

        foreach ($this->resolveRequestPlaceholderDefinitions($request) as $ph) {
            $key = $ph->placeholder_key;
            if (isset($values[$key])) continue;

            if ($ph->source_type === PlaceholderSourceType::FORM) {
                $ref = trim((string) ($ph->source_ref ?? ''));
                if ($ref !== '') {
                    $values[$key] = $this->stringifyPlaceholderValue($data[$ref] ?? null);
                    continue;
                }

                $field = $fields->firstWhere('maps_to_placeholder_key', $key);
                if (!$field) continue;
                $values[$key] = $this->stringifyPlaceholderValue($data[$field->key] ?? null);
            } elseif ($ph->source_type === PlaceholderSourceType::PROFILE) {
                $values[$key] = $this->resolveProfileValue($request, (string) $ph->source_ref);
            } elseif ($ph->source_type === PlaceholderSourceType::INTERNAL) {
                if (in_array($key, ['NIP_PENANDATANGAN', 'NAMA_PENANDATANGAN'], true)) {
                    $signerUser = $this->resolveSignatureIdentityUser($request);

                    if ($key === 'NAMA_PENANDATANGAN') {
                        $values[$key] = (string) ($signerUser?->name ?? '');
                    } else {
                        $values[$key] = $this->resolveSignerIdentityNumber($request, $signerUser);
                    }
                } else {
                    // currently only NOMOR_SURAT / signer-derived placeholders supported; others should have been blocked on publish readiness
                    $values[$key] = '';
                }
            } elseif ($ph->source_type === PlaceholderSourceType::SYSTEM_AUTOFILL) {
                // currently only TANGGAL_SURAT supported
                $values[$key] = '';
            }
        }

        return $values;
    }

    private function resolveSignerIdentityNumber(UltRequest $request, ?User $signerUser): string
    {
        $fromUser = function (?User $u): string {
            if (!$u) return '';

            $candidates = [
                $u->student_number ?? null,
                data_get($u, 'user_number'),
                data_get($u, 'nip'),
                data_get($u, 'employee_number'),
                data_get($u, 'nik'),
            ];

            foreach ($candidates as $v) {
                $s = trim((string) ($v ?? ''));
                if ($s !== '') return $s;
            }

            return '';
        };

        $num = $fromUser($signerUser);
        if ($num !== '') {
            return $num;
        }

        $signers = $this->resolveRequestSignerDefinitions($request);
        $targetSigner = $signers
            ->where('is_required', true)
            ->sortBy('order_index')
            ->last()
            ?: $signers->sortBy('order_index')->last();
        $role = strtoupper(trim((string) ($targetSigner?->role ?? '')));

        if ($role !== '') {
            $resolved = trim($this->resolveProfileValue($request, "signer.{$role}.user_number"));
            if ($resolved !== '') {
                return $resolved;
            }

            $fallbackUser = User::role($role)
                ->whereNotNull('student_number')
                ->where('student_number', '!=', '')
                ->orderBy('name')
                ->first();
            $num = $fromUser($fallbackUser);
            if ($num !== '') {
                return $num;
            }
        }

        return '-';
    }

    private function stringifyPlaceholderValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if ($value instanceof \Stringable) {
            return (string) $value;
        }

        if (is_array($value)) {
            // For file/json payloads in data_json, avoid "Array to string conversion".
            // If array is a plain scalar list, join it; otherwise keep it blank.
            $isList = array_is_list($value);
            if ($isList) {
                $parts = [];
                foreach ($value as $item) {
                    if (is_scalar($item) || $item === null) {
                        $parts[] = (string) ($item ?? '');
                    }
                }
                if (!empty($parts) && count($parts) === count($value)) {
                    return implode(', ', $parts);
                }
            }

            if (array_key_exists('value', $value) && (is_scalar($value['value']) || $value['value'] === null)) {
                return (string) ($value['value'] ?? '');
            }

            return '';
        }

        return '';
    }

    private function resolveProfileValue(UltRequest $request, string $ref): string
    {
        if (preg_match('/^signer\\.([A-Z0-9_]+)\\.(.+)$/', $ref, $m) === 1) {
            $role = $m[1];
            $field = $m[2];
            $signoff = $request->signoffs?->firstWhere('signer_role', $role);
            $user = $signoff?->decider ?: $signoff?->signerUser ?: $this->resolveFallbackSignerUser($request, $role);
            if (!$user && $field !== 'jabatan') return '';

            $signerUnit = $user?->unit;
            $signerProdi = $signerUnit?->ancestorOfType(UnitType::prodi);
            $signerJurusan = $signerUnit?->ancestorOfType(UnitType::jurusan);
            $signerFakultas = $signerUnit?->ancestorOfType(UnitType::fakultas);

            if (in_array($field, ['student_number', 'user_number'], true)) {
                $num = trim((string) ($user?->student_number ?? ''));
                if ($num === '') {
                    $roleCandidates = [$role];
                    if ($role === 'DEKAN') {
                        $roleCandidates = array_merge($roleCandidates, ['WD_AKADEMIK', 'WD_UMUM', 'WD_KEMAHASISWAAN']);
                    }

                    foreach ($roleCandidates as $candidateRole) {
                        $fallbackNum = User::role($candidateRole)
                            ->whereNotNull('student_number')
                            ->where('student_number', '!=', '')
                            ->orderBy('name')
                            ->value('student_number');
                        $num = trim((string) ($fallbackNum ?? ''));
                        if ($num !== '') {
                            break;
                        }
                    }
                }
                return $num !== '' ? $num : '-';
            }

            return match ($field) {
                'jabatan' => $this->formatSignerJabatanForTemplate((string) ($user?->jabatan ?: $this->signerRoleTitle($role))),
                'name' => (string) $user->name,
                'email' => (string) $user->email,
                'unit.name', 'unit.prodi.name' => (string) ($signerProdi?->name ?? $signerUnit?->name ?? ''),
                'unit.parent.name', 'unit.jurusan.name' => (string) ($signerJurusan?->name ?? $signerUnit?->parent?->name ?? ''),
                'unit.parent.parent.name', 'unit.fakultas.name' => (string) ($signerFakultas?->name ?? $signerUnit?->parent?->parent?->name ?? ''),
                default => '',
            };
        }

        $user = $request->student;
        if (!$user) return '';
        $unit = $user->unit;
        $prodi = $unit?->ancestorOfType(UnitType::prodi);
        $jurusan = $unit?->ancestorOfType(UnitType::jurusan);
        $fakultas = $unit?->ancestorOfType(UnitType::fakultas);

        return match ($ref) {
            'user.name' => (string) $user->name,
            'user.email' => (string) $user->email,
            'user.student_number', 'user.user_number' => (string) ($user->student_number ?? ''),
            'user.jabatan' => (string) ($user->jabatan ?? ''),
            'unit.name', 'unit.prodi.name' => (string) ($prodi?->name ?? $unit?->name ?? ''),
            'unit.parent.name', 'unit.jurusan.name' => (string) ($jurusan?->name ?? $unit?->parent?->name ?? ''),
            'unit.parent.parent.name', 'unit.fakultas.name' => (string) ($fakultas?->name ?? $unit?->parent?->parent?->name ?? ''),
            default => '',
        };
    }

    private function signerRoleTitle(string $role): string
    {
        $r = strtoupper(trim($role));

        return match ($r) {
            'DEKAN' => 'Dekan',
            'WD_AKADEMIK' => 'Wakil Dekan Bidang Akademik dan Kerja Sama',
            'WD_UMUM' => 'Wakil Dekan Bidang Umum dan Keuangan',
            'WD_KEMAHASISWAAN' => 'Wakil Dekan Bidang Kemahasiswaan dan Alumni',
            'DOSEN' => 'Dosen',
            'KAJUR', 'KAJUR_SCOPE' => 'Ketua Jurusan',
            'SEKJUR', 'SEKJUR_SCOPE' => 'Sekretaris Jurusan',
            'KAPRODI', 'KAPRODI_SCOPE' => 'Ketua Prodi',
            'APPROVER_ULT', 'STAF ULT', 'STAF_ULT', 'STAFF_ULT' => 'Staf ULT',
            'KETUA_ORG' => 'Ketua Organisasi',
            'SEKRETARIS_ORG' => 'Sekretaris Organisasi',
            'PEMOHON' => 'Pemohon',
            'CUSTOM' => 'Penandatangan (Custom)',
            default => "Penandatangan {$r}",
        };
    }

    private function formatSignerJabatanForTemplate(string $jabatan): string
    {
        $jabatan = trim((string) (preg_replace('/\\s+/u', ' ', $jabatan) ?? $jabatan));
        if ($jabatan === '') {
            return '';
        }

        $lines = $this->wrapTextByWords($jabatan, 40);
        if (count($lines) <= 1) {
            return $jabatan;
        }

        // New line keeps same tab stop as the template line.
        return implode("\n\t", $lines);
    }

    /**
     * @return array<int,string>
     */
    private function wrapTextByWords(string $text, int $maxLen): array
    {
        $words = preg_split('/\\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (empty($words) || $maxLen < 1) {
            return [$text];
        }

        $lines = [];
        $line = '';

        foreach ($words as $word) {
            $word = (string) $word;
            if ($line === '') {
                $line = $word;
                continue;
            }

            $candidate = $line.' '.$word;
            if (mb_strlen($candidate, 'UTF-8') <= $maxLen) {
                $line = $candidate;
                continue;
            }

            $lines[] = $line;
            $line = $word;
        }

        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines;
    }

    private function resolveSignatureIdentityUser(UltRequest $request): ?User
    {
        $signers = $this->resolveRequestSignerDefinitions($request);
        $targetSigner = $signers
            ->where('is_required', true)
            ->sortBy('order_index')
            ->last();
        if (!$targetSigner) {
            $targetSigner = $signers->sortBy('order_index')->last();
        }

        if ($targetSigner) {
            $signoff = $request->signoffs->firstWhere('order_index', (int) $targetSigner->order_index)
                ?: $request->signoffs->firstWhere('signer_role', (string) $targetSigner->role);
            $user = $signoff?->decider ?: $signoff?->signerUser;
            if ($user) return $user;

            $fallback = $this->resolveFallbackSignerUser($request, (string) $targetSigner->role);
            if ($fallback) return $fallback;
        }

        $lastApproved = $request->signoffs
            ->where('status', RequestSignoffStatus::APPROVED)
            ->sortBy('order_index')
            ->last();
        if ($lastApproved?->decider) return $lastApproved->decider;
        if ($lastApproved?->signerUser) return $lastApproved->signerUser;

        return null;
    }

    private function resolveFallbackSignerUser(UltRequest $request, string $role): ?User
    {
        $role = strtoupper(trim($role));
        if ($role === '') return null;

        $studentUnit = $request->student?->unit;
        $prodiId = (int) ($studentUnit?->ancestorOfType(UnitType::prodi)?->id ?? 0);
        $jurusanId = (int) ($studentUnit?->ancestorOfType(UnitType::jurusan)?->id ?? 0);
        $fakultasId = (int) ($studentUnit?->ancestorOfType(UnitType::fakultas)?->id ?? 0);

        if ($role === 'KAPRODI_SCOPE') {
            $q = User::query()->role('KAPRODI');
            if ($prodiId > 0) $q->where('unit_id', $prodiId);
            return $q->orderBy('name')->first();
        }

        if ($role === 'KAJUR_SCOPE') {
            $q = User::query()->role('KAJUR');
            if ($jurusanId > 0) $q->where('unit_id', $jurusanId);
            return $q->orderBy('name')->first();
        }

        if ($role === 'SEKJUR_SCOPE') {
            try {
                $roleQuery = User::query()->role('SEKJUR');
                if ($jurusanId > 0) $roleQuery->where('unit_id', $jurusanId);
                $candidate = $roleQuery->orderBy('name')->first();
                if ($candidate) {
                    return $candidate;
                }
            } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
                // Continue with legacy/jabatan fallback.
            }

            $q = User::query()
                ->whereRaw("UPPER(COALESCE(jabatan, '')) LIKE ?", ['%SEKRETARIS JURUSAN%']);
            if ($jurusanId > 0) $q->where('unit_id', $jurusanId);
            $candidate = $q->orderBy('name')->first();
            if ($candidate) {
                return $candidate;
            }

            return null;
        }

        $q = User::query()->role($role);
        if (in_array($role, ['DEKAN', 'WD_AKADEMIK', 'WD_UMUM', 'WD_KEMAHASISWAAN'], true) && $fakultasId > 0) {
            $q->where(function ($qq) use ($fakultasId) {
                $qq->whereNull('unit_id')->orWhere('unit_id', $fakultasId);
            });
        }

        return $q->orderBy('name')->first();
    }

    /**
     * Build a new Docx on disk (temp file path) from stored template + replacements + signature placements.
     */
    private function buildDocxFromTemplate(string $disk, string $storedTemplatePath, array $values, array $placements, UltRequest $request): string
    {
        $tmpDir = storage_path('app/tmp/docx');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }
        $src = $tmpDir.'/'.uniqid('req_tpl_', true).'.docx';
        $out = $tmpDir.'/'.uniqid('req_out_', true).'.docx';

        $stream = Storage::disk($disk)->readStream($storedTemplatePath);
        if (!is_resource($stream)) throw new \RuntimeException('Template not readable.');
        $fp = fopen($src, 'wb');
        stream_copy_to_stream($stream, $fp);
        if (is_resource($stream)) fclose($stream);
        if (is_resource($fp)) fclose($fp);

        $workDir = $tmpDir.'/'.uniqid('unz_', true);
        @mkdir($workDir, 0775, true);

        $zip = new ZipArchive();
        if ($zip->open($src) !== true) {
            throw new \RuntimeException('Invalid Docx zip.');
        }
        $zip->extractTo($workDir);
        $zip->close();

        // Replace photo placeholders first (FORM file upload), so image is injected into the placeholder frame.
        $photoPlaceholders = $this->buildFormImagePlaceholderMap($request);
        if (!empty($photoPlaceholders)) {
            $docXml = $workDir.'/word/document.xml';
            $relsXml = $workDir.'/word/_rels/document.xml.rels';
            $mediaDir = $workDir.'/word/media';
            @mkdir($mediaDir, 0775, true);

            $doc = file_get_contents($docXml);
            $rels = file_get_contents($relsXml);
            if ($doc !== false && $rels !== false) {
                [$doc, $rels] = $this->applyFormImagePlaceholders($doc, $rels, $mediaDir, $photoPlaceholders);
                file_put_contents($docXml, $doc);
                file_put_contents($relsXml, $rels);

                // Ensure text-replacement pass does not leave unresolved marker for image placeholders.
                foreach (array_keys($photoPlaceholders) as $phKey) {
                    $values[$phKey] = '';
                }
            }
        }

        // Strict mode: replace only placeholder tokens in XML text, keep template styling/layout unchanged.
        $this->replacePlaceholdersInWordParts($workDir, $values);

        // Insert signature images into document.xml based on placements.
        // If placements are not provided (e.g. quick preview), fallback to auto anchor near signer name.
        if (!empty($placements)) {
            $docXml = $workDir.'/word/document.xml';
            $relsXml = $workDir.'/word/_rels/document.xml.rels';
            $mediaDir = $workDir.'/word/media';
            @mkdir($mediaDir, 0775, true);

            $doc = file_get_contents($docXml);
            $rels = file_get_contents($relsXml);
            if ($doc === false || $rels === false) {
                throw new \RuntimeException('Docx parts missing (document.xml or rels).');
            }

            [$doc, $rels] = $this->applySignaturePlacements($doc, $rels, $mediaDir, $placements, $request);

            file_put_contents($docXml, $doc);
            file_put_contents($relsXml, $rels);
        } else {
            $docXml = $workDir.'/word/document.xml';
            $relsXml = $workDir.'/word/_rels/document.xml.rels';
            $mediaDir = $workDir.'/word/media';
            @mkdir($mediaDir, 0775, true);

            $doc = file_get_contents($docXml);
            $rels = file_get_contents($relsXml);
            if ($doc !== false && $rels !== false) {
                [$doc, $rels] = $this->applyAutoSignatureByNameAnchor($doc, $rels, $mediaDir, $request, $values);
                file_put_contents($docXml, $doc);
                file_put_contents($relsXml, $rels);
            }
        }

        // Keep package content types in sync with injected media files.
        $this->syncWordMediaContentTypes($workDir);

        // Zip back to output
        $outZip = new ZipArchive();
        if ($outZip->open($out, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Failed to create output docx.');
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($workDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($files as $f) {
            /** @var \SplFileInfo $f */
            $path = $f->getRealPath();
            if (!$path) continue;
            $workDirReal = realpath($workDir) ?: $workDir;
            $localName = str_replace('\\', '/', substr($path, strlen($workDirReal) + 1));
            if ($f->isDir()) {
                $outZip->addEmptyDir($localName);
            } else {
                $outZip->addFile($path, $localName);
            }
        }
        $outZip->close();

        // cleanup
        @unlink($src);
        $this->rrmdir($workDir);

        return $out;
    }

    /**
     * @return array<string,array{stored_path:string,name:string}>
     */
    private function buildFormImagePlaceholderMap(UltRequest $request): array
    {
        $request->loadMissing(['service.placeholders', 'service.fields', 'data', 'attachments']);

        $data = is_array($request->data?->data_json) ? $request->data->data_json : [];
        if (empty($data)) {
            return [];
        }

        $attachmentsById = $request->attachments->keyBy('id');
        $map = [];

        $fields = $this->resolveRequestFieldDefinitions($request);
        foreach ($this->resolveRequestPlaceholderDefinitions($request) as $ph) {
            if ($ph->source_type !== PlaceholderSourceType::FORM) {
                continue;
            }

            $placeholderKey = PlaceholderKeyNormalizer::normalize((string) $ph->placeholder_key);
            if ($placeholderKey === '') {
                continue;
            }

            $ref = trim((string) ($ph->source_ref ?? ''));
            if ($ref === '') {
                $field = $fields->firstWhere('maps_to_placeholder_key', $placeholderKey);
                $ref = (string) ($field?->key ?? '');
            }
            if ($ref === '' || !array_key_exists($ref, $data)) {
                continue;
            }

            $attachmentId = null;
            $raw = $data[$ref];
            if (is_array($raw) && isset($raw['attachment_id']) && is_numeric($raw['attachment_id'])) {
                $attachmentId = (int) $raw['attachment_id'];
            } elseif (is_numeric($raw)) {
                $attachmentId = (int) $raw;
            }
            if (!$attachmentId || $attachmentId < 1) {
                continue;
            }

            /** @var Attachment|null $att */
            $att = $attachmentsById->get($attachmentId);
            if (!$att || (int) $att->request_id !== (int) $request->id) {
                continue;
            }
            if (!str_starts_with((string) ($att->mime ?? ''), 'image/')) {
                continue;
            }
            if (trim((string) ($att->stored_path ?? '')) === '') {
                continue;
            }

            $map[$placeholderKey] = [
                'stored_path' => (string) $att->stored_path,
                'name' => (string) ($att->original_name ?: $placeholderKey),
            ];
        }

        return $map;
    }

    private function replacePlaceholdersInWordParts(string $workDir, array $values): void
    {
        $patterns = [
            $workDir.'/word/document.xml',
            $workDir.'/word/header*.xml',
            $workDir.'/word/footer*.xml',
            $workDir.'/word/footnotes.xml',
            $workDir.'/word/endnotes.xml',
            $workDir.'/word/comments.xml',
        ];

        $files = [];
        foreach ($patterns as $pattern) {
            foreach (glob($pattern) ?: [] as $f) {
                if (is_file($f)) $files[] = $f;
            }
        }

        $files = array_values(array_unique($files));
        if (empty($files)) return;

        foreach ($files as $file) {
            $xml = file_get_contents($file);
            if (!is_string($xml) || $xml === '') continue;
            $replaced = $this->replacePlaceholderTokensInXml($xml, $values);
            if ($replaced !== $xml) {
                file_put_contents($file, $replaced);
            }
        }
    }

    private function replacePlaceholderTokensInXml(string $xml, array $values): string
    {
        if ($xml === '' || empty($values)) return $xml;

        // Replace only placeholder tokens while keeping run/style/spacing structure untouched.
        $normalizedMap = [];
        foreach ($values as $k => $v) {
            $nk = PlaceholderKeyNormalizer::normalize((string) $k);
            if ($nk === '') continue;
            $normalizedMap[$nk] = $this->stringifyPlaceholderValue($v);
        }
        if (empty($normalizedMap)) return $xml;

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = false;
        if (!@$dom->loadXML($xml)) return $xml;

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $paragraphs = $xpath->query('//w:p');
        if (!$paragraphs) return $xml;

        foreach ($paragraphs as $pNode) {
            if (!($pNode instanceof \DOMElement)) continue;

            $textNodes = $xpath->query('.//w:t', $pNode);
            if (!$textNodes || $textNodes->length === 0) continue;

            $segments = [];
            $joined = '';
            foreach ($textNodes as $tn) {
                if (!($tn instanceof \DOMElement)) continue;
                $txt = (string) $tn->textContent;
                $start = strlen($joined);
                $joined .= $txt;
                $segments[] = [
                    'node' => $tn,
                    'start' => $start,
                    'end' => strlen($joined),
                    'text' => $txt,
                ];
            }

            if ($joined === '' || strpos($joined, '{{') === false) continue;

            if (!preg_match_all('/\\{\\{\\s*([^{}]+?)\\s*\\}\\}/u', $joined, $m, PREG_OFFSET_CAPTURE)) continue;

            $fullMatches = $m[0] ?? [];
            $keyMatches = $m[1] ?? [];
            if (empty($fullMatches) || empty($keyMatches)) continue;

            $changed = false;
            for ($i = count($fullMatches) - 1; $i >= 0; $i--) {
                $full = (string) ($fullMatches[$i][0] ?? '');
                $offset = (int) ($fullMatches[$i][1] ?? -1);
                $rawKey = (string) ($keyMatches[$i][0] ?? '');
                if ($full === '' || $offset < 0) continue;

                $key = PlaceholderKeyNormalizer::normalize($rawKey);
                if ($key === '' || !array_key_exists($key, $normalizedMap)) continue;

                $ok = $this->replaceRangeAcrossTextSegments(
                    $segments,
                    $offset,
                    $offset + strlen($full),
                    $normalizedMap[$key]
                );
                if ($ok) $changed = true;
            }

            if (!$changed) continue;

            foreach ($segments as $seg) {
                $node = $seg['node'] ?? null;
                if (!($node instanceof \DOMElement)) continue;

                $txt = (string) ($seg['text'] ?? '');
                $node->nodeValue = $txt;

                if ($this->containsHtml($txt)) {
                    $this->writeHtmlToWordRun($node, $txt);
                } else {
                    $this->writeWordTextNode($node, $txt);
                }
            }
        }

        $out = $dom->saveXML();
        return is_string($out) && $out !== '' ? $out : $xml;
    }

    private function containsHtml(string $string): bool
    {
        return preg_match('/<(?:p|b|strong|i|em|u|br|ul|ol|li)\b[^>]*>/i', $string) === 1;
    }

    private function writeHtmlToWordRun(\DOMElement $tNode, string $html): void
    {
        $originalRun = $tNode->parentNode;
        if (!($originalRun instanceof \DOMElement) || $originalRun->localName !== 'r') {
            $tNode->nodeValue = strip_tags($html);
            return;
        }

        $paragraph = $originalRun->parentNode;
        if (!$paragraph) {
            $tNode->nodeValue = strip_tags($html);
            return;
        }

        $dom = new \DOMDocument();
        \libxml_use_internal_errors(true);
        $htmlFrag = '<div>' . $html . '</div>';
        $dom->loadHTML('<?xml encoding="utf-8"?>' . $htmlFrag, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        \libxml_clear_errors();

        $root = $dom->documentElement;
        if (!$root) {
            $tNode->nodeValue = strip_tags($html);
            return;
        }

        $insertCursor = $originalRun;
        $state = [
            'bold' => false,
            'italic' => false,
            'underline' => false,
            'in_list' => false,
            'list_type' => null,
            'list_index' => 0,
        ];

        $this->traverseHtmlAndInsertRuns($root, $originalRun, $paragraph, $insertCursor, $state);

        $tNode->nodeValue = '';
        $xmlNs = 'http://www.w3.org/XML/1998/namespace';
        if ($tNode->hasAttributeNS($xmlNs, 'space')) {
            $tNode->removeAttributeNS($xmlNs, 'space');
        }
    }

    private function traverseHtmlAndInsertRuns(
        \DOMNode $htmlNode,
        \DOMElement $originalRun,
        \DOMNode $paragraph,
        \DOMNode &$insertCursor,
        array $state
    ): void {
        $ns = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
        
        foreach ($htmlNode->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text = $child->textContent;
                if ($text === '') {
                    continue;
                }

                $newRun = $originalRun->cloneNode(true);
                $xpath = new \DOMXPath($newRun->ownerDocument);
                $xpath->registerNamespace('w', $ns);
                
                $ts = $xpath->query('.//w:t', $newRun);
                foreach ($ts as $t) {
                    $t->parentNode->removeChild($t);
                }
                $brs = $xpath->query('.//w:br', $newRun);
                foreach ($brs as $br) {
                    $br->parentNode->removeChild($br);
                }

                $rPrs = $newRun->getElementsByTagNameNS($ns, 'rPr');
                if ($rPrs->length > 0) {
                    $rPr = $rPrs->item(0);
                } else {
                    $rPr = $newRun->ownerDocument->createElementNS($ns, 'w:rPr');
                    $newRun->insertBefore($rPr, $newRun->firstChild);
                }

                if ($state['bold']) {
                    $bTags = $rPr->getElementsByTagNameNS($ns, 'b');
                    if ($bTags->length === 0) {
                        $rPr->appendChild($newRun->ownerDocument->createElementNS($ns, 'w:b'));
                    }
                }
                if ($state['italic']) {
                    $iTags = $rPr->getElementsByTagNameNS($ns, 'i');
                    if ($iTags->length === 0) {
                        $rPr->appendChild($newRun->ownerDocument->createElementNS($ns, 'w:i'));
                    }
                }
                if ($state['underline']) {
                    $uTags = $rPr->getElementsByTagNameNS($ns, 'u');
                    if ($uTags->length === 0) {
                        $uTag = $newRun->ownerDocument->createElementNS($ns, 'w:u');
                        $uTag->setAttributeNS($ns, 'w:val', 'single');
                        $rPr->appendChild($uTag);
                    }
                }

                $newT = $newRun->ownerDocument->createElementNS($ns, 'w:t');
                $newT->nodeValue = $text;
                $this->applyWordXmlSpace($newT, $text);
                $newRun->appendChild($newT);

                $this->insertWordNodeAfter($insertCursor, $newRun);
                $insertCursor = $newRun;

            } elseif ($child->nodeType === XML_ELEMENT_NODE) {
                $tagName = strtolower($child->nodeName);
                $newState = $state;

                if ($tagName === 'b' || $tagName === 'strong') {
                    $newState['bold'] = true;
                    $this->traverseHtmlAndInsertRuns($child, $originalRun, $paragraph, $insertCursor, $newState);
                } elseif ($tagName === 'i' || $tagName === 'em') {
                    $newState['italic'] = true;
                    $this->traverseHtmlAndInsertRuns($child, $originalRun, $paragraph, $insertCursor, $newState);
                } elseif ($tagName === 'u') {
                    $newState['underline'] = true;
                    $this->traverseHtmlAndInsertRuns($child, $originalRun, $paragraph, $insertCursor, $newState);
                } elseif ($tagName === 'br') {
                    $newRun = $originalRun->cloneNode(true);
                    $xpath = new \DOMXPath($newRun->ownerDocument);
                    $xpath->registerNamespace('w', $ns);
                    $ts = $xpath->query('.//w:t', $newRun);
                    foreach ($ts as $t) { $t->parentNode->removeChild($t); }
                    $brs = $xpath->query('.//w:br', $newRun);
                    foreach ($brs as $br) { $br->parentNode->removeChild($br); }

                    $brElement = $newRun->ownerDocument->createElementNS($ns, 'w:br');
                    $newRun->appendChild($brElement);

                    $this->insertWordNodeAfter($insertCursor, $newRun);
                    $insertCursor = $newRun;
                } elseif ($tagName === 'p' || $tagName === 'div') {
                    if ($insertCursor !== $originalRun) {
                        $newRun = $originalRun->cloneNode(true);
                        $xpath = new \DOMXPath($newRun->ownerDocument);
                        $xpath->registerNamespace('w', $ns);
                        $ts = $xpath->query('.//w:t', $newRun);
                        foreach ($ts as $t) { $t->parentNode->removeChild($t); }
                        $brs = $xpath->query('.//w:br', $newRun);
                        foreach ($brs as $br) { $br->parentNode->removeChild($br); }

                        $brElement = $newRun->ownerDocument->createElementNS($ns, 'w:br');
                        $newRun->appendChild($brElement);
                        $this->insertWordNodeAfter($insertCursor, $newRun);
                        $insertCursor = $newRun;
                    }

                    $this->traverseHtmlAndInsertRuns($child, $originalRun, $paragraph, $insertCursor, $newState);

                    if ($child->nextSibling) {
                        $newRun = $originalRun->cloneNode(true);
                        $xpath = new \DOMXPath($newRun->ownerDocument);
                        $xpath->registerNamespace('w', $ns);
                        $ts = $xpath->query('.//w:t', $newRun);
                        foreach ($ts as $t) { $t->parentNode->removeChild($t); }
                        $brs = $xpath->query('.//w:br', $newRun);
                        foreach ($brs as $br) { $br->parentNode->removeChild($br); }

                        $brElement = $newRun->ownerDocument->createElementNS($ns, 'w:br');
                        $newRun->appendChild($brElement);
                        $this->insertWordNodeAfter($insertCursor, $newRun);
                        $insertCursor = $newRun;
                    }
                } elseif ($tagName === 'ul') {
                    $newState['in_list'] = true;
                    $newState['list_type'] = 'ul';

                    if ($insertCursor !== $originalRun) {
                        $newRun = $originalRun->cloneNode(true);
                        $xpath = new \DOMXPath($newRun->ownerDocument);
                        $xpath->registerNamespace('w', $ns);
                        $ts = $xpath->query('.//w:t', $newRun);
                        foreach ($ts as $t) { $t->parentNode->removeChild($t); }
                        $brs = $xpath->query('.//w:br', $newRun);
                        foreach ($brs as $br) { $br->parentNode->removeChild($br); }

                        $brElement = $newRun->ownerDocument->createElementNS($ns, 'w:br');
                        $newRun->appendChild($brElement);
                        $this->insertWordNodeAfter($insertCursor, $newRun);
                        $insertCursor = $newRun;
                    }

                    $this->traverseHtmlAndInsertRuns($child, $originalRun, $paragraph, $insertCursor, $newState);
                } elseif ($tagName === 'ol') {
                    $newState['in_list'] = true;
                    $newState['list_type'] = 'ol';
                    $newState['list_index'] = 0;

                    if ($insertCursor !== $originalRun) {
                        $newRun = $originalRun->cloneNode(true);
                        $xpath = new \DOMXPath($newRun->ownerDocument);
                        $xpath->registerNamespace('w', $ns);
                        $ts = $xpath->query('.//w:t', $newRun);
                        foreach ($ts as $t) { $t->parentNode->removeChild($t); }
                        $brs = $xpath->query('.//w:br', $newRun);
                        foreach ($brs as $br) { $br->parentNode->removeChild($br); }

                        $brElement = $newRun->ownerDocument->createElementNS($ns, 'w:br');
                        $newRun->appendChild($brElement);
                        $this->insertWordNodeAfter($insertCursor, $newRun);
                        $insertCursor = $newRun;
                    }

                    $this->traverseHtmlAndInsertRuns($child, $originalRun, $paragraph, $insertCursor, $newState);
                } elseif ($tagName === 'li') {
                    $bulletText = '• ';
                    if ($state['list_type'] === 'ol') {
                        $newState['list_index']++;
                        $bulletText = $newState['list_index'] . '. ';
                    }

                    $newRun = $originalRun->cloneNode(true);
                    $xpath = new \DOMXPath($newRun->ownerDocument);
                    $xpath->registerNamespace('w', $ns);
                    $ts = $xpath->query('.//w:t', $newRun);
                    foreach ($ts as $t) { $t->parentNode->removeChild($t); }
                    $brs = $xpath->query('.//w:br', $newRun);
                    foreach ($brs as $br) { $br->parentNode->removeChild($br); }

                    $newT = $newRun->ownerDocument->createElementNS($ns, 'w:t');
                    $newT->nodeValue = $bulletText;
                    $this->applyWordXmlSpace($newT, $bulletText);
                    $newRun->appendChild($newT);

                    $this->insertWordNodeAfter($insertCursor, $newRun);
                    $insertCursor = $newRun;

                    $this->traverseHtmlAndInsertRuns($child, $originalRun, $paragraph, $insertCursor, $newState);

                    if ($child->nextSibling) {
                        $newRun = $originalRun->cloneNode(true);
                        $xpath = new \DOMXPath($newRun->ownerDocument);
                        $xpath->registerNamespace('w', $ns);
                        $ts = $xpath->query('.//w:t', $newRun);
                        foreach ($ts as $t) { $t->parentNode->removeChild($t); }
                        $brs = $xpath->query('.//w:br', $newRun);
                        foreach ($brs as $br) { $br->parentNode->removeChild($br); }

                        $brElement = $newRun->ownerDocument->createElementNS($ns, 'w:br');
                        $newRun->appendChild($brElement);
                        $this->insertWordNodeAfter($insertCursor, $newRun);
                        $insertCursor = $newRun;
                    }
                } else {
                    $this->traverseHtmlAndInsertRuns($child, $originalRun, $paragraph, $insertCursor, $newState);
                }
            }
        }
    }

    private function replaceRangeAcrossTextSegments(array &$segments, int $start, int $end, string $replacement): bool
    {
        if ($start < 0 || $end <= $start) return false;

        $startIdx = null;
        $endIdx = null;

        foreach ($segments as $i => $seg) {
            $segStart = (int) ($seg['start'] ?? 0);
            $segEnd = (int) ($seg['end'] ?? 0);
            if ($startIdx === null && $start >= $segStart && $start < $segEnd) {
                $startIdx = $i;
            }
            if ($endIdx === null && $end > $segStart && $end <= $segEnd) {
                $endIdx = $i;
                break;
            }
        }

        if ($startIdx === null || $endIdx === null) return false;

        $startSegStart = (int) ($segments[$startIdx]['start'] ?? 0);
        $endSegStart = (int) ($segments[$endIdx]['start'] ?? 0);
        $startText = (string) ($segments[$startIdx]['text'] ?? '');
        $endText = (string) ($segments[$endIdx]['text'] ?? '');

        $prefix = substr($startText, 0, max(0, $start - $startSegStart));
        $suffix = substr($endText, max(0, $end - $endSegStart));

        $segments[$startIdx]['text'] = $prefix.$replacement.$suffix;
        for ($j = $startIdx + 1; $j <= $endIdx; $j++) {
            $segments[$j]['text'] = '';
        }

        return true;
    }

    private function writeWordTextNode(\DOMElement $node, string $txt): void
    {
        $run = $node->parentNode;
        if (
            !($run instanceof \DOMElement)
            || $run->localName !== 'r'
            || !preg_match('/[\r\n\t]/', $txt)
        ) {
            $node->nodeValue = $txt;
            $this->applyWordXmlSpace($node, $txt);
            return;
        }

        $ns = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
        $normalized = str_replace(["\r\n", "\r"], "\n", $txt);
        $lines = explode("\n", $normalized);

        $first = array_shift($lines);
        $first = is_string($first) ? $first : '';
        $firstTabs = strspn($first, "\t");
        for ($i = 0; $i < $firstTabs; $i++) {
            $tab = $node->ownerDocument->createElementNS($ns, 'w:tab');
            $run->insertBefore($tab, $node);
        }

        $firstText = substr($first, $firstTabs);
        $node->nodeValue = $firstText;
        $this->applyWordXmlSpace($node, $firstText);
        $cursor = $node;

        foreach ($lines as $line) {
            $br = $node->ownerDocument->createElementNS($ns, 'w:br');
            $this->insertWordNodeAfter($cursor, $br);
            $cursor = $br;

            $tabs = strspn($line, "\t");
            for ($i = 0; $i < $tabs; $i++) {
                $tab = $node->ownerDocument->createElementNS($ns, 'w:tab');
                $this->insertWordNodeAfter($cursor, $tab);
                $cursor = $tab;
            }

            $lineText = substr($line, $tabs);
            $newText = $node->ownerDocument->createElementNS($ns, 'w:t');
            $this->insertWordNodeAfter($cursor, $newText);
            $newText->nodeValue = $lineText;
            $this->applyWordXmlSpace($newText, $lineText);
            $cursor = $newText;
        }
    }

    private function insertWordNodeAfter(\DOMNode $ref, \DOMNode $node): void
    {
        $parent = $ref->parentNode;
        if (!$parent) return;

        if ($ref->nextSibling) {
            $parent->insertBefore($node, $ref->nextSibling);
        } else {
            $parent->appendChild($node);
        }
    }

    private function applyWordXmlSpace(\DOMElement $node, string $txt): void
    {
        $xmlNs = 'http://www.w3.org/XML/1998/namespace';
        if ($txt !== '' && preg_match('/^\\s|\\s$/u', $txt)) {
            $node->setAttributeNS($xmlNs, 'xml:space', 'preserve');
            return;
        }

        if ($node->hasAttributeNS($xmlNs, 'space')) {
            $node->removeAttributeNS($xmlNs, 'space');
        }
    }

    private function syncWordMediaContentTypes(string $workDir): void
    {
        $contentTypesPath = $workDir.'/[Content_Types].xml';
        $mediaDir = $workDir.'/word/media';
        if (!is_file($contentTypesPath) || !is_dir($mediaDir)) {
            return;
        }

        $mediaFiles = glob($mediaDir.'/*.*') ?: [];
        if (empty($mediaFiles)) {
            return;
        }

        $mimeByExt = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpe' => 'image/jpeg',
            'jfif' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'tif' => 'image/tiff',
            'tiff' => 'image/tiff',
            'webp' => 'image/webp',
        ];

        $required = [];
        foreach ($mediaFiles as $file) {
            $ext = strtolower((string) pathinfo($file, PATHINFO_EXTENSION));
            if ($ext === '' || !isset($mimeByExt[$ext])) {
                continue;
            }
            $required[$ext] = $mimeByExt[$ext];
        }

        if (empty($required)) {
            return;
        }

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        if (!@$dom->loadXML((string) file_get_contents($contentTypesPath))) {
            return;
        }

        $xpath = new \DOMXPath($dom);
        $ctNs = 'http://schemas.openxmlformats.org/package/2006/content-types';
        $xpath->registerNamespace('ct', $ctNs);
        $types = $dom->documentElement;
        if (!$types) {
            return;
        }

        $existing = [];
        $defaults = $xpath->query('/ct:Types/ct:Default');
        foreach ($defaults as $node) {
            if (!($node instanceof \DOMElement)) {
                continue;
            }
            $ext = strtolower(trim((string) $node->getAttribute('Extension')));
            if ($ext !== '') {
                $existing[$ext] = true;
            }
        }

        $changed = false;
        foreach ($required as $ext => $mime) {
            if (isset($existing[$ext])) {
                continue;
            }
            $def = $dom->createElementNS($ctNs, 'Default');
            $def->setAttribute('Extension', $ext);
            $def->setAttribute('ContentType', $mime);
            $types->appendChild($def);
            $changed = true;
        }

        if ($changed) {
            file_put_contents($contentTypesPath, (string) $dom->saveXML());
        }
    }

    private function buildOutputOriginalFilenameFromOutput(RequestOutput $output, string $ext): string
    {
        $req = UltRequest::query()
            ->with(['service', 'student'])
            ->find((int) $output->request_id);

        return $this->buildOutputOriginalFilename($req, $ext, (int) $output->request_id);
    }

    public function resolveOutputDownloadFilename(RequestOutput $output): string
    {
        $current = trim((string) $output->original_filename);
        if ($current !== '' && !$this->isLegacyGeneratedOutputFilename($current)) {
            return $current;
        }

        $ext = strtolower((string) pathinfo((string) $output->file_path, PATHINFO_EXTENSION));
        if ($ext === '') {
            $ext = strtolower((string) $output->output_type);
        }
        if (!in_array($ext, ['pdf', 'docx'], true)) {
            $ext = 'pdf';
        }

        $resolved = $this->buildOutputOriginalFilenameFromOutput($output, $ext);

        if ($resolved !== $current) {
            $output->forceFill(['original_filename' => $resolved])->save();
        }

        return $resolved;
    }

    private function buildOutputOriginalFilename(?UltRequest $request, string $ext, ?int $fallbackRequestId = null): string
    {
        $titleRaw = (string) ($request?->service?->title_id ?? $request?->service?->title_en ?? 'Surat');
        $codeRaw = (string) ($request?->request_code ?? '');
        $nameRaw = (string) ($request?->student?->name ?? 'Pemohon');

        $title = $this->sanitizeFilenameSegment($titleRaw, 'Surat');
        $code = $this->sanitizeFilenameSegment(
            $codeRaw !== '' ? $codeRaw : (($fallbackRequestId ?? 0) > 0 ? (string) $fallbackRequestId : ''),
            'Request'
        );
        $name = $this->sanitizeFilenameSegment($nameRaw, 'Pemohon');

        $ext = strtolower(trim($ext));
        if ($ext === '') $ext = 'pdf';

        $filename = "{$title}-{$code}-{$name}.{$ext}";

        // Keep filename length practical for different clients/filesystems.
        if (strlen($filename) > 180) {
            $base = "{$title}-{$code}-{$name}";
            $maxBase = max(20, 180 - (strlen($ext) + 1));
            $base = mb_substr($base, 0, $maxBase, 'UTF-8');
            $filename = "{$base}.{$ext}";
        }

        return $filename;
    }

    private function sanitizeFilenameSegment(string $value, string $fallback): string
    {
        $v = trim((string) Str::ascii($value));
        $v = preg_replace('/\\s+/u', ' ', $v) ?? $v;
        // Remove filename-reserved characters; keep spaces and dash.
        $v = preg_replace('/[\\\\\\/:*?"<>|\\x00-\\x1F]+/u', '', $v) ?? $v;
        $v = trim((string) $v, " .\t\n\r\0\x0B");

        return $v !== '' ? $v : $fallback;
    }

    private function isLegacyGeneratedOutputFilename(string $filename): bool
    {
        return preg_match('/^output-\d+\.(pdf|docx)$/i', trim($filename)) === 1;
    }

    private function rrmdir(string $dir): void
    {
        if (!is_dir($dir)) return;
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $file) {
            if ($file->isDir()) @rmdir($file->getRealPath());
            else @unlink($file->getRealPath());
        }
        @rmdir($dir);
    }

    /**
     * Replace placeholders per paragraph to preserve template layout.
     * This still supports placeholders split across runs inside the same paragraph.
     */
    private function replacePlaceholdersInWordXml(string $xml, array $values): string
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        if (!@$dom->loadXML($xml)) {
            return $xml;
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $paragraphs = $xpath->query('//w:p');
        if (!$paragraphs) {
            return $xml;
        }

        foreach ($paragraphs as $p) {
            $textNodes = $xpath->query('.//w:t', $p);
            if (!$textNodes || $textNodes->length === 0) {
                continue;
            }

            $joined = '';
            foreach ($textNodes as $t) {
                $joined .= (string) $t->textContent;
            }
            if ($joined === '') {
                continue;
            }

            $replaced = $joined;
            foreach ($values as $k => $v) {
                $replaced = str_replace('{{'.$k.'}}', (string) $v, $replaced);
            }
            if ($replaced === $joined) {
                continue;
            }

            // Keep paragraph boundary intact; only rewrite this paragraph text runs.
            $textNodes->item(0)->nodeValue = $replaced;
            for ($i = 1; $i < $textNodes->length; $i++) {
                $textNodes->item($i)->nodeValue = '';
            }
        }

        return $dom->saveXML();
    }

    /**
     * @param array<string,array{stored_path:string,name:string}> $placeholderImages
     * @return array{0:string,1:string} [documentXml, relsXml]
     */
    private function applyFormImagePlaceholders(string $documentXml, string $relsXml, string $mediaDir, array $placeholderImages): array
    {
        if (empty($placeholderImages)) {
            return [$documentXml, $relsXml];
        }

        $docDom = new \DOMDocument();
        $docDom->preserveWhiteSpace = false;
        $docDom->formatOutput = false;
        @$docDom->loadXML($documentXml);

        $relsDom = new \DOMDocument();
        $relsDom->preserveWhiteSpace = false;
        $relsDom->formatOutput = false;
        @$relsDom->loadXML($relsXml);

        $docXpath = new \DOMXPath($docDom);
        $docXpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $docXpath->registerNamespace('wp', 'http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing');
        $docXpath->registerNamespace('pic', 'http://schemas.openxmlformats.org/drawingml/2006/picture');
        $docXpath->registerNamespace('wps', 'http://schemas.microsoft.com/office/word/2010/wordprocessingShape');
        $docXpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
        $docXpath->registerNamespace('v', 'urn:schemas-microsoft-com:vml');

        $relsXpath = new \DOMXPath($relsDom);
        $relsXpath->registerNamespace('r', 'http://schemas.openxmlformats.org/package/2006/relationships');

        $relsRoot = $relsDom->documentElement;
        if (!$relsRoot) {
            return [$documentXml, $relsXml];
        }

        $nextRelId = $this->nextRelationshipId($relsXpath);
        $nextDrawingId = $this->nextDrawingElementId($docXpath);
        // Default pasfoto 3x4 cm; can be overridden by frame/table cell size around placeholder.
        $defaultWEmu = 3 * 360000;
        $defaultHEmu = 4 * 360000;

        $disk = config('ult.private_disk');
        $paras = $docXpath->query('//w:p');
        if (!$paras) {
            return [$documentXml, $relsXml];
        }

        foreach ($paras as $p) {
            if (!($p instanceof \DOMElement)) {
                continue;
            }

            $texts = $docXpath->query('.//w:t', $p);
            if (!$texts || $texts->length < 1) {
                continue;
            }

            $joined = '';
            foreach ($texts as $t) {
                $joined .= (string) $t->textContent;
            }
            $joined = trim($joined);
            if ($joined === '') {
                continue;
            }

            if (preg_match('/^\{\{\s*([^{}]+?)\s*\}\}$/u', $joined, $m) !== 1) {
                continue;
            }
            $key = PlaceholderKeyNormalizer::normalize((string) ($m[1] ?? ''));
            if ($key === '' || !isset($placeholderImages[$key])) {
                continue;
            }
            [$wEmu, $hEmu] = $this->resolveImageFrameExtentFromParagraph($p, $docXpath, $defaultWEmu, $defaultHEmu);

            $img = $placeholderImages[$key];
            $storedPath = (string) ($img['stored_path'] ?? '');
            if ($storedPath === '') {
                continue;
            }

            $tmp = storage_path('app/tmp/docx/'.uniqid('ph_img_', true));
            $stream = Storage::disk($disk)->readStream($storedPath);
            if (!is_resource($stream)) {
                continue;
            }

            $fp = fopen($tmp, 'wb');
            stream_copy_to_stream($stream, $fp);
            if (is_resource($stream)) fclose($stream);
            if (is_resource($fp)) fclose($fp);

            $ext = $this->resolveSignatureImageExtension($storedPath, $tmp);
            $safeKey = strtolower(preg_replace('/[^a-z0-9_]+/i', '_', $key) ?: 'img');
            $mediaName = 'ph_'.$safeKey.'_'.Str::random(8).'.'.$ext;
            $mediaPath = $mediaDir.'/'.$mediaName;
            @copy($tmp, $mediaPath);
            $cropRect = $this->resolveImageCropRectForFrame($tmp, $wEmu, $hEmu);
            @unlink($tmp);
            if (!is_file($mediaPath)) {
                continue;
            }

            $relId = 'rId'.$nextRelId++;
            $rel = $relsDom->createElementNS('http://schemas.openxmlformats.org/package/2006/relationships', 'Relationship');
            $rel->setAttribute('Id', $relId);
            $rel->setAttribute('Type', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/image');
            $rel->setAttribute('Target', 'media/'.$mediaName);
            $relsRoot->appendChild($rel);

            $docPrId = $nextDrawingId++;
            $cNvPrId = $nextDrawingId++;
            $imgP = $this->buildInlineImageParagraph(
                $docDom,
                $relId,
                (string) ($img['name'] ?? $key),
                $wEmu,
                $hEmu,
                $docPrId,
                $cNvPrId,
                null,
                0,
                $cropRect
            );

            $this->injectInlineSignatureIntoParagraph($docDom, $p, $imgP);
            $this->cleanupTextBoxPlaceholderParagraph($p, $docXpath);
        }

        return [$docDom->saveXML(), $relsDom->saveXML()];
    }

    /**
     * Resolve target frame size from nearest table cell/row around paragraph.
     *
     * @return array{0:int,1:int} [wEmu, hEmu]
     */
    private function resolveImageFrameExtentFromParagraph(\DOMElement $paragraph, \DOMXPath $docXpath, int $defaultWEmu, int $defaultHEmu): array
    {
        $wEmu = max(1, $defaultWEmu);
        $hEmu = max(1, $defaultHEmu);

        // Preferred: placeholder inside Word shape textbox (wps:wsp) uses this exact frame size.
        $wsp = $docXpath->query('ancestor::wps:wsp[1]', $paragraph)?->item(0);
        if ($wsp instanceof \DOMElement) {
            $ext = $docXpath->query('./wps:spPr/a:xfrm/a:ext', $wsp)?->item(0);
            if ($ext instanceof \DOMElement) {
                $cx = (int) $ext->getAttribute('cx');
                $cy = (int) $ext->getAttribute('cy');
                if ($cx > 0) $wEmu = $cx;
                if ($cy > 0) $hEmu = $cy;
            }

            return [$wEmu, $hEmu];
        }

        $tc = $docXpath->query('ancestor::w:tc[1]', $paragraph)?->item(0);
        $vRect = $docXpath->query('ancestor::v:rect[1]', $paragraph)?->item(0);
        if ($vRect instanceof \DOMElement) {
            $style = (string) $vRect->getAttribute('style');
            if (preg_match('/(?:^|;)\\s*width\\s*:\\s*([0-9.]+)pt\\s*(?:;|$)/i', $style, $mw) === 1) {
                $wPt = (float) ($mw[1] ?? 0);
                if ($wPt > 0) {
                    $wEmu = max(1, (int) round($wPt * 12700));
                }
            }
            if (preg_match('/(?:^|;)\\s*height\\s*:\\s*([0-9.]+)pt\\s*(?:;|$)/i', $style, $mh) === 1) {
                $hPt = (float) ($mh[1] ?? 0);
                if ($hPt > 0) {
                    $hEmu = max(1, (int) round($hPt * 12700));
                }
            }
            return [$wEmu, $hEmu];
        }

        if ($tc instanceof \DOMElement) {
            $tcW = $docXpath->query('./w:tcPr/w:tcW', $tc)?->item(0);
            if ($tcW instanceof \DOMElement) {
                $type = strtolower((string) $tcW->getAttribute('w:type'));
                $val = (int) $tcW->getAttribute('w:w');
                if ($type === 'dxa' && $val > 0) {
                    $wEmu = max(1, (int) round($val * 635));
                }
            }

            $tcMar = $docXpath->query('./w:tcPr/w:tcMar', $tc)?->item(0);
            if ($tcMar instanceof \DOMElement) {
                $left = $docXpath->query('./w:left', $tcMar)?->item(0);
                $right = $docXpath->query('./w:right', $tcMar)?->item(0);
                $top = $docXpath->query('./w:top', $tcMar)?->item(0);
                $bottom = $docXpath->query('./w:bottom', $tcMar)?->item(0);

                $mxL = ($left instanceof \DOMElement && strtolower((string) $left->getAttribute('w:type')) === 'dxa')
                    ? (int) $left->getAttribute('w:w')
                    : 0;
                $mxR = ($right instanceof \DOMElement && strtolower((string) $right->getAttribute('w:type')) === 'dxa')
                    ? (int) $right->getAttribute('w:w')
                    : 0;
                $mxT = ($top instanceof \DOMElement && strtolower((string) $top->getAttribute('w:type')) === 'dxa')
                    ? (int) $top->getAttribute('w:w')
                    : 0;
                $mxB = ($bottom instanceof \DOMElement && strtolower((string) $bottom->getAttribute('w:type')) === 'dxa')
                    ? (int) $bottom->getAttribute('w:w')
                    : 0;

                $wEmu = max(1, $wEmu - ((int) round(($mxL + $mxR) * 635)));
                $hEmu = max(1, $hEmu - ((int) round(($mxT + $mxB) * 635)));
            }
        }

        $tr = $docXpath->query('ancestor::w:tr[1]', $paragraph)?->item(0);
        if ($tr instanceof \DOMElement) {
            $trH = $docXpath->query('./w:trPr/w:trHeight', $tr)?->item(0);
            if ($trH instanceof \DOMElement) {
                $val = (int) $trH->getAttribute('w:val');
                if ($val > 0) {
                    $hEmu = max(1, (int) round($val * 635));
                }
            }
        }

        return [$wEmu, $hEmu];
    }

    private function cleanupTextBoxPlaceholderParagraph(\DOMElement $paragraph, \DOMXPath $docXpath): void
    {
        $txbx = $docXpath->query('ancestor::w:txbxContent[1]', $paragraph)?->item(0);
        if (!($txbx instanceof \DOMElement)) {
            return;
        }

        $toRemove = [];
        foreach ($txbx->childNodes as $node) {
            if (!($node instanceof \DOMElement) || $node->localName !== 'p') {
                continue;
            }
            if ($node->isSameNode($paragraph)) {
                continue;
            }
            if ($this->isParagraphVisuallyEmpty($node, $docXpath)) {
                $toRemove[] = $node;
            }
        }

        foreach ($toRemove as $node) {
            $node->parentNode?->removeChild($node);
        }

        $wsp = $docXpath->query('ancestor::wps:wsp[1]', $paragraph)?->item(0);
        if ($wsp instanceof \DOMElement) {
            $bodyPr = $docXpath->query('./wps:bodyPr', $wsp)?->item(0);
            if ($bodyPr instanceof \DOMElement) {
                // Keep image pinned from top when textbox has extra vertical room.
                $bodyPr->setAttribute('anchor', 't');
                $bodyPr->setAttribute('anchorCtr', '0');
                $bodyPr->setAttribute('spcFirstLastPara', '0');
                $bodyPr->setAttribute('vertOverflow', 'clip');
                $bodyPr->setAttribute('lIns', '0');
                $bodyPr->setAttribute('tIns', '0');
                $bodyPr->setAttribute('rIns', '0');
                $bodyPr->setAttribute('bIns', '0');
            }
        }

        $vTextbox = $docXpath->query('ancestor::v:textbox[1]', $paragraph)?->item(0);
        if ($vTextbox instanceof \DOMElement) {
            // Remove fallback textbox padding so image can fully occupy frame.
            $vTextbox->setAttribute('inset', '0,0,0,0');
        }

        $vRect = $docXpath->query('ancestor::v:rect[1]', $paragraph)?->item(0);
        if ($vRect instanceof \DOMElement) {
            $style = (string) $vRect->getAttribute('style');
            $style = $this->setInlineStyleProperty($style, 'v-text-anchor', 'top');
            $vRect->setAttribute('style', $style);
        }
    }

    private function setInlineStyleProperty(string $style, string $prop, string $value): string
    {
        $style = trim($style);
        $quotedProp = preg_quote($prop, '/');
        $pattern = '/(^|;)\\s*'.$quotedProp.'\\s*:[^;]*/i';
        if (preg_match($pattern, $style) === 1) {
            return (string) preg_replace($pattern, '$1'.$prop.':'.$value, $style, 1);
        }

        if ($style !== '' && !str_ends_with($style, ';')) {
            $style .= ';';
        }
        return $style.$prop.':'.$value;
    }

    /**
     * @return array{0:string,1:string} [documentXml, relsXml]
     */
    private function applySignaturePlacements(string $documentXml, string $relsXml, string $mediaDir, array $placements, UltRequest $request): array
    {
        $docDom = new \DOMDocument();
        $docDom->preserveWhiteSpace = false;
        $docDom->formatOutput = false;
        @$docDom->loadXML($documentXml);

        $relsDom = new \DOMDocument();
        $relsDom->preserveWhiteSpace = false;
        $relsDom->formatOutput = false;
        @$relsDom->loadXML($relsXml);

        $docXpath = new \DOMXPath($docDom);
        $docXpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $docXpath->registerNamespace('wp', 'http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing');
        $docXpath->registerNamespace('pic', 'http://schemas.openxmlformats.org/drawingml/2006/picture');

        $relsXpath = new \DOMXPath($relsDom);
        $relsXpath->registerNamespace('r', 'http://schemas.openxmlformats.org/package/2006/relationships');

        $relsRoot = $relsDom->documentElement;
        $docBody = $docXpath->query('/w:document/w:body')->item(0);
        if (!$relsRoot || !$docBody) {
            throw new \RuntimeException('Invalid document.xml structure.');
        }

        $nextRelId = $this->nextRelationshipId($relsXpath);
        $nextDrawingId = $this->nextDrawingElementId($docXpath);

        foreach ($placements as $p) {
            $role = $p['signer_role'];
            $page = (int) $p['page_number'];
            $xEmu = (int) round(((float)$p['x_pt']) * 12700);
            $yEmu = (int) round(((float)$p['y_pt']) * 12700);
            $wEmu = (int) round(((float)$p['width_pt']) * 12700);
            $hEmu = (int) round(((float)$p['height_pt']) * 12700);

            $signoff = $request->signoffs->firstWhere('signer_role', $role);
            if (!$signoff || !$signoff->signature_file_path) continue;

            $disk = config('ult.private_disk');
            $sigTmp = storage_path('app/tmp/docx/'.uniqid('sig_', true));
            $stream = Storage::disk($disk)->readStream($signoff->signature_file_path);
            if (!is_resource($stream)) {
                throw new \RuntimeException("Signature file not readable for {$role}.");
            }
            $fp = fopen($sigTmp, 'wb');
            stream_copy_to_stream($stream, $fp);
            if (is_resource($stream)) fclose($stream);
            if (is_resource($fp)) fclose($fp);

            $ext = $this->resolveSignatureImageExtension($signoff->signature_file_path, $sigTmp);
            $mediaName = 'sig_'.$role.'_'.Str::random(8).'.'.$ext;
            $mediaPath = $mediaDir.'/'.$mediaName;
            copy($sigTmp, $mediaPath);
            @unlink($sigTmp);

            $relId = 'rId'.$nextRelId;
            $nextRelId++;
            $rel = $relsDom->createElementNS('http://schemas.openxmlformats.org/package/2006/relationships', 'Relationship');
            $rel->setAttribute('Id', $relId);
            $rel->setAttribute('Type', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/image');
            $rel->setAttribute('Target', 'media/'.$mediaName);
            $relsRoot->appendChild($rel);

            $pNode = $this->findParagraphForPage($docXpath, $docBody, $page);
            if (!$pNode) {
                $pNode = $this->findSectionPropertiesNode($docBody);
            }
            $docPrId = $nextDrawingId++;
            $cNvPrId = $nextDrawingId++;
            $imgParagraph = $this->buildImageParagraph($docDom, $relId, $role, $xEmu, $yEmu, $wEmu, $hEmu, $docPrId, $cNvPrId);
            $docBody->insertBefore($imgParagraph, $pNode);
        }

        return [$docDom->saveXML(), $relsDom->saveXML()];
    }

    private function nextRelationshipId(\DOMXPath $relsXpath): int
    {
        $max = 0;
        $rels = $relsXpath->query('//r:Relationship');
        foreach ($rels as $r) {
            $id = $r->attributes?->getNamedItem('Id')?->nodeValue;
            if (!$id) continue;
            if (preg_match('/^rId(\\d+)$/', $id, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }
        return $max + 1;
    }

    private function findParagraphForPage(\DOMXPath $docXpath, \DOMElement $body, int $pageNumber): ?\DOMNode
    {
        if ($pageNumber <= 1) {
            return $body->firstChild;
        }

        $targetBreaks = $pageNumber - 1;
        $breaks = 0;

        $paras = $docXpath->query('.//w:p', $body);
        foreach ($paras as $p) {
            $br = $docXpath->query('.//w:br[@w:type="page"]', $p);
            $breaks += $br?->length ?? 0;
            if ($breaks >= $targetBreaks) {
                return $p->nextSibling;
            }
        }

        return null;
    }

    private function findParagraphContainingText(\DOMXPath $docXpath, \DOMElement $body, string $needle, bool $preferLast = false): ?\DOMElement
    {
        $needle = trim($needle);
        if ($needle === '') return null;

        $paras = $docXpath->query('.//w:p', $body);
        $found = null;
        foreach ($paras as $p) {
            $texts = $docXpath->query('.//w:t', $p);
            $joined = '';
            foreach ($texts as $t) {
                $joined .= (string) $t->textContent;
            }
            if ($this->textContainsNeedle($joined, $needle)) {
                if (!($p instanceof \DOMElement)) {
                    continue;
                }
                if (!$preferLast) {
                    return $p;
                }
                $found = $p;
            }
        }

        return $found;
    }

    private function textContainsNeedle(string $haystack, string $needle): bool
    {
        $haystack = trim($haystack);
        $needle = trim($needle);
        if ($haystack === '' || $needle === '') {
            return false;
        }

        // Fast path for exact-like substring before normalization fallback.
        if (mb_stripos($haystack, $needle) !== false) {
            return true;
        }

        $normHaystack = $this->normalizeSearchText($haystack);
        $normNeedle = $this->normalizeSearchText($needle);
        if ($normHaystack === '' || $normNeedle === '') {
            return false;
        }

        return mb_stripos($normHaystack, $normNeedle) !== false;
    }

    private function normalizeSearchText(string $value): string
    {
        $value = str_replace("\xC2\xA0", ' ', $value);
        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        return trim($value);
    }

    private function findSectionPropertiesNode(\DOMElement $body): ?\DOMNode
    {
        foreach ($body->childNodes as $child) {
            if ($child instanceof \DOMElement && $child->localName === 'sectPr') {
                return $child;
            }
        }
        return null;
    }

    private function buildImageParagraph(\DOMDocument $docDom, string $relId, string $role, int $xEmu, int $yEmu, int $wEmu, int $hEmu, int $docPrId, int $cNvPrId): \DOMElement
    {
        $safeRole = htmlspecialchars($role, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $xml = <<<XML
<w:p xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"
     xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"
     xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"
     xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"
     xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">
  <w:r>
    <w:drawing>
      <wp:anchor distT="0" distB="0" distL="0" distR="0" simplePos="0" relativeHeight="251658240" behindDoc="0" locked="0" layoutInCell="1" allowOverlap="1">
        <wp:simplePos x="0" y="0"/>
        <wp:positionH relativeFrom="page">
          <wp:posOffset>{$xEmu}</wp:posOffset>
        </wp:positionH>
        <wp:positionV relativeFrom="page">
          <wp:posOffset>{$yEmu}</wp:posOffset>
        </wp:positionV>
        <wp:extent cx="{$wEmu}" cy="{$hEmu}"/>
        <wp:effectExtent l="0" t="0" r="0" b="0"/>
        <wp:wrapNone/>
        <wp:docPr id="{$docPrId}" name="Signature {$safeRole}"/>
        <wp:cNvGraphicFramePr/>
        <a:graphic>
          <a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">
            <pic:pic>
              <pic:nvPicPr>
                <pic:cNvPr id="{$cNvPrId}" name="Signature {$safeRole}"/>
                <pic:cNvPicPr/>
              </pic:nvPicPr>
              <pic:blipFill>
                <a:blip r:embed="{$relId}"/>
                <a:stretch><a:fillRect/></a:stretch>
              </pic:blipFill>
              <pic:spPr>
                <a:xfrm>
                  <a:off x="0" y="0"/>
                  <a:ext cx="{$wEmu}" cy="{$hEmu}"/>
                </a:xfrm>
                <a:prstGeom prst="rect"><a:avLst/></a:prstGeom>
              </pic:spPr>
            </pic:pic>
          </a:graphicData>
        </a:graphic>
      </wp:anchor>
    </w:drawing>
  </w:r>
</w:p>
XML;

        $frag = $docDom->createDocumentFragment();
        if (!$frag->appendXML($xml)) {
            return $docDom->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:p');
        }
        /** @var \DOMElement $p */
        $p = $frag->firstChild;
        return $p;
    }

    /**
     * @param array{l?:int,t?:int,r?:int,b?:int}|null $cropRect
     */
    private function buildInlineImageParagraph(\DOMDocument $docDom, string $relId, string $name, int $wEmu, int $hEmu, int $docPrId, int $cNvPrId, ?\DOMElement $refParagraph = null, int $leadingTabs = 0, ?array $cropRect = null, bool $forceLeftAlign = false): \DOMElement
    {
        $pPrXml = '';
        if ($refParagraph) {
            $candidate = $this->renderTightParagraphPropertiesXml($docDom, $refParagraph);
            if ($candidate !== null) {
                $pPrXml = $candidate;
            }
        }
        $leadingTabs = max(0, $leadingTabs);
        $tabsXml = str_repeat('<w:tab/>', $leadingTabs);
        $cropXml = '';
        if (is_array($cropRect)) {
            $l = max(0, min(100000, (int) ($cropRect['l'] ?? 0)));
            $t = max(0, min(100000, (int) ($cropRect['t'] ?? 0)));
            $r = max(0, min(100000, (int) ($cropRect['r'] ?? 0)));
            $b = max(0, min(100000, (int) ($cropRect['b'] ?? 0)));
            if ($l !== 0 || $t !== 0 || $r !== 0 || $b !== 0) {
                $cropXml = "<a:srcRect l=\"{$l}\" t=\"{$t}\" r=\"{$r}\" b=\"{$b}\"/>";
            }
        }

        $safeName = htmlspecialchars($name, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $xml = <<<XML
<w:p xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"
     xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"
     xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"
     xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"
     xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">
  {$pPrXml}
  <w:r>
    {$tabsXml}
    <w:drawing>
      <wp:inline distT="0" distB="0" distL="0" distR="0">
        <wp:extent cx="{$wEmu}" cy="{$hEmu}"/>
        <wp:effectExtent l="0" t="0" r="0" b="0"/>
        <wp:docPr id="{$docPrId}" name="Signature {$safeName}"/>
        <wp:cNvGraphicFramePr/>
        <a:graphic>
          <a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">
            <pic:pic>
              <pic:nvPicPr>
                <pic:cNvPr id="{$cNvPrId}" name="Signature {$safeName}"/>
                <pic:cNvPicPr/>
              </pic:nvPicPr>
              <pic:blipFill>
                <a:blip r:embed="{$relId}"/>
                {$cropXml}
                <a:stretch><a:fillRect/></a:stretch>
              </pic:blipFill>
              <pic:spPr>
                <a:xfrm>
                  <a:off x="0" y="0"/>
                  <a:ext cx="{$wEmu}" cy="{$hEmu}"/>
                </a:xfrm>
                <a:prstGeom prst="rect"><a:avLst/></a:prstGeom>
              </pic:spPr>
            </pic:pic>
          </a:graphicData>
        </a:graphic>
      </wp:inline>
    </w:drawing>
  </w:r>
</w:p>
XML;

        $frag = $docDom->createDocumentFragment();
        if (!$frag->appendXML($xml)) {
            return $docDom->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:p');
        }
        $p = $frag->firstChild;
        if (!($p instanceof \DOMElement)) {
            return $docDom->createElement('w:p');
        }

        if ($forceLeftAlign) {
            $this->forceParagraphLeftAlign($docDom, $p);
        }

        return $p;
    }

    private function forceParagraphLeftAlign(\DOMDocument $docDom, \DOMElement $paragraph): void
    {
        if ($paragraph->localName !== 'p') {
            return;
        }

        $wordNs = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
        $pPr = null;
        foreach ($paragraph->childNodes as $child) {
            if ($child instanceof \DOMElement && $child->localName === 'pPr') {
                $pPr = $child;
                break;
            }
        }

        if (!$pPr) {
            $pPr = $docDom->createElementNS($wordNs, 'w:pPr');
            $insertBefore = null;
            foreach ($paragraph->childNodes as $child) {
                if ($child instanceof \DOMElement && $child->localName === 'r') {
                    $insertBefore = $child;
                    break;
                }
            }
            if ($insertBefore) {
                $paragraph->insertBefore($pPr, $insertBefore);
            } else {
                $paragraph->appendChild($pPr);
            }
        }

        $toRemove = [];
        foreach ($pPr->childNodes as $child) {
            if ($child instanceof \DOMElement && $child->localName === 'jc') {
                $toRemove[] = $child;
            }
        }
        foreach ($toRemove as $node) {
            $pPr->removeChild($node);
        }

        $jc = $docDom->createElementNS($wordNs, 'w:jc');
        $jc->setAttribute('w:val', 'left');
        $pPr->appendChild($jc);
    }

    private function nextDrawingElementId(\DOMXPath $docXpath): int
    {
        $max = 0;

        $docPrNodes = $docXpath->query('//wp:docPr');
        foreach ($docPrNodes as $n) {
            if (!($n instanceof \DOMElement)) continue;
            $id = (int) $n->getAttribute('id');
            $max = max($max, $id);
        }

        $picNodes = $docXpath->query('//pic:cNvPr');
        foreach ($picNodes as $n) {
            if (!($n instanceof \DOMElement)) continue;
            $id = (int) $n->getAttribute('id');
            $max = max($max, $id);
        }

        return max(1, $max + 1);
    }

    private function renderTightParagraphPropertiesXml(\DOMDocument $docDom, \DOMElement $paragraph): ?string
    {
        $pPr = null;
        foreach ($paragraph->childNodes as $child) {
            if ($child instanceof \DOMElement && $child->localName === 'pPr') {
                $pPr = $child;
                break;
            }
        }
        if (!$pPr) {
            return null;
        }

        $clone = $pPr->cloneNode(true);
        if (!($clone instanceof \DOMElement)) {
            return null;
        }

        return $docDom->saveXML($clone) ?: null;
    }

    private function paragraphHasProperties(\DOMElement $paragraph): bool
    {
        foreach ($paragraph->childNodes as $child) {
            if ($child instanceof \DOMElement && $child->localName === 'pPr') {
                return true;
            }
        }
        return false;
    }

    private function findPreviousNonEmptyParagraphWithProps(\DOMNode $node, \DOMXPath $docXpath): ?\DOMElement
    {
        $cur = $node->previousSibling;
        while ($cur) {
            if ($cur instanceof \DOMElement && $cur->localName === 'p' && !$this->isParagraphVisuallyEmpty($cur, $docXpath) && $this->paragraphHasProperties($cur)) {
                return $cur;
            }
            $cur = $cur->previousSibling;
        }
        return null;
    }

    private function findPreviousNonEmptyParagraph(\DOMNode $node, \DOMXPath $docXpath): ?\DOMElement
    {
        $cur = $node->previousSibling;
        while ($cur) {
            if ($cur instanceof \DOMElement && $cur->localName === 'p' && !$this->isParagraphVisuallyEmpty($cur, $docXpath)) {
                return $cur;
            }
            $cur = $cur->previousSibling;
        }

        // Fallback: previous paragraph in document order (works across nested table/textbox parents).
        $allPrev = $docXpath->query('preceding::w:p', $node);
        if ($allPrev) {
            for ($i = $allPrev->length - 1; $i >= 0; $i--) {
                $p = $allPrev->item($i);
                if ($p instanceof \DOMElement && !$this->isParagraphVisuallyEmpty($p, $docXpath)) {
                    return $p;
                }
            }
        }

        return null;
    }

    /**
     * @return array{0:string,1:string}
     */
    private function applyAutoSignatureByNameAnchor(string $documentXml, string $relsXml, string $mediaDir, UltRequest $request, array $values): array
    {
        $allSignoffs = $request->signoffs
            ->sortBy('order_index')
            ->values();
        if ($allSignoffs->isEmpty()) {
            return [$documentXml, $relsXml];
        }
        $candidates = $allSignoffs
            ->filter(fn ($s) => !empty($s->signature_file_path))
            ->values();

        $docDom = new \DOMDocument();
        $docDom->preserveWhiteSpace = false;
        $docDom->formatOutput = false;
        @$docDom->loadXML($documentXml);

        $relsDom = new \DOMDocument();
        $relsDom->preserveWhiteSpace = false;
        $relsDom->formatOutput = false;
        @$relsDom->loadXML($relsXml);

        $docXpath = new \DOMXPath($docDom);
        $docXpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $docXpath->registerNamespace('wp', 'http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing');
        $docXpath->registerNamespace('pic', 'http://schemas.openxmlformats.org/drawingml/2006/picture');
        $relsXpath = new \DOMXPath($relsDom);
        $relsXpath->registerNamespace('r', 'http://schemas.openxmlformats.org/package/2006/relationships');

        $relsRoot = $relsDom->documentElement;
        $docBody = $docXpath->query('/w:document/w:body')->item(0);
        if (!$relsRoot || !$docBody) {
            return [$documentXml, $relsXml];
        }

        $disk = config('ult.private_disk');
        $nextRelId = $this->nextRelationshipId($relsXpath);
        $nextDrawingId = $this->nextDrawingElementId($docXpath);
        $fixedHeightEmu = (int) round(54 * 12700);

        foreach ($candidates as $candidate) {
            $signaturePath = (string) ($candidate->signature_file_path ?? '');
            if ($signaturePath === '') continue;

            $targetParagraph = $this->resolveTargetParagraphForAutoPlacement($request, $candidate, $values, $docXpath, $docBody);
            if (!$targetParagraph) continue;

            $referenceParagraph = $targetParagraph;
            if (!$this->paragraphHasProperties($referenceParagraph)) {
                $fallback = $this->findPreviousNonEmptyParagraphWithProps($targetParagraph, $docXpath);
                if ($fallback) {
                    $referenceParagraph = $fallback;
                }
            }

            $leadingTabs = $this->countLeadingTabsInParagraph($targetParagraph);
            if ($leadingTabs < 1 && $referenceParagraph !== $targetParagraph) {
                $leadingTabs = $this->countLeadingTabsInParagraph($referenceParagraph);
            }

            $sameCellBlankSlot = $this->findClosestBlankParagraphBefore($targetParagraph, $docXpath);
            $blankSlot = $this->findPreferredSignatureSlotBefore($targetParagraph, $docXpath);
            if ($blankSlot) {
                $this->removeExtraBlankParagraphsAbove($blankSlot, $docXpath);
                $this->stripSoftBreaksFromBlankSignatureSlot($blankSlot, $docXpath);

                // If chosen slot is from the previous table row, drop redundant
                // in-cell blank lines right above signer name.
                if ($sameCellBlankSlot && !$sameCellBlankSlot->isSameNode($blankSlot)) {
                    $this->removeExtraBlankParagraphsAbove($targetParagraph, $docXpath);
                }
            }

            $sigTmp = storage_path('app/tmp/docx/'.uniqid('sig_auto_', true));
            $stream = Storage::disk($disk)->readStream($signaturePath);
            if (!is_resource($stream)) {
                continue;
            }

            $fp = fopen($sigTmp, 'wb');
            stream_copy_to_stream($stream, $fp);
            if (is_resource($stream)) fclose($stream);
            if (is_resource($fp)) fclose($fp);

            [$wEmu, $hEmu] = $this->resolveAutoSignatureInlineExtent($sigTmp, $fixedHeightEmu);
            $ext = $this->resolveSignatureImageExtension($signaturePath, $sigTmp);
            $mediaName = 'sig_auto_'.Str::random(8).'.'.$ext;
            $mediaPath = $mediaDir.'/'.$mediaName;
            @copy($sigTmp, $mediaPath);
            @unlink($sigTmp);
            if (!is_file($mediaPath)) {
                continue;
            }

            $relId = 'rId'.$nextRelId++;
            $rel = $relsDom->createElementNS('http://schemas.openxmlformats.org/package/2006/relationships', 'Relationship');
            $rel->setAttribute('Id', $relId);
            $rel->setAttribute('Type', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/image');
            $rel->setAttribute('Target', 'media/'.$mediaName);
            $relsRoot->appendChild($rel);

            $docPrId = $nextDrawingId++;
            $cNvPrId = $nextDrawingId++;
            $imgP = $this->buildInlineImageParagraph(
                $docDom,
                $relId,
                (string) ($candidate->signer_role ?? 'SIGNER'),
                $wEmu,
                $hEmu,
                $docPrId,
                $cNvPrId,
                $referenceParagraph,
                $leadingTabs,
                null,
                true
            );

            // If template already provides an empty row between role and signer name, fill that row.
            if ($blankSlot && $this->injectInlineSignatureIntoParagraph($docDom, $blankSlot, $imgP)) {
                $this->removeExtraBlankParagraphsAbove($blankSlot, $docXpath);
                continue;
            }

            // Otherwise insert exactly one row for signature between role/title and signer name.
            $this->removeExtraBlankParagraphsAbove($targetParagraph, $docXpath);
            $parent = $targetParagraph->parentNode;
            if ($parent instanceof \DOMNode) {
                $parent->insertBefore($imgP, $targetParagraph);
            }
        }

        // Keep exactly one blank signature slot line for unsigned signers,
        // so mixed signed/unsigned blocks stay visually consistent.
        $unsignedCandidates = $allSignoffs
            ->filter(fn ($s) => empty($s->signature_file_path))
            ->values();
        foreach ($unsignedCandidates as $candidate) {
            $targetParagraph = $this->resolveTargetParagraphForAutoPlacement($request, $candidate, $values, $docXpath, $docBody);
            if (!$targetParagraph) {
                continue;
            }

            $sameCellBlankSlot = $this->findClosestBlankParagraphBefore($targetParagraph, $docXpath);
            $blankSlot = $this->findPreferredSignatureSlotBefore($targetParagraph, $docXpath);
            if ($blankSlot) {
                $this->removeExtraBlankParagraphsAbove($blankSlot, $docXpath);
                $this->stripSoftBreaksFromBlankSignatureSlot($blankSlot, $docXpath);
                if ($sameCellBlankSlot && !$sameCellBlankSlot->isSameNode($blankSlot)) {
                    $this->removeExtraBlankParagraphsAbove($targetParagraph, $docXpath);
                }
            }
        }

        return [$docDom->saveXML(), $relsDom->saveXML()];
    }

    private function resolveTargetParagraphForAutoPlacement(
        UltRequest $request,
        object $candidate,
        array $values,
        \DOMXPath $docXpath,
        \DOMElement $docBody
    ): ?\DOMElement {
        $role = strtoupper(trim((string) ($candidate->signer_role ?? '')));

        // CUSTOM/DOSEN signer must follow admin label block first,
        // because signer user/decider name may point to account owner instead of doc signer text.
        if (in_array($role, ['CUSTOM', 'DOSEN'], true)) {
            $label = $this->resolveCustomSignerLabelForAutoPlacement($request, $candidate);
            if ($label !== '') {
                $roleParagraph = $this->findParagraphContainingText($docXpath, $docBody, $label, true);
                if ($roleParagraph) {
                    $numberParagraph =
                        $this->findNextNumberParagraphAfterInSameTableCell($roleParagraph, $docXpath)
                        ?:
                        $this->findNextNumberParagraphAfterInSameTableColumn($roleParagraph, $docXpath)
                        ?: $this->findNextNumberParagraphAfter($roleParagraph, $docXpath);
                    if ($numberParagraph) {
                        $nameParagraph = $this->findPreviousNonEmptyParagraphBeforeInSameTableColumn($numberParagraph, $docXpath)
                            ?: $this->findPreviousNonEmptyParagraphBeforeInSameTableCell($numberParagraph, $docXpath)
                            ?: $this->findPreviousNonEmptyParagraphInSameContainer($numberParagraph, $docXpath)
                            ?: $this->findPreviousNonEmptyParagraph($numberParagraph, $docXpath);
                        if ($nameParagraph && $nameParagraph !== $roleParagraph) {
                            return $nameParagraph;
                        }
                        return $numberParagraph;
                    }

                    // If no NIP/NIM/NPM line is detected, fallback to the second non-empty row after label.
                    $first =
                        $this->findNextNonEmptyParagraphAfterInSameTableCell($roleParagraph, $docXpath)
                        ?:
                        $this->findNextNonEmptyParagraphAfterInSameTableColumn($roleParagraph, $docXpath)
                        ?: $this->findNextNonEmptyParagraphAfter($roleParagraph, $docXpath);
                    if ($first) {
                        $second =
                            $this->findNextNonEmptyParagraphAfterInSameTableCell($first, $docXpath)
                            ?:
                            $this->findNextNonEmptyParagraphAfterInSameTableColumn($first, $docXpath)
                            ?: $this->findNextNonEmptyParagraphAfter($first, $docXpath);
                        return $second ?: $first;
                    }

                    return $roleParagraph;
                }
            }
        }

        $signerName = $this->resolveSignerNameForAutoPlacement($request, $candidate, $values);
        $signerNumber = $this->resolveSignerNumberForAutoPlacement($request, $candidate);

        // 1) Primary: signer name in rendered document.
        $targetParagraph = $signerName !== ''
            ? $this->findParagraphContainingText($docXpath, $docBody, $signerName, true)
            : null;
        if ($targetParagraph) {
            return $targetParagraph;
        }

        // 2) Fallback: line with NIP/NIM/NPM, then take the line above as name anchor.
        if ($signerNumber !== '') {
            $numberParagraph = $this->findParagraphContainingText($docXpath, $docBody, $signerNumber, true);
            if ($numberParagraph) {
                return $this->findPreviousNonEmptyParagraph($numberParagraph, $docXpath) ?: $numberParagraph;
            }
        }

        return null;
    }

    private function resolveCustomSignerLabelForAutoPlacement(UltRequest $request, object $candidate): string
    {
        $order = (int) ($candidate->order_index ?? 0);
        $role = strtoupper(trim((string) ($candidate->signer_role ?? '')));
        if (!in_array($role, ['CUSTOM', 'DOSEN'], true)) {
            return '';
        }

        $serviceSigner = $this->resolveRequestSignerDefinitions($request)
            ->first(function ($s) use ($order, $role) {
                return strtoupper(trim((string) ($s->role ?? ''))) === $role
                    && (int) ($s->order_index ?? 0) === $order;
            });
        $label = trim((string) ($serviceSigner?->custom_label ?? ''));
        if ($label !== '') {
            return $label;
        }

        // Backward-compatible fallback from signoff note.
        $note = trim((string) ($candidate->note ?? ''));
        if ($note !== '' && preg_match('/Label\\s*:\\s*(.+?)\\./u', $note, $m) === 1) {
            $parsed = trim((string) ($m[1] ?? ''));
            if ($parsed !== '') {
                return $parsed;
            }
        }

        return '';
    }

    private function findNextNonEmptyParagraphAfterInSameTableCell(\DOMNode $start, \DOMXPath $docXpath): ?\DOMElement
    {
        $tc = $docXpath->query('ancestor::w:tc[1]', $start)?->item(0);
        if (!($tc instanceof \DOMElement)) {
            return null;
        }

        $paras = $docXpath->query('.//w:p', $tc);
        if (!$paras || $paras->length < 1) {
            return null;
        }

        $foundStart = false;
        foreach ($paras as $p) {
            if (!($p instanceof \DOMElement)) {
                continue;
            }
            if (!$foundStart) {
                if ($p->isSameNode($start)) {
                    $foundStart = true;
                }
                continue;
            }
            if (!$this->isParagraphVisuallyEmpty($p, $docXpath)) {
                return $p;
            }
        }

        return null;
    }

    private function findNextNumberParagraphAfterInSameTableCell(\DOMNode $start, \DOMXPath $docXpath): ?\DOMElement
    {
        $tc = $docXpath->query('ancestor::w:tc[1]', $start)?->item(0);
        if (!($tc instanceof \DOMElement)) {
            return null;
        }

        $paras = $docXpath->query('.//w:p', $tc);
        if (!$paras || $paras->length < 1) {
            return null;
        }

        $foundStart = false;
        foreach ($paras as $p) {
            if (!($p instanceof \DOMElement)) {
                continue;
            }
            if (!$foundStart) {
                if ($p->isSameNode($start)) {
                    $foundStart = true;
                }
                continue;
            }
            $text = $this->extractParagraphText($p, $docXpath);
            if ($this->isSignerNumberParagraphText($text)) {
                return $p;
            }
        }

        return null;
    }

    private function findPreviousNonEmptyParagraphBeforeInSameTableCell(\DOMNode $start, \DOMXPath $docXpath): ?\DOMElement
    {
        $tc = $docXpath->query('ancestor::w:tc[1]', $start)?->item(0);
        if (!($tc instanceof \DOMElement)) {
            return null;
        }

        $paras = $docXpath->query('.//w:p', $tc);
        if (!$paras || $paras->length < 1) {
            return null;
        }

        $targetIndex = -1;
        for ($i = 0; $i < $paras->length; $i++) {
            $p = $paras->item($i);
            if ($p instanceof \DOMNode && $p->isSameNode($start)) {
                $targetIndex = $i;
                break;
            }
        }
        if ($targetIndex < 0) {
            return null;
        }

        for ($i = $targetIndex - 1; $i >= 0; $i--) {
            $p = $paras->item($i);
            if ($p instanceof \DOMElement && !$this->isParagraphVisuallyEmpty($p, $docXpath)) {
                return $p;
            }
        }

        return null;
    }

    private function findPreviousNonEmptyParagraphBeforeInSameTableColumn(\DOMNode $start, \DOMXPath $docXpath, int $maxRows = 16): ?\DOMElement
    {
        $ctx = $this->resolveTableColumnContext($start, $docXpath);
        if (!$ctx) {
            return null;
        }

        $row = $ctx['row'];
        $cellIndex = $ctx['cellIndex'];

        // Search current row/cell first, before the start paragraph.
        $cells = $docXpath->query('./w:tc', $row);
        $currentCell = ($cells && $cells->length > $cellIndex) ? $cells->item($cellIndex) : null;
        if ($currentCell instanceof \DOMElement) {
            $paras = $docXpath->query('.//w:p', $currentCell);
            if ($paras && $paras->length > 0) {
                $targetIndex = -1;
                for ($i = 0; $i < $paras->length; $i++) {
                    $p = $paras->item($i);
                    if ($p instanceof \DOMNode && $p->isSameNode($start)) {
                        $targetIndex = $i;
                        break;
                    }
                }
                if ($targetIndex > 0) {
                    for ($i = $targetIndex - 1; $i >= 0; $i--) {
                        $p = $paras->item($i);
                        if ($p instanceof \DOMElement && !$this->isParagraphVisuallyEmpty($p, $docXpath)) {
                            return $p;
                        }
                    }
                }
            }
        }

        $ignorableRowMarkers = [
            'bookmarkStart',
            'bookmarkEnd',
            'proofErr',
            'permStart',
            'permEnd',
            'commentRangeStart',
            'commentRangeEnd',
            'moveFromRangeStart',
            'moveFromRangeEnd',
            'moveToRangeStart',
            'moveToRangeEnd',
        ];

        $seenRows = 0;
        $prev = $row->previousSibling;
        while ($prev && $seenRows < $maxRows) {
            if ($prev instanceof \DOMText && trim((string) $prev->nodeValue) === '') {
                $prev = $prev->previousSibling;
                continue;
            }
            if ($prev instanceof \DOMElement && in_array($prev->localName, $ignorableRowMarkers, true)) {
                $prev = $prev->previousSibling;
                continue;
            }
            if (!($prev instanceof \DOMElement) || $prev->localName !== 'tr') {
                break;
            }

            $seenRows++;
            $prevCells = $docXpath->query('./w:tc', $prev);
            if ($prevCells && $prevCells->length > $cellIndex) {
                $prevCell = $prevCells->item($cellIndex);
                if ($prevCell instanceof \DOMElement) {
                    $paras = $docXpath->query('.//w:p', $prevCell);
                    if ($paras && $paras->length > 0) {
                        for ($i = $paras->length - 1; $i >= 0; $i--) {
                            $p = $paras->item($i);
                            if ($p instanceof \DOMElement && !$this->isParagraphVisuallyEmpty($p, $docXpath)) {
                                return $p;
                            }
                        }
                    }
                }
            }

            $prev = $prev->previousSibling;
        }

        return null;
    }

    /**
     * Find next non-empty paragraph after anchor, restricted to the same table column when possible.
     */
    private function findNextNonEmptyParagraphAfterInSameTableColumn(\DOMNode $start, \DOMXPath $docXpath, int $maxRows = 16): ?\DOMElement
    {
        $ctx = $this->resolveTableColumnContext($start, $docXpath);
        if (!$ctx) {
            return null;
        }

        $row = $ctx['row'];
        $cellIndex = $ctx['cellIndex'];

        $cells = $docXpath->query('./w:tc', $row);
        $currentCell = ($cells && $cells->length > $cellIndex) ? $cells->item($cellIndex) : null;
        if ($currentCell instanceof \DOMElement) {
            $paras = $docXpath->query('.//w:p', $currentCell);
            $passedStart = false;
            foreach ($paras as $p) {
                if (!($p instanceof \DOMElement)) {
                    continue;
                }
                if (!$passedStart) {
                    if ($p->isSameNode($start)) {
                        $passedStart = true;
                    }
                    continue;
                }
                if (!$this->isParagraphVisuallyEmpty($p, $docXpath)) {
                    return $p;
                }
            }
        }

        $ignorableRowMarkers = [
            'bookmarkStart',
            'bookmarkEnd',
            'proofErr',
            'permStart',
            'permEnd',
            'commentRangeStart',
            'commentRangeEnd',
            'moveFromRangeStart',
            'moveFromRangeEnd',
            'moveToRangeStart',
            'moveToRangeEnd',
        ];

        $seenRows = 0;
        $next = $row->nextSibling;
        while ($next && $seenRows < $maxRows) {
            if ($next instanceof \DOMText && trim((string) $next->nodeValue) === '') {
                $next = $next->nextSibling;
                continue;
            }
            if ($next instanceof \DOMElement && in_array($next->localName, $ignorableRowMarkers, true)) {
                $next = $next->nextSibling;
                continue;
            }
            if (!($next instanceof \DOMElement) || $next->localName !== 'tr') {
                break;
            }

            $seenRows++;
            $nextCells = $docXpath->query('./w:tc', $next);
            if ($nextCells && $nextCells->length > $cellIndex) {
                $nextCell = $nextCells->item($cellIndex);
                if ($nextCell instanceof \DOMElement) {
                    $paras = $docXpath->query('.//w:p', $nextCell);
                    foreach ($paras as $p) {
                        if ($p instanceof \DOMElement && !$this->isParagraphVisuallyEmpty($p, $docXpath)) {
                            return $p;
                        }
                    }
                }
            }

            $next = $next->nextSibling;
        }

        return null;
    }

    /**
     * Find next NIP/NIM/NPM paragraph after anchor, restricted to the same table column when possible.
     */
    private function findNextNumberParagraphAfterInSameTableColumn(\DOMNode $start, \DOMXPath $docXpath, int $maxRows = 16): ?\DOMElement
    {
        $ctx = $this->resolveTableColumnContext($start, $docXpath);
        if (!$ctx) {
            return null;
        }

        $row = $ctx['row'];
        $cellIndex = $ctx['cellIndex'];

        $cells = $docXpath->query('./w:tc', $row);
        $currentCell = ($cells && $cells->length > $cellIndex) ? $cells->item($cellIndex) : null;
        if ($currentCell instanceof \DOMElement) {
            $paras = $docXpath->query('.//w:p', $currentCell);
            $passedStart = false;
            foreach ($paras as $p) {
                if (!($p instanceof \DOMElement)) {
                    continue;
                }
                if (!$passedStart) {
                    if ($p->isSameNode($start)) {
                        $passedStart = true;
                    }
                    continue;
                }
                $text = $this->extractParagraphText($p, $docXpath);
                if ($this->isSignerNumberParagraphText($text)) {
                    return $p;
                }
            }
        }

        $ignorableRowMarkers = [
            'bookmarkStart',
            'bookmarkEnd',
            'proofErr',
            'permStart',
            'permEnd',
            'commentRangeStart',
            'commentRangeEnd',
            'moveFromRangeStart',
            'moveFromRangeEnd',
            'moveToRangeStart',
            'moveToRangeEnd',
        ];

        $seenRows = 0;
        $next = $row->nextSibling;
        while ($next && $seenRows < $maxRows) {
            if ($next instanceof \DOMText && trim((string) $next->nodeValue) === '') {
                $next = $next->nextSibling;
                continue;
            }
            if ($next instanceof \DOMElement && in_array($next->localName, $ignorableRowMarkers, true)) {
                $next = $next->nextSibling;
                continue;
            }
            if (!($next instanceof \DOMElement) || $next->localName !== 'tr') {
                break;
            }

            $seenRows++;
            $nextCells = $docXpath->query('./w:tc', $next);
            if ($nextCells && $nextCells->length > $cellIndex) {
                $nextCell = $nextCells->item($cellIndex);
                if ($nextCell instanceof \DOMElement) {
                    $paras = $docXpath->query('.//w:p', $nextCell);
                    foreach ($paras as $p) {
                        if (!($p instanceof \DOMElement)) {
                            continue;
                        }
                        $text = $this->extractParagraphText($p, $docXpath);
                        if ($this->isSignerNumberParagraphText($text)) {
                            return $p;
                        }
                    }
                }
            }

            $next = $next->nextSibling;
        }

        return null;
    }

    /**
     * Previous non-empty paragraph in the same parent container (no cross-table fallback).
     */
    private function findPreviousNonEmptyParagraphInSameContainer(\DOMNode $node, \DOMXPath $docXpath): ?\DOMElement
    {
        $cur = $node->previousSibling;
        while ($cur) {
            if ($cur instanceof \DOMText && trim((string) $cur->nodeValue) === '') {
                $cur = $cur->previousSibling;
                continue;
            }
            if ($cur instanceof \DOMElement && $cur->localName === 'p' && !$this->isParagraphVisuallyEmpty($cur, $docXpath)) {
                return $cur;
            }
            $cur = $cur->previousSibling;
        }

        return null;
    }

    /**
     * @return array{row:\DOMElement,cellIndex:int}|null
     */
    private function resolveTableColumnContext(\DOMNode $node, \DOMXPath $docXpath): ?array
    {
        $tc = $docXpath->query('ancestor::w:tc[1]', $node)?->item(0);
        $tr = $docXpath->query('ancestor::w:tr[1]', $node)?->item(0);
        if (!($tc instanceof \DOMElement) || !($tr instanceof \DOMElement)) {
            return null;
        }

        $cells = $docXpath->query('./w:tc', $tr);
        if (!$cells || $cells->length < 1) {
            return null;
        }

        $cellIndex = -1;
        for ($i = 0; $i < $cells->length; $i++) {
            $cell = $cells->item($i);
            if ($cell instanceof \DOMNode && $cell->isSameNode($tc)) {
                $cellIndex = $i;
                break;
            }
        }
        if ($cellIndex < 0) {
            return null;
        }

        return [
            'row' => $tr,
            'cellIndex' => $cellIndex,
        ];
    }

    private function findNextNonEmptyParagraphAfter(\DOMNode $start, \DOMXPath $docXpath): ?\DOMElement
    {
        $cur = $start->nextSibling;
        while ($cur) {
            if ($cur instanceof \DOMElement && $cur->localName === 'p' && !$this->isParagraphVisuallyEmpty($cur, $docXpath)) {
                return $cur;
            }
            $cur = $cur->nextSibling;
        }

        // Fallback: next paragraph in document order (works across nested table/textbox parents).
        $allNext = $docXpath->query('following::w:p', $start);
        if ($allNext) {
            foreach ($allNext as $p) {
                if ($p instanceof \DOMElement && !$this->isParagraphVisuallyEmpty($p, $docXpath)) {
                    return $p;
                }
            }
        }

        return null;
    }

    private function findNextNumberParagraphAfter(\DOMNode $start, \DOMXPath $docXpath, int $maxLookAhead = 16): ?\DOMElement
    {
        $cur = $start->nextSibling;
        $seenParagraph = 0;

        while ($cur) {
            if ($cur instanceof \DOMElement && $cur->localName === 'p') {
                $seenParagraph++;
                $text = $this->extractParagraphText($cur, $docXpath);
                if ($this->isSignerNumberParagraphText($text)) {
                    return $cur;
                }
                if ($seenParagraph >= $maxLookAhead) {
                    break;
                }
            }
            $cur = $cur->nextSibling;
        }

        // Fallback: search by document order when role/number paragraphs are not siblings.
        $allNext = $docXpath->query('following::w:p', $start);
        if ($allNext) {
            $seen = 0;
            foreach ($allNext as $p) {
                if (!($p instanceof \DOMElement)) {
                    continue;
                }
                $seen++;
                $text = $this->extractParagraphText($p, $docXpath);
                if ($this->isSignerNumberParagraphText($text)) {
                    return $p;
                }
                if ($seen >= $maxLookAhead) {
                    break;
                }
            }
        }

        return null;
    }

    private function extractParagraphText(\DOMElement $paragraph, \DOMXPath $docXpath): string
    {
        $texts = $docXpath->query('.//w:t', $paragraph);
        $joined = '';
        foreach ($texts as $t) {
            $joined .= (string) $t->textContent;
        }

        return trim($joined);
    }

    private function isSignerNumberParagraphText(string $text): bool
    {
        if ($text === '') {
            return false;
        }

        $normalized = $this->normalizeSearchText($text);
        if ($normalized === '') {
            return false;
        }

        return preg_match('/\\b(nip|nim|npm)\\b/u', $normalized) === 1;
    }

    private function resolveSignerNumberForAutoPlacement(UltRequest $request, object $candidate): string
    {
        $number = trim((string) (
            $candidate->decider?->user_number
            ?? $candidate->decider?->student_number
            ?? $candidate->signerUser?->user_number
            ?? $candidate->signerUser?->student_number
            ?? ''
        ));
        if ($number !== '') {
            return $number;
        }

        $role = strtoupper(trim((string) ($candidate->signer_role ?? '')));
        if ($role !== '') {
            $resolved = trim($this->resolveProfileValue($request, "signer.{$role}.user_number"));
            if ($resolved !== '') {
                return $resolved;
            }
            $resolved = trim($this->resolveProfileValue($request, "signer.{$role}.student_number"));
            if ($resolved !== '') {
                return $resolved;
            }
        }

        return '';
    }

    private function resolveSignerNameForAutoPlacement(UltRequest $request, object $candidate, array $values): string
    {
        $name = trim((string) ($candidate->decider?->name ?? $candidate->signerUser?->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        $role = strtoupper(trim((string) ($candidate->signer_role ?? '')));
        if ($role !== '') {
            $resolved = trim($this->resolveProfileValue($request, "signer.{$role}.name"));
            if ($resolved !== '') {
                return $resolved;
            }
        }

        // Avoid cross-signer fallback for contextual roles: wrong global value
        // may anchor signature to another signer's name block.
        if (in_array($role, ['PEMOHON', 'DOSEN', 'CUSTOM'], true)) {
            return '';
        }

        $fallback = trim((string) ($values['NAMA_PENANDATANGAN'] ?? ''));
        return $fallback;
    }

    private function findClosestBlankParagraphBefore(\DOMElement $targetParagraph, \DOMXPath $docXpath): ?\DOMElement
    {
        $node = $targetParagraph->previousSibling;
        while ($node) {
            if ($node instanceof \DOMText && trim((string) $node->nodeValue) === '') {
                $node = $node->previousSibling;
                continue;
            }

            if ($node instanceof \DOMElement && $node->localName === 'p') {
                return $this->isParagraphAvailableSignatureSlot($node, $docXpath) ? $node : null;
            }

            if ($node instanceof \DOMElement && in_array($node->localName, [
                'bookmarkStart',
                'bookmarkEnd',
                'proofErr',
                'permStart',
                'permEnd',
                'commentRangeStart',
                'commentRangeEnd',
                'moveFromRangeStart',
                'moveFromRangeEnd',
                'moveToRangeStart',
                'moveToRangeEnd',
            ], true)) {
                $node = $node->previousSibling;
                continue;
            }

            return null;
        }

        return null;
    }

    private function findPreferredSignatureSlotBefore(\DOMElement $targetParagraph, \DOMXPath $docXpath): ?\DOMElement
    {
        $rowSlot = $this->findBlankParagraphInPreviousTableRowSameColumn($targetParagraph, $docXpath);
        if ($rowSlot) {
            return $rowSlot;
        }

        return $this->findClosestBlankParagraphBefore($targetParagraph, $docXpath);
    }

    private function findBlankParagraphInPreviousTableRowSameColumn(\DOMElement $targetParagraph, \DOMXPath $docXpath): ?\DOMElement
    {
        $tc = $docXpath->query('ancestor::w:tc[1]', $targetParagraph)?->item(0);
        $tr = $docXpath->query('ancestor::w:tr[1]', $targetParagraph)?->item(0);
        if (!($tc instanceof \DOMElement) || !($tr instanceof \DOMElement)) {
            return null;
        }

        $cells = $docXpath->query('./w:tc', $tr);
        if (!$cells || $cells->length < 1) {
            return null;
        }

        $cellIndex = -1;
        for ($i = 0; $i < $cells->length; $i++) {
            $cell = $cells->item($i);
            if ($cell instanceof \DOMNode && $cell->isSameNode($tc)) {
                $cellIndex = $i;
                break;
            }
        }
        if ($cellIndex < 0) {
            return null;
        }

        $ignorableRowMarkers = [
            'bookmarkStart',
            'bookmarkEnd',
            'proofErr',
            'permStart',
            'permEnd',
            'commentRangeStart',
            'commentRangeEnd',
            'moveFromRangeStart',
            'moveFromRangeEnd',
            'moveToRangeStart',
            'moveToRangeEnd',
        ];

        $prev = $tr->previousSibling;
        while ($prev) {
            if ($prev instanceof \DOMText && trim((string) $prev->nodeValue) === '') {
                $prev = $prev->previousSibling;
                continue;
            }

            if ($prev instanceof \DOMElement && in_array($prev->localName, $ignorableRowMarkers, true)) {
                $prev = $prev->previousSibling;
                continue;
            }

            if (!($prev instanceof \DOMElement) || $prev->localName !== 'tr') {
                return null;
            }

            $prevCells = $docXpath->query('./w:tc', $prev);
            if (!$prevCells || $prevCells->length <= $cellIndex) {
                return null;
            }

            $prevCell = $prevCells->item($cellIndex);
            if (!($prevCell instanceof \DOMElement)) {
                return null;
            }

            $paras = $docXpath->query('.//w:p', $prevCell);
            if (!$paras || $paras->length < 1) {
                return null;
            }

            for ($j = $paras->length - 1; $j >= 0; $j--) {
                $p = $paras->item($j);
                if ($p instanceof \DOMElement && $this->isParagraphAvailableSignatureSlot($p, $docXpath)) {
                    return $p;
                }
            }

            return null;
        }

        return null;
    }

    private function removeExtraBlankParagraphsAbove(\DOMElement $anchorParagraph, \DOMXPath $docXpath): void
    {
        $node = $anchorParagraph->previousSibling;
        while ($node) {
            if ($node instanceof \DOMText && trim((string) $node->nodeValue) === '') {
                $node = $node->previousSibling;
                continue;
            }

            if ($node instanceof \DOMElement && in_array($node->localName, [
                'bookmarkStart',
                'bookmarkEnd',
                'proofErr',
                'permStart',
                'permEnd',
                'commentRangeStart',
                'commentRangeEnd',
                'moveFromRangeStart',
                'moveFromRangeEnd',
                'moveToRangeStart',
                'moveToRangeEnd',
            ], true)) {
                $node = $node->previousSibling;
                continue;
            }

            if ($node instanceof \DOMElement && $node->localName === 'p' && $this->isParagraphAvailableSignatureSlot($node, $docXpath)) {
                $prev = $node->previousSibling;
                $node->parentNode?->removeChild($node);
                $node = $prev;
                continue;
            }

            break;
        }
    }

    private function isParagraphAvailableSignatureSlot(\DOMElement $paragraph, \DOMXPath $docXpath): bool
    {
        $texts = $docXpath->query('.//w:t', $paragraph);
        $joined = '';
        foreach ($texts as $t) {
            $joined .= (string) $t->textContent;
        }
        if (trim(str_replace("\xC2\xA0", ' ', $joined)) !== '') {
            return false;
        }

        // Keep paragraphs with page break / rendered break / drawing untouched.
        $hasBlockingNodes = $docXpath->query(
            './/w:drawing|.//w:object|.//w:pict|.//w:br[@w:type="page"]|.//w:lastRenderedPageBreak',
            $paragraph
        );
        if ($hasBlockingNodes && $hasBlockingNodes->length > 0) {
            return false;
        }

        return true;
    }

    private function stripSoftBreaksFromBlankSignatureSlot(\DOMElement $paragraph, \DOMXPath $docXpath): void
    {
        if (!$this->isParagraphAvailableSignatureSlot($paragraph, $docXpath)) {
            return;
        }

        $toRemove = [];
        $breaks = $docXpath->query('.//w:br[not(@w:type) or @w:type!="page"]', $paragraph);
        foreach ($breaks as $br) {
            if ($br instanceof \DOMNode) {
                $toRemove[] = $br;
            }
        }

        foreach ($toRemove as $node) {
            $node->parentNode?->removeChild($node);
        }
    }

    private function injectInlineSignatureIntoParagraph(\DOMDocument $docDom, \DOMElement $targetParagraph, \DOMElement $imageParagraph): bool
    {
        $imageRun = null;
        $imageParagraphProps = null;
        foreach ($imageParagraph->childNodes as $child) {
            if ($child instanceof \DOMElement && $child->localName === 'pPr') {
                $imageParagraphProps = $child;
                continue;
            }
            if ($child instanceof \DOMElement && $child->localName === 'r') {
                $imageRun = $child;
                break;
            }
        }
        if (!$imageRun) return false;

        $toRemove = [];
        foreach ($targetParagraph->childNodes as $child) {
            $toRemove[] = $child;
        }
        foreach ($toRemove as $node) {
            $targetParagraph->removeChild($node);
        }

        if ($imageParagraphProps instanceof \DOMElement) {
            $targetParagraph->appendChild($docDom->importNode($imageParagraphProps, true));
        }
        $targetParagraph->appendChild($docDom->importNode($imageRun, true));
        return true;
    }

    private function countLeadingTabsInParagraph(\DOMElement $paragraph): int
    {
        $tabs = 0;
        $ignorableParagraphMarkers = [
            'bookmarkStart',
            'bookmarkEnd',
            'proofErr',
            'permStart',
            'permEnd',
            'commentRangeStart',
            'commentRangeEnd',
            'moveFromRangeStart',
            'moveFromRangeEnd',
            'moveToRangeStart',
            'moveToRangeEnd',
        ];

        foreach ($paragraph->childNodes as $child) {
            if ($child instanceof \DOMElement && $child->localName === 'pPr') {
                continue;
            }

            if ($child instanceof \DOMElement && in_array($child->localName, $ignorableParagraphMarkers, true)) {
                continue;
            }

            if (!($child instanceof \DOMElement) || $child->localName !== 'r') {
                if ($child instanceof \DOMText && trim((string) $child->nodeValue) === '') {
                    continue;
                }
                break;
            }

            foreach ($child->childNodes as $runChild) {
                if (!($runChild instanceof \DOMElement)) {
                    continue;
                }
                if ($runChild->localName === 'rPr') {
                    continue;
                }
                if (in_array($runChild->localName, $ignorableParagraphMarkers, true)) {
                    continue;
                }
                if ($runChild->localName === 'tab') {
                    $tabs++;
                    continue;
                }
                if ($runChild->localName === 't' && trim((string) $runChild->textContent) === '') {
                    continue;
                }

                return $tabs;
            }
        }

        return $tabs;
    }

    private function isParagraphVisuallyEmpty(\DOMElement $paragraph, \DOMXPath $docXpath): bool
    {
        $texts = $docXpath->query('.//w:t', $paragraph);
        $joined = '';
        foreach ($texts as $t) {
            $joined .= (string) $t->textContent;
        }

        if (trim(str_replace("\xC2\xA0", ' ', $joined)) !== '') {
            return false;
        }

        // If paragraph has drawing/object, keep it.
        $hasDrawing = $docXpath->query('.//w:drawing|.//w:object|.//w:pict', $paragraph);
        if ($hasDrawing && $hasDrawing->length > 0) {
            return false;
        }

        return true;
    }

    /**
     * Scan entire docx for unresolved placeholders like {{SOMETHING}}.
     *
     * @return array<int,string>
     */
    private function scanUnresolvedPlaceholders(string $docxPath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($docxPath) !== true) return [];

        $keys = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!is_string($name)) continue;
            if (!preg_match('#^word/(document|header\\d+|footer\\d+)\\.xml$#', $name)) continue;
            $xml = $zip->getFromIndex($i);
            if (!is_string($xml)) continue;
            $text = preg_replace('/<[^>]+>/', '', $xml) ?? '';
            if ($text === '') continue;
            if (preg_match_all('/\\{\\{\\s*([^{}]+?)\\s*\\}\\}/u', $text, $m)) {
                foreach ($m[1] as $raw) {
                    $key = PlaceholderKeyNormalizer::normalize($raw);
                    if ($key) $keys[$key] = true;
                }
            }
        }

        $zip->close();
        $unique = array_keys($keys);
        sort($unique);
        return $unique;
    }

    private function convertDocxToPdf(string $docxTmpPath): ?string
    {
        // Fidelity first and only: LibreOffice.
        $soffice = $this->findSofficeBinary();
        if ($soffice) {
            $pdf = $this->convertDocxToPdfViaSoffice($docxTmpPath, $soffice);
            if ($pdf) {
                return $pdf;
            }
        }

        // Windows fallback: use installed Microsoft Word automation via VBScript late binding.
        return $this->convertDocxToPdfViaWordVbs($docxTmpPath);
    }

    private function convertDocxToPdfViaSoffice(string $docxTmpPath, string $soffice): ?string
    {
        $tmpDir = storage_path('app/tmp/docx');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }

        $expected = $tmpDir.'/'.pathinfo($docxTmpPath, PATHINFO_FILENAME).'.pdf';
        for ($attempt = 1; $attempt <= 2; $attempt++) {
            @unlink($expected);

            // Isolated LibreOffice profile avoids first-run/profile-lock failures.
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
                    $docxTmpPath,
                ]);

                if (!$result->successful() || !is_file($expected)) {
                    Log::warning('doc.preview.soffice_failed', [
                        'attempt' => $attempt,
                        'docx' => $docxTmpPath,
                        'expected_pdf' => $expected,
                        'exit_code' => $result->exitCode(),
                        'stdout' => trim($result->output()),
                        'stderr' => trim($result->errorOutput()),
                    ]);
                    if ($attempt < 2) {
                        usleep(300000);
                        continue;
                    }
                    return null;
                }

                $tmpPdf = $tmpDir.'/'.uniqid('out_', true).'.pdf';
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

    public function warmUpPdfConverter(): bool
    {
        $tmpDir = storage_path('app/tmp/docx');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }

        $docxTmp = $tmpDir.'/'.uniqid('warmup_', true).'.docx';
        $pdfTmp = null;

        try {
            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            $section = $phpWord->addSection();
            $section->addText('ULT FKIP Unila converter warm-up');
            $section->addText('Generated at '.now()->toDateTimeString());

            $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save($docxTmp);

            $pdfTmp = $this->convertDocxToPdf($docxTmp);
            if (!$pdfTmp || !is_file($pdfTmp)) {
                Log::warning('doc.preview.warmup_failed', [
                    'docx' => $docxTmp,
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('doc.preview.warmup_exception', [
                'error' => $e->getMessage(),
            ]);
            return false;
        } finally {
            @unlink($docxTmp);
            if (is_string($pdfTmp) && $pdfTmp !== '') {
                @unlink($pdfTmp);
            }
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
                // continue searching
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
                // continue searching
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

    private function convertDocxToPdfViaWordVbs(string $docxTmpPath): ?string
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return null;
        }

        $cscript = $this->findCscriptBinary();
        if (!$cscript) {
            return null;
        }

        $tmpDir = storage_path('app/tmp/docx');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }
        $tmpPdf = $tmpDir.'/'.uniqid('out_', true).'.pdf';
        $tmpVbs = $tmpDir.'/'.uniqid('word_export_', true).'.vbs';
        $vbs = <<<'VBS'
On Error Resume Next
Dim word, doc, inFile, outFile
inFile = WScript.Arguments(0)
outFile = WScript.Arguments(1)
Set word = CreateObject("Word.Application")
If Err.Number <> 0 Then
  WScript.Quit 2
End If
word.Visible = False
word.DisplayAlerts = 0
Set doc = word.Documents.Open(inFile, False, True)
If Err.Number <> 0 Then
  word.Quit
  WScript.Quit 3
End If
' wdExportFormatPDF = 17
call doc.ExportAsFixedFormat(outFile, 17)
If Err.Number <> 0 Then
  doc.Close False
  word.Quit
  WScript.Quit 4
End If
doc.Close False
word.Quit
WScript.Quit 0
VBS;

        try {
            file_put_contents($tmpVbs, $vbs);
            $result = Process::timeout(180)->run([
                $cscript,
                '//NoLogo',
                $tmpVbs,
                $docxTmpPath,
                $tmpPdf,
            ]);

            if (!$result->successful() || !is_file($tmpPdf)) {
                Log::warning('doc.preview.word_vbs_failed', [
                    'docx' => $docxTmpPath,
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
            Log::warning('doc.preview.word_vbs_exception', [
                'docx' => $docxTmpPath,
                'pdf' => $tmpPdf,
                'cscript' => $cscript,
                'error' => $e->getMessage(),
            ]);
            return null;
        } finally {
            @unlink($tmpVbs);
        }
    }

    /**
     * Compute centered "cover" crop for Word DrawingML srcRect.
     *
     * @return array{l:int,t:int,r:int,b:int}
     */
    private function resolveImageCropRectForFrame(string $imageFile, int $frameWEmu, int $frameHEmu): array
    {
        $out = ['l' => 0, 't' => 0, 'r' => 0, 'b' => 0];
        if ($frameWEmu <= 0 || $frameHEmu <= 0) {
            return $out;
        }

        $dim = @getimagesize($imageFile);
        $imgW = (int) ($dim[0] ?? 0);
        $imgH = (int) ($dim[1] ?? 0);
        if ($imgW < 1 || $imgH < 1) {
            return $out;
        }

        $imgRatio = $imgW / $imgH;
        $frameRatio = $frameWEmu / $frameHEmu;
        $scale = 100000.0;

        if (abs($imgRatio - $frameRatio) < 0.0001) {
            return $this->applyImageCropBleed($out, $imgW, $imgH);
        }

        // Wider image: crop left+right. Taller image: crop top+bottom.
        if ($imgRatio > $frameRatio) {
            $visibleW = $imgH * $frameRatio;
            $cropEach = max(0.0, min(0.499999, (($imgW - $visibleW) / 2.0) / $imgW));
            $crop = (int) round($cropEach * $scale);
            $out['l'] = $crop;
            $out['r'] = $crop;
            return $this->applyImageCropBleed($out, $imgW, $imgH);
        }

        $visibleH = $imgW / $frameRatio;
        $cropEach = max(0.0, min(0.499999, (($imgH - $visibleH) / 2.0) / $imgH));
        $crop = (int) round($cropEach * $scale);
        $out['t'] = $crop;
        $out['b'] = $crop;

        return $this->applyImageCropBleed($out, $imgW, $imgH);
    }

    /**
     * Keep auto-signature height consistent and scale width by original image ratio.
     *
     * @return array{0:int,1:int} [wEmu, hEmu]
     */
    private function resolveAutoSignatureInlineExtent(string $imageFile, int $fixedHeightEmu): array
    {
        $hEmu = max(1, $fixedHeightEmu);
        $fallbackWEmu = (int) round(160 * 12700);

        $dim = @getimagesize($imageFile);
        $imgW = (int) ($dim[0] ?? 0);
        $imgH = (int) ($dim[1] ?? 0);
        if ($imgW < 1 || $imgH < 1) {
            return [$fallbackWEmu, $hEmu];
        }

        $ratio = $imgW / $imgH;
        if (!is_finite($ratio) || $ratio <= 0) {
            return [$fallbackWEmu, $hEmu];
        }

        $wEmu = max(1, (int) round($hEmu * $ratio));
        return [$wEmu, $hEmu];
    }

    /**
     * Apply a tiny symmetric bleed crop so rendered image fully touches frame edges.
     *
     * @param array{l:int,t:int,r:int,b:int} $cropRect
     * @return array{l:int,t:int,r:int,b:int}
     */
    private function applyImageCropBleed(array $cropRect, int $imgW, int $imgH): array
    {
        // Around 2 source pixels per side to avoid thin anti-alias border in PDF renderer.
        $bleedX = max(1, (int) ceil((2.0 / max(1, $imgW)) * 100000.0));
        $bleedY = max(1, (int) ceil((2.0 / max(1, $imgH)) * 100000.0));

        $cropRect['l'] = max(0, min(100000, (int) ($cropRect['l'] ?? 0) + $bleedX));
        $cropRect['r'] = max(0, min(100000, (int) ($cropRect['r'] ?? 0) + $bleedX));
        $cropRect['t'] = max(0, min(100000, (int) ($cropRect['t'] ?? 0) + $bleedY));
        $cropRect['b'] = max(0, min(100000, (int) ($cropRect['b'] ?? 0) + $bleedY));

        // Keep the visible area valid after adding bleed.
        $maxPair = 99000;
        $sumX = $cropRect['l'] + $cropRect['r'];
        if ($sumX > $maxPair && $sumX > 0) {
            $scaleX = $maxPair / $sumX;
            $cropRect['l'] = (int) floor($cropRect['l'] * $scaleX);
            $cropRect['r'] = (int) floor($cropRect['r'] * $scaleX);
        }

        $sumY = $cropRect['t'] + $cropRect['b'];
        if ($sumY > $maxPair && $sumY > 0) {
            $scaleY = $maxPair / $sumY;
            $cropRect['t'] = (int) floor($cropRect['t'] * $scaleY);
            $cropRect['b'] = (int) floor($cropRect['b'] * $scaleY);
        }

        return $cropRect;
    }

    private function resolveSignatureImageExtension(string $pathHint, string $localFile): string
    {
        $allowed = ['png' => true, 'jpg' => true, 'jpeg' => true, 'jpe' => true, 'jfif' => true, 'webp' => true];
        $fromPath = strtolower((string) pathinfo($pathHint, PATHINFO_EXTENSION));
        if ($fromPath !== '' && isset($allowed[$fromPath])) {
            return $fromPath;
        }

        $mime = @mime_content_type($localFile) ?: '';
        return match ($mime) {
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            default => 'png',
        };
    }

    private function resolveTemplatePathForRequest(UltRequest $request): ?string
    {
        $snapshot = is_array($request->data?->document_snapshot_json)
            ? $request->data->document_snapshot_json
            : [];
        $snapshotPath = trim((string) data_get($snapshot, 'template.file_path', ''));
        if ($snapshotPath !== '') {
            return $snapshotPath;
        }

        $tpl = $request->service->templates->firstWhere('type', ServiceTemplateType::MAIN_DOCX);
        return $tpl ? (string) $tpl->file_path : null;
    }

    private function resolveRequestPlaceholderDefinitions(UltRequest $request): \Illuminate\Support\Collection
    {
        $snapshot = is_array($request->data?->document_snapshot_json)
            ? $request->data->document_snapshot_json
            : [];
        $placeholders = $snapshot['placeholders'] ?? null;
        if (!is_array($placeholders) || empty($placeholders)) {
            return $request->service->placeholders->values();
        }

        return collect($placeholders)
            ->filter(fn ($ph) => is_array($ph) && trim((string) ($ph['placeholder_key'] ?? '')) !== '')
            ->map(function (array $ph) {
                return (object) [
                    'placeholder_key' => (string) ($ph['placeholder_key'] ?? ''),
                    'source_type' => ($ph['source_type'] ?? null) !== null
                        ? PlaceholderSourceType::from((string) $ph['source_type'])
                        : null,
                    'source_ref' => $ph['source_ref'] ?? null,
                    'is_required' => (bool) ($ph['is_required'] ?? false),
                    'notes' => $ph['notes'] ?? null,
                ];
            })
            ->values();
    }

    private function resolveRequestFieldDefinitions(UltRequest $request): \Illuminate\Support\Collection
    {
        $snapshot = is_array($request->data?->document_snapshot_json)
            ? $request->data->document_snapshot_json
            : [];
        $fields = $snapshot['fields'] ?? null;
        if (!is_array($fields) || empty($fields)) {
            return $request->service->fields->sortBy('sort_order')->values();
        }

        return collect($fields)
            ->filter(fn ($field) => is_array($field))
            ->map(function (array $field) {
                return (object) [
                    'id' => (int) ($field['service_field_id'] ?? 0),
                    'key' => (string) ($field['key'] ?? ''),
                    'maps_to_placeholder_key' => $field['maps_to_placeholder_key'] ?? null,
                    'label_id' => (string) ($field['label_id'] ?? ''),
                    'label_en' => $field['label_en'] ?? null,
                    'type' => (string) ($field['type'] ?? 'text'),
                    'required' => (bool) ($field['required'] ?? false),
                    'rules_json' => is_array($field['rules_json'] ?? null) ? $field['rules_json'] : null,
                    'options_json' => is_array($field['options_json'] ?? null) ? $field['options_json'] : null,
                    'sort_order' => (int) ($field['sort_order'] ?? 0),
                ];
            })
            ->sortBy('sort_order')
            ->values();
    }

    private function resolveRequestSignerDefinitions(UltRequest $request): \Illuminate\Support\Collection
    {
        $snapshot = is_array($request->data?->document_snapshot_json)
            ? $request->data->document_snapshot_json
            : [];
        $signers = $snapshot['signers'] ?? null;
        if (!is_array($signers) || empty($signers)) {
            return $request->service->signers->sortBy('order_index')->values();
        }

        return collect($signers)
            ->filter(fn ($signer) => is_array($signer))
            ->map(function (array $signer) {
                return (object) [
                    'role' => (string) ($signer['role'] ?? ''),
                    'custom_label' => $signer['custom_label'] ?? null,
                    'order_index' => (int) ($signer['order_index'] ?? 0),
                    'is_required' => (bool) ($signer['is_required'] ?? true),
                    'requires_signature_upload' => (bool) ($signer['requires_signature_upload'] ?? false),
                    'signature_file_types' => is_array($signer['signature_file_types'] ?? null) ? $signer['signature_file_types'] : null,
                    'signature_max_size_kb' => isset($signer['signature_max_size_kb']) ? (int) $signer['signature_max_size_kb'] : null,
                ];
            })
            ->sortBy('order_index')
            ->values();
    }
}
