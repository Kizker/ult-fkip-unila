<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $existing = DB::table('cms_categories')
            ->where('type', 'service')
            ->where('slug', 'lainnya')
            ->first();

        if ($existing) {
            DB::table('cms_categories')
                ->where('id', $existing->id)
                ->update([
                    'name_id' => 'Lainnya',
                    'name_en' => 'Other',
                    'updated_at' => $now,
                ]);

            return;
        }

        DB::table('cms_categories')->insert([
            'type' => 'service',
            'slug' => 'lainnya',
            'name_id' => 'Lainnya',
            'name_en' => 'Other',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('cms_categories')
            ->where('type', 'service')
            ->where('slug', 'lainnya')
            ->delete();
    }
};
