<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::firstOrCreate([
            'name' => 'feedbacks.manage',
            'guard_name' => 'web',
        ]);

        foreach ([
            'Superadmin',
            'Staf ULT',
            'Admin Fakultas',
            'Admin Jurusan',
        ] as $roleName) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            if (!$role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([
            'Superadmin',
            'Staf ULT',
            'Admin Fakultas',
            'Admin Jurusan',
        ] as $roleName) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();

            if ($role && $role->hasPermissionTo('feedbacks.manage')) {
                $role->revokePermissionTo('feedbacks.manage');
            }
        }

        Permission::query()
            ->where('name', 'feedbacks.manage')
            ->where('guard_name', 'web')
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
