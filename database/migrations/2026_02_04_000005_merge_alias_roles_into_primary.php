<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const MODEL_TYPE = 'App\\Models\\User';

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $map = [
            'SUPERADMIN' => 'Superadmin',
            'MAHASISWA' => 'Mahasiswa',
            'ADMIN_PRODI' => 'Admin Prodi',
            'ADMIN_JURUSAN' => 'Admin Jurusan',
        ];

        foreach ($map as $from => $to) {
            $this->mergeRole($from, $to);
        }

        // Normalize document-module gate_role values.
        if (Schema::hasTable('service_workflows')) {
            DB::table('service_workflows')
                ->where('gate_role', 'ADMIN_PRODI')
                ->update(['gate_role' => 'Admin Prodi']);

            DB::table('service_workflows')
                ->where('gate_role', 'ADMIN_JURUSAN')
                ->update(['gate_role' => 'Admin Jurusan']);

            // Also normalize steps_json role_required labels if stored in alias form.
            $rows = DB::table('service_workflows')
                ->select(['id', 'steps_json'])
                ->whereNotNull('steps_json')
                ->get();

            foreach ($rows as $row) {
                $steps = json_decode((string) $row->steps_json, true);
                if (!is_array($steps)) continue;

                $changed = false;
                foreach ($steps as &$step) {
                    if (!is_array($step)) continue;
                    $roleRequired = isset($step['role_required']) ? trim((string) $step['role_required']) : '';
                    if ($roleRequired === 'ADMIN_PRODI') {
                        $step['role_required'] = 'Admin Prodi';
                        $changed = true;
                    } elseif ($roleRequired === 'ADMIN_JURUSAN') {
                        $step['role_required'] = 'Admin Jurusan';
                        $changed = true;
                    } elseif ($roleRequired === 'SUPERADMIN') {
                        $step['role_required'] = 'Superadmin';
                        $changed = true;
                    } elseif ($roleRequired === 'MAHASISWA') {
                        $step['role_required'] = 'Mahasiswa';
                        $changed = true;
                    }
                }
                unset($step);

                if ($changed) {
                    DB::table('service_workflows')
                        ->where('id', $row->id)
                        ->update(['steps_json' => json_encode($steps, JSON_UNESCAPED_UNICODE)]);
                }
            }
        }

        // Remove alias roles to avoid duplicates in dropdown.
        $this->deleteRolesIfExist(array_keys($map));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // no-op
    }

    private function mergeRole(string $from, string $to): void
    {
        $fromRoleId = DB::table('roles')->where('name', $from)->value('id');
        if (!$fromRoleId) return;

        $toRole = Role::firstOrCreate(['name' => $to, 'guard_name' => 'web']);
        $toRoleId = (int) $toRole->id;

        // Merge permissions (keep target's existing ones).
        if (Schema::hasTable('role_has_permissions')) {
            $permIds = DB::table('role_has_permissions')
                ->where('role_id', $fromRoleId)
                ->pluck('permission_id')
                ->map(fn ($v) => (int) $v)
                ->all();

            foreach ($permIds as $pid) {
                $exists = DB::table('role_has_permissions')
                    ->where('role_id', $toRoleId)
                    ->where('permission_id', $pid)
                    ->exists();
                if (!$exists) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $pid,
                        'role_id' => $toRoleId,
                    ]);
                }
            }
        }

        // Move users.
        $userIds = DB::table('model_has_roles')
            ->where('role_id', $fromRoleId)
            ->where('model_type', self::MODEL_TYPE)
            ->pluck('model_id')
            ->map(fn ($v) => (int) $v)
            ->all();

        foreach ($userIds as $uid) {
            $exists = DB::table('model_has_roles')
                ->where('role_id', $toRoleId)
                ->where('model_type', self::MODEL_TYPE)
                ->where('model_id', $uid)
                ->exists();

            if (!$exists) {
                DB::table('model_has_roles')->insert([
                    'role_id' => $toRoleId,
                    'model_type' => self::MODEL_TYPE,
                    'model_id' => $uid,
                ]);
            }
        }

        DB::table('model_has_roles')
            ->where('role_id', $fromRoleId)
            ->where('model_type', self::MODEL_TYPE)
            ->delete();
    }

    private function deleteRolesIfExist(array $names): void
    {
        $ids = DB::table('roles')->whereIn('name', $names)->pluck('id')->map(fn ($v) => (int) $v)->all();
        if (count($ids) < 1) return;

        if (Schema::hasTable('role_has_permissions')) {
            DB::table('role_has_permissions')->whereIn('role_id', $ids)->delete();
        }
        if (Schema::hasTable('model_has_roles')) {
            DB::table('model_has_roles')->whereIn('role_id', $ids)->delete();
        }
        DB::table('roles')->whereIn('id', $ids)->delete();
    }
};

