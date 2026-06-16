<?php

namespace Database\Seeders;

use App\Models\CmsCategory;
use App\Models\Service;
use App\Models\ServiceField;
use App\Models\ServiceWorkflow;
use App\Models\User;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    private ?int $creatorId = null;

    public function run(): void
    {
        $this->creatorId = User::where('email', 'superadmin@demo.test')->value('id');

        $catAcademicPartnership = CmsCategory::firstOrCreate(
            ['type' => 'service', 'slug' => 'akademik-dan-kerja-sama'],
            ['name_id' => 'Akademik dan Kerja Sama', 'name_en' => 'Academic and Partnerships']
        );
        CmsCategory::firstOrCreate(
            ['type' => 'service', 'slug' => 'umum-dan-keuangan'],
            ['name_id' => 'Umum dan Keuangan', 'name_en' => 'General and Finance']
        );
        CmsCategory::firstOrCreate(
            ['type' => 'service', 'slug' => 'kemahasiswaan-dan-alumni'],
            ['name_id' => 'Kemahasiswaan dan Alumni', 'name_en' => 'Student Affairs and Alumni']
        );
        CmsCategory::firstOrCreate(
            ['type' => 'service', 'slug' => 'lainnya'],
            ['name_id' => 'Lainnya', 'name_en' => 'Other']
        );

        // Common fields
        $commonFields = [
            ['key' => 'keperluan', 'label_id' => 'Keperluan', 'label_en' => 'Purpose', 'type' => 'text', 'required' => true, 'sort_order' => 1],
            ['key' => 'angkatan', 'label_id' => 'Angkatan', 'label_en' => 'Cohort', 'type' => 'number', 'required' => true, 'rules_json' => ['min:2000', 'max:2100'], 'sort_order' => 2],
            ['key' => 'catatan', 'label_id' => 'Catatan Tambahan', 'label_en' => 'Additional Notes', 'type' => 'textarea', 'required' => false, 'sort_order' => 3],
        ];

        // ==========================================================
        // Service A) Tanpa TTD (langsung ULT proses & output)
        // ==========================================================
        $a = $this->upsertService(
            slug: 'informasi-akademik-umum',
            categoryId: $catAcademicPartnership->id,
            titleId: 'Informasi Akademik Umum (Tanpa TTD)',
            titleEn: 'General Academic Information (No Signature)',
            summaryId: 'Layanan informasi/rekap sederhana yang tidak memerlukan tanda tangan unit/fakultas.',
            summaryEn: 'Simple information service that does not require signatures.',
            requirementsHtmlId: '<ul><li>Keterangan kebutuhan informasi</li></ul>',
            sopHtmlId: '<ol><li>Mahasiswa mengajukan</li><li>ULT memproses</li><li>Output diunggah & selesai</li></ol>',
            slaDays: null // DATA TIDAK TERSEDIA: SLA final per layanan
        );

        $this->upsertFields($a->id, $commonFields);

        ServiceWorkflow::updateOrCreate(
            ['service_id' => $a->id],
            [
                'require_prodi' => false,
                'require_jurusan' => false,
                'require_unit_signature' => false,
                'require_ult_review' => true,
                'require_faculty_signature' => false,
                'issue_number_at_step' => null,
                'workflow_schema_version' => 1,
                // Order (ULT FKIP): ULT -> Output
                'steps_json' => [
                    [
                        'key' => 'ult_process',
                        'label_id' => 'Diproses ULT',
                        'label_en' => 'Processed by ULT',
                        'role_required' => 'Staf ULT',
                        'unit_scope' => 'fakultas',
                        'actions_allowed' => ['review', 'request_revision', 'reject'],
                        'next_on_approve' => 'output',
                        'next_on_reject' => 'done',
                        'can_request_revision' => true,
                    ],
                    [
                        'key' => 'output',
                        'label_id' => 'Output & Selesai',
                        'label_en' => 'Output & Complete',
                        'role_required' => 'Staf ULT',
                        'unit_scope' => 'fakultas',
                        'actions_allowed' => ['upload_output', 'complete'],
                        'next_on_approve' => 'done',
                        'next_on_reject' => 'done',
                        'can_request_revision' => false,
                    ],
                ],
            ]
        );

        // ==========================================================
        // Service B) Prodi + (opsional TTD Unit) + Review ULT + TTD Fakultas + Nomor
        // Order ULT FKIP: Prodi -> ULT -> Fakultas -> ULT (Penomoran/Output)
        // ==========================================================
        $b = $this->upsertService(
            slug: 'surat-keterangan-aktif-kuliah',
            categoryId: $catAcademicPartnership->id,
            titleId: 'Surat Keterangan Aktif Kuliah (Prodi→ULT→Fakultas)',
            titleEn: 'Active Student Letter (Program→ULT→Faculty)',
            summaryId: 'Surat keterangan untuk keperluan beasiswa/administrasi.',
            summaryEn: 'Letter for scholarship/administration needs.',
            requirementsHtmlId: '<ul><li>KTP</li><li>KRS terakhir</li></ul>',
            sopHtmlId: '<ol><li>Mahasiswa mengajukan</li><li>Prodi verifikasi</li><li>ULT review (gatekeeper)</li><li>TTD Fakultas</li><li>ULT penomoran & output</li></ol>',
            slaDays: 3
        );

        $this->upsertFields($b->id, $commonFields);

        ServiceWorkflow::updateOrCreate(
            ['service_id' => $b->id],
            [
                'require_prodi' => true,
                'require_jurusan' => false,
                'require_unit_signature' => true,
                'require_ult_review' => true,
                'require_faculty_signature' => true,
                'issue_number_at_step' => 'ult_issue',
                'workflow_schema_version' => 1,
                'steps_json' => [
                    [
                        'key' => 'prodi_verify',
                        'label_id' => 'Verifikasi Prodi',
                        'label_en' => 'Program Verification',
                        'role_required' => 'Admin Jurusan',
                        'unit_scope' => 'prodi',
                        'actions_allowed' => ['verify', 'request_revision', 'reject'],
                        'next_on_approve' => 'ult_review',
                        'next_on_reject' => 'done',
                        'can_request_revision' => true,
                    ],
                    [
                        'key' => 'ult_review',
                        'label_id' => 'Review ULT',
                        'label_en' => 'ULT Review',
                        'role_required' => 'Staf ULT',
                        'unit_scope' => 'fakultas',
                        'actions_allowed' => ['review', 'forward_faculty', 'request_revision', 'reject'],
                        'next_on_approve' => 'faculty_sign',
                        'next_on_reject' => 'done',
                        'can_request_revision' => true,
                    ],
                    [
                        'key' => 'faculty_sign',
                        'label_id' => 'Tanda Tangan Fakultas',
                        'label_en' => 'Faculty Signature',
                        'role_required' => 'Admin Fakultas',
                        'unit_scope' => 'fakultas',
                        'actions_allowed' => ['sign', 'reject'],
                        'next_on_approve' => 'ult_issue',
                        'next_on_reject' => 'done',
                        'can_request_revision' => false,
                    ],
                    [
                        'key' => 'ult_issue',
                        'label_id' => 'Penomoran',
                        'label_en' => 'Document Numbering',
                        'role_required' => 'Staf ULT',
                        'unit_scope' => 'fakultas',
                        'actions_allowed' => ['issue_number'],
                        'next_on_approve' => 'output',
                        'next_on_reject' => 'done',
                        'can_request_revision' => false,
                    ],
                    [
                        'key' => 'output',
                        'label_id' => 'Output & Selesai',
                        'label_en' => 'Output & Complete',
                        'role_required' => 'Staf ULT',
                        'unit_scope' => 'fakultas',
                        'actions_allowed' => ['upload_output', 'complete'],
                        'next_on_approve' => 'done',
                        'next_on_reject' => 'done',
                        'can_request_revision' => false,
                    ],
                ],
            ]
        );

        // ==========================================================
        // Service C) Jurusan + TTD Unit + Review ULT + TTD Fakultas + Nomor + Output
        // Order ULT FKIP: Prodi (optional) -> Jurusan -> ULT -> Fakultas -> ULT -> Output
        // ==========================================================
        $c = $this->upsertService(
            slug: 'surat-rekomendasi',
            categoryId: $catAcademicPartnership->id,
            titleId: 'Surat Rekomendasi (Jurusan→ULT→Fakultas)',
            titleEn: 'Recommendation Letter (Department→ULT→Faculty)',
            summaryId: 'Contoh layanan yang memerlukan verifikasi Jurusan, tanda tangan Fakultas, dan penomoran.',
            summaryEn: 'Example service requiring department verification, faculty signature, and numbering.',
            requirementsHtmlId: '<ul><li>Draft surat (jika ada)</li><li>KRS/KHS</li></ul>',
            sopHtmlId: '<ol><li>Mahasiswa mengajukan</li><li>Jurusan verifikasi</li><li>ULT review</li><li>TTD Fakultas</li><li>ULT penomoran</li><li>Selesai</li></ol>',
            slaDays: 5
        );

        $this->upsertFields($c->id, $commonFields);

        ServiceWorkflow::updateOrCreate(
            ['service_id' => $c->id],
            [
                'require_prodi' => false,
                'require_jurusan' => true,
                'require_unit_signature' => true,
                'require_ult_review' => true,
                'require_faculty_signature' => true,
                'issue_number_at_step' => 'ult_issue',
                'workflow_schema_version' => 1,
                'steps_json' => [
                    [
                        'key' => 'jurusan_verify',
                        'label_id' => 'Verifikasi Jurusan',
                        'label_en' => 'Department Verification',
                        'role_required' => 'Admin Jurusan',
                        'unit_scope' => 'jurusan',
                        'actions_allowed' => ['verify', 'request_revision', 'reject'],
                        'next_on_approve' => 'ult_review',
                        'next_on_reject' => 'done',
                        'can_request_revision' => true,
                    ],
                    [
                        'key' => 'ult_review',
                        'label_id' => 'Review ULT',
                        'label_en' => 'ULT Review',
                        'role_required' => 'Staf ULT',
                        'unit_scope' => 'fakultas',
                        'actions_allowed' => ['review', 'forward_faculty', 'request_revision', 'reject'],
                        'next_on_approve' => 'faculty_sign',
                        'next_on_reject' => 'done',
                        'can_request_revision' => true,
                    ],
                    [
                        'key' => 'faculty_sign',
                        'label_id' => 'Tanda Tangan Fakultas',
                        'label_en' => 'Faculty Signature',
                        'role_required' => 'Admin Fakultas',
                        'unit_scope' => 'fakultas',
                        'actions_allowed' => ['sign', 'reject'],
                        'next_on_approve' => 'ult_issue',
                        'next_on_reject' => 'done',
                        'can_request_revision' => false,
                    ],
                    [
                        'key' => 'ult_issue',
                        'label_id' => 'Penomoran',
                        'label_en' => 'Document Numbering',
                        'role_required' => 'Staf ULT',
                        'unit_scope' => 'fakultas',
                        'actions_allowed' => ['issue_number'],
                        'next_on_approve' => 'output',
                        'next_on_reject' => 'done',
                        'can_request_revision' => false,
                    ],
                    [
                        'key' => 'output',
                        'label_id' => 'Output & Selesai',
                        'label_en' => 'Output & Complete',
                        'role_required' => 'Staf ULT',
                        'unit_scope' => 'fakultas',
                        'actions_allowed' => ['upload_output', 'complete'],
                        'next_on_approve' => 'done',
                        'next_on_reject' => 'done',
                        'can_request_revision' => false,
                    ],
                ],
            ]
        );
    }

    private function upsertService(
        string $slug,
        int $categoryId,
        string $titleId,
        string $titleEn,
        string $summaryId,
        string $summaryEn,
        string $requirementsHtmlId,
        string $sopHtmlId,
        ?int $slaDays
    ): Service {
        return Service::updateOrCreate(
            ['slug' => $slug],
            [
                'category_id' => $categoryId,
                'title_id' => $titleId,
                'title_en' => $titleEn,
                'summary_id' => $summaryId,
                'summary_en' => $summaryEn,
                'requirements_html_id' => $requirementsHtmlId,
                'requirements_html_en' => $requirementsHtmlId,
                'sop_html_id' => $sopHtmlId,
                'sop_html_en' => $sopHtmlId,
                'sla_days' => $slaDays,
                'is_active' => true,
                'status' => \App\Enums\ServiceStatus::PUBLISHED,
                'created_by' => $this->creatorId,
            ]
        );
    }

    private function upsertFields(int $serviceId, array $fields): void
    {
        foreach ($fields as $f) {
            ServiceField::updateOrCreate(
                ['service_id' => $serviceId, 'key' => $f['key']],
                [
                    'label_id' => $f['label_id'],
                    'label_en' => $f['label_en'] ?? null,
                    'type' => $f['type'],
                    'required' => (bool)($f['required'] ?? false),
                    'rules_json' => $f['rules_json'] ?? null,
                    'options_json' => $f['options_json'] ?? null,
                    'sort_order' => $f['sort_order'] ?? 999,
                ]
            );
        }
    }
}
