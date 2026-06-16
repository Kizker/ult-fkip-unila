<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $isSqlite = DB::getDriverName() === 'sqlite';
        $nowExpr = $isSqlite ? 'CURRENT_TIMESTAMP' : 'NOW()';

        DB::table('requests')
            ->where('current_status', 'MENUNGGU_LEGALISIR')
            ->update([
                'current_status' => 'COMPLETED',
                'completed_at' => DB::raw("COALESCE(completed_at, {$nowExpr})"),
            ]);

        if (Schema::hasTable('request_status_histories')) {
            DB::table('request_status_histories')
                ->where('to_status', 'MENUNGGU_LEGALISIR')
                ->update(['to_status' => 'COMPLETED']);
        }

        if (Schema::hasTable('service_workflows')) {
            DB::table('service_workflows')->update(['require_legalization' => false]);

            DB::table('service_workflows')
                ->select(['id', 'steps_json'])
                ->orderBy('id')
                ->chunkById(100, function ($rows): void {
                    foreach ($rows as $row) {
                        $steps = json_decode((string) $row->steps_json, true);
                        if (!is_array($steps) || $steps === []) {
                            continue;
                        }

                        $filtered = [];
                        foreach ($steps as $step) {
                            if (!is_array($step)) {
                                continue;
                            }

                            if (($step['key'] ?? null) === 'legalization') {
                                continue;
                            }

                            if (($step['next_on_approve'] ?? null) === 'legalization') {
                                $step['next_on_approve'] = 'done';
                            }

                            $filtered[] = $step;
                        }

                        DB::table('service_workflows')
                            ->where('id', $row->id)
                            ->update(['steps_json' => json_encode($filtered, JSON_UNESCAPED_UNICODE)]);
                    }
                });
        }

        if (Schema::hasColumn('service_workflows', 'require_legalization')) {
            Schema::table('service_workflows', function (Blueprint $table) {
                $table->dropColumn('require_legalization');
            });
        }

        Schema::dropIfExists('legalization_refs');
    }

    public function down(): void
    {
        if (!Schema::hasColumn('service_workflows', 'require_legalization')) {
            Schema::table('service_workflows', function (Blueprint $table) {
                $table->boolean('require_legalization')->default(false)->after('require_faculty_signature');
            });
        }

        if (!Schema::hasTable('legalization_refs')) {
            Schema::create('legalization_refs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
                $table->string('external_ref')->nullable();
                $table->string('external_url')->nullable();
                $table->string('status', 30)->default('belum');
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }
};
