<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['fakultas','jurusan','prodi'])->index();
            $table->foreignId('parent_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type','parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
