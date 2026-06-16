<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_signers', function (Blueprint $table) {
            if (!Schema::hasColumn('service_signers', 'custom_label')) {
                $table->string('custom_label', 120)->nullable()->after('role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_signers', function (Blueprint $table) {
            if (Schema::hasColumn('service_signers', 'custom_label')) {
                $table->dropColumn('custom_label');
            }
        });
    }
};

