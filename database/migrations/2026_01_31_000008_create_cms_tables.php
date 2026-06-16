<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_categories', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['service','blog','announcement'])->index();
            $table->string('slug', 190)->index();
            $table->string('name_id', 190);
            $table->string('name_en', 190)->nullable();
            $table->timestamps();

            $table->unique(['type','slug']);
        });

        Schema::create('cms_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('cms_categories')->nullOnDelete();
            $table->enum('type', ['blog','announcement'])->index();
            $table->string('slug', 190)->unique();
            $table->string('title_id', 190);
            $table->string('title_en', 190)->nullable();
            $table->longText('content_html_id')->nullable();
            $table->longText('content_html_en')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('hero_banners', function (Blueprint $table) {
            $table->id();
            $table->string('title_id', 190)->nullable();
            $table->string('title_en', 190)->nullable();
            $table->string('subtitle_id', 300)->nullable();
            $table->string('subtitle_en', 300)->nullable();
            $table->string('image_path', 255)->nullable();
            $table->string('cta_label_id', 120)->nullable();
            $table->string('cta_label_en', 120)->nullable();
            $table->string('cta_url', 255)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_banners');
        Schema::dropIfExists('cms_posts');
        Schema::dropIfExists('cms_categories');
    }
};
