<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'document_source_type')) {
                $table->enum('document_source_type', ['MAIN_DOCX_TEMPLATE', 'REQUEST_PPTX'])
                    ->default('MAIN_DOCX_TEMPLATE')
                    ->after('status')
                    ->index();
            }
        });

        DB::table('services')
            ->whereNull('document_source_type')
            ->update(['document_source_type' => 'MAIN_DOCX_TEMPLATE']);
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'document_source_type')) {
                $table->dropColumn('document_source_type');
            }
        });
    }
};

