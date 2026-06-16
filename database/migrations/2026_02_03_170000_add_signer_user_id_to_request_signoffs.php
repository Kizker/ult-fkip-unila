<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_signoffs', function (Blueprint $table) {
            $table->foreignId('signer_user_id')
                ->nullable()
                ->after('request_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->index(['request_id', 'signer_user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('request_signoffs', function (Blueprint $table) {
            $table->dropIndex(['request_id', 'signer_user_id']);
            $table->dropConstrainedForeignId('signer_user_id');
        });
    }
};

