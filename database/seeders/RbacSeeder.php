<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $perms = [
            // Identity & access
            'users.manage',
            'roles.manage',
            'permissions.manage',

            // Core domain
            'services.manage',
            'cms.manage',
            'site_settings.manage',
            'academics.manage',

            // Requests access
            'requests.view_any',
            'requests.view_unit',
            'requests.view_own',
            'requests.create_own',
            'requests.update_own',

            // Processing / gatekeeper
            'requests.process_unit',
            'requests.review_ult',
            'requests.forward_faculty',

            // Approvals & numbering
            'approvals.unit.sign',
            'approvals.faculty.sign',
            'document_numbers.issue',
            'document_numbers.manage_formats',
            'letter_numbers.manage_formats',

            // Attachments
            'attachments.upload_own',
            'attachments.upload_output',
            'attachments.download_private',

            // Reporting & audit
            'reports.view',
            'audit_logs.view',
            'feedbacks.manage',

            // Document module (layanan dokumen)
            'doc_services.manage',
            'doc_services.publish',
            'doc_templates.upload',
            'doc_placeholders.manage',
            'doc_signers.manage',
            'doc_requests.gate',
            'doc_signoffs.decide',
            'doc_requests.assemble',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $selfServicePerms = [
            'requests.view_own',
            'requests.create_own',
            'requests.update_own',
            'attachments.upload_own',
            'attachments.download_private',
        ];

        $roles = [
            'Superadmin' => $perms,

            // Gatekeeper utama tingkat fakultas
            'Staf ULT' => [
                'document_numbers.manage_formats',
                'letter_numbers.manage_formats',
                'requests.view_any',
                'requests.review_ult',
                'requests.forward_faculty',
                'requests.process_unit',
                'document_numbers.issue',
                'doc_requests.gate',
                'attachments.upload_output',
                'attachments.download_private',
                'audit_logs.view',
                'doc_requests.assemble',
                'doc_signoffs.decide',
                'feedbacks.manage',
            ],

            // Admin fakultas (operasional, bukan gatekeeper khusus)
            'Admin Fakultas' => [
                'requests.view_any',
                'requests.process_unit',
                'approvals.faculty.sign',
                'attachments.upload_output',
                'attachments.download_private',
                'services.manage',
                'cms.manage',
                'site_settings.manage',
                'academics.manage',
                'feedbacks.manage',
            ],

            'Admin Jurusan' => [
                'document_numbers.manage_formats',
                'letter_numbers.manage_formats',
                'requests.view_unit',
                'requests.process_unit',
                'approvals.unit.sign',
                'attachments.upload_output',
                'attachments.download_private',
                'academics.manage',
                'doc_requests.gate',
                'doc_requests.assemble',
                'feedbacks.manage',
                ...$selfServicePerms,
            ],

            // NOTE: "Approver" roles are merged into Admin roles.
            // - Unit approval: Admin Jurusan
            // - Faculty approval: Admin Fakultas
            // - ULT signer (doc module): Staf ULT

            'Mahasiswa' => [
                'requests.view_own',
                'requests.create_own',
                'requests.update_own',
                'attachments.upload_own',
                'attachments.download_private',
            ],
            'Dosen' => [
                'doc_signoffs.decide',
                'attachments.download_private',
                ...$selfServicePerms,
            ],
            'STAFF_FINAL' => [
                'requests.view_unit',
                'doc_requests.assemble',
                'attachments.download_private',
            ],

            // Document module signer roles (permission-gated + dynamic step enforced in controller)
            'KETUA_ORG' => [
                'doc_signoffs.decide',
                ...$selfServicePerms,
            ],
            'SEKRETARIS_ORG' => [
                'doc_signoffs.decide',
                ...$selfServicePerms,
            ],
            'SEKJUR' => [
                'doc_signoffs.decide',
                ...$selfServicePerms,
            ],
            'KAPRODI' => [
                'doc_signoffs.decide',
                ...$selfServicePerms,
            ],
            'KAJUR' => [
                'doc_signoffs.decide',
                ...$selfServicePerms,
            ],
            'DEKAN' => ['doc_signoffs.decide'],
            'WD_AKADEMIK' => ['doc_signoffs.decide'],
            'WD_UMUM' => ['doc_signoffs.decide'],
            'WD_KEMAHASISWAAN' => ['doc_signoffs.decide'],
        ];

        foreach ($roles as $role => $ps) {
            $r = Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
            $r->syncPermissions($ps);
        }

        // Cleanup removed roles from system.
        Role::query()->whereIn('name', ['Admin Prodi', 'Auditor'])->delete();
    }
}
