<?php

namespace Tests\Support;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

trait CreatesRolesPermissions
{
    protected function seedPermissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $perms = [
            'users.manage','roles.manage','permissions.manage',
            'services.manage','cms.manage','site_settings.manage',
            'requests.view_any','requests.view_unit','requests.view_own',
            'requests.create_own','requests.update_own',
            'requests.process_unit','requests.review_ult','requests.forward_faculty',
            'approvals.unit.sign','approvals.faculty.sign',
            'document_numbers.issue',
            'attachments.upload_own','attachments.upload_output','attachments.download_private',
            'reports.view',
            'audit_logs.view',
            'feedbacks.manage',
            'letter_numbers.manage_formats',

            // Document module
            'doc_services.manage','doc_services.publish','doc_templates.upload','doc_placeholders.manage','doc_signers.manage',
            'doc_requests.gate','doc_signoffs.decide','doc_requests.assemble',
        ];

        foreach ($perms as $p) {
            Permission::findOrCreate($p);
        }
    }

    protected function makeRole(string $name, array $permissions = []): Role
    {
        $role = Role::findOrCreate($name);
        if ($permissions) $role->syncPermissions($permissions);
        return $role;
    }
}
