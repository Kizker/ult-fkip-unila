<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceField;
use App\Services\Documents\DocumentServiceSetupService;
use App\Services\Documents\ServiceDocumentReadinessChecker;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentServiceSetupController extends Controller
{
    public function __construct(
        private readonly DocumentServiceSetupService $setup,
        private readonly ServiceDocumentReadinessChecker $readiness,
    ) {}

    public function edit(Service $layanan)
    {
        $layanan->load(['templates','placeholders','fields','workflow','signers']);
        $errors = $this->readiness->check($layanan);

        return view('admin.services.documents.edit', [
            'service' => $layanan,
            'readinessErrors' => $errors,
        ]);
    }

    public function uploadTemplate(Service $layanan, Request $request)
    {
        if ($layanan->usesRequestPptxSource()) {
            abort(422, 'Mode Sertifikat/Piagam tidak memakai upload template MAIN_DOCX.');
        }

        $data = $request->validate([
            'file' => ['required','file','mimes:docx','max:10240'], // 10MB
            'extract_placeholders' => ['nullable','boolean'],
        ]);
        $file = $data['file'];

        $this->setup->uploadMainTemplate($layanan, $request->user(), $file);

        $shouldExtract = (bool) ($data['extract_placeholders'] ?? false);
        if ($shouldExtract && $request->user()->can('doc_placeholders.manage')) {
            $keys = $this->setup->extractAndUpsertPlaceholders($layanan, $request->user());
            return back()->with('status', 'Template diunggah. Placeholder diekstrak: '.count($keys));
        }

        return back()->with('status', 'Template diunggah.');
    }

    public function extractPlaceholders(Service $layanan, Request $request)
    {
        if ($layanan->usesRequestPptxSource()) {
            abort(422, 'Mode Sertifikat/Piagam tidak memakai ekstraksi placeholder MAIN_DOCX.');
        }

        $keys = $this->setup->extractAndUpsertPlaceholders($layanan, $request->user());
        return back()->with('status', 'Placeholder diekstrak: '.count($keys));
    }

    public function saveMappings(Service $layanan, Request $request)
    {
        if ($layanan->usesRequestPptxSource()) {
            abort(422, 'Mode Sertifikat/Piagam tidak memakai mapping placeholder MAIN_DOCX.');
        }

        $data = $request->validate([
            'items' => ['required','array'],
            'items.*.placeholder_key' => ['required','string','max:120'],
            'items.*.source_type' => ['required','string','max:32'],
            'items.*.source_ref' => ['nullable','string','max:190'],
            'items.*.is_required' => ['nullable','boolean'],
            'items.*.notes' => ['nullable','string','max:500'],
        ]);

        $this->setup->upsertMappings($layanan, $request->user(), $data['items']);

        return back()->with('status', 'Mapping disimpan.');
    }

    public function saveGate(Service $layanan, Request $request)
    {
        $data = $request->validate([
            'gate_role' => ['required','string','max:120'],
            'gate_steps_json' => ['required','string'],
        ]);

        $steps = json_decode($data['gate_steps_json'], true);
        if (!is_array($steps)) {
            return back()->withErrors(['gate_steps_json' => 'Invalid JSON.']);
        }

        $this->setup->updateGate($layanan, $request->user(), $data['gate_role'], $steps);
        return back()->with('status', 'Gate disimpan.');
    }

    public function saveSigners(Service $layanan, Request $request)
    {
        if ($layanan->usesRequestPptxSource()) {
            abort(422, 'Mode Sertifikat/Piagam memakai signer dinamis dari pemohon saat pengajuan.');
        }

        $data = $request->validate([
            'signers_json' => ['required','string'],
            'signer_labels' => ['nullable','array'],
            'signer_labels.*' => ['nullable','string','max:120'],
        ]);

        $items = json_decode($data['signers_json'], true);
        if (!is_array($items)) {
            return back()->withErrors(['signers_json' => 'Invalid JSON.']);
        }
        $items = $this->mergeSignerLabelsFromFallbackInputs(
            $items,
            is_array($data['signer_labels'] ?? null) ? $data['signer_labels'] : [],
        );

        $this->setup->setSigners($layanan, $request->user(), $items);
        return back()->with('status', 'Signer chain disimpan.');
    }

    public function publish(Service $layanan, Request $request)
    {
        $this->setup->publish($layanan, $request->user());
        return back()->with('status', 'Layanan dipublish.');
    }

    public function createField(Service $layanan, Request $request)
    {
        $data = $request->validate([
            'key' => ['required','string','max:120'],
            'label_id' => ['required','string','max:190'],
            'type' => ['required','in:text,textarea,richtext,number,date,select,checkbox,json,file'],
            'required' => ['nullable','boolean'],
            'rules_json' => ['nullable','string'],
            'options_json' => ['nullable','string'],
            'maps_to_placeholder_key' => ['nullable','string','max:120'],
            'sort_order' => ['nullable','integer','min:0','max:9999'],
        ]);

        $rules = $data['rules_json'] ? json_decode($data['rules_json'], true) : null;
        if ($data['rules_json'] && !is_array($rules)) {
            return back()->withErrors(['rules_json' => 'Invalid JSON rules_json.']);
        }

        $opts = $data['options_json'] ? json_decode($data['options_json'], true) : null;
        if ($data['options_json'] && !is_array($opts)) {
            return back()->withErrors(['options_json' => 'Invalid JSON options_json.']);
        }

        $key = Str::lower(trim($data['key']));
        if (!preg_match('/^[a-z0-9_]+$/', $key)) {
            return back()->withErrors(['key' => 'Key hanya boleh a-z 0-9 underscore.']);
        }

        ServiceField::create([
            'service_id' => $layanan->id,
            'key' => $key,
            'maps_to_placeholder_key' => $data['maps_to_placeholder_key'] ? strtoupper(trim($data['maps_to_placeholder_key'])) : null,
            'label_id' => $data['label_id'],
            'label_en' => null,
            'type' => $data['type'],
            'required' => (bool) ($data['required'] ?? false),
            'rules_json' => $rules,
            'options_json' => $opts,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => true,
        ]);

        return back()->with('status', 'Field ditambahkan.');
    }

    public function updateField(Service $layanan, ServiceField $field, Request $request)
    {
        abort_unless($field->service_id === $layanan->id, 404);

        $data = $request->validate([
            'label_id' => ['required','string','max:190'],
            'type' => ['required','in:text,textarea,richtext,number,date,select,checkbox,json,file'],
            'required' => ['nullable','boolean'],
            'rules_json' => ['nullable','string'],
            'options_json' => ['nullable','string'],
            'maps_to_placeholder_key' => ['nullable','string','max:120'],
            'sort_order' => ['nullable','integer','min:0','max:9999'],
        ]);

        $rules = $data['rules_json'] ? json_decode($data['rules_json'], true) : null;
        if ($data['rules_json'] && !is_array($rules)) {
            return back()->withErrors(['rules_json' => 'Invalid JSON rules_json.']);
        }

        $opts = $data['options_json'] ? json_decode($data['options_json'], true) : null;
        if ($data['options_json'] && !is_array($opts)) {
            return back()->withErrors(['options_json' => 'Invalid JSON options_json.']);
        }

        $field->update([
            'label_id' => $data['label_id'],
            'type' => $data['type'],
            'required' => (bool) ($data['required'] ?? false),
            'rules_json' => $rules,
            'options_json' => $opts,
            'maps_to_placeholder_key' => $data['maps_to_placeholder_key'] ? strtoupper(trim($data['maps_to_placeholder_key'])) : null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => true,
        ]);

        return back()->with('status', 'Field diupdate.');
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
}
