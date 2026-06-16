<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $fkip = Unit::updateOrCreate(
            ['code' => 'FKIP'],
            ['type' => 'fakultas', 'parent_id' => null, 'name' => 'Fakultas Keguruan dan Ilmu Pendidikan', 'is_active' => true]
        );

        $jurusan = [
            'JUR-IP' => [
                'name' => 'Ilmu Pendidikan',
                'prodi' => [
                    'PROD-BK' => 'Bimbingan dan Konseling',
                    'PROD-PGPAUD' => 'Pendidikan Guru Pendidikan Anak Usia Dini',
                    'PROD-PGSD' => 'Pendidikan Guru Sekolah Dasar',
                    'PROD-PJAS' => 'Pendidikan Jasmani',
                ],
            ],
            'JUR-BHS' => [
                'name' => 'Pendidikan Bahasa dan Sastra',
                'prodi' => [
                    'PROD-PBI' => 'Pendidikan Bahasa dan Sastra Indonesia',
                    'PROD-PBE' => 'Pendidikan Bahasa Inggris',
                    'PROD-PBL' => 'Pendidikan Bahasa Lampung',
                    'PROD-PBPR' => 'Pendidikan Bahasa Perancis',
                    'PROD-PMUS' => 'Pendidikan Musik',
                    'PROD-PTAR' => 'Pendidikan Tari',
                ],
            ],
            'JUR-MIPA' => [
                'name' => 'Pendidikan MIPA',
                'prodi' => [
                    'PROD-PBIO' => 'Pendidikan Biologi',
                    'PROD-PFIS' => 'Pendidikan Fisika',
                    'PROD-PKIM' => 'Pendidikan Kimia',
                    'PROD-PMTK' => 'Pendidikan Matematika',
                    'PROD-PTI' => 'Pendidikan Teknologi Informasi',
                ],
            ],
            'JUR-IPS' => [
                'name' => 'Pendidikan IPS',
                'prodi' => [
                    'PROD-PEKO' => 'Pendidikan Ekonomi',
                    'PROD-PGEO' => 'Pendidikan Geografi',
                    'PROD-PPKN' => 'Pendidikan Pancasila dan Kewarganegaraan',
                    'PROD-PSJR' => 'Pendidikan Sejarah',
                ],
            ],
        ];

        foreach ($jurusan as $jurusanCode => $definition) {
            $department = Unit::updateOrCreate(
                ['code' => $jurusanCode],
                [
                    'type' => 'jurusan',
                    'parent_id' => $fkip->id,
                    'name' => $definition['name'],
                    'is_active' => true,
                ]
            );

            foreach ($definition['prodi'] as $prodiCode => $prodiName) {
                Unit::updateOrCreate(
                    ['code' => $prodiCode],
                    [
                        'type' => 'prodi',
                        'parent_id' => $department->id,
                        'name' => $prodiName,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
