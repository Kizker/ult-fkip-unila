<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $fkip = Unit::where('code', 'FKIP')->first();
        $prodi = Unit::where('code', 'PROD-PTI')->first();
        $jur = Unit::where('code', 'JUR-IP')->first();

        // Cleanup removed demo users/roles.
        User::query()->whereIn('email', [
            'prodi@demo.test',
            'approver.unit@demo.test',
            'auditor@demo.test',
        ])->delete();
        Role::query()->whereIn('name', ['Admin Prodi', 'Auditor'])->delete();

        $super = User::firstOrCreate(['email' => 'superadmin@demo.test'], [
            'name' => 'Super Admin',
            'password' => Hash::make('Password!2345'),
            'unit_id' => $fkip?->id,
            'email_verified_at' => now(),
        ]);
        $super->syncRoles(['Superadmin']);

        $ult = User::firstOrCreate(['email' => 'ult@demo.test'], [
            'name' => 'Staf ULT',
            'password' => Hash::make('Password!2345'),
            'unit_id' => $fkip?->id,
            'email_verified_at' => now(),
        ]);
        $ult->syncRoles(['Staf ULT']);

        $adminJur = User::firstOrCreate(['email' => 'jurusan@demo.test'], [
            'name' => 'Admin Jurusan',
            'password' => Hash::make('Password!2345'),
            'unit_id' => $jur?->id,
            'email_verified_at' => now(),
        ]);
        $adminJur->syncRoles(['Admin Jurusan']);

        // Admin Jurusan per Prodi: role tetap Admin Jurusan, namun scope operasional di level prodi.
        $adminJurPerProdi = User::firstOrCreate(['email' => 'jurusan.prodi@demo.test'], [
            'name' => 'Admin Jurusan (Per Prodi)',
            'password' => Hash::make('Password!2345'),
            'unit_id' => $prodi?->id,
            'email_verified_at' => now(),
        ]);
        $adminJurPerProdi->syncRoles(['Admin Jurusan']);
        if ($prodi) {
            $adminJurPerProdi->unitScopes()->syncWithoutDetaching([$prodi->id]);
        }

        $kaprodi = User::firstOrCreate(['email' => 'kaprodi@demo.test'], [
            'name' => 'Ketua Program Studi Demo',
            'password' => Hash::make('Password!2345'),
            'unit_id' => $prodi?->id,
            'jabatan' => 'Ketua Program Studi',
            'email_verified_at' => now(),
        ]);
        $kaprodi->syncRoles(['KAPRODI']);

        $kajur = User::firstOrCreate(['email' => 'kajur@demo.test'], [
            'name' => 'Ketua Jurusan Demo',
            'password' => Hash::make('Password!2345'),
            'unit_id' => $jur?->id,
            'jabatan' => 'Ketua Jurusan',
            'email_verified_at' => now(),
        ]);
        $kajur->syncRoles(['KAJUR']);

        $sekjur = User::firstOrCreate(['email' => 'sekjur@demo.test'], [
            'name' => 'Sekretaris Jurusan Demo',
            'password' => Hash::make('Password!2345'),
            'unit_id' => $jur?->id,
            'jabatan' => 'Sekretaris Jurusan',
            'email_verified_at' => now(),
        ]);
        $sekjur->syncRoles(['SEKJUR']);

        // Former "Approver Fakultas" merged into Admin Fakultas
        $approverFac = User::firstOrCreate(['email' => 'dekan@demo.test'], [
            'name' => 'Admin Fakultas',
            'password' => Hash::make('Password!2345'),
            'unit_id' => $fkip?->id,
            'email_verified_at' => now(),
        ]);
        $approverFac->syncRoles(['Admin Fakultas']);

        $mhs = User::firstOrCreate(['email' => 'mahasiswa@demo.test'], [
            'name' => 'Mahasiswa Demo',
            'password' => Hash::make('Password!2345'),
            'unit_id' => $prodi?->id,
            'student_number' => '2215060000',
            'email_verified_at' => now(),
        ]);
        $mhs->syncRoles(['Mahasiswa']);
    }
}
