<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $definitions = [
            'akademik-dan-kerja-sama' => [
                'name_id' => 'Akademik dan Kerja Sama',
                'name_en' => 'Academic and Partnerships',
            ],
            'umum-dan-keuangan' => [
                'name_id' => 'Umum dan Keuangan',
                'name_en' => 'General and Finance',
            ],
            'kemahasiswaan-dan-alumni' => [
                'name_id' => 'Kemahasiswaan dan Alumni',
                'name_en' => 'Student Affairs and Alumni',
            ],
        ];

        $now = now();
        $categoryIds = [];

        foreach ($definitions as $slug => $meta) {
            $existing = DB::table('cms_categories')
                ->where('type', 'service')
                ->where('slug', $slug)
                ->first();

            if ($existing) {
                DB::table('cms_categories')
                    ->where('id', $existing->id)
                    ->update([
                        'name_id' => $meta['name_id'],
                        'name_en' => $meta['name_en'],
                        'updated_at' => $now,
                    ]);
                $categoryIds[$slug] = (int) $existing->id;
                continue;
            }

            $categoryIds[$slug] = (int) DB::table('cms_categories')->insertGetId([
                'type' => 'service',
                'slug' => $slug,
                'name_id' => $meta['name_id'],
                'name_en' => $meta['name_en'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $defaultId = $categoryIds['akademik-dan-kerja-sama'];
        $allowedIds = array_values($categoryIds);

        $legacyAkademikId = DB::table('cms_categories')
            ->where('type', 'service')
            ->where('slug', 'akademik')
            ->value('id');

        if ($legacyAkademikId && (int) $legacyAkademikId !== $defaultId) {
            DB::table('services')
                ->where('category_id', (int) $legacyAkademikId)
                ->update(['category_id' => $defaultId]);
        }

        DB::table('services')
            ->where(function ($q) use ($allowedIds) {
                $q->whereNull('category_id')
                    ->orWhereNotIn('category_id', $allowedIds);
            })
            ->update(['category_id' => $defaultId]);
    }

    public function down(): void
    {
        // Intentionally left empty: this migration normalizes production data.
    }
};

