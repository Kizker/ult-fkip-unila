<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const MODEL_TYPE = 'App\\Models\\User';

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'doc_signoffs.decide',
            'requests.view_own',
            'requests.create_own',
            'requests.update_own',
            'attachments.upload_own',
            'attachments.download_private',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        $sekjurRole = Role::firstOrCreate(['name' => 'SEKJUR', 'guard_name' => 'web']);
        $sekjurRole->givePermissionTo($permissions);

        // Backfill: users who were modeled as Sekretaris Jurusan via legacy role.
        $legacyRoleId = DB::table('roles')->where('name', 'SEKRETARIS_ORG')->value('id');
        if ($legacyRoleId) {
            $sekjurRoleId = (int) $sekjurRole->id;
            $userIds = DB::table('model_has_roles')
                ->join('users', 'users.id', '=', 'model_has_roles.model_id')
                ->where('model_has_roles.role_id', $legacyRoleId)
                ->where('model_has_roles.model_type', self::MODEL_TYPE)
                ->whereRaw("UPPER(COALESCE(users.jabatan, '')) LIKE ?", ['%SEKRETARIS JURUSAN%'])
                ->pluck('model_has_roles.model_id')
                ->map(fn ($v) => (int) $v)
                ->all();

            foreach ($userIds as $userId) {
                $exists = DB::table('model_has_roles')
                    ->where('role_id', $sekjurRoleId)
                    ->where('model_type', self::MODEL_TYPE)
                    ->where('model_id', $userId)
                    ->exists();

                if (!$exists) {
                    DB::table('model_has_roles')->insert([
                        'role_id' => $sekjurRoleId,
                        'model_type' => self::MODEL_TYPE,
                        'model_id' => $userId,
                    ]);
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // no-op
    }
};
