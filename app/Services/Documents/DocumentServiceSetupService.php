<?php

namespace App\Services\Documents;

use App\Enums\PlaceholderSourceType;
use App\Enums\ServiceStatus;
use App\Enums\ServiceTemplateType;
use App\Models\Service;
use App\Models\ServiceField;
use App\Models\ServicePlaceholder;
use App\Models\ServiceSigner;
use App\Models\ServiceTemplate;
use App\Models\RequestData;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\Uploads\UniqueUploadNamer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentServiceSetupService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly DocxPlaceholderExtractor $extractor,
        private readonly ServiceDocumentReadinessChecker $readiness,
        private readonly UniqueUploadNamer $uploadNamer,
    ) {}

    public function uploadMainTemplate(Service $service, User $actor, UploadedFile $file): ServiceTemplate
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: '');
        if ($ext !== 'docx') {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'File harus .docx');
        }

        $mime = (string) ($file->getMimeType() ?: '');
        $allowed = [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip', // some servers report docx as zip
        ];
        if (!in_array($mime, $allowed, true)) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'MIME DOCX tidak valid.');
        }

        return DB::transaction(function () use ($service, $actor, $file) {
            $this->snapshotExistingRequestsBeforeTemplateReplacement($service);

            $disk = config('ult.private_disk');
            $path = $this->uploadNamer->makePath(
                $disk,
                "services/{$service->id}/templates",
                'main_template',
                'docx',
            );

            $stream = fopen($file->getRealPath(), 'rb');
            Storage::disk($disk)->put($path, $stream);
            if (is_resource($stream)) fclose($stream);

            // Replace existing MAIN_DOCX (keep latest only)
            ServiceTemplate::query()
                ->where('service_id', $service->id)
                ->where('type', ServiceTemplateType::MAIN_DOCX)
                ->delete();

            ServicePlaceholder::query()
                ->where('service_id', $service->id)
                ->update(['is_active' => false]);

            ServiceField::query()
                ->where('service_id', $service->id)
                ->whereNotNull('maps_to_placeholder_key')
                ->update([
                    'maps_to_placeholder_key' => null,
                    'is_active' => false,
                ]);

            $tpl = ServiceTemplate::create([
                'service_id' => $service->id,
                'type' => ServiceTemplateType::MAIN_DOCX,
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'uploaded_by' => $actor->id,
                'created_at' => now(),
            ]);

            $this->audit->log('doc.service.template_uploaded', 'service_templates', (string) $tpl->id, [
                'service_id' => $service->id,
                'path' => $path,
                'original' => $tpl->original_filename,
            ]);

            return $tpl;
        });
    }

    /**
     * Extract and upsert placeholders from MAIN_DOCX.
     *
     * @return array<int,string> placeholder keys
     */
    public function extractAndUpsertPlaceholders(Service $service, User $actor): array
    {
        $service->loadMissing(['templates', 'placeholders']);

        $tpl = $service->templates->firstWhere('type', ServiceTemplateType::MAIN_DOCX);
        if (!$tpl) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'MAIN_DOCX template belum ada.');
        }

        $disk = config('ult.private_disk');
        $keys = $this->extractor->extractFromStoredDocx($disk, $tpl->file_path);

        return DB::transaction(function () use ($service, $actor, $keys) {
            foreach ($keys as $k) {
                $existing = ServicePlaceholder::query()
                    ->where('service_id', $service->id)
                    ->where('placeholder_key', $k)
                    ->first();

                if ($existing) {
                    $existing->source_type = null;
                    $existing->source_ref = null;
                    $existing->is_required = true;
                    $existing->notes = null;
                    $existing->is_active = true;

                    if ($k === 'NOMOR_SURAT') {
                        $existing->source_type = PlaceholderSourceType::INTERNAL;
                        $existing->notes = 'Locked: input manual oleh petugas gate awal sebelum signing.';
                    } elseif ($k === 'TANGGAL_SURAT') {
                        $existing->source_type = PlaceholderSourceType::SYSTEM_AUTOFILL;
                        $existing->notes = 'Locked: diisi otomatis setelah required signer terakhir approve.';
                    } elseif (in_array($k, ['NIP_PENANDATANGAN', 'NAMA_PENANDATANGAN'], true)) {
                        $existing->source_type = PlaceholderSourceType::INTERNAL;
                        $existing->is_required = false;
                        $existing->notes = 'Otomatis: mengikuti penandatangan terakhir (nama + NIP/NPM dari profil user).';
                    }
                    $existing->save();
                    continue;
                }

                $row = [
                    'service_id' => $service->id,
                    'placeholder_key' => $k,
                    'source_type' => null,
                    'source_ref' => null,
                    'is_required' => true,
                    'is_active' => true,
                ];

                // Locked placeholders auto-mapped
                if ($k === 'NOMOR_SURAT') {
                    $row['source_type'] = PlaceholderSourceType::INTERNAL;
                    $row['notes'] = 'Locked: input manual oleh petugas gate awal sebelum signing.';
                }
                if ($k === 'TANGGAL_SURAT') {
                    $row['source_type'] = PlaceholderSourceType::SYSTEM_AUTOFILL;
                    $row['notes'] = 'Locked: diisi otomatis setelah required signer terakhir approve.';
                }

                if (in_array($k, ['NIP_PENANDATANGAN', 'NAMA_PENANDATANGAN'], true)) {
                    $row['source_type'] = PlaceholderSourceType::INTERNAL;
                    $row['is_required'] = false;
                    $row['notes'] = 'Otomatis: mengikuti penandatangan terakhir (nama + NIP/NPM dari profil user).';
                }

                ServicePlaceholder::create($row);
            }

            $this->audit->log('doc.service.placeholders_extracted', 'services', (string) $service->id, [
                'count' => count($keys),
            ]);

            return $keys;
        });
    }

    /**
     * Bulk update placeholder mappings.
     *
     * @param array<int,array{placeholder_key:string,source_type:string,source_ref?:?string,is_required?:bool,notes?:?string}> $items
     */
    public function upsertMappings(Service $service, User $actor, array $items): void
    {
        $service->loadMissing(['placeholders', 'fields']);

        $existingKeys = $service->placeholders->pluck('placeholder_key')->all();
        $existingSet = array_fill_keys($existingKeys, true);

        DB::transaction(function () use ($service, $actor, $items, $existingSet) {
            $maxSort = (int) ServiceField::query()->where('service_id', $service->id)->max('sort_order');

            foreach ($items as $it) {
                if (!is_array($it)) continue;
                $key = PlaceholderKeyNormalizer::normalize($it['placeholder_key'] ?? null);
                if (!$key || !isset($existingSet[$key])) {
                    throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, "Placeholder {$key} tidak ditemukan pada hasil ekstraksi.");
                }

                $ph = ServicePlaceholder::query()->where('service_id', $service->id)->where('placeholder_key', $key)->firstOrFail();

                // Locked
                if ($key === 'NOMOR_SURAT') {
                    $ph->source_type = PlaceholderSourceType::INTERNAL;
                    $ph->source_ref = null;
                    $ph->is_required = true;
                    $ph->save();
                    continue;
                }
                if ($key === 'TANGGAL_SURAT') {
                    $ph->source_type = PlaceholderSourceType::SYSTEM_AUTOFILL;
                    $ph->source_ref = null;
                    $ph->is_required = true;
                    $ph->save();
                    continue;
                }

                $src = strtoupper((string) ($it['source_type'] ?? ''));
                if (!in_array($src, ['FORM','PROFILE','INTERNAL','SYSTEM_AUTOFILL'], true)) {
                    throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, "source_type invalid for {$key}.");
                }

                $ph->source_type = PlaceholderSourceType::from($src);
                $rawRef = isset($it['source_ref']) ? trim((string) $it['source_ref']) : null;

                if ($ph->source_type === PlaceholderSourceType::FORM) {
                    // Make FORM mapping easy: auto-create (or re-use) a ServiceField so pemohon fills it.
                    $ref = $rawRef !== '' ? $rawRef : null;
                    $pendingRequired = array_key_exists('is_required', $it) ? (bool) $it['is_required'] : (bool) $ph->is_required;
                    $ref = $this->ensureFormFieldForPlaceholder($service, $key, $ref, $pendingRequired, $maxSort);
                    $ph->source_ref = $ref;
                } else {
                    $ph->source_ref = $ph->source_type === PlaceholderSourceType::PROFILE
                        ? $this->normalizeProfileSourceRef($rawRef)
                        : ($rawRef !== '' ? $rawRef : null);
                }
                if (array_key_exists('is_required', $it)) $ph->is_required = (bool) $it['is_required'];
                $ph->notes = isset($it['notes']) ? trim((string) $it['notes']) : $ph->notes;
                $ph->save();
            }

            $this->audit->log('doc.service.placeholders_mapped', 'services', (string) $service->id, [
                'count' => count($items),
            ]);
        });
    }

    private function ensureFormFieldForPlaceholder(Service $service, string $placeholderKey, ?string $preferredFieldKey, bool $required, int &$maxSort): string
    {
        $service->loadMissing(['allFields']);
        $allFields = $service->relationLoaded('allFields')
            ? $service->getRelation('allFields')
            : ServiceField::query()->where('service_id', $service->id)->orderBy('sort_order')->get();

        // 1) Re-use existing mapping if present.
        $existingMapped = $allFields->firstWhere('maps_to_placeholder_key', $placeholderKey);
        if ($existingMapped && $existingMapped->key) {
            if (!$existingMapped->is_active) {
                $existingMapped->is_active = true;
                $existingMapped->save();
            }
            return (string) $existingMapped->key;
        }

        // 2) If admin selected a field key and it exists, bind it.
        if ($preferredFieldKey) {
            $pref = Str::lower(trim($preferredFieldKey));
            $field = $allFields->firstWhere('key', $pref);
            if ($field) {
                $field->maps_to_placeholder_key = $placeholderKey;
                $field->is_active = true;
                $field->save();
                return (string) $field->key;
            }
        }

        // 3) Auto-create a field key from placeholder key.
        $base = Str::of($placeholderKey)->lower()->replace('-', '_')->toString();
        $base = preg_replace('/[^a-z0-9_]+/', '_', $base) ?: 'field';
        $base = trim($base, '_');
        if ($base === '') $base = 'field';
        if (preg_match('/^[0-9]/', $base) === 1) $base = 'field_'.$base;

        $key = $base;
        $n = 2;
        while ($allFields->firstWhere('key', $key)) {
            $key = $base.'_'.$n;
            $n++;
        }

        $label = Str::of($placeholderKey)
            ->replace('_', ' ')
            ->lower()
            ->title()
            ->toString();
        if ($label === '') $label = $placeholderKey;

        $autoType = $this->isPhotoPlaceholderKey($placeholderKey) ? 'file' : 'text';
        $autoRules = $autoType === 'file'
            ? ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048']
            : null;

        $maxSort++;
        $created = ServiceField::create([
            'service_id' => $service->id,
            'key' => $key,
            'maps_to_placeholder_key' => $placeholderKey,
            'label_id' => $label,
            'label_en' => null,
            'type' => $autoType,
            'required' => $required,
            'rules_json' => $autoRules,
            'options_json' => null,
            'sort_order' => $maxSort,
            'is_active' => true,
        ]);

        // Update local cache to avoid duplicates within the same transaction loop.
        $allFields->push($created);

        return $key;
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

    private function snapshotExistingRequestsBeforeTemplateReplacement(Service $service): void
    {
        $service->loadMissing(['templates', 'placeholders', 'fields', 'signers']);
        $snapshot = $this->buildDocumentSnapshotFromCurrentService($service);

        RequestData::query()
            ->whereNull('document_snapshot_json')
            ->whereHas('request', fn ($q) => $q->where('service_id', $service->id))
            ->chunkById(100, function ($rows) use ($snapshot): void {
                foreach ($rows as $row) {
                    $row->document_snapshot_json = $snapshot;
                    $row->save();
                }
            });
    }

    private function buildDocumentSnapshotFromCurrentService(Service $service): array
    {
        $template = $service->templates->firstWhere('type', ServiceTemplateType::MAIN_DOCX);

        return [
            'template' => $template ? [
                'type' => (string) ($template->type?->value ?? $template->type),
                'file_path' => (string) $template->file_path,
                'original_filename' => (string) ($template->original_filename ?? ''),
                'created_at' => optional($template->created_at)?->toISOString(),
            ] : null,
            'placeholders' => $service->placeholders
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
            'fields' => $service->fields
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
            'signers' => $service->signers
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

    private function normalizeProfileSourceRef(?string $raw): ?string
    {
        $raw = trim((string) ($raw ?? ''));
        if ($raw === '') return null;

        $compact = strtolower(preg_replace('/\\s+/', '', $raw) ?? '');

        $simpleAliases = [
            'nama' => 'user.name',
            'name' => 'user.name',
            'email' => 'user.email',
            'jabatan' => 'user.jabatan',
            'npm' => 'user.user_number',
            'nim' => 'user.user_number',
            'nip' => 'user.user_number',
            'student_number' => 'user.user_number',
            'studentnumber' => 'user.user_number',
            'user_number' => 'user.user_number',
            'usernumber' => 'user.user_number',
            'unit' => 'unit.prodi.name',
            'prodi' => 'unit.prodi.name',
            'jurusan' => 'unit.jurusan.name',
            'fakultas' => 'unit.fakultas.name',
            'unit.prodi' => 'unit.prodi.name',
            'unit.prodi.name' => 'unit.prodi.name',
            'unit.jurusan' => 'unit.jurusan.name',
            'unit.jurusan.name' => 'unit.jurusan.name',
            'unit.fakultas' => 'unit.fakultas.name',
            'unit.fakultas.name' => 'unit.fakultas.name',
            // Legacy aliases (kept for backward compatibility)
            'unit.name' => 'unit.prodi.name',
            'unit.parent' => 'unit.jurusan.name',
            'unit.parent.name' => 'unit.jurusan.name',
            'unit.parent.parent' => 'unit.fakultas.name',
            'unit.parent.parent.name' => 'unit.fakultas.name',
            'unitparent' => 'unit.jurusan.name',
            'unitparentname' => 'unit.jurusan.name',
            'unitparentparent' => 'unit.fakultas.name',
            'unitparentparentname' => 'unit.fakultas.name',
        ];
        if (isset($simpleAliases[$compact])) {
            return $simpleAliases[$compact];
        }

        // signer.<ROLE>.<field>
        if (preg_match('/^signer\\.([a-z0-9_]+)\\.(.+)$/i', $raw, $m) === 1) {
            $role = strtoupper($m[1]);
            $fieldRaw = strtolower(preg_replace('/\\s+/', '', $m[2]) ?? '');

            $fieldAliases = [
                'nama' => 'name',
                'name' => 'name',
                'email' => 'email',
                'nip' => 'user_number',
                'nim' => 'user_number',
                'npm' => 'user_number',
                'student_number' => 'user_number',
                'studentnumber' => 'user_number',
                'user_number' => 'user_number',
                'usernumber' => 'user_number',
                'jabatan' => 'jabatan',
                'position' => 'jabatan',
                'title' => 'jabatan',
                'role_label' => 'jabatan',
                'rolelabel' => 'jabatan',
                'unit' => 'unit.prodi.name',
                'prodi' => 'unit.prodi.name',
                'jurusan' => 'unit.jurusan.name',
                'fakultas' => 'unit.fakultas.name',
                'unit.name' => 'unit.prodi.name',
                'unit.prodi' => 'unit.prodi.name',
                'unit.prodi.name' => 'unit.prodi.name',
                'unit.jurusan' => 'unit.jurusan.name',
                'unit.jurusan.name' => 'unit.jurusan.name',
                'unit.fakultas' => 'unit.fakultas.name',
                'unit.fakultas.name' => 'unit.fakultas.name',
                // Legacy aliases
                'unit.parent' => 'unit.jurusan.name',
                'unit.parent.name' => 'unit.jurusan.name',
                'unit.parent.parent' => 'unit.fakultas.name',
                'unit.parent.parent.name' => 'unit.fakultas.name',
                'unit_parent' => 'unit.jurusan.name',
                'unit_parent.name' => 'unit.jurusan.name',
                'unit_parent_parent' => 'unit.fakultas.name',
                'unit_parent_parent.name' => 'unit.fakultas.name',
            ];
            $field = $fieldAliases[$fieldRaw] ?? null;

            if ($field !== null) {
                return "signer.{$role}.{$field}";
            }

            // Try to normalize dot-path variations (best-effort)
            $fieldRaw = str_replace('_', '.', $fieldRaw);
            if (in_array($fieldRaw, ['name','email','student.number','user.number','unit.name','unit.parent.name','unit.parent.parent.name','unit.prodi.name','unit.jurusan.name','unit.fakultas.name'], true)) {
                if ($fieldRaw === 'student.number') $fieldRaw = 'user_number';
                if ($fieldRaw === 'user.number') $fieldRaw = 'user_number';
                if ($fieldRaw === 'unit.name') $fieldRaw = 'unit.prodi.name';
                if ($fieldRaw === 'unit.parent.name') $fieldRaw = 'unit.jurusan.name';
                if ($fieldRaw === 'unit.parent.parent.name') $fieldRaw = 'unit.fakultas.name';
                return "signer.{$role}.{$fieldRaw}";
            }

            return trim($raw);
        }

        // user.* / unit.*
        if (preg_match('/^(user|unit)\\.(.+)$/i', $raw, $m) === 1) {
            $base = strtolower($m[1]);
            $rest = strtolower(preg_replace('/\\s+/', '', $m[2]) ?? '');

            if ($base === 'user') {
                if (in_array($rest, ['name','email'], true)) return "{$base}.{$rest}";
                if (in_array($rest, ['studentnumber','student_number','npm','nim','nip'], true)) return 'user.user_number';
                if (in_array($rest, ['usernumber','user_number'], true)) return 'user.user_number';
                if (in_array($rest, ['jabatan','position','title'], true)) return 'user.jabatan';
            }

            if ($base === 'unit') {
                if (in_array($rest, ['name', 'prodi', 'prodi.name'], true)) return 'unit.prodi.name';
                if (in_array($rest, ['jurusan', 'jurusan.name', 'parent','parent.name','parentname','parent_name'], true)) return 'unit.jurusan.name';
                if (in_array($rest, ['fakultas', 'fakultas.name', 'parent.parent', 'parent.parent.name', 'parentparent', 'parentparentname', 'parent_parent', 'parent_parent.name'], true)) {
                    return 'unit.fakultas.name';
                }
            }
        }

        return trim($raw);
    }

    /**
     * Configure mandatory gate workflow (cannot be disabled).
     *
     * @param array<int,string> $steps
     */
    public function updateGate(Service $service, User $actor, string $gateRole, array $steps): void
    {
        $gateRole = $this->normalizeGateRole($gateRole);
        if (!in_array($gateRole, ['Admin Jurusan', 'Staf ULT'], true)) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'gate_role tidak valid.');
        }

        $steps = array_values(array_unique(array_map(fn ($s) => strtoupper(trim((string) $s)), $steps)));
        foreach (['VERIFY_INITIAL','INPUT_NOMOR_SURAT'] as $req) {
            if (!in_array($req, $steps, true)) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, "gate_steps_json wajib memuat {$req}.");
            }
        }

        $wf = $service->workflow;
        if (!$wf) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'service_workflows belum ada.');
        }

        $wf->gate_enabled = true;
        $wf->gate_role = $gateRole;
        $wf->gate_steps_json = $steps;
        $wf->save();

        $this->audit->log('doc.service.gate_updated', 'service_workflows', (string) $wf->id, [
            'service_id' => $service->id,
            'gate_role' => $gateRole,
            'gate_steps' => $steps,
        ]);
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

    /**
     * Bulk set signer chain.
     *
     * @param array<int,array{role:string,custom_label?:?string,order_index:int,is_required?:bool,requires_signature_upload?:bool,signature_file_types?:array<int,string>,signature_max_size_kb?:?int}> $items
     */
    public function setSigners(Service $service, User $actor, array $items): void
    {
        $rows = [];
        foreach ($items as $it) {
            if (!is_array($it)) continue;
            $role = strtoupper(trim((string) ($it['role'] ?? '')));
            $order = (int) ($it['order_index'] ?? 0);
            if ($role === '' || $order < 1) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Signer role/order invalid.');
            }

            $customLabel = isset($it['custom_label']) ? trim((string) $it['custom_label']) : null;
            if ($role === 'CUSTOM' && $customLabel === '') {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, "Signer CUSTOM pada urutan {$order} wajib punya label.");
            }
            if (in_array($role, ['CUSTOM', 'DOSEN', 'PEMOHON'], true) && $customLabel !== null && mb_strlen($customLabel, 'UTF-8') > 120) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, "Label signer {$role} pada urutan {$order} maksimal 120 karakter.");
            }

            $reqSig = (bool) ($it['requires_signature_upload'] ?? false);
            $types = $reqSig ? (array) ($it['signature_file_types'] ?? []) : null;
            $maxKb = $reqSig ? (int) ($it['signature_max_size_kb'] ?? 0) : null;
            if ($reqSig && (empty($types) || $maxKb <= 0)) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, "Signer {$role} requires_signature_upload but missing types/max size.");
            }

            $rows[] = [
                'service_id' => $service->id,
                'role' => $role,
                'custom_label' => in_array($role, ['CUSTOM', 'DOSEN', 'PEMOHON'], true)
                    ? ($customLabel !== '' ? $customLabel : null)
                    : null,
                'order_index' => $order,
                'is_required' => (bool) ($it['is_required'] ?? true),
                'requires_signature_upload' => $reqSig,
                'signature_file_types' => $types,
                'signature_max_size_kb' => $maxKb,
            ];
        }

        // Validate sequential order
        $orders = array_map(fn ($r) => $r['order_index'], $rows);
        sort($orders);
        $expected = range(1, count($orders));
        if ($orders !== $expected) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'order_index harus berurutan mulai 1.');
        }

        DB::transaction(function () use ($service, $rows) {
            ServiceSigner::query()->where('service_id', $service->id)->delete();
            foreach ($rows as $r) {
                ServiceSigner::create($r);
            }
        });

        $this->audit->log('doc.service.signers_updated', 'services', (string) $service->id, [
            'count' => count($rows),
        ]);
    }

    public function publish(Service $service, User $actor): void
    {
        $errors = $this->readiness->check($service);
        if (!empty($errors)) {
            $flat = [];
            foreach ($errors as $cat => $msgs) {
                foreach ($msgs as $m) $flat[] = "[{$cat}] {$m}";
            }
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, implode(' ', $flat));
        }

        $service->status = ServiceStatus::PUBLISHED;
        $service->is_active = true;
        $service->save();

        $this->audit->log('doc.service.published', 'services', (string) $service->id, [
            'status' => 'PUBLISHED',
        ]);
    }
}
