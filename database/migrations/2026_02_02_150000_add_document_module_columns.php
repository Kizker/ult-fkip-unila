<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // services: add publish lifecycle metadata (compat: nullable for existing rows)
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'status')) {
                $table->enum('status', ['DRAFT', 'PUBLISHED', 'ARCHIVED'])->nullable()->after('sla_days')->index();
            }
            if (!Schema::hasColumn('services', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
        });

        // service_fields: placeholder mapping hook (compat)
        Schema::table('service_fields', function (Blueprint $table) {
            if (!Schema::hasColumn('service_fields', 'maps_to_placeholder_key')) {
                $table->string('maps_to_placeholder_key', 120)->nullable()->after('key')->index();
            }
        });

        // service_workflows: doc module gate config (compat; cannot be disabled by app rules)
        Schema::table('service_workflows', function (Blueprint $table) {
            if (!Schema::hasColumn('service_workflows', 'gate_enabled')) {
                $table->boolean('gate_enabled')->default(true)->after('steps_json');
            }
            if (!Schema::hasColumn('service_workflows', 'gate_role')) {
                $table->string('gate_role', 120)->nullable()->after('gate_enabled');
            }
            if (!Schema::hasColumn('service_workflows', 'gate_steps_json')) {
                $table->json('gate_steps_json')->nullable()->after('gate_role');
            }
        });

        // requests: doc module operational fields
        Schema::table('requests', function (Blueprint $table) {
            if (!Schema::hasColumn('requests', 'nomor_surat')) {
                $table->string('nomor_surat', 120)->nullable()->after('submitted_at');
                $table->index(['service_id', 'nomor_surat']);
            }
            if (!Schema::hasColumn('requests', 'tanggal_surat')) {
                $table->date('tanggal_surat')->nullable()->after('nomor_surat');
            }
            if (!Schema::hasColumn('requests', 'last_required_approved_at')) {
                $table->timestamp('last_required_approved_at')->nullable()->after('tanggal_surat');
            }
            if (!Schema::hasColumn('requests', 'current_signer_order_index')) {
                $table->unsignedInteger('current_signer_order_index')->nullable()->after('last_required_approved_at');
                $table->index(['service_id', 'current_signer_order_index']);
            }
            if (!Schema::hasColumn('requests', 'resume_signer_order_index')) {
                $table->unsignedInteger('resume_signer_order_index')->nullable()->after('current_signer_order_index');
            }
        });

        // Extend enum values for service_fields.type and requests.current_status for MySQL.
        // SQLite/Postgres handle enums differently; tests use SQLite (string).
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            // service_fields.type add "file"
            DB::statement(
                "ALTER TABLE `service_fields` MODIFY `type` ENUM('text','textarea','number','date','select','checkbox','json','file') NOT NULL DEFAULT 'text'"
            );

            // requests.current_status add document-module statuses without removing existing ones
            DB::statement(
                "ALTER TABLE `requests` MODIFY `current_status` ENUM(
                    'DIAJUKAN','PERLU_PERBAIKAN','DIVERIFIKASI_UNIT','MENUNGGU_TTD_UNIT',
                    'REVIEW_ULT','MENUNGGU_TTD_FAKULTAS','NOMOR_DOKUMEN_TERBIT','MENUNGGU_LEGALISIR',
                    'DIPROSES','SELESAI','DITOLAK',
                    'GATE_VERIFIED','NOMOR_SURAT_FILLED','IN_SIGNING','REJECTED_IN_SIGNING','READY_FOR_FINAL','COMPLETED','DITOLAK_ADMIN'
                ) NOT NULL"
            );
        }
    }

    public function down(): void
    {
        // Best-effort rollback. Enum shrinking is unsafe; avoid altering enums in down().
        Schema::table('requests', function (Blueprint $table) {
            foreach (['resume_signer_order_index','current_signer_order_index','last_required_approved_at','tanggal_surat','nomor_surat'] as $col) {
                if (Schema::hasColumn('requests', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('service_workflows', function (Blueprint $table) {
            foreach (['gate_steps_json','gate_role','gate_enabled'] as $col) {
                if (Schema::hasColumn('service_workflows', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('service_fields', function (Blueprint $table) {
            if (Schema::hasColumn('service_fields', 'maps_to_placeholder_key')) {
                $table->dropColumn('maps_to_placeholder_key');
            }
        });

        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
            if (Schema::hasColumn('services', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};

