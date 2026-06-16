<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->enum('type', ['MAIN_DOCX'])->index();
            $table->string('file_path', 255);
            $table->string('original_filename', 255);
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['service_id', 'type']);
        });

        Schema::create('service_placeholders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('placeholder_key', 120);
            $table->enum('source_type', ['FORM','PROFILE','INTERNAL','SYSTEM_AUTOFILL'])->nullable();
            $table->string('source_ref', 190)->nullable();
            $table->boolean('is_required')->default(true);
            $table->string('notes', 500)->nullable();
            $table->timestamps();

            $table->unique(['service_id','placeholder_key']);
            $table->index(['service_id','source_type']);
        });

        Schema::create('service_signers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('role', 120);
            $table->unsignedInteger('order_index');
            $table->boolean('is_required')->default(true);
            $table->boolean('requires_signature_upload')->default(false);
            $table->json('signature_file_types')->nullable();
            $table->unsignedInteger('signature_max_size_kb')->nullable();
            $table->timestamps();

            $table->unique(['service_id','order_index']);
            $table->index(['service_id','role']);
        });

        Schema::create('request_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->unique()->constrained('requests')->cascadeOnDelete();
            $table->json('data_json')->nullable();
            $table->json('attachments_json')->nullable();
            $table->timestamps();
        });

        Schema::create('request_signoffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->string('signer_role', 120);
            $table->unsignedInteger('order_index');
            $table->boolean('is_required')->default(true);
            $table->enum('status', ['PENDING','APPROVED','REVISION_REQUESTED','REJECTED'])->default('PENDING')->index();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->text('note')->nullable();
            $table->string('signature_file_path', 255)->nullable();
            $table->timestamps();

            $table->index(['request_id','order_index']);
        });

        Schema::create('request_outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->enum('output_type', ['PDF','DOCX'])->index();
            $table->string('file_path', 255);
            $table->string('original_filename', 255)->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_private')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['request_id','created_at']);
        });

        // Append-only placement events (preview/finalize); used for auditing and replay.
        Schema::create('request_signature_placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->json('placements_json');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['request_id','created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_signature_placements');
        Schema::dropIfExists('request_outputs');
        Schema::dropIfExists('request_signoffs');
        Schema::dropIfExists('request_data');
        Schema::dropIfExists('service_signers');
        Schema::dropIfExists('service_placeholders');
        Schema::dropIfExists('service_templates');
    }
};

