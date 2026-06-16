<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_guides', function (Blueprint $table) {
            $table->string('content_type', 20)->default('pdf')->after('summary_en')->index();
            $table->text('video_url')->nullable()->after('size');
        });

        DB::table('user_guides')->update([
            'content_type' => 'pdf',
        ]);
    }

    public function down(): void
    {
        Schema::table('user_guides', function (Blueprint $table) {
            $table->dropColumn(['content_type', 'video_url']);
        });
    }
};
