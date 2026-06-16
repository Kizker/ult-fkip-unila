<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_number_formats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->string('format_key', 120);
            $table->string('name', 160);
            $table->string('template', 2000);
            $table->unsignedTinyInteger('seq_padding')->default(3);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['unit_id', 'format_key'], 'uniq_letter_format');
            $table->index(['unit_id', 'is_active']);
        });

        Schema::create('letter_number_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('format_id')->constrained('letter_number_formats')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('last_seq')->default(0);
            $table->timestamps();

            $table->unique(['format_id', 'year'], 'uniq_letter_seq');
            $table->index(['year', 'format_id']);
        });

        Schema::create('letter_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->unique()->constrained('requests')->cascadeOnDelete();
            $table->foreignId('format_id')->constrained('letter_number_formats')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->string('format_key', 120);
            $table->unsignedInteger('number_seq');
            $table->unsignedSmallInteger('year');
            $table->string('number_text', 120);
            $table->string('template_snapshot', 2000);
            $table->boolean('is_manual_override')->default(false);
            $table->timestamp('issued_at')->useCurrent();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();

            $table->index(['format_id', 'year']);
            $table->index(['unit_id', 'year']);
            $table->index(['year', 'number_seq']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_numbers');
        Schema::dropIfExists('letter_number_sequences');
        Schema::dropIfExists('letter_number_formats');
    }
};

