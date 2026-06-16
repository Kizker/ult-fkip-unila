<?php

namespace App\Services\Documents;

use App\Enums\PlaceholderSourceType;
use App\Models\Service;
use Illuminate\Support\Arr;

class ServiceDocumentReadinessChecker
{
    /**
     * Returns an array of actionable validation errors keyed by category.
     *
     * @return array<string, array<int, string>>
     */
    public function check(Service $service): array
    {
        $errors = [];

        $service->loadMissing(['templates', 'placeholders', 'fields', 'workflow', 'signers']);

        $isCertificateMode = $service->usesRequestPptxSource();
        if ($isCertificateMode) {
            $wf = $service->workflow;
            if (!$wf) {
                $errors['workflow'][] = 'service_workflows belum ada.';
            } else {
                if (!$wf->gate_enabled) {
                    $errors['workflow'][] = 'Gate nomor surat wajib aktif (gate_enabled=true).';
                }

                $normalized = $this->normalizeGateRole($wf->gate_role);
                if ($normalized === '' || !$this->isAllowedGateRole($normalized)) {
                    $errors['workflow'][] = 'gate_role wajib Admin Jurusan atau Staf ULT.';
                }

                $steps = is_array($wf->gate_steps_json) ? $wf->gate_steps_json : [];
                foreach (['VERIFY_INITIAL', 'INPUT_NOMOR_SURAT'] as $rk) {
                    if (!in_array($rk, $steps, true)) {
                        $errors['workflow'][] = "gate_steps_json wajib memuat {$rk}.";
                    }
                }
            }

            if ($service->status !== null && $service->status->value !== 'DRAFT' && $service->status->value !== 'PUBLISHED') {
                $errors['service'][] = 'Status layanan tidak valid untuk publish workflow dokumen.';
            }

            return $errors;
        }

        $mainTpl = $service->templates->firstWhere('type', \App\Enums\ServiceTemplateType::MAIN_DOCX);
        if (!$mainTpl) {
            $errors['template'][] = 'MAIN_DOCX template belum ada.';
        }

        if ($service->placeholders->isEmpty()) {
            $errors['placeholders'][] = 'Placeholder belum diekstrak/disimpan.';
            return $errors;
        }

        $phKeys = $service->placeholders->pluck('placeholder_key')->all();
        $phKeySet = array_fill_keys($phKeys, true);

        foreach ($service->placeholders as $ph) {
            if ($ph->source_type === null) {
                $errors['mapping'][] = "Placeholder {$ph->placeholder_key} belum punya mapping source_type.";
                continue;
            }

            if ($ph->placeholder_key === 'NOMOR_SURAT') {
                if ($ph->source_type !== PlaceholderSourceType::INTERNAL) {
                    $errors['mapping'][] = 'NOMOR_SURAT wajib source_type=INTERNAL dan tidak boleh diubah.';
                }
            }
            if ($ph->placeholder_key === 'TANGGAL_SURAT') {
                if ($ph->source_type !== PlaceholderSourceType::SYSTEM_AUTOFILL) {
                    $errors['mapping'][] = 'TANGGAL_SURAT wajib source_type=SYSTEM_AUTOFILL dan tidak boleh diubah.';
                }
            }
        }

        if (!isset($phKeySet['NOMOR_SURAT'])) {
            $errors['placeholders'][] = 'Template wajib memuat {{NOMOR_SURAT}}.';
        }
        if (!isset($phKeySet['TANGGAL_SURAT'])) {
            $errors['placeholders'][] = 'Template wajib memuat {{TANGGAL_SURAT}}.';
        }

        // FORM placeholders: support either source_ref (field key) OR maps_to_placeholder_key fallback.
        foreach ($service->placeholders->where('source_type', PlaceholderSourceType::FORM) as $ph) {
            $ref = trim((string) ($ph->source_ref ?? ''));
            if ($ref !== '') {
                $field = $service->fields->firstWhere('key', $ref);
                if (!$field) {
                    $errors['form'][] = "Source ref FORM {$ref} tidak ditemukan di Form Builder (placeholder {$ph->placeholder_key}).";
                }
                continue;
            }

            $field = $service->fields->firstWhere('maps_to_placeholder_key', $ph->placeholder_key);
            if (!$field) {
                $errors['form'][] = "Placeholder FORM {$ph->placeholder_key} belum punya service_fields.maps_to_placeholder_key atau isi source_ref.";
            }
        }

        // Reject stray mapping: no service field may point to a placeholder not in template.
        foreach ($service->fields as $f) {
            if (!$f->maps_to_placeholder_key) continue;
            if (!isset($phKeySet[$f->maps_to_placeholder_key])) {
                $errors['form'][] = "Field {$f->key} memetakan placeholder {$f->maps_to_placeholder_key} yang tidak ada di template.";
            }
        }

        // Gate config: cannot be disabled; must contain steps
        $wf = $service->workflow;
        if (!$wf) {
            $errors['workflow'][] = 'service_workflows belum ada.';
        } else {
            if (!$wf->gate_enabled) {
                $errors['workflow'][] = 'Gate nomor surat wajib aktif (gate_enabled=true).';
            }

            $normalized = $this->normalizeGateRole($wf->gate_role);
            if ($normalized === '' || !$this->isAllowedGateRole($normalized)) {
                $errors['workflow'][] = 'gate_role wajib Admin Jurusan atau Staf ULT.';
            }

            $steps = is_array($wf->gate_steps_json) ? $wf->gate_steps_json : [];
            $required = ['VERIFY_INITIAL', 'INPUT_NOMOR_SURAT'];
            foreach ($required as $rk) {
                if (!in_array($rk, $steps, true)) {
                    $errors['workflow'][] = "gate_steps_json wajib memuat {$rk}.";
                }
            }
        }

        // Signers: must exist and sequential
        if ($service->signers->isEmpty()) {
            $errors['signers'][] = 'Signer chain belum didefinisikan.';
        } else {
            $orders = $service->signers->pluck('order_index')->sort()->values()->all();
            $expected = range(1, count($orders));
            if ($orders !== $expected) {
                $errors['signers'][] = 'order_index signer harus berurutan mulai 1 tanpa gap.';
            }

            foreach ($service->signers as $s) {
                $role = strtoupper(trim((string) ($s->role ?? '')));
                if ($role === 'CUSTOM') {
                    $customLabel = trim((string) ($s->custom_label ?? ''));
                    if ($customLabel === '') {
                        $errors['signers'][] = "Signer CUSTOM urutan {$s->order_index} wajib punya label dari admin.";
                    }
                }

                if ($s->requires_signature_upload) {
                    $types = is_array($s->signature_file_types) ? $s->signature_file_types : [];
                    $maxKb = $s->signature_max_size_kb;
                    if (empty($types) || !is_int($maxKb) || $maxKb <= 0) {
                        $errors['signers'][] = "Signer {$s->role} requires_signature_upload=true wajib punya signature_file_types + signature_max_size_kb.";
                    }

                    // Restrict signature types to safe image formats only (no svg)
                    $allowed = ['image/png','image/jpeg','image/webp'];
                    $bad = array_values(array_diff($types, $allowed));
                    if (!empty($bad)) {
                        $errors['signers'][] = "Signer {$s->role} signature_file_types hanya boleh: ".implode(', ', $allowed).'.';
                    }
                } else {
                    // If optional signer is skippable, it must not require signature upload.
                    if (!$s->is_required && $s->requires_signature_upload) {
                        $errors['signers'][] = "Signer {$s->role} tidak boleh optional sekaligus requires_signature_upload=true.";
                    }
                }
            }
        }

        // PROFILE placeholders: validate known refs
        $profileAllowed = [
            'user.name',
            'user.email',
            'user.student_number',
            'user.user_number',
            'user.jabatan',
            'unit.name',
            'unit.parent.name',
            'unit.parent.parent.name',
            'unit.prodi.name',
            'unit.jurusan.name',
            'unit.fakultas.name',
        ];
        $signerRoles = $service->signers->pluck('role')->filter()->unique()->values()->all();
        foreach ($service->placeholders->where('source_type', PlaceholderSourceType::PROFILE) as $ph) {
            $ref = (string) ($ph->source_ref ?? '');
            if ($ref === '') {
                $errors['profile'][] = "DATA TIDAK TERSEDIA: profile field {$ph->source_ref} (placeholder {$ph->placeholder_key}).";
                continue;
            }

            if (in_array($ref, $profileAllowed, true)) {
                continue;
            }

            if (preg_match('/^signer\\.([A-Z0-9_]+)\\.(jabatan|name|email|student_number|user_number|unit\\.name|unit\\.parent\\.name|unit\\.parent\\.parent\\.name|unit\\.prodi\\.name|unit\\.jurusan\\.name|unit\\.fakultas\\.name)$/', $ref, $m) === 1) {
                $role = $m[1];
                if (!in_array($role, $signerRoles, true)) {
                    $errors['profile'][] = "Signer role {$role} tidak ada di signer chain (placeholder {$ph->placeholder_key}).";
                }
                continue;
            }

            $errors['profile'][] = "DATA TIDAK TERSEDIA: profile field {$ph->source_ref} (placeholder {$ph->placeholder_key}).";
        }

        // INTERNAL placeholders: currently supported keys
        foreach ($service->placeholders->where('source_type', PlaceholderSourceType::INTERNAL) as $ph) {
            if ($ph->placeholder_key === 'NOMOR_SURAT') continue;
            if (in_array($ph->placeholder_key, ['NIP_PENANDATANGAN', 'NAMA_PENANDATANGAN'], true)) continue;
            $errors['internal'][] = "DATA TIDAK TERSEDIA: internal source untuk placeholder {$ph->placeholder_key}.";
        }

        // SYSTEM_AUTOFILL placeholders: currently supported keys
        foreach ($service->placeholders->where('source_type', PlaceholderSourceType::SYSTEM_AUTOFILL) as $ph) {
            if ($ph->placeholder_key === 'TANGGAL_SURAT') continue;
            $errors['system'][] = "DATA TIDAK TERSEDIA: system autofill untuk placeholder {$ph->placeholder_key}.";
        }

        // Publish lifecycle (compat): allow null status for legacy services; document module uses DRAFT/PUBLISHED.
        if ($service->status !== null && $service->status->value !== 'DRAFT' && $service->status->value !== 'PUBLISHED') {
            $errors['service'][] = 'Status layanan tidak valid untuk publish workflow dokumen.';
        }

        return $errors;
    }

    public function isReady(Service $service): bool
    {
        return empty(Arr::flatten($this->check($service)));
    }

    private function normalizeGateRole(?string $raw): string
    {
        $role = trim((string) $raw);
        $normalized = strtoupper(str_replace(' ', '_', $role));

        return match ($normalized) {
            'ADMIN_JURUSAN',
            'ADMIN_JURUSAN_PER_PRODI',
            'ADMIN_PRODI' => 'Admin Jurusan',
            'STAF_ULT',
            'STAFF_ULT' => 'Staf ULT',
            default => $role,
        };
    }

    private function isAllowedGateRole(string $role): bool
    {
        return in_array($role, ['Admin Jurusan', 'Staf ULT'], true);
    }
}
