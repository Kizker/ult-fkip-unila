<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'allow_general_attachments')) {
                $table->boolean('allow_general_attachments')
                    ->default(false)
                    ->after('document_source_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'allow_general_attachments')) {
                $table->dropColumn('allow_general_attachments');
            }
        });
    }
};
