<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_number_formats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units');
            $table->string('format_key', 120)->default('default');
            $table->string('name', 160)->default('Default');
            $table->text('template');
            $table->unsignedTinyInteger('seq_padding')->default(3);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['unit_id','format_key'], 'uniq_doc_format_unit_key');
            $table->index(['unit_id','is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_number_formats');
    }
};
