<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $roleId = DB::table('roles')
            ->where('name', 'Petugas Legalisir')
            ->where('guard_name', 'web')
            ->value('id');

        if (! $roleId) {
            return;
        }

        DB::table('model_has_roles')->where('role_id', $roleId)->delete();
        DB::table('role_has_permissions')->where('role_id', $roleId)->delete();
        DB::table('roles')->where('id', $roleId)->delete();
    }

    public function down(): void
    {
        //
    }
};
