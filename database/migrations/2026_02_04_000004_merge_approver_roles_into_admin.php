<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const MODEL_TYPE = 'App\\Models\\User';

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permUnitSign = Permission::firstOrCreate(['name' => 'approvals.unit.sign', 'guard_name' => 'web']);
        $permFacultySign = Permission::firstOrCreate(['name' => 'approvals.faculty.sign', 'guard_name' => 'web']);
        $permDocDecide = Permission::firstOrCreate(['name' => 'doc_signoffs.decide', 'guard_name' => 'web']);
        $permDocAssemble = Permission::firstOrCreate(['name' => 'doc_requests.assemble', 'guard_name' => 'web']);

        $adminProdi = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web']);
        $adminJurusan = Role::firstOrCreate(['name' => 'Admin Jurusan', 'guard_name' => 'web']);
        $adminFakultas = Role::firstOrCreate(['name' => 'Admin Fakultas', 'guard_name' => 'web']);
        $stafUlt = Role::firstOrCreate(['name' => 'Staf ULT', 'guard_name' => 'web']);

        // Ensure "Admin" roles cover approvals (replacing legacy Approver roles).
        $adminProdi->givePermissionTo([$permUnitSign]);
        $adminJurusan->givePermissionTo([$permUnitSign]);
        $adminFakultas->givePermissionTo([$permFacultySign]);

        // Ensure ULT staff can be used as document-module signer (replacing APPROVER_ULT).
        $stafUlt->givePermissionTo([$permDocDecide, $permDocAssemble]);

        $now = now();

        // Update doc-module signer role strings.
        if (Schema::hasTable('service_signers')) {
            DB::table('service_signers')
                ->where('role', 'APPROVER_ULT')
                ->update(['role' => 'Staf ULT', 'updated_at' => $now]);
        }
        if (Schema::hasTable('request_signoffs')) {
            DB::table('request_signoffs')
                ->where('signer_role', 'APPROVER_ULT')
                ->update(['signer_role' => 'Staf ULT', 'updated_at' => $now]);
        }

        // Update workflow step display roles (non-blocking; approvals are permission-based).
        if (Schema::hasTable('service_workflows')) {
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
                    if ($roleRequired === 'Approver Fakultas') {
                        $step['role_required'] = 'Admin Fakultas';
                        $changed = true;
                    }
                    if ($roleRequired === 'Approver Unit') {
                        $step['role_required'] = 'Admin Prodi';
                        $changed = true;
                    }
                }
                unset($step);

                if ($changed) {
                    DB::table('service_workflows')
                        ->where('id', $row->id)
                        ->update([
                            'steps_json' => json_encode($steps, JSON_UNESCAPED_UNICODE),
                            'updated_at' => $now,
                        ]);
                }
            }
        }

        // Reassign users from legacy Approver roles to Admin roles.
        $this->moveUsersFromRole('Approver Fakultas', 'Admin Fakultas');
        $this->moveUsersFromRole('APPROVER_ULT', 'Staf ULT');
        $this->moveApproverUnitUsersToAdmin();

        // Remove legacy roles (no longer selectable/used).
        $this->deleteRolesIfExist(['Approver Unit', 'Approver Fakultas', 'APPROVER_ULT']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Intentionally no-op: this migration consolidates roles and updates data.
    }

    private function moveUsersFromRole(string $fromRoleName, string $toRoleName): void
    {
        $fromRoleId = DB::table('roles')->where('name', $fromRoleName)->value('id');
        if (!$fromRoleId) return;

        $toRoleId = DB::table('roles')->where('name', $toRoleName)->value('id');
        if (!$toRoleId) {
            $toRoleId = Role::firstOrCreate(['name' => $toRoleName, 'guard_name' => 'web'])->id;
        }

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

    private function moveApproverUnitUsersToAdmin(): void
    {
        $fromRoleId = DB::table('roles')->where('name', 'Approver Unit')->value('id');
        if (!$fromRoleId) return;

        $adminProdiId = Role::firstOrCreate(['name' => 'Admin Prodi', 'guard_name' => 'web'])->id;
        $adminJurusanId = Role::firstOrCreate(['name' => 'Admin Jurusan', 'guard_name' => 'web'])->id;
        $adminFakultasId = Role::firstOrCreate(['name' => 'Admin Fakultas', 'guard_name' => 'web'])->id;

        $userIds = DB::table('model_has_roles')
            ->where('role_id', $fromRoleId)
            ->where('model_type', self::MODEL_TYPE)
            ->pluck('model_id')
            ->map(fn ($v) => (int) $v)
            ->all();

        foreach ($userIds as $uid) {
            $unitType = DB::table('users')
                ->leftJoin('units', 'units.id', '=', 'users.unit_id')
                ->where('users.id', $uid)
                ->value('units.type');

            $targetRoleId = match ((string) $unitType) {
                'jurusan' => $adminJurusanId,
                'fakultas' => $adminFakultasId,
                default => $adminProdiId,
            };

            $exists = DB::table('model_has_roles')
                ->where('role_id', $targetRoleId)
                ->where('model_type', self::MODEL_TYPE)
                ->where('model_id', $uid)
                ->exists();

            if (!$exists) {
                DB::table('model_has_roles')->insert([
                    'role_id' => $targetRoleId,
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

