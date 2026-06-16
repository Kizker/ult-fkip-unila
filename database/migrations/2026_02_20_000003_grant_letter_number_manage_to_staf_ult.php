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
            'name' => 'letter_numbers.manage_formats',
            'guard_name' => 'web',
        ]);

        $role = Role::firstOrCreate([
            'name' => 'Staf ULT',
            'guard_name' => 'web',
        ]);

        if (!$role->hasPermissionTo($permission)) {
            $role->givePermissionTo($permission);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::query()
            ->where('name', 'Staf ULT')
            ->where('guard_name', 'web')
            ->first();

        if ($role && $role->hasPermissionTo('letter_numbers.manage_formats')) {
            $role->revokePermissionTo('letter_numbers.manage_formats');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};

