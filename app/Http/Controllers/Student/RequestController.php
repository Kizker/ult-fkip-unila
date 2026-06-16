<?php

namespace App\Http\Controllers\Student;

use App\Enums\AttachmentKind;
use App\Enums\AttachmentVerifiedStatus;
use App\Enums\RequestSignoffStatus;
use App\Enums\RequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Request as UltRequest;
use App\Models\RequestData;
use App\Models\RequestFieldValue;
use App\Models\RequestSignoff;
use App\Models\Service;
use App\Models\ServiceField;
use App\Models\Unit;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\HtmlSanitizer;
use App\Services\Documents\CertificateDocumentService;
use App\Services\Documents\DocumentRequestInitializer;
use App\Services\RequestWorkflowService;
use App\Services\Uploads\UniqueUploadNamer;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RequestController extends Controller
{
    /**
     * Role pool shown when pemohon selects DOSEN signer target.
     *
     * @var array<int,string>
     */
    private const DOSEN_SIGNER_SELECTABLE_ROLES = [
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
        private readonly RequestWorkflowService $workflow,
        private readonly AuditLogger $audit,
        private readonly HtmlSanitizer $htmlSanitizer,
        private readonly CertificateDocumentService $certificateDocs,
        private readonly DocumentRequestInitializer $docInit,
        private readonly UniqueUploadNamer $uploadNamer,
    ) {}

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $recent = UltRequest::query()
            ->where('student_id', $user->id)
            ->with('service')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(5)
            ->withQueryString();

        $rawCounts = UltRequest::query()
            ->selectRaw('current_status, COUNT(*) as c')
            ->where('student_id', $user->id)
            ->groupBy('current_status')
            ->pluck('c', 'current_status');

        $counts = $rawCounts
            ->reduce(function (array $carry, int $count, string $status) {
                $normalizedStatus = $this->normalizeStudentStatusFilter((string) $status);
                $carry[$normalizedStatus] = ($carry[$normalizedStatus] ?? 0) + $count;
                return $carry;
            }, []);

        $order = [
            RequestStatus::DIAJUKAN->value => 1,
            RequestStatus::REVIEW_ULT->value => 2,
            RequestStatus::PERLU_PERBAIKAN->value => 3,
            RequestStatus::IN_SIGNING->value => 4,
            RequestStatus::SELESAI->value => 5,
            RequestStatus::DITOLAK_ADMIN->value => 6,
        ];

        uksort($counts, function($a, $b) use ($order) {
            $orderA = $order[$a] ?? 99;
            $orderB = $order[$b] ?? 99;
            if ($orderA === $orderB) {
                return strcmp($a, $b);
            }
            return $orderA <=> $orderB;
        });

        return view('student.dashboard', compact('recent', 'counts'));
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $status = $request->query('status');
        $serviceId = $request->query('service_id');

        $statusFilterValues = $this->expandStudentStatusFilterValues($status);
        $overallTotal = UltRequest::query()
            ->where('student_id', $user->id)
            ->count();

        $rawStatusTotals = UltRequest::query()
            ->selectRaw('current_status, COUNT(*) as c')
            ->where('student_id', $user->id)
            ->groupBy('current_status')
            ->pluck('c', 'current_status');

        $statusTotals = $rawStatusTotals
            ->reduce(function (array $carry, int $count, string $currentStatus) {
                $normalizedStatus = $this->normalizeStudentStatusFilter((string) $currentStatus);
                $carry[$normalizedStatus] = ($carry[$normalizedStatus] ?? 0) + $count;
                return $carry;
            }, []);

        $order = [
            RequestStatus::DIAJUKAN->value => 1,
            RequestStatus::REVIEW_ULT->value => 2,
            RequestStatus::PERLU_PERBAIKAN->value => 3,
            RequestStatus::IN_SIGNING->value => 4,
            RequestStatus::SELESAI->value => 5,
            RequestStatus::DITOLAK_ADMIN->value => 6,
        ];

        uksort($statusTotals, function($a, $b) use ($order) {
            $orderA = $order[$a] ?? 99;
            $orderB = $order[$b] ?? 99;
            if ($orderA === $orderB) {
                return strcmp($a, $b);
            }
            return $orderA <=> $orderB;
        });

        $items = UltRequest::query()
            ->where('student_id', $user->id)
            ->when(!empty($statusFilterValues), fn($q) => $q->whereIn('current_status', $statusFilterValues))
            ->when($serviceId, fn($q) => $q->where('service_id', $serviceId))
            ->with('service')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $services = Service::query()->where('is_active', true)->orderBy('title_id')->get();
        $statusOptions = $this->studentStatusFilterOptions();

        if ($request->boolean('_infinite')) {
            return response()->json([
                'html' => view('student.requests._items', [
                    'items' => $items,
                ])->render(),
                'next_page_url' => $items->nextPageUrl(),
                'has_more' => $items->hasMorePages(),
            ]);
        }

        return view('student.requests.index', compact(
            'items',
            'services',
            'status',
            'serviceId',
            'statusOptions',
            'overallTotal',
            'statusTotals',
        ));
    }

    /**
     * @return array<int,string>
     */
    private function expandStudentStatusFilterValues(?string $status): array
    {
        $normalized = $this->normalizeStudentStatusFilter((string) $status);

        return match ($normalized) {
            RequestStatus::SELESAI->value => [
                RequestStatus::SELESAI->value,
                RequestStatus::COMPLETED->value,
            ],
            RequestStatus::IN_SIGNING->value => [
                RequestStatus::IN_SIGNING->value,
                RequestStatus::READY_FOR_FINAL->value,
            ],
            default => $normalized !== '' ? [$normalized] : [],
        };
    }

    private function normalizeStudentStatusFilter(string $status): string
    {
        $value = strtoupper(trim($status));

        return match ($value) {
            RequestStatus::COMPLETED->value => RequestStatus::SELESAI->value,
            RequestStatus::READY_FOR_FINAL->value => RequestStatus::IN_SIGNING->value,
            default => $value,
        };
    }

    /**
     * @return array<int,array{value:string,label:string}>
     */
    private function studentStatusFilterOptions(): array
    {
        $labels = [
            RequestStatus::DIAJUKAN->value => 'Diajukan',
            RequestStatus::PERLU_PERBAIKAN->value => 'Perlu Perbaikan',
            RequestStatus::DIVERIFIKASI_UNIT->value => 'Diverifikasi Unit',
            RequestStatus::MENUNGGU_TTD_UNIT->value => 'Menunggu TTD Unit',
            RequestStatus::REVIEW_ULT->value => 'Review ULT',
            RequestStatus::MENUNGGU_TTD_FAKULTAS->value => 'Menunggu TTD Fakultas',
            RequestStatus::NOMOR_DOKUMEN_TERBIT->value => 'Nomor Terbit',
            RequestStatus::DIPROSES->value => 'Diproses',
            RequestStatus::SELESAI->value => 'Selesai',
            RequestStatus::DITOLAK->value => 'Ditolak',
            RequestStatus::GATE_VERIFIED->value => 'Gate Verified',
            RequestStatus::NOMOR_SURAT_FILLED->value => 'Nomor Surat Diisi',
            RequestStatus::IN_SIGNING->value => 'Penandatangan',
            RequestStatus::REJECTED_IN_SIGNING->value => 'Ditolak TTD',
            RequestStatus::READY_FOR_FINAL->value => 'Penandatangan',
            RequestStatus::DITOLAK_ADMIN->value => 'Ditolak Admin',
        ];

        $seen = [];
        $options = [];

        foreach (RequestStatus::cases() as $status) {
            $value = $this->normalizeStudentStatusFilter($status->value);
            if (isset($seen[$value])) {
                continue;
            }

            $seen[$value] = true;
            $options[] = [
                'value' => $value,
                'label' => $labels[$value] ?? str_replace('_', ' ', $value),
            ];
        }

        return $options;
    }

    public function create(Service $service, Request $request)
    {
        abort_unless($service->is_active, 404);
        abort_unless($service->status === null || $service->status?->value === 'PUBLISHED', 404);
        $service->loadMissing(['fields', 'placeholders', 'signers']);
        $isCertificateService = $this->certificateDocs->isCertificateService($service);
        $this->ensureFormFieldsFromPlaceholders($service);
        $fields = $service->fields()->orderBy('sort_order')->get();

        $customSigners = $service->signers
            ->filter(fn ($s) => strtoupper((string) $s->role) === 'CUSTOM')
            ->sortBy('order_index')
            ->values();

        $pemohonSignatureSigners = $service->signers
            ->filter(fn ($s) => strtoupper((string) $s->role) === 'PEMOHON')
            ->sortBy('order_index')
            ->values();

        $dosenSelectSigners = $service->signers
            ->filter(fn ($s) => strtoupper((string) $s->role) === 'DOSEN')
            ->sortBy('order_index')
            ->values();
        $dosenSignerOptions = $this->eligibleDosenSignerUsers();
        $certificateInternalSignerOptions = $isCertificateService
            ? $this->certificateDocs->internalSignerOptions()
            : collect();
        $certificateEditorState = [
            'is_certificate' => $isCertificateService,
            'source_attachment' => null,
            'source_original_name' => null,
            'signers' => [],
        ];

        return view('student.requests.create', compact(
            'service',
            'fields',
            'isCertificateService',
            'customSigners',
            'pemohonSignatureSigners',
            'dosenSelectSigners',
            'dosenSignerOptions',
            'certificateInternalSignerOptions',
            'certificateEditorState',
        ));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'service_id' => ['required','exists:services,id'],
        ]);

        $service = Service::with(['fields', 'placeholders', 'workflow', 'signers', 'templates'])->findOrFail($data['service_id']);
        $isCertificateService = $this->certificateDocs->isCertificateService($service);
        abort_unless($service->is_active, 404);
        abort_unless($service->status === null || $service->status?->value === 'PUBLISHED', 404);
        $this->ensureFormFieldsFromPlaceholders($service);
        $service->load('fields');

        // Build dynamic rules from service_fields
        $rules = [];
        foreach ($service->fields as $field) {
            $r = [];
            if ($field->required) $r[] = 'required';
            else $r[] = 'nullable';

            // type-based
            switch ($field->type) {
                case 'text': $r[] = 'string'; $r[] = 'max:2000'; break;
                case 'textarea': $r[] = 'string'; $r[] = 'max:5000'; break;
                case 'richtext': $r[] = 'string'; $r[] = 'max:50000'; break;
                case 'number': $r[] = 'numeric'; break;
                case 'date': $r[] = 'date'; break;
                case 'select': $r[] = 'string'; break;
                case 'json': $r[] = 'string'; break;
                case 'file': $r[] = 'file'; break;
                default: $r[] = 'string'; $r[] = 'max:2000';
            }
            // merge rules_json
            if (is_array($field->rules_json)) $r = array_merge($r, $field->rules_json);

            $rules['fields.'.$field->id] = $r;
        }

        if ($service->allow_general_attachments) {
            $rules['attachments'] = ['nullable', 'array'];
            $rules['attachments.*'] = ['nullable', 'file', 'max:'.(config('ult.upload.max_size_mb') * 1024)];
        }

        $customSignatureSigners = $service->signers
            ->filter(fn ($s) => strtoupper((string) $s->role) === 'CUSTOM')
            ->sortBy('order_index')
            ->values();

        $pemohonSignatureSigners = $service->signers
            ->filter(fn ($s) => strtoupper((string) $s->role) === 'PEMOHON')
            ->sortBy('order_index')
            ->values();

        $dosenSelectSigners = $service->signers
            ->filter(fn ($s) => strtoupper((string) $s->role) === 'DOSEN')
            ->sortBy('order_index')
            ->values();

        if (!$isCertificateService && $customSignatureSigners->isNotEmpty()) {
            $rules['custom_signatures'] = ['required', 'array'];
            foreach ($customSignatureSigners as $signer) {
                $idx = (int) $signer->order_index;
                [$types, $maxKb] = $this->resolveSignerSignatureConstraints($signer);

                $rules["custom_signatures.$idx"] = [
                    'required',
                    'file',
                    'mimetypes:'.implode(',', $types),
                    'max:'.$maxKb,
                ];
            }
        }

        if (!$isCertificateService && $pemohonSignatureSigners->isNotEmpty()) {
            $rules['pemohon_signatures'] = ['required', 'array'];
            foreach ($pemohonSignatureSigners as $signer) {
                $idx = (int) $signer->order_index;
                [$types, $maxKb] = $this->resolveSignerSignatureConstraints($signer);

                $rules["pemohon_signatures.$idx"] = [
                    'required',
                    'file',
                    'mimetypes:'.implode(',', $types),
                    'max:'.$maxKb,
                ];
            }
        }

        if (!$isCertificateService && $dosenSelectSigners->isNotEmpty()) {
            $allowedSignerIds = $this->eligibleDosenSignerUserIds();
            $rules['dosen_signers'] = ['required', 'array'];
            foreach ($dosenSelectSigners as $signer) {
                $idx = (int) $signer->order_index;
                $rules["dosen_signers.$idx"] = [
                    (bool) ($signer->is_required ?? true) ? 'required' : 'nullable',
                    'integer',
                    Rule::in($allowedSignerIds),
                ];
            }
        }

        $payload = $request->validate($rules);

        $wf = $service->workflow;
        $steps = $wf ? $this->workflow->getSteps($wf) : [];
        $firstStep = $steps[0]['key'] ?? 'submit';

        $unit = $this->resolveInitialUnit($user, $wf);

        $ult = UltRequest::create([
            'service_id' => $service->id,
            'student_id' => $user->id,
            'current_status' => RequestStatus::DIAJUKAN,
            'current_step_key' => $firstStep,
            'current_unit_id' => $unit?->id,
            'submitted_at' => now(),
        ]);

        $dataJson = [];
        $attachmentsJson = [];

        foreach ($service->fields as $field) {
            $value = $payload['fields'][$field->id] ?? null;

            $row = [
                'request_id' => $ult->id,
                'service_field_id' => $field->id,
                'value_text' => null,
                'value_json' => null,
                'value_date' => null,
                'value_number' => null,
            ];

            if ($value === null || $value === '') {
                // keep null
            } else {
                switch ($field->type) {
                    case 'number': $row['value_number'] = (float) $value; break;
                    case 'date': $row['value_date'] = $value; break;
                    case 'richtext': $row['value_text'] = $this->htmlSanitizer->clean((string) $value); break;
                    case 'file':
                        // Store file as private attachment and reference it.
                        $file = $request->file('fields.'.$field->id);
                        if ($file) {
                            $disk = config('ult.private_disk');
                            $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
                            $blocked = ['php','phtml','phar','exe','sh','bat','cmd','js','html','htm'];
                            if (in_array($ext, $blocked, true)) {
                                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'File type blocked.');
                            }

                            $path = $this->uploadNamer->makePathForUploadedFile(
                                $disk,
                                "requests/{$ult->id}/input",
                                "input_{$field->key}",
                                $file,
                            );
                            $stream = fopen($file->getRealPath(), 'rb');
                            Storage::disk($disk)->put($path, $stream);
                            if (is_resource($stream)) fclose($stream);

                            $sha = hash_file('sha256', $file->getRealPath());
                            $attachment = $ult->attachments()->create([
                                'uploaded_by' => $user->id,
                                'kind' => \App\Enums\AttachmentKind::input,
                                'service_field_id' => $field->id,
                                'original_name' => $file->getClientOriginalName(),
                                'stored_path' => $path,
                                'mime' => $file->getMimeType(),
                                'size' => $file->getSize(),
                                'sha256' => $sha,
                                'verified_status' => \App\Enums\AttachmentVerifiedStatus::pending,
                            ]);

                            $attachmentsJson[$field->key] = $attachment->id;
                            $row['value_json'] = ['attachment_id' => $attachment->id, 'original' => $attachment->original_name];
                        }
                        break;
                    case 'json': $row['value_json'] = is_array($value) ? $value : ['value' => $value]; break;
                    default: $row['value_text'] = (string) $value;
                }
            }

            RequestFieldValue::create($row);

            // Snapshot data_json (for document module placeholder rendering)
            if ($field->type !== 'file') {
                if ($row['value_text'] !== null) $dataJson[$field->key] = $row['value_text'];
                elseif ($row['value_number'] !== null) $dataJson[$field->key] = $row['value_number'];
                elseif ($row['value_date'] !== null) $dataJson[$field->key] = $row['value_date'];
                elseif ($row['value_json'] !== null) $dataJson[$field->key] = $row['value_json'];
            } else {
                if (isset($attachmentsJson[$field->key])) {
                    $dataJson[$field->key] = ['attachment_id' => $attachmentsJson[$field->key]];
                }
            }
        }

        $this->audit->log('request.created', 'requests', (string) $ult->id, [
            'service_id' => $service->id,
            'unit_id' => $ult->current_unit_id,
        ]);

        if ($isCertificateService) {
            try {
                $this->certificateDocs->persistSubmissionData($ult, $user, $request, $dataJson, $attachmentsJson, false);
            } catch (\Throwable $e) {
                $ult->delete();
                throw $e;
            }
        } else {
            // Initialize document-module data/signoffs if this service uses templates
            $this->docInit->initialize($ult, $dataJson, $attachmentsJson);
            $this->storeGeneralAttachments($ult, $user->id, $request->file('attachments', []));

            $this->assignSelectedDosenSigners(
                $ult,
                (array) ($payload['dosen_signers'] ?? []),
                $dosenSelectSigners,
                $user,
                'pengajuan',
            );
            $this->applyCustomSignatureUploads($ult, $user, $request, $customSignatureSigners);
            $this->applyPemohonSignatureUploads($ult, $user, $request, $pemohonSignatureSigners);
        }

        // Notify status change using workflow service (records history + notifications)
        $this->workflow->transition($ult, RequestStatus::DIAJUKAN, $user, 'Permohonan diajukan', $firstStep, $unit);

        return redirect()->route('student.requests.show', $ult)->with('status', __('app.request_created'));
    }

    public function show(UltRequest $request, Request $http)
    {
        Gate::authorize('view', $request);

        $request->load([
            'service.fields',
            'service.templates',
            'service.workflow',
            'service.signers',
            'fieldValues.field',
            'attachments',
            'histories.actor',
            'notes.actor',
            'documentNumber',
            'outputs',
            'signoffs',
        ]);
        $certificateEditorState = $this->certificateDocs->editorState($request);
        $isCertificateService = (bool) ($certificateEditorState['is_certificate'] ?? false);
        $requestFields = $this->resolveRequestFieldDefinitions($request);
        $requestSigners = $this->resolveRequestSignerDefinitions($request);

        return view('student.requests.show', [
            'req' => $request,
            'requestFields' => $requestFields,
            'requestSigners' => $requestSigners,
            'dosenSignerOptions' => $this->eligibleDosenSignerUsers(),
            'certificateEditorState' => $certificateEditorState,
            'certificateInternalSignerOptions' => $isCertificateService ? $this->certificateDocs->internalSignerOptions() : collect(),
        ]);
    }

    public function previewCertificateSource(Request $http)
    {
        $payload = $http->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'certificate_source_pptx' => ['required', 'file', 'mimes:pptx', 'max:20480'],
        ]);

        $service = Service::query()->findOrFail((int) $payload['service_id']);
        if (!$this->certificateDocs->isCertificateService($service)) {
            abort(422, 'Layanan ini bukan mode Sertifikat/Piagam.');
        }

        $sourceFile = $http->file('certificate_source_pptx');
        if (!$sourceFile instanceof UploadedFile) {
            abort(422, 'File sumber .pptx belum dipilih.');
        }

        $preview = $this->certificateDocs->buildUploadedSourcePreviewArtifact($sourceFile);

        $this->audit->log('doc.certificate.source.preview_before_submit', 'services', (string) $service->id, [
            'service_id' => $service->id,
            'actor_id' => $http->user()->id,
            'mime' => $preview['mime'] ?? null,
            'filename' => $preview['filename'] ?? null,
        ]);

        return response()->file($preview['path'], [
            'Content-Type' => (string) $preview['mime'],
            'Content-Disposition' => 'inline; filename="'.((string) $preview['filename']).'"',
            'Cache-Control' => 'no-store',
        ])->deleteFileAfterSend(true);
    }

    public function signaturePreview(UltRequest $request, RequestSignoff $signoff, Request $http)
    {
        Gate::authorize('view', $request);

        if ((int) $signoff->request_id !== (int) $request->id) {
            abort(404);
        }

        $role = strtoupper(trim((string) $signoff->signer_role));
        if (!in_array($role, ['PEMOHON', 'CUSTOM', 'CERT_PEMOHON', 'CERT_CUSTOM'], true)) {
            abort(404);
        }

        $path = trim((string) ($signoff->signature_file_path ?? ''));
        if ($path === '') {
            abort(404);
        }

        $disk = config('ult.private_disk');
        if (!Storage::disk($disk)->exists($path)) {
            abort(404);
        }

        $mime = (string) (Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream');
        if (!str_starts_with($mime, 'image/')) {
            abort(404);
        }

        $filename = basename($path);
        $this->audit->log('doc.signoff.signature.preview_by_student', 'request_signoffs', (string) $signoff->id, [
            'request_id' => $request->id,
            'actor_id' => $http->user()->id,
            'signer_role' => $signoff->signer_role,
        ]);

        return Storage::disk($disk)->response($path, $filename, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Cache-Control' => 'private, max-age=120',
        ]);
    }

    public function updateData(UltRequest $request, Request $http)
    {
        Gate::authorize('update', $request);

        $status = $request->current_status->value ?? $request->current_status;
        if ($status !== RequestStatus::PERLU_PERBAIKAN->value) {
            abort(422, 'Data hanya dapat diubah saat status PERLU_PERBAIKAN.');
        }

        $request->loadMissing(['service.fields', 'service.signers', 'fieldValues', 'data', 'signoffs']);
        $service = $request->service;
        if (!$service) {
            abort(404);
        }
        $isCertificateService = $this->certificateDocs->isCertificateService($service);
        $requestFields = $this->resolveRequestFieldDefinitions($request);
        $requestSigners = $this->resolveRequestSignerDefinitions($request);

        $fieldValues = $request->fieldValues->keyBy('service_field_id');
        $existingDataJson = is_array($request->data?->data_json) ? $request->data->data_json : [];
        $signoffs = $request->signoffs ?? collect();
        $customSignatureSigners = $requestSigners
            ->filter(fn ($s) => strtoupper((string) $s->role) === 'CUSTOM')
            ->sortBy('order_index')
            ->values();
        $pemohonSignatureSigners = $requestSigners
            ->filter(fn ($s) => strtoupper((string) $s->role) === 'PEMOHON')
            ->sortBy('order_index')
            ->values();
        $dosenSelectSigners = $requestSigners
            ->filter(fn ($s) => strtoupper((string) $s->role) === 'DOSEN')
            ->sortBy('order_index')
            ->values();
        $rules = [];
        foreach ($requestFields as $field) {
            if ($field->type === 'file') {
                $existingAttachmentId = $this->extractAttachmentIdFromFieldValue($fieldValues->get($field->id));
                if (!$existingAttachmentId && array_key_exists($field->key, $existingDataJson)) {
                    $existingAttachmentId = $this->extractAttachmentIdFromRaw($existingDataJson[$field->key]);
                }

                $r = [];
                if ($field->required && !$existingAttachmentId) $r[] = 'required';
                else $r[] = 'nullable';
                $r[] = 'file';

                if (is_array($field->rules_json)) $r = array_merge($r, $field->rules_json);

                $rules['fields.'.$field->id] = $r;
                continue;
            }

            $r = [];
            if ($field->required) $r[] = 'required';
            else $r[] = 'nullable';

            switch ($field->type) {
                case 'textarea':
                    $r[] = 'string'; $r[] = 'max:5000'; break;
                case 'richtext':
                    $r[] = 'string'; $r[] = 'max:50000'; break;
                case 'number':
                    $r[] = 'numeric'; break;
                case 'date':
                    $r[] = 'date'; break;
                case 'select':
                    $r[] = 'string'; break;
                case 'json':
                    $r[] = 'string'; break;
                default:
                    $r[] = 'string'; $r[] = 'max:2000';
            }

            if (is_array($field->rules_json)) $r = array_merge($r, $field->rules_json);
            $rules['fields.'.$field->id] = $r;
        }

        if (!$isCertificateService && $customSignatureSigners->isNotEmpty()) {
            $rules['custom_signatures'] = ['nullable', 'array'];
            foreach ($customSignatureSigners as $signer) {
                $idx = (int) $signer->order_index;
                [$types, $maxKb] = $this->resolveSignerSignatureConstraints($signer);
                $existingSignoff = $signoffs->first(function ($so) use ($idx, $signer) {
                    return (int) ($so->order_index ?? 0) === $idx
                        && strtoupper(trim((string) ($so->signer_role ?? ''))) === strtoupper(trim((string) ($signer->role ?? '')));
                });
                $hasExistingSignature = trim((string) ($existingSignoff->signature_file_path ?? '')) !== '';

                $rules["custom_signatures.$idx"] = [
                    $hasExistingSignature ? 'nullable' : 'required',
                    'file',
                    'mimetypes:'.implode(',', $types),
                    'max:'.$maxKb,
                ];
            }
        }

        if (!$isCertificateService && $pemohonSignatureSigners->isNotEmpty()) {
            $rules['pemohon_signatures'] = ['nullable', 'array'];
            foreach ($pemohonSignatureSigners as $signer) {
                $idx = (int) $signer->order_index;
                [$types, $maxKb] = $this->resolveSignerSignatureConstraints($signer);
                $existingSignoff = $signoffs->first(function ($so) use ($idx, $signer) {
                    return (int) ($so->order_index ?? 0) === $idx
                        && strtoupper(trim((string) ($so->signer_role ?? ''))) === strtoupper(trim((string) ($signer->role ?? '')));
                });
                $hasExistingSignature = trim((string) ($existingSignoff->signature_file_path ?? '')) !== '';

                $rules["pemohon_signatures.$idx"] = [
                    $hasExistingSignature ? 'nullable' : 'required',
                    'file',
                    'mimetypes:'.implode(',', $types),
                    'max:'.$maxKb,
                ];
            }
        }

        if (!$isCertificateService && $dosenSelectSigners->isNotEmpty()) {
            $allowedSignerIds = $this->eligibleDosenSignerUserIds();
            $rules['dosen_signers'] = ['nullable', 'array'];
            foreach ($dosenSelectSigners as $signer) {
                $idx = (int) $signer->order_index;
                $existingSignoff = $signoffs->first(function ($so) use ($idx, $signer) {
                    return (int) ($so->order_index ?? 0) === $idx
                        && strtoupper(trim((string) ($so->signer_role ?? ''))) === strtoupper(trim((string) ($signer->role ?? '')));
                });
                $hasSelectedSigner = (int) ($existingSignoff->signer_user_id ?? 0) > 0;

                $rules["dosen_signers.$idx"] = [
                    ((bool) ($signer->is_required ?? true) && !$hasSelectedSigner) ? 'required' : 'nullable',
                    'integer',
                    Rule::in($allowedSignerIds),
                ];
            }
        }

        if ($service->allow_general_attachments) {
            $rules['attachments'] = ['nullable', 'array'];
            $rules['attachments.*'] = ['nullable', 'file', 'max:'.(config('ult.upload.max_size_mb') * 1024)];
        }

        $payload = $http->validate($rules);

        DB::transaction(function () use ($request, $payload, $fieldValues, $http, $customSignatureSigners, $pemohonSignatureSigners, $dosenSelectSigners, $isCertificateService, $requestFields) {
            $dataJson = is_array($request->data?->data_json) ? $request->data->data_json : [];
            $attachmentsJson = is_array($request->data?->attachments_json) ? $request->data->attachments_json : [];

            foreach ($requestFields as $field) {
                if ($field->type === 'file') {
                    $file = $http->file('fields.'.$field->id);
                    if (!$file) {
                        continue;
                    }

                    $disk = config('ult.private_disk');
                    $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
                    $blocked = ['php','phtml','phar','exe','sh','bat','cmd','js','html','htm'];
                    if (in_array($ext, $blocked, true)) {
                        throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'File type blocked.');
                    }

                    $path = $this->uploadNamer->makePathForUploadedFile(
                        $disk,
                        "requests/{$request->id}/input",
                        "input_{$field->key}",
                        $file,
                    );
                    $stream = fopen($file->getRealPath(), 'rb');
                    Storage::disk($disk)->put($path, $stream);
                    if (is_resource($stream)) fclose($stream);

                    $sha = hash_file('sha256', $file->getRealPath());
                    $attachment = $request->attachments()->create([
                        'uploaded_by' => $http->user()->id,
                        'kind' => \App\Enums\AttachmentKind::input,
                        'service_field_id' => $field->id,
                        'original_name' => $file->getClientOriginalName(),
                        'stored_path' => $path,
                        'mime' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'sha256' => $sha,
                        'verified_status' => \App\Enums\AttachmentVerifiedStatus::pending,
                    ]);

                    $fileValue = ['attachment_id' => $attachment->id, 'original' => $attachment->original_name];
                    $existing = $fieldValues->get($field->id);
                    if ($existing) {
                        $existing->update([
                            'value_text' => null,
                            'value_json' => $fileValue,
                            'value_date' => null,
                            'value_number' => null,
                        ]);
                    } else {
                        RequestFieldValue::create([
                            'request_id' => $request->id,
                            'service_field_id' => $field->id,
                            'value_text' => null,
                            'value_json' => $fileValue,
                            'value_date' => null,
                            'value_number' => null,
                        ]);
                    }

                    $attachmentsJson[$field->key] = $attachment->id;
                    $dataJson[$field->key] = ['attachment_id' => $attachment->id];
                    continue;
                }

                $value = data_get($payload, 'fields.'.$field->id);
                $row = [
                    'value_text' => null,
                    'value_json' => null,
                    'value_date' => null,
                    'value_number' => null,
                ];

                if ($value !== null && $value !== '') {
                    switch ($field->type) {
                        case 'number':
                            $row['value_number'] = (float) $value;
                            break;
                        case 'date':
                            $row['value_date'] = $value;
                            break;
                        case 'richtext':
                            $row['value_text'] = $this->htmlSanitizer->clean((string) $value);
                            break;
                        case 'json':
                            $row['value_json'] = ['value' => (string) $value];
                            break;
                        default:
                            $row['value_text'] = (string) $value;
                    }
                }

                $existing = $fieldValues->get($field->id);
                if ($existing) {
                    $existing->update($row);
                } else {
                    RequestFieldValue::create(array_merge($row, [
                        'request_id' => $request->id,
                        'service_field_id' => $field->id,
                    ]));
                }

                unset($dataJson[$field->key]);
                if ($row['value_text'] !== null) $dataJson[$field->key] = $row['value_text'];
                elseif ($row['value_number'] !== null) $dataJson[$field->key] = $row['value_number'];
                elseif ($row['value_date'] !== null) $dataJson[$field->key] = $row['value_date'];
                elseif ($row['value_json'] !== null) $dataJson[$field->key] = $row['value_json'];
            }

            if ($isCertificateService) {
                $this->certificateDocs->persistSubmissionData($request, $http->user(), $http, $dataJson, $attachmentsJson, true);
            } else {
                RequestData::updateOrCreate(
                    ['request_id' => $request->id],
                    ['data_json' => $dataJson, 'attachments_json' => $attachmentsJson]
                );
                $this->storeGeneralAttachments($request, $http->user()->id, $http->file('attachments', []));

                $this->assignSelectedDosenSigners(
                    $request,
                    (array) ($payload['dosen_signers'] ?? []),
                    $dosenSelectSigners,
                    $http->user(),
                    'perbaikan',
                );
                $this->applyCustomSignatureUploads($request, $http->user(), $http, $customSignatureSigners, 'perbaikan');
                $this->applyPemohonSignatureUploads($request, $http->user(), $http, $pemohonSignatureSigners, 'perbaikan');
            }

            $this->audit->log('request.data_updated_by_student', 'requests', (string) $request->id, [
                'request_id' => $request->id,
                'updated_fields_count' => count(array_keys((array) ($payload['fields'] ?? []))),
                'actor_id' => $http->user()->id,
            ]);
        });

        return back()->with('status', 'Data permohonan berhasil diperbarui. Lanjutkan dengan Kirim perbaikan.');
    }

    /**
     * @param \Illuminate\Support\Collection<int,\App\Models\ServiceSigner> $customSignatureSigners
     */
    private function applyCustomSignatureUploads(
        UltRequest $request,
        User $actor,
        Request $http,
        \Illuminate\Support\Collection $customSignatureSigners,
        string $source = 'pengajuan'
    ): void {
        if ($customSignatureSigners->isEmpty()) {
            return;
        }

        foreach ($customSignatureSigners as $signer) {
            $idx = (int) $signer->order_index;
            $file = $http->file('custom_signatures.'.$idx);
            if (!$file instanceof UploadedFile) {
                continue;
            }

            [$types, $maxKb] = $this->resolveSignerSignatureConstraints($signer);

            $mime = (string) ($file->getMimeType() ?: '');
            if (!in_array($mime, $types, true)) {
                continue;
            }
            if (($file->getSize() ?: 0) > ($maxKb * 1024)) {
                continue;
            }

            $ext = match ($mime) {
                'image/png' => 'png',
                'image/jpeg' => 'jpg',
                'image/webp' => 'webp',
                default => strtolower($file->getClientOriginalExtension() ?: 'bin'),
            };

            $disk = config('ult.private_disk');
            $path = $this->uploadNamer->makePath(
                $disk,
                "requests/{$request->id}/signatures",
                "signature_custom_{$idx}",
                $ext,
            );

            $stream = fopen($file->getRealPath(), 'rb');
            Storage::disk($disk)->put($path, $stream);
            if (is_resource($stream)) fclose($stream);

            $signoff = RequestSignoff::query()
                ->where('request_id', $request->id)
                ->where('order_index', $idx)
                ->where('signer_role', (string) $signer->role)
                ->first();

            if (!$signoff) {
                $signoff = new RequestSignoff([
                    'request_id' => $request->id,
                    'signer_role' => (string) $signer->role,
                    'order_index' => $idx,
                    'is_required' => (bool) $signer->is_required,
                ]);
            }

            if (!$signoff->signer_role) {
                $signoff->signer_role = (string) $signer->role;
            }
            $customLabel = trim((string) ($signer->custom_label ?? ''));
            if ($customLabel === '') {
                $customLabel = "Penandatangan #{$idx}";
            }
            $signoff->status = RequestSignoffStatus::APPROVED;
            $signoff->decided_by = $actor->id;
            $signoff->decided_at = now();
            $signoff->note = $source === 'perbaikan'
                ? "Label: {$customLabel}. Signature CUSTOM diperbarui saat perbaikan oleh pemohon."
                : "Label: {$customLabel}. Signature CUSTOM diunggah saat pengajuan oleh pemohon.";
            $signoff->signature_file_path = $path;
            $signoff->save();

            $this->audit->log(
                $source === 'perbaikan' ? 'doc.signoff.custom_updated_by_student' : 'doc.signoff.custom_prefilled_by_student',
                'request_signoffs',
                (string) $signoff->id,
                [
                'request_id' => $request->id,
                'order_index' => $idx,
                'signer_role' => $signoff->signer_role,
                'signature_file_path' => $path,
                ]
            );
        }
    }

    /**
     * @param \Illuminate\Support\Collection<int,\App\Models\ServiceSigner> $pemohonSignatureSigners
     */
    private function applyPemohonSignatureUploads(
        UltRequest $request,
        User $actor,
        Request $http,
        \Illuminate\Support\Collection $pemohonSignatureSigners,
        string $source = 'pengajuan'
    ): void {
        if ($pemohonSignatureSigners->isEmpty()) {
            return;
        }

        foreach ($pemohonSignatureSigners as $signer) {
            $idx = (int) $signer->order_index;
            $file = $http->file('pemohon_signatures.'.$idx);
            if (!$file instanceof UploadedFile) {
                continue;
            }

            [$types, $maxKb] = $this->resolveSignerSignatureConstraints($signer);

            $mime = (string) ($file->getMimeType() ?: '');
            if (!in_array($mime, $types, true)) {
                continue;
            }
            if (($file->getSize() ?: 0) > ($maxKb * 1024)) {
                continue;
            }

            $ext = match ($mime) {
                'image/png' => 'png',
                'image/jpeg' => 'jpg',
                'image/webp' => 'webp',
                default => strtolower($file->getClientOriginalExtension() ?: 'bin'),
            };

            $disk = config('ult.private_disk');
            $path = $this->uploadNamer->makePath(
                $disk,
                "requests/{$request->id}/signatures",
                "signature_pemohon_{$idx}",
                $ext,
            );

            $stream = fopen($file->getRealPath(), 'rb');
            Storage::disk($disk)->put($path, $stream);
            if (is_resource($stream)) fclose($stream);

            $signoff = RequestSignoff::query()
                ->where('request_id', $request->id)
                ->where('order_index', $idx)
                ->where('signer_role', (string) $signer->role)
                ->first();

            if (!$signoff) {
                $signoff = new RequestSignoff([
                    'request_id' => $request->id,
                    'signer_role' => (string) $signer->role,
                    'order_index' => $idx,
                    'is_required' => (bool) $signer->is_required,
                ]);
            }

            if (!$signoff->signer_role) {
                $signoff->signer_role = (string) $signer->role;
            }
            $label = trim((string) ($signer->custom_label ?? ''));
            if ($label === '') {
                $label = "Pemohon #{$idx}";
            }
            $signoff->signer_user_id = $actor->id;
            $signoff->status = RequestSignoffStatus::APPROVED;
            $signoff->decided_by = $actor->id;
            $signoff->decided_at = now();
            $signoff->note = $source === 'perbaikan'
                ? "Label: {$label}. Signature diperbarui saat perbaikan oleh pemohon."
                : "Label: {$label}. Signature diunggah saat pengajuan oleh pemohon.";
            $signoff->signature_file_path = $path;
            $signoff->save();

            $this->audit->log(
                $source === 'perbaikan' ? 'doc.signoff.updated_by_student' : 'doc.signoff.prefilled_by_student',
                'request_signoffs',
                (string) $signoff->id,
                [
                'request_id' => $request->id,
                'order_index' => $idx,
                'signer_role' => $signoff->signer_role,
                'signature_file_path' => $path,
                ]
            );
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int,\App\Models\User>
     */
    private function eligibleDosenSignerUsers(): \Illuminate\Support\Collection
    {
        return User::query()
            ->select(['users.id', 'users.name', 'users.jabatan'])
            ->with('roles:id,name')
            ->whereHas('roles', fn ($r) => $r->whereIn('name', self::DOSEN_SIGNER_SELECTABLE_ROLES))
            ->get()
            ->sortBy(fn (User $user) => $user->signerHierarchySortKey())
            ->values();
    }

    /**
     * @return array<int,int>
     */
    private function eligibleDosenSignerUserIds(): array
    {
        return $this->eligibleDosenSignerUsers()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();
    }

    /**
     * @param array<int|string,mixed> $selectedSignersByOrder
     * @param \Illuminate\Support\Collection<int,\App\Models\ServiceSigner> $dosenSelectSigners
     */
    private function assignSelectedDosenSigners(
        UltRequest $request,
        array $selectedSignersByOrder,
        \Illuminate\Support\Collection $dosenSelectSigners,
        User $actor,
        string $source = 'pengajuan'
    ): void {
        if ($dosenSelectSigners->isEmpty()) {
            return;
        }

        foreach ($dosenSelectSigners as $signer) {
            $idx = (int) $signer->order_index;
            $rawUserId = data_get($selectedSignersByOrder, (string) $idx);
            if ($rawUserId === null || $rawUserId === '') {
                continue;
            }

            $selectedUserId = (int) $rawUserId;
            if ($selectedUserId < 1) {
                continue;
            }

            $signoff = RequestSignoff::query()
                ->where('request_id', $request->id)
                ->where('order_index', $idx)
                ->where('signer_role', (string) $signer->role)
                ->first();

            if (!$signoff) {
                // Non-doc service: no request_signoffs initialized.
                continue;
            }

            $previousSignerId = (int) ($signoff->signer_user_id ?? 0);
            if ($previousSignerId === $selectedUserId) {
                continue;
            }

            $signoff->signer_user_id = $selectedUserId;
            if ($signoff->status !== RequestSignoffStatus::PENDING) {
                $signoff->status = RequestSignoffStatus::PENDING;
                $signoff->decided_by = null;
                $signoff->decided_at = null;
                $signoff->note = 'Signer DOSEN dipilih ulang oleh pemohon saat '.$source.'.';
                $signoff->signature_file_path = null;
            }
            $signoff->save();

            $this->audit->log('doc.signoff.dosen_selected_by_student', 'request_signoffs', (string) $signoff->id, [
                'request_id' => $request->id,
                'order_index' => $idx,
                'signer_role' => $signoff->signer_role,
                'selected_signer_user_id' => $selectedUserId,
                'previous_signer_user_id' => $previousSignerId > 0 ? $previousSignerId : null,
                'source' => $source,
                'actor_id' => $actor->id,
            ]);
        }
    }

    /**
     * @return array{0:array<int,string>,1:int}
     */
    private function resolveSignerSignatureConstraints(object $signer): array
    {
        $allowed = ['image/png', 'image/jpeg', 'image/webp'];

        $types = is_array($signer->signature_file_types)
            ? array_values(array_filter($signer->signature_file_types, fn ($v) => is_string($v) && trim($v) !== ''))
            : [];
        $types = array_values(array_intersect($types, $allowed));
        if (empty($types)) {
            $types = $allowed;
        }

        $maxKb = (int) ($signer->signature_max_size_kb ?: 0);
        if ($maxKb <= 0) {
            $maxKb = 256;
        }

        return [$types, $maxKb];
    }

    private function extractAttachmentIdFromFieldValue(?RequestFieldValue $value): ?int
    {
        if (!$value || !is_array($value->value_json)) {
            return null;
        }

        $raw = $value->value_json;
        if (isset($raw['attachment_id']) && is_numeric($raw['attachment_id'])) {
            return (int) $raw['attachment_id'];
        }

        return null;
    }

    private function extractAttachmentIdFromRaw(mixed $raw): ?int
    {
        if (is_array($raw) && isset($raw['attachment_id']) && is_numeric($raw['attachment_id'])) {
            return (int) $raw['attachment_id'];
        }

        if (is_numeric($raw)) {
            return (int) $raw;
        }

        return null;
    }

    public function submitRevision(UltRequest $request, Request $http)
    {
        Gate::authorize('update', $request);

        $http->validate(['note' => ['required','string','max:2000']]);

        $this->workflow->transition($request, RequestStatus::DIAJUKAN, $http->user(), $http->string('note'), $request->current_step_key, $request->currentUnit);

        return back()->with('status', __('app.revision_submitted'));
    }

    public function addNote(UltRequest $request, Request $http)
    {
        Gate::authorize('view', $request);

        $data = $http->validate(['body' => ['required','string','max:2000']]);

        $request->notes()->create([
            'actor_id' => $http->user()->id,
            'body' => $data['body'],
            'is_internal' => false,
            'created_at' => now(),
        ]);

        return back();
    }

    private function resolveInitialUnit($user, $wf): ?Unit
    {
        //  user.unit_id mengarah ke prodi untuk mahasiswa.
        // Dampak: jika unit belum diset, initial unit fallback ke Fakultas.
        $unit = $user->unit;

        if (!$wf || !$unit) {
            return Unit::where('type', 'fakultas')->first();
        }

        if ($wf->require_prodi) return $unit;
        if ($wf->require_jurusan) return $unit->parent ?: Unit::where('type', 'jurusan')->first();

        return Unit::where('type', 'fakultas')->first();
    }

    /**
     * Guarantee placeholders mapped as FORM are available as service_fields for student form rendering.
     */
    private function ensureFormFieldsFromPlaceholders(Service $service): void
    {
        $service->loadMissing(['fields', 'placeholders', 'allFields']);

        $formPlaceholders = $service->placeholders
            ->filter(fn ($p) => ($p->source_type?->value ?? null) === 'FORM')
            ->filter(fn ($p) => trim((string) $p->source_ref) !== '');

        if ($formPlaceholders->isEmpty()) {
            return;
        }

        $existingFields = $service->relationLoaded('allFields')
            ? $service->getRelation('allFields')
            : $service->fields;
        $maxSort = (int) ($existingFields->max('sort_order') ?? 0);

        foreach ($formPlaceholders as $ph) {
            $placeholderKey = strtoupper(trim((string) $ph->placeholder_key));
            if ($placeholderKey === '') {
                continue;
            }

            $preferredKey = strtolower(trim((string) $ph->source_ref));
            $preferredKey = preg_replace('/[^a-z0-9_]+/', '_', $preferredKey ?? '') ?: '';
            $preferredKey = trim($preferredKey, '_');
            if ($preferredKey === '') {
                $preferredKey = strtolower($placeholderKey);
            }
            if (preg_match('/^[0-9]/', $preferredKey) === 1) {
                $preferredKey = 'field_'.$preferredKey;
            }

            $field = $existingFields->firstWhere('key', $preferredKey)
                ?? $existingFields->firstWhere('maps_to_placeholder_key', $placeholderKey);

            if ($field) {
                $dirty = false;
                if (!$field->maps_to_placeholder_key) {
                    $field->maps_to_placeholder_key = $placeholderKey;
                    $dirty = true;
                }
                if (!$field->is_active) {
                    $field->is_active = true;
                    $dirty = true;
                }
                if ($dirty) {
                    $field->save();
                }
                continue;
            }

            $key = $preferredKey;
            $suffix = 2;
            while ($existingFields->firstWhere('key', $key)) {
                $key = $preferredKey.'_'.$suffix;
                $suffix++;
            }

            $maxSort++;
            $autoType = $this->isPhotoPlaceholderKey($placeholderKey) ? 'file' : 'text';
            $autoRules = $autoType === 'file'
                ? ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048']
                : null;
            $created = ServiceField::create([
                'service_id' => $service->id,
                'key' => $key,
                'maps_to_placeholder_key' => $placeholderKey,
                'label_id' => str_replace('_', ' ', ucwords(strtolower($placeholderKey), '_')),
                'label_en' => null,
                'type' => $autoType,
                'required' => (bool) $ph->is_required,
                'rules_json' => $autoRules,
                'options_json' => null,
                'sort_order' => $maxSort,
                'is_active' => true,
            ]);

            $existingFields->push($created);
        }
    }

    private function resolveRequestFieldDefinitions(UltRequest $request): \Illuminate\Support\Collection
    {
        $snapshot = is_array($request->data?->document_snapshot_json)
            ? $request->data->document_snapshot_json
            : [];
        $fields = $snapshot['fields'] ?? null;
        if (!is_array($fields) || empty($fields)) {
            return $request->service?->fields?->sortBy('sort_order')->values() ?? collect();
        }

        return collect($fields)
            ->filter(fn ($field) => is_array($field) && (int) ($field['service_field_id'] ?? 0) > 0)
            ->map(function (array $field) {
                return (object) [
                    'id' => (int) $field['service_field_id'],
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
            return $request->service?->signers?->sortBy('order_index')->values() ?? collect();
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

    private function isPhotoPlaceholderKey(string $placeholderKey): bool
    {
        $k = strtoupper(trim($placeholderKey));
        if ($k === '') {
            return false;
        }

        return str_contains($k, 'PASPHOTO')
            || str_contains($k, 'PAS_FOTO')
            || str_contains($k, 'PASFOTO')
            || str_contains($k, 'PHOTO')
            || str_contains($k, 'FOTO');
    }

    /**
     * @param array<int,UploadedFile|null>|UploadedFile|null $files
     */
    private function storeGeneralAttachments(UltRequest $request, int $userId, array|UploadedFile|null $files): void
    {
        if ($files instanceof UploadedFile) {
            $files = [$files];
        }

        if (!is_array($files) || empty($files)) {
            return;
        }

        $disk = config('ult.private_disk');
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $this->assertAllowedAttachmentUpload($file);

            $path = $this->uploadNamer->makePathForUploadedFile(
                $disk,
                "requests/{$request->id}/input",
                'input_attachment',
                $file,
            );

            $stream = fopen($file->getRealPath(), 'rb');
            Storage::disk($disk)->put($path, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }

            $attachment = $request->attachments()->create([
                'uploaded_by' => $userId,
                'kind' => AttachmentKind::input,
                'service_field_id' => null,
                'original_name' => $file->getClientOriginalName(),
                'stored_path' => $path,
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'sha256' => hash_file('sha256', $file->getRealPath()),
                'verified_status' => AttachmentVerifiedStatus::pending,
            ]);

            $this->audit->log('attachment.upload_input', 'attachments', (string) $attachment->id, [
                'request_id' => $request->id,
                'path' => $path,
                'scope' => 'general_form_attachment',
            ]);
        }
    }

    private function assertAllowedAttachmentUpload(UploadedFile $file): void
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $blocked = ['php', 'phtml', 'phar', 'exe', 'sh', 'bat', 'cmd', 'js', 'html', 'htm'];

        if (in_array($ext, $blocked, true)) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'File type blocked.');
        }
    }
}
