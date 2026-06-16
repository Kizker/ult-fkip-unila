<?php

namespace Database\Seeders;

use App\Enums\UnitType;
use App\Models\LetterNumberFormat;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class LetterNumberFormatSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('letter_number_formats')) {
            $this->command?->warn('Skip LetterNumberFormatSeeder: table letter_number_formats not found. Run migrations first.');
            return;
        }

        $jurusanUnits = Unit::query()
            ->where('type', UnitType::jurusan)
            ->orderBy('name')
            ->get();

        foreach ($jurusanUnits as $u) {
            // 1) Default template
            LetterNumberFormat::updateOrCreate(
                ['unit_id' => $u->id, 'format_key' => 'default'],
                [
                    'name' => 'Default',
                    'template' => '{SEQ:3}/ULT-FKIP/{UNIT_CODE}/{YYYY}',
                    'seq_padding' => 3,
                    'is_active' => true,
                ]
            );

            // 2) Example: Surat Keterangan
            LetterNumberFormat::updateOrCreate(
                ['unit_id' => $u->id, 'format_key' => 'sk'],
                [
                    'name' => 'Surat Keterangan',
                    'template' => '{SEQ:3}/SK/{UNIT_CODE}/{YYYY}',
                    'seq_padding' => 3,
                    'is_active' => true,
                ]
            );

            // 3) Example: Surat Rekomendasi
            LetterNumberFormat::updateOrCreate(
                ['unit_id' => $u->id, 'format_key' => 'rekom'],
                [
                    'name' => 'Surat Rekomendasi',
                    'template' => '{SEQ:3}/REKOM/{UNIT_CODE}/{YYYY}',
                    'seq_padding' => 3,
                    'is_active' => true,
                ]
            );
        }
    }
}

