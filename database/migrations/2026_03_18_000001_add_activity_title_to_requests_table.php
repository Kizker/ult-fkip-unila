<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            if (!Schema::hasColumn('requests', 'activity_title')) {
                $table->string('activity_title', 190)->nullable()->after('service_id');
                $table->index('activity_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            if (Schema::hasColumn('requests', 'activity_title')) {
                $table->dropIndex(['activity_title']);
                $table->dropColumn('activity_title');
            }
        });
    }
};
