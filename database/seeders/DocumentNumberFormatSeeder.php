<?php

namespace Database\Seeders;

use App\Models\DocumentNumberFormat;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class DocumentNumberFormatSeeder extends Seeder
{
    public function run(): void
    {
        $tpl = '{SEQ:3}/ULT-FKIP/{UNIT_CODE}/{YYYY}';

        $units = Unit::query()->where('is_active', true)->get();
        foreach ($units as $u) {
            // One default per unit; admins can change later.
            DocumentNumberFormat::firstOrCreate(
                ['unit_id' => $u->id, 'format_key' => 'default'],
                [
                    'name' => 'Default',
                    'template' => $tpl,
                    'seq_padding' => 3,
                    'is_active' => true,
                ]
            );
        }
    }
}
