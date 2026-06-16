<?php

namespace Database\Seeders;

use App\Enums\RequestStatus;
use App\Models\Request as UltRequest;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoRequestsSeeder extends Seeder
{
    public function run(): void
    {
        $service = Service::where('slug', 'surat-keterangan-aktif-kuliah')->first();
        $mhs = User::where('email', 'mahasiswa@demo.test')->first();

        if (!$service || !$mhs) return;

        $req = UltRequest::firstOrCreate(
            ['service_id' => $service->id, 'student_id' => $mhs->id, 'current_status' => RequestStatus::DIAJUKAN->value],
            [
                'current_step_key' => 'prodi_verify',
                'current_unit_id' => $mhs->unit_id,
                'submitted_at' => now(),
            ]
        );

        $req->histories()->firstOrCreate(
            ['to_status' => RequestStatus::DIAJUKAN->value, 'created_at' => now()],
            ['from_status' => null, 'step_key' => 'prodi_verify', 'note' => 'Demo submit', 'actor_id' => $mhs->id]
        );
    }
}
