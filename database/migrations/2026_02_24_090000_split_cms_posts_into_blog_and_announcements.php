<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_blogs', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 190)->unique();
            $table->string('title_id', 190);
            $table->string('title_en', 190)->nullable();
            $table->longText('content_html_id')->nullable();
            $table->longText('content_html_en')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('cms_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 190)->unique();
            $table->string('title_id', 190);
            $table->string('title_en', 190)->nullable();
            $table->longText('content_html_id')->nullable();
            $table->longText('content_html_en')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });

        if (Schema::hasTable('cms_posts')) {
            $this->copyPostsTo('blog', 'cms_blogs');
            $this->copyPostsTo('announcement', 'cms_announcements');
            Schema::dropIfExists('cms_posts');
        }

        if (Schema::hasTable('cms_categories')) {
            DB::table('cms_categories')
                ->whereIn('type', ['blog', 'announcement'])
                ->delete();
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('cms_posts')) {
            Schema::create('cms_posts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->nullable()->constrained('cms_categories')->nullOnDelete();
                $table->enum('type', ['blog', 'announcement'])->index();
                $table->string('slug', 190)->unique();
                $table->string('title_id', 190);
                $table->string('title_en', 190)->nullable();
                $table->longText('content_html_id')->nullable();
                $table->longText('content_html_en')->nullable();
                $table->timestamp('published_at')->nullable()->index();
                $table->boolean('is_published')->default(false)->index();
                $table->timestamps();
            });
        }

        $usedSlugs = [];

        if (Schema::hasTable('cms_blogs')) {
            foreach (DB::table('cms_blogs')->orderBy('id')->cursor() as $row) {
                $slug = $this->resolveUniqueSlug((string) $row->slug, $usedSlugs);

                DB::table('cms_posts')->insert([
                    'category_id' => null,
                    'type' => 'blog',
                    'slug' => $slug,
                    'title_id' => $row->title_id,
                    'title_en' => $row->title_en,
                    'content_html_id' => $row->content_html_id,
                    'content_html_en' => $row->content_html_en,
                    'published_at' => $row->published_at,
                    'is_published' => (bool) $row->is_published,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }

        if (Schema::hasTable('cms_announcements')) {
            foreach (DB::table('cms_announcements')->orderBy('id')->cursor() as $row) {
                $slug = $this->resolveUniqueSlug((string) $row->slug, $usedSlugs);

                DB::table('cms_posts')->insert([
                    'category_id' => null,
                    'type' => 'announcement',
                    'slug' => $slug,
                    'title_id' => $row->title_id,
                    'title_en' => $row->title_en,
                    'content_html_id' => $row->content_html_id,
                    'content_html_en' => $row->content_html_en,
                    'published_at' => $row->published_at,
                    'is_published' => (bool) $row->is_published,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }

        Schema::dropIfExists('cms_announcements');
        Schema::dropIfExists('cms_blogs');
    }

    private function copyPostsTo(string $type, string $targetTable): void
    {
        foreach (DB::table('cms_posts')->where('type', $type)->orderBy('id')->cursor() as $row) {
            DB::table($targetTable)->insert([
                'slug' => $row->slug,
                'title_id' => $row->title_id,
                'title_en' => $row->title_en,
                'content_html_id' => $row->content_html_id,
                'content_html_en' => $row->content_html_en,
                'published_at' => $row->published_at,
                'is_published' => (bool) $row->is_published,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    /**
     * Keep slugs unique if rollback merges two tables back into one.
     */
    private function resolveUniqueSlug(string $slug, array &$usedSlugs): string
    {
        $base = $slug !== '' ? $slug : 'post';
        $candidate = $base;
        $counter = 1;

        while (in_array($candidate, $usedSlugs, true)) {
            $candidate = $base . '-' . $counter;
            $counter++;
        }

        $usedSlugs[] = $candidate;

        return $candidate;
    }
};
