<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FacultyLeadersSeeder extends Seeder
{
    public function run(): void
    {
        $fkip = Unit::where('code', 'FKIP')->first();

        $users = [
            [
                'email' => 'dekan@fkip.unila.test',
                'name' => 'Dr. Albet Maydiantoro, M.Pd.',
                'role' => 'DEKAN',
            ],
            [
                'email' => 'wd.akademik@fkip.unila.test',
                'name' => 'Dr. Riswandi, M.Pd.',
                'role' => 'WD_AKADEMIK',
            ],
            [
                'email' => 'wd.umum@fkip.unila.test',
                'name' => 'Bambang Riadi, S.Pd., M.Pd.',
                'role' => 'WD_UMUM',
            ],
            [
                'email' => 'wd.kemahasiswaan@fkip.unila.test',
                'name' => 'Hermi Yanzi, S.Pd., M.Pd.',
                'role' => 'WD_KEMAHASISWAAN',
            ],
        ];

        foreach ($users as $u) {
            $user = User::firstOrCreate(['email' => $u['email']], [
                'name' => $u['name'],
                'password' => Hash::make('Password!2345'),
                'unit_id' => $fkip?->id,
                'email_verified_at' => now(),
            ]);

            $user->syncRoles([$u['role']]);
        }
    }
}

