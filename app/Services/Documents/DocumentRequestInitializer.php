<?php

namespace App\Services\Documents;

use App\Enums\UnitType;
use App\Models\Request as UltRequest;
use App\Models\RequestData;
use App\Models\RequestSignoff;
use App\Models\ServiceTemplate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class DocumentRequestInitializer
{
    /**
     * Initialize doc-module state for a newly submitted request:
     * - snapshot request_data (data_json + attachments_json)
     * - create request_signoffs from service_signers
     *
     * Safe to call multiple times (idempotent).
     */
    public function initialize(UltRequest $request, array $dataJson, array $attachmentsJson = []): void
    {
        $request->loadMissing(['service.signers', 'service.templates', 'student.unit.parent']);

        $hasTemplate = $request->service->templates
            ->firstWhere('type', \App\Enums\ServiceTemplateType::MAIN_DOCX) instanceof ServiceTemplate;
        if (!$hasTemplate) {
            // Not a document-module service; skip.
            return;
        }

        DB::transaction(function () use ($request, $dataJson, $attachmentsJson) {
            RequestData::updateOrCreate(
                ['request_id' => $request->id],
                [
                    'data_json' => $dataJson,
                    'attachments_json' => $attachmentsJson,
                    'document_snapshot_json' => $this->buildDocumentSnapshot($request),
                ]
            );

            // Create signoffs only if not exists yet.
            $existing = $request->signoffs()->count();
            if ($existing > 0) return;

            $rows = [];
            foreach ($request->service->signers as $s) {
                $role = strtoupper(trim((string) $s->role));
                $signerUserId = null;
                if ($role === 'PEMOHON') {
                    $signerUserId = $request->student_id;
                }
                if ($role === 'KAPRODI_SCOPE') {
                    $prodiId = (int) ($request->student?->unit?->ancestorOfType(UnitType::prodi)?->id ?: 0);
                    if ($prodiId > 0) {
                        $signerUserId = User::query()
                            ->role('KAPRODI')
                            ->where('unit_id', $prodiId)
                            ->orderBy('name')
                            ->value('id');
                    }
                }
                if ($role === 'KAJUR_SCOPE') {
                    $jurusanId = (int) ($request->student?->unit?->ancestorOfType(UnitType::jurusan)?->id ?: 0);
                    if ($jurusanId > 0) {
                        $signerUserId = User::query()
                            ->role('KAJUR')
                            ->where('unit_id', $jurusanId)
                            ->orderBy('name')
                            ->value('id');
                    }
                }
                if ($role === 'SEKJUR_SCOPE') {
                    $jurusanId = (int) ($request->student?->unit?->ancestorOfType(UnitType::jurusan)?->id ?: 0);
                    if ($jurusanId > 0) {
                        $signerUserId = $this->resolveSekjurSignerUserId($jurusanId);
                    }
                }

                $rows[] = [
                    'request_id' => $request->id,
                    'signer_role' => $s->role,
                    'signer_user_id' => $signerUserId,
                    'order_index' => $s->order_index,
                    'is_required' => (bool) $s->is_required,
                    'status' => \App\Enums\RequestSignoffStatus::PENDING,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($rows)) {
                RequestSignoff::insert($rows);
            }
        });
    }

    private function buildDocumentSnapshot(UltRequest $request): array
    {
        $request->loadMissing(['service.fields', 'service.placeholders', 'service.signers', 'service.templates']);

        $template = $request->service->templates
            ->firstWhere('type', \App\Enums\ServiceTemplateType::MAIN_DOCX);

        return [
            'template' => $template ? [
                'type' => (string) ($template->type?->value ?? $template->type),
                'file_path' => (string) $template->file_path,
                'original_filename' => (string) ($template->original_filename ?? ''),
                'created_at' => optional($template->created_at)?->toISOString(),
            ] : null,
            'placeholders' => $request->service->placeholders
                ->sortBy('placeholder_key')
                ->values()
                ->map(fn ($ph) => [
                    'placeholder_key' => (string) $ph->placeholder_key,
                    'source_type' => $ph->source_type?->value,
                    'source_ref' => $ph->source_ref,
                    'is_required' => (bool) $ph->is_required,
                    'notes' => $ph->notes,
                ])
                ->all(),
            'fields' => $request->service->fields
                ->sortBy('sort_order')
                ->values()
                ->map(fn ($field) => [
                    'service_field_id' => (int) $field->id,
                    'key' => (string) $field->key,
                    'maps_to_placeholder_key' => $field->maps_to_placeholder_key,
                    'label_id' => (string) $field->label_id,
                    'label_en' => $field->label_en,
                    'type' => (string) $field->type,
                    'required' => (bool) $field->required,
                    'rules_json' => is_array($field->rules_json) ? $field->rules_json : null,
                    'options_json' => is_array($field->options_json) ? $field->options_json : null,
                    'sort_order' => (int) $field->sort_order,
                ])
                ->all(),
            'signers' => $request->service->signers
                ->sortBy('order_index')
                ->values()
                ->map(fn ($signer) => [
                    'role' => (string) $signer->role,
                    'custom_label' => $signer->custom_label,
                    'order_index' => (int) $signer->order_index,
                    'is_required' => (bool) $signer->is_required,
                    'requires_signature_upload' => (bool) $signer->requires_signature_upload,
                    'signature_file_types' => is_array($signer->signature_file_types) ? $signer->signature_file_types : null,
                    'signature_max_size_kb' => $signer->signature_max_size_kb,
                ])
                ->all(),
        ];
    }

    private function resolveSekjurSignerUserId(int $jurusanId): ?int
    {
        try {
            $sekjurRoleBased = User::query()
                ->role('SEKJUR')
                ->where('unit_id', $jurusanId)
                ->orderBy('name')
                ->value('id');
            if ($sekjurRoleBased) {
                return (int) $sekjurRoleBased;
            }
        } catch (RoleDoesNotExist $e) {
            // Fallback to jabatan-based lookup when role is not seeded yet.
        }

        $jabatanBased = User::query()
            ->where('unit_id', $jurusanId)
            ->whereRaw("UPPER(COALESCE(jabatan, '')) LIKE ?", ['%SEKRETARIS JURUSAN%'])
            ->orderBy('name')
            ->value('id');

        return $jabatanBased ? (int) $jabatanBased : null;
    }
}
