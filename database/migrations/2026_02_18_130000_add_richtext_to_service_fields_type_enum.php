<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('service_fields') || !Schema::hasColumn('service_fields', 'type')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE `service_fields` MODIFY `type` ENUM('text','textarea','richtext','number','date','select','checkbox','json','file') NOT NULL DEFAULT 'text'"
            );
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('service_fields') || !Schema::hasColumn('service_fields', 'type')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE `service_fields` MODIFY `type` ENUM('text','textarea','number','date','select','checkbox','json','file') NOT NULL DEFAULT 'text'"
            );
        }
    }
};

