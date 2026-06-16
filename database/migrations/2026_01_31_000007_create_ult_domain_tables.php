<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable()->index(); // FK added after cms_categories exists
            $table->string('slug', 190)->unique();

            $table->string('title_id', 190);
            $table->string('title_en', 190)->nullable();

            $table->string('summary_id', 300)->nullable();
            $table->string('summary_en', 300)->nullable();

            $table->longText('requirements_html_id')->nullable();
            $table->longText('requirements_html_en')->nullable();

            $table->longText('sop_html_id')->nullable();
            $table->longText('sop_html_en')->nullable();

            // DATA TIDAK TERSEDIA: SLA per layanan. Disimpan NULL sampai ditetapkan.
            $table->unsignedInteger('sla_days')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('service_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('key', 120);
            $table->string('label_id', 190);
            $table->string('label_en', 190)->nullable();
            $table->enum('type', ['text','textarea','number','date','select','checkbox','json'])->default('text');
            $table->boolean('required')->default(false);
            $table->json('rules_json')->nullable();
            $table->json('options_json')->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['service_id','sort_order']);
        });

        Schema::create('service_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();

            $table->boolean('require_prodi')->default(false);
            $table->boolean('require_jurusan')->default(false);
            $table->boolean('require_unit_signature')->default(false);

            $table->boolean('require_ult_review')->default(false);
            $table->boolean('require_faculty_signature')->default(false);
            $table->boolean('require_legalization')->default(false);

            $table->string('issue_number_at_step', 120)->nullable();
            $table->unsignedInteger('workflow_schema_version')->default(1);
            $table->json('steps_json')->nullable();

            $table->timestamps();
        });

        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();

            $table->enum('current_status', [
                'DIAJUKAN','PERLU_PERBAIKAN','DIVERIFIKASI_UNIT','MENUNGGU_TTD_UNIT',
                'REVIEW_ULT','MENUNGGU_TTD_FAKULTAS','NOMOR_DOKUMEN_TERBIT','MENUNGGU_LEGALISIR',
                'DIPROSES','SELESAI','DITOLAK',

                // Document module (layanan dokumen) additional strict states
                'GATE_VERIFIED','NOMOR_SURAT_FILLED','IN_SIGNING','REJECTED_IN_SIGNING','READY_FOR_FINAL','COMPLETED','DITOLAK_ADMIN'
            ])->index();

            $table->string('current_step_key', 120)->nullable();
            $table->foreignId('current_unit_id')->nullable()->constrained('units')->nullOnDelete();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->timestamps();

            $table->index(['student_id','current_status']);
            $table->index(['current_unit_id']);
            $table->index(['service_id']);
            $table->index(['created_at']);
        });

        Schema::create('request_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->foreignId('service_field_id')->constrained('service_fields')->cascadeOnDelete();
            $table->text('value_text')->nullable();
            $table->json('value_json')->nullable();
            $table->date('value_date')->nullable();
            $table->double('value_number')->nullable();
            $table->timestamps();
        });

        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();

            $table->enum('kind', ['input','output'])->index();
            $table->foreignId('service_field_id')->nullable()->constrained('service_fields')->nullOnDelete();

            $table->string('original_name', 255);
            $table->string('stored_path', 255);
            $table->string('mime', 190);
            $table->unsignedBigInteger('size');
            $table->string('sha256', 64);

            $table->enum('verified_status', ['pending','accepted','rejected'])->default('pending')->index();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->string('verification_note', 500)->nullable();

            $table->timestamps();
        });

        Schema::create('request_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->string('from_status', 60)->nullable();
            $table->string('to_status', 60);
            $table->string('step_key', 120)->nullable();
            $table->text('note')->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['request_id','created_at']);
        });

        Schema::create('request_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_internal')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->string('step_key', 120)->index();
            $table->string('role_name', 120)->index();
            $table->foreignId('unit_id_scope')->nullable()->constrained('units')->nullOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending','approved','rejected'])->default('pending')->index();
            $table->text('note')->nullable();
            $table->timestamp('decided_at')->nullable();

            //  dokumen ttd manual (optional)
            $table->string('signature_file_path', 255)->nullable();

            $table->timestamps();
            $table->index(['request_id','step_key','status']);
        });

        Schema::create('document_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->unique()->constrained('requests')->cascadeOnDelete();
            $table->string('format_key', 120);
            $table->unsignedInteger('number_seq');
            $table->unsignedInteger('year');
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->timestamp('issued_at')->useCurrent();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();

            $table->index(['year','unit_id']);
        });

        Schema::create('legalization_refs', function (Blueprint $table) {
            $table->foreignId('request_id')->primary()->constrained('requests')->cascadeOnDelete();
            $table->enum('status', ['belum','proses','selesai'])->default('belum');
            $table->string('external_ref', 190)->nullable();
            $table->string('external_url', 255)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legalization_refs');
        Schema::dropIfExists('document_numbers');
        Schema::dropIfExists('approvals');
        Schema::dropIfExists('request_notes');
        Schema::dropIfExists('request_status_histories');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('request_field_values');
        Schema::dropIfExists('requests');
        Schema::dropIfExists('service_workflows');
        Schema::dropIfExists('service_fields');
        Schema::dropIfExists('services');
    }
};
