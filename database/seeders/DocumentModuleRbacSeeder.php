<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Safe RBAC seeder for Document Module:
 * - Adds missing permissions
 * - Grants them to Superadmin roles without removing existing permissions
 * - Optionally creates signer roles (without syncing)
 */
class DocumentModuleRbacSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $perms = [
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

        // Ensure Superadmin roles get the doc-module permissions (no sync -> no removal).
        Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web'])
            ->givePermissionTo($perms);

        // Ensure admin unit role has gate + assemble permissions (no sync).
        foreach (['Admin Jurusan'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web'])
                ->givePermissionTo(['doc_requests.gate', 'doc_requests.assemble']);
        }

        // Ensure staff final role exists.
        Role::firstOrCreate(['name' => 'STAFF_FINAL', 'guard_name' => 'web'])
            ->givePermissionTo(['doc_requests.assemble']);

        // Ensure ULT staff role can decide signoffs (no sync).
        Role::firstOrCreate(['name' => 'Staf ULT', 'guard_name' => 'web'])
            ->givePermissionTo(['doc_signoffs.decide', 'doc_requests.assemble']);

        // Ensure signer roles exist with decide permission (no sync).
        foreach ([
            'KETUA_ORG',
            'SEKRETARIS_ORG',
            'SEKJUR',
            'KAPRODI',
            'KAJUR',
            'DEKAN',
            'WD_AKADEMIK',
            'WD_UMUM',
            'WD_KEMAHASISWAAN',
        ] as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web'])
                ->givePermissionTo(['doc_signoffs.decide']);
        }
    }
}
