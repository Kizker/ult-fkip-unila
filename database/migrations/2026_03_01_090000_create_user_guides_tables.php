<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_guides', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 190)->unique();
            $table->string('title_id', 190);
            $table->string('title_en', 190)->nullable();
            $table->text('summary_id')->nullable();
            $table->text('summary_en')->nullable();
            $table->string('original_name', 255);
            $table->string('stored_path', 255);
            $table->string('mime', 100)->default('application/pdf');
            $table->unsignedBigInteger('size')->default(0);
            $table->boolean('is_public')->default(false)->index();
            $table->boolean('is_published')->default(true)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('user_guide_role', function (Blueprint $table) {
            $table->foreignId('user_guide_id')->constrained('user_guides')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['user_guide_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_guide_role');
        Schema::dropIfExists('user_guides');
    }
};

