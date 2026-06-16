<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_unit_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'unit_id']);
            $table->index(['unit_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_unit_scopes');
    }
};

