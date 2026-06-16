<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_number_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('format_key', 120)->default('default');
            $table->unsignedSmallInteger('year');
            $table->foreignId('unit_id')->constrained('units');
            $table->unsignedInteger('last_seq')->default(0);
            $table->timestamps();

            $table->unique(['format_key','year','unit_id'], 'uniq_doc_seq');
            $table->index(['year','unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_number_sequences');
    }
};
