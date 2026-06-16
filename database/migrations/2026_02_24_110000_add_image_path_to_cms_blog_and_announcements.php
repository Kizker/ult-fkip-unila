<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('cms_blogs', 'image_path')) {
            Schema::table('cms_blogs', function (Blueprint $table) {
                $table->string('image_path', 255)->nullable()->after('title_en');
            });
        }

        if (!Schema::hasColumn('cms_announcements', 'image_path')) {
            Schema::table('cms_announcements', function (Blueprint $table) {
                $table->string('image_path', 255)->nullable()->after('title_en');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('cms_blogs', 'image_path')) {
            Schema::table('cms_blogs', function (Blueprint $table) {
                $table->dropColumn('image_path');
            });
        }

        if (Schema::hasColumn('cms_announcements', 'image_path')) {
            Schema::table('cms_announcements', function (Blueprint $table) {
                $table->dropColumn('image_path');
            });
        }
    }
};
