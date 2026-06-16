<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_workflows', function (Blueprint $table) {
            if (!Schema::hasColumn('service_workflows', 'require_org_chair_signature')) {
                $table->boolean('require_org_chair_signature')->default(false)->after('require_unit_signature');
            }
            if (!Schema::hasColumn('service_workflows', 'require_org_secretary_signature')) {
                $table->boolean('require_org_secretary_signature')->default(false)->after('require_org_chair_signature');
            }
            if (!Schema::hasColumn('service_workflows', 'require_kaprodi_signature')) {
                $table->boolean('require_kaprodi_signature')->default(false)->after('require_org_secretary_signature');
            }
            if (!Schema::hasColumn('service_workflows', 'require_kajur_signature')) {
                $table->boolean('require_kajur_signature')->default(false)->after('require_kaprodi_signature');
            }
            if (!Schema::hasColumn('service_workflows', 'require_other_lecturer_signature')) {
                $table->boolean('require_other_lecturer_signature')->default(false)->after('require_kajur_signature');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_workflows', function (Blueprint $table) {
            foreach ([
                'require_org_chair_signature',
                'require_org_secretary_signature',
                'require_kaprodi_signature',
                'require_kajur_signature',
                'require_other_lecturer_signature',
            ] as $col) {
                if (Schema::hasColumn('service_workflows', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

