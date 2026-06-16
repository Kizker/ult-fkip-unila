<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CmsCategoryType;
use App\Enums\DocumentSourceType;
use App\Http\Controllers\Controller;
use App\Models\CmsCategory;
use App\Models\Service;
use App\Models\ServiceField;
use App\Models\ServiceWorkflow;
use App\Models\User;
use App\Services\Documents\DocumentServiceSetupService;
use App\Services\Documents\ServiceDocumentReadinessChecker;
use App\Services\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceAdminController extends Controller
{
    /**
     * Dropdown options for signer roles based on existing accounts.
     *
     * @return array<int, array{value:string,label:string}>
     */
    private function signerRoleOptions(): array
    {
        $rolePools = [
            ['value' => 'DEKAN', 'query_roles' => ['DEKAN'], 'fallback_label' => 'DEKAN - Dekan'],
            ['value' => 'WD_AKADEMIK', 'query_roles' => ['WD_AKADEMIK'], 'fallback_label' => 'WD_AKADEMIK - Wakil Dekan Akademik'],
            ['value' => 'WD_UMUM', 'query_roles' => ['WD_UMUM'], 'fallback_label' => 'WD_UMUM - Wakil Dekan Umum'],
            ['value' => 'WD_KEMAHASISWAAN', 'query_roles' => ['WD_KEMAHASISWAAN'], 'fallback_label' => 'WD_KEMAHASISWAAN - Wakil Dekan Kemahasiswaan'],
        ];

        $out = [];
        foreach ($rolePools as $cfg) {
            $value = (string) ($cfg['value'] ?? '');
            $queryRoles = array_values(array_filter(
                (array) ($cfg['query_roles'] ?? []),
                fn ($r) => is_string($r) && trim($r) !== ''
            ));
            if ($value === '' || empty($queryRoles)) {
                continue;
            }

            $baseQuery = User::query()->whereHas('roles', fn ($r) => $r->whereIn('name', $queryRoles));
            $count = (int) (clone $baseQuery)->count();
            $first = (string) ((clone $baseQuery)->orderBy('name')->value('name') ?? '');

            if ($count < 1) {
                $out[] = ['value' => $value, 'label' => (string) ($cfg['fallback_label'] ?? $value)];
                continue;
            }

            $label = $count === 1 && $first !== ''
                ? "{$value} - {$first}"
                : "{$value} - {$count} akun";

            $out[] = ['value' => $value, 'label' => $label];
        }

        // Scope signer roles follow pemohon unit hierarchy.
        $out[] = ['value' => 'KAJUR_SCOPE', 'label' => 'KAJUR_SCOPE - Ketua Jurusan'];
        $out[] = ['value' => 'SEKJUR_SCOPE', 'label' => 'SEKJUR_SCOPE - Sekretaris Jurusan'];
        $out[] = ['value' => 'KAPRODI_SCOPE', 'label' => 'KAPRODI_SCOPE - Ketua Program Studi'];

        // DOSEN signer is picked by pemohon during request submission.
        $out[] = ['value' => 'DOSEN', 'label' => 'DOSEN - Dosen'];
        $out[] = ['value' => 'PEMOHON', 'label' => 'PEMOHON - Pemohon'];
        $out[] = ['value' => 'CUSTOM', 'label' => 'CUSTOM - Penandatangan lain'];

        return $out;
    }

    public function index()
    {
        $items = Service::query()->with(['workflow','templates','category'])->orderBy('title_id')->paginate(15);
        return view('admin.services.index', compact('items'));
    }

    public function create()
    {
        return view('admin.services.create', [
            'signerRoleOptions' => $this->signerRoleOptions(),
            'serviceCategories' => $this->serviceCategoryOptions(),
        ]);
    }

	    	    public function store(Request $request, HtmlSanitizer $san, DocumentServiceSetupService $setup): \Illuminate\Http\RedirectResponse
	    {
	        $data = $request->validate([
                'category_id' => [
                    'required',
                    'integer',
                    Rule::exists('cms_categories', 'id')->where(function ($q) {
                        $q->where('type', CmsCategoryType::service->value)
                            ->whereIn('slug', $this->allowedServiceCategorySlugs());
                    }),
                ],
	            'title_id' => ['required','string','max:190'],
	            'title_en' => ['nullable','string','max:190'],
	            'summary_id' => ['nullable','string','max:300'],
	            'summary_en' => ['nullable','string','max:300'],
	            'requirements_html_id' => ['nullable','string'],
	            'requirements_html_en' => ['nullable','string'],
	            'sop_html_id' => ['nullable','string'],
	            'sop_html_en' => ['nullable','string'],
                'document_source_type' => ['nullable', Rule::in([
                    DocumentSourceType::MAIN_DOCX_TEMPLATE->value,
                    DocumentSourceType::REQUEST_PPTX->value,
                ])],
                'allow_general_attachments' => ['boolean'],
	            'is_active' => ['boolean'],
	            'main_template' => ['nullable','file','max:10240'],
	            'extract_placeholders' => ['nullable','boolean'],
	            // workflow (fixed defaults)
	            'workflow_flags' => ['array'],
	            'workflow_flags.*' => ['boolean'],
	            // doc setup (optional on create)
	            'gate_role' => ['nullable','string','max:120'],
	            'gate_steps_json' => ['nullable','string'],
	            'signers_json' => ['nullable','string'],
            'signer_labels' => ['nullable','array'],
            'signer_labels.*' => ['nullable','string','max:120'],
            'placeholders_items_json' => ['nullable','string'],
	            'fields_json' => ['nullable','string'],
	        ]);

        $selectedDocumentSourceType = (string) ($data['document_source_type'] ?? DocumentSourceType::MAIN_DOCX_TEMPLATE->value);
        $docFlowEnabled = $selectedDocumentSourceType !== DocumentSourceType::REQUEST_PPTX->value;
        $flags = $docFlowEnabled && is_array($data['workflow_flags'] ?? null) ? $data['workflow_flags'] : [];

	        $steps = $this->defaultWorkflowSteps();

        // Gate setup is optional on create.
        // Only validate/persist gate_role+gate_steps_json when gate_steps_json is actually provided.
        $gateRole = null;
        $gateSteps = null;
        if (!empty($data['gate_steps_json'])) {
            $gateRoleRaw = isset($data['gate_role']) ? trim((string) $data['gate_role']) : '';
            $gateRole = $this->normalizeGateRole($gateRoleRaw);
            if (!$gateRole) {
                return back()->withErrors(['gate_role' => 'gate_role wajib diisi.'])->withInput();
            }

            $decoded = json_decode($data['gate_steps_json'], true);
            if (!is_array($decoded)) {
                return back()->withErrors(['gate_steps_json' => 'Format JSON langkah gate tidak valid.'])->withInput();
            }
            $gateSteps = $decoded;
        }

        $signers = null;
        if (!empty($data['signers_json'])) {
            $decoded = json_decode($data['signers_json'], true);
            if (!is_array($decoded)) {
                return back()->withErrors(['signers_json' => 'Format JSON signer tidak valid.'])->withInput();
            }
            $decoded = $this->mergeSignerLabelsFromFallbackInputs(
                $decoded,
                is_array($data['signer_labels'] ?? null) ? $data['signer_labels'] : [],
            );
            if (!empty($decoded)) {
                $signers = $decoded;
            }
        }

        $mappingItems = null;
        if (!empty($data['placeholders_items_json'])) {
            $decoded = json_decode($data['placeholders_items_json'], true);
            if (!is_array($decoded)) {
                return back()->withErrors(['placeholders_items_json' => 'Format JSON mapping placeholder tidak valid.'])->withInput();
            }
            if (!empty($decoded)) {
                if (!$request->hasFile('main_template')) {
                    return back()->withErrors(['placeholders_items_json' => 'Upload main_template + ekstrak placeholder dulu untuk mengisi mapping.'])->withInput();
                }
                if (empty($data['extract_placeholders'])) {
                    return back()->withErrors(['placeholders_items_json' => 'Centang "Ekstrak placeholder otomatis" agar mapping bisa diproses.'])->withInput();
                }
                $mappingItems = $decoded;
            }
        }

        $fields = null;
        if (!empty($data['fields_json'])) {
            $decoded = json_decode($data['fields_json'], true);
            if (!is_array($decoded)) {
                return back()->withErrors(['fields_json' => 'Format JSON field tidak valid.'])->withInput();
            }
            if (!empty($decoded)) {
                $fields = $decoded;
            }
        }

        $service = null;
        $uploadedTemplatePath = null;

        try {
            $service = Service::create([
                ...$data,
                'slug' => Str::slug($data['title_id']).'-'.Str::random(6),
                'requirements_html_id' => $san->clean($data['requirements_html_id'] ?? ''),
                'requirements_html_en' => $san->clean($data['requirements_html_en'] ?? ''),
                'sop_html_id' => $san->clean($data['sop_html_id'] ?? ''),
                'sop_html_en' => $san->clean($data['sop_html_en'] ?? ''),
                // Security-by-default: new services start as DRAFT and not publicly active unless explicitly enabled.
                'status' => \App\Enums\ServiceStatus::DRAFT,
                'created_by' => $request->user()->id,
                'document_source_type' => (string) ($data['document_source_type'] ?? DocumentSourceType::MAIN_DOCX_TEMPLATE->value),
                'allow_general_attachments' => (bool) ($data['allow_general_attachments'] ?? false),
                'is_active' => (bool)($data['is_active'] ?? false),
            ]);

	            // minimal workflow stub (customizable from create UI)
	            ServiceWorkflow::create([
	                'service_id' => $service->id,
	                // Default layanan dokumen: diproses Admin Jurusan -> Review ULT -> TTD Fakultas
	                'require_prodi' => false,
	            'require_jurusan' => true,
	            'require_unit_signature' => false,
                'require_org_chair_signature' => (bool)($flags['require_org_chair_signature'] ?? false),
                'require_pemohon_signature' => (bool)($flags['require_pemohon_signature'] ?? false),
                'require_org_secretary_signature' => (bool)($flags['require_org_secretary_signature'] ?? false),
                'require_kaprodi_signature' => (bool)($flags['require_kaprodi_signature'] ?? false),
                'require_kajur_signature' => (bool)($flags['require_kajur_signature'] ?? false),
                'require_other_lecturer_signature' => (bool)($flags['require_other_lecturer_signature'] ?? false),
	            'require_ult_review' => true,
                'require_faculty_signature' => true,
                'issue_number_at_step' => null,
	            'workflow_schema_version' => 1,
	            'steps_json' => $steps,
	            // Document module gate defaults (must be finalized via Setup Layanan Dokumen UI)
	            'gate_enabled' => true,
	                'gate_role' => 'Admin Jurusan',
	                'gate_steps_json' => ['VERIFY_INITIAL','INPUT_NOMOR_SURAT'],
            ]);

            // Optional: upload MAIN_DOCX during create (recommended).
            if ($request->hasFile('main_template') && $request->user()->can('doc_services.manage') && $request->user()->can('doc_templates.upload')) {
                $tpl = $setup->uploadMainTemplate($service, $request->user(), $request->file('main_template'));
                $uploadedTemplatePath = $tpl->file_path;

                $shouldExtract = (bool)($data['extract_placeholders'] ?? false);
                if ($shouldExtract && $request->user()->can('doc_placeholders.manage')) {
                    $setup->extractAndUpsertPlaceholders($service, $request->user());
                }
            }

            // Optional: save gate/signers/mapping/fields directly from create form (permission-guarded).
            if ($gateRole && $gateSteps && $request->user()->can('doc_services.manage')) {
                $setup->updateGate($service, $request->user(), $gateRole, $gateSteps);
            }
            if (is_array($signers) && !empty($signers) && $request->user()->can('doc_signers.manage')) {
                $setup->setSigners($service, $request->user(), $signers);
            }
            if (is_array($mappingItems) && !empty($mappingItems) && $request->user()->can('doc_placeholders.manage')) {
                $setup->upsertMappings($service, $request->user(), $mappingItems);
            }
            if (is_array($fields) && !empty($fields) && $request->user()->can('doc_services.manage')) {
                foreach ($fields as $it) {
                    if (!is_array($it)) continue;

                    $key = Str::lower(trim((string) ($it['key'] ?? '')));
                    if ($key === '' || !preg_match('/^[a-z0-9_]+$/', $key)) {
                        throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, "Field key invalid: {$key}");
                    }

                    $labelId = trim((string) ($it['label_id'] ?? ''));
                    if ($labelId === '') {
                        throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, "Field {$key} label_id wajib.");
                    }

                    $type = (string) ($it['type'] ?? 'text');
                    $allowedTypes = ['text','textarea','richtext','number','date','select','checkbox','json','file'];
                    if (!in_array($type, $allowedTypes, true)) {
                        throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, "Field {$key} type tidak valid.");
                    }

                    $rules = $it['rules_json'] ?? null;
                    if (!is_null($rules) && !is_array($rules)) {
                        throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, "Field {$key} rules_json harus array.");
                    }

                    $opts = $it['options_json'] ?? null;
                    if (!is_null($opts) && !is_array($opts)) {
                        throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, "Field {$key} options_json harus array.");
                    }

                    $mapsTo = isset($it['maps_to_placeholder_key']) ? strtoupper(trim((string) $it['maps_to_placeholder_key'])) : null;

                    ServiceField::create([
                        'service_id' => $service->id,
                        'key' => $key,
                        'maps_to_placeholder_key' => $mapsTo ?: null,
                        'label_id' => $labelId,
                        'label_en' => null,
                        'type' => $type,
                        'required' => (bool) ($it['required'] ?? false),
                        'rules_json' => $rules,
                        'options_json' => $opts,
                        'sort_order' => (int) ($it['sort_order'] ?? 0),
                        'is_active' => true,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            if ($service) {
                $service->delete();
            }
            if (is_string($uploadedTemplatePath) && $uploadedTemplatePath !== '') {
                $disk = config('ult.private_disk');
                Storage::disk($disk)->delete($uploadedTemplatePath);
            }

            $msg = $e->getMessage() ?: 'Gagal menyimpan layanan.';
            return back()->withErrors(['doc_setup' => $msg])->withInput();
        }

        $redirectUrl = route('admin.layanan.edit', $service) . '#setup-dokumen';
        return redirect()->to($redirectUrl)->with('status', __('app.saved'));
    }

    public function edit(Service $layanan, ServiceDocumentReadinessChecker $readiness)
    {
        $layanan->load(['templates','placeholders','fields','workflow','signers']);
        $errors = $readiness->check($layanan);

        return view('admin.services.edit', [
            'service' => $layanan,
            'readinessErrors' => $errors,
            'signerRoleOptions' => $this->signerRoleOptions(),
            'serviceCategories' => $this->serviceCategoryOptions(),
        ]);
    }

    public function show(Service $layanan)
    {
        return redirect()
            ->route('admin.layanan.edit', $layanan)
            ->with('status', 'Mode detail diarahkan ke halaman edit layanan.');
    }

	    public function update(Request $request, Service $layanan, HtmlSanitizer $san)
	    {
	        $data = $request->validate([
                'category_id' => [
                    'required',
                    'integer',
                    Rule::exists('cms_categories', 'id')->where(function ($q) {
                        $q->where('type', CmsCategoryType::service->value)
                            ->whereIn('slug', $this->allowedServiceCategorySlugs());
                    }),
                ],
	            'title_id' => ['required','string','max:190'],
            'title_en' => ['nullable','string','max:190'],
            'summary_id' => ['nullable','string','max:300'],
            'summary_en' => ['nullable','string','max:300'],
            'requirements_html_id' => ['nullable','string'],
            'requirements_html_en' => ['nullable','string'],
	            'sop_html_id' => ['nullable','string'],
	            'sop_html_en' => ['nullable','string'],
                'document_source_type' => ['nullable', Rule::in([
                    DocumentSourceType::MAIN_DOCX_TEMPLATE->value,
                    DocumentSourceType::REQUEST_PPTX->value,
                ])],
                'allow_general_attachments' => ['boolean'],
	            'is_active' => ['boolean'],
	            // workflow steps are fixed (no UI)
	            'workflow_flags' => ['array'],
	            'workflow_flags.*' => ['boolean'],
	        ]);

        $selectedDocumentSourceType = (string) ($data['document_source_type'] ?? $layanan->document_source_type?->value ?? DocumentSourceType::MAIN_DOCX_TEMPLATE->value);
        $docFlowEnabled = $selectedDocumentSourceType !== DocumentSourceType::REQUEST_PPTX->value;
        $flags = $docFlowEnabled && is_array($data['workflow_flags'] ?? null) ? $data['workflow_flags'] : [];

        $layanan->update([
            ...$data,
            'requirements_html_id' => $san->clean($data['requirements_html_id'] ?? ''),
            'requirements_html_en' => $san->clean($data['requirements_html_en'] ?? ''),
            'sop_html_id' => $san->clean($data['sop_html_id'] ?? ''),
            'sop_html_en' => $san->clean($data['sop_html_en'] ?? ''),
            'document_source_type' => (string) ($data['document_source_type'] ?? $layanan->document_source_type?->value ?? DocumentSourceType::MAIN_DOCX_TEMPLATE->value),
            'allow_general_attachments' => (bool) ($data['allow_general_attachments'] ?? false),
            'is_active' => (bool)($data['is_active'] ?? false),
        ]);

	        $wf = $layanan->workflow;
	        if ($wf) {
	            $steps = $this->defaultWorkflowSteps();

	            $wf->update([
	                // Default layanan dokumen: diproses Admin Jurusan -> Review ULT -> TTD Fakultas
	                'require_prodi' => false,
	                'require_jurusan' => true,
	                'require_unit_signature' => false,
                    'require_org_chair_signature' => (bool)($flags['require_org_chair_signature'] ?? false),
                    'require_pemohon_signature' => (bool)($flags['require_pemohon_signature'] ?? false),
                    'require_org_secretary_signature' => (bool)($flags['require_org_secretary_signature'] ?? false),
                    'require_kaprodi_signature' => (bool)($flags['require_kaprodi_signature'] ?? false),
                    'require_kajur_signature' => (bool)($flags['require_kajur_signature'] ?? false),
                    'require_other_lecturer_signature' => (bool)($flags['require_other_lecturer_signature'] ?? false),
	                'require_ult_review' => true,
	                'require_faculty_signature' => true,
	                'steps_json' => $steps,
	            ]);
	        }

        return back()->with('status', __('app.saved'));
    }

	    public function destroy(Service $layanan)
	    {
	        $layanan->delete();
	        return redirect()->route('admin.layanan.index')->with('status', __('app.deleted'));
	    }

	    private function defaultWorkflowSteps(): array
	    {
	        return [
	            [
	                'key' => 'submit',
	                'label_id' => 'Pengajuan',
	                'label_en' => 'Submission',
	                'role_required' => 'Admin Jurusan',
	                'unit_scope' => 'jurusan',
	                'actions_allowed' => ['verify', 'request_revision', 'reject'],
	                'next_on_approve' => null,
	                'next_on_reject' => null,
	                'can_request_revision' => true,
	            ],
	            [
	                'key' => 'output',
	                'label_id' => 'Output',
	                'label_en' => 'Output',
	                'role_required' => 'Admin Jurusan',
	                'unit_scope' => 'jurusan',
	                'actions_allowed' => ['upload_output', 'complete', 'request_revision', 'reject'],
	                'next_on_approve' => null,
	                'next_on_reject' => null,
	                'can_request_revision' => true,
	            ],
	        ];
	    }

    private function allowedServiceCategorySlugs(): array
    {
        return [
            'akademik-dan-kerja-sama',
            'umum-dan-keuangan',
            'kemahasiswaan-dan-alumni',
            'lainnya',
        ];
    }

    private function serviceCategoryOptions()
    {
        $definitions = [
            'akademik-dan-kerja-sama' => [
                'name_id' => 'Akademik dan Kerja Sama',
                'name_en' => 'Academic and Partnerships',
            ],
            'umum-dan-keuangan' => [
                'name_id' => 'Umum dan Keuangan',
                'name_en' => 'General and Finance',
            ],
            'kemahasiswaan-dan-alumni' => [
                'name_id' => 'Kemahasiswaan dan Alumni',
                'name_en' => 'Student Affairs and Alumni',
            ],
            'lainnya' => [
                'name_id' => 'Lainnya',
                'name_en' => 'Other',
            ],
        ];

        foreach ($definitions as $slug => $meta) {
            CmsCategory::updateOrCreate(
                ['type' => CmsCategoryType::service, 'slug' => $slug],
                ['name_id' => $meta['name_id'], 'name_en' => $meta['name_en']]
            );
        }

        $cats = CmsCategory::query()
            ->where('type', CmsCategoryType::service)
            ->whereIn('slug', array_keys($definitions))
            ->get()
            ->keyBy('slug');

        return collect(array_keys($definitions))
            ->map(fn ($slug) => $cats->get($slug))
            ->filter();
    }

    /**
     * Fallback for stale JS bundle: merge signer_labels[] into signers_json items.
     *
     * @param array<int,mixed> $items
     * @param array<int,mixed> $labels
     * @return array<int,mixed>
     */
    private function mergeSignerLabelsFromFallbackInputs(array $items, array $labels): array
    {
        foreach ($items as $idx => $item) {
            if (!is_array($item)) {
                continue;
            }

            $role = strtoupper(trim((string) ($item['role'] ?? '')));
            if (!in_array($role, ['CUSTOM', 'DOSEN', 'PEMOHON'], true)) {
                continue;
            }

            $jsonLabel = array_key_exists('custom_label', $item)
                ? trim((string) ($item['custom_label'] ?? ''))
                : '';
            $fallbackLabel = isset($labels[$idx]) ? trim((string) $labels[$idx]) : '';

            if ($jsonLabel === '' && $fallbackLabel !== '') {
                $item['custom_label'] = $fallbackLabel;
                $items[$idx] = $item;
            }
        }

        return $items;
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
	}

