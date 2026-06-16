<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_fields', function (Blueprint $table) {
            if (!Schema::hasColumn('service_fields', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('sort_order')->index();
            }
        });

        Schema::table('service_placeholders', function (Blueprint $table) {
            if (!Schema::hasColumn('service_placeholders', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('notes')->index();
            }
        });

        Schema::table('request_data', function (Blueprint $table) {
            if (!Schema::hasColumn('request_data', 'document_snapshot_json')) {
                $table->json('document_snapshot_json')->nullable()->after('attachments_json');
            }
        });

        DB::table('service_fields')
            ->whereNull('is_active')
            ->update(['is_active' => true]);

        DB::table('service_placeholders')
            ->whereNull('is_active')
            ->update(['is_active' => true]);
    }

    public function down(): void
    {
        Schema::table('request_data', function (Blueprint $table) {
            if (Schema::hasColumn('request_data', 'document_snapshot_json')) {
                $table->dropColumn('document_snapshot_json');
            }
        });

        Schema::table('service_placeholders', function (Blueprint $table) {
            if (Schema::hasColumn('service_placeholders', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });

        Schema::table('service_fields', function (Blueprint $table) {
            if (Schema::hasColumn('service_fields', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
