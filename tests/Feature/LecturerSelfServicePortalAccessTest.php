<?php

namespace Tests\Feature;

use App\Models\Request as UltRequest;
use App\Models\Service;
use App\Models\ServiceWorkflow;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesRolesPermissions;
use Tests\TestCase;

class LecturerSelfServicePortalAccessTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRolesPermissions;

    public function test_admin_jurusan_per_prodi_can_submit_request_via_student_portal(): void
    {
        $this->seedPermissions();
        $this->makeRole('Admin Jurusan');

        $fakultas = Unit::factory()->create();
        $jurusan = Unit::factory()->jurusan($fakultas)->create();
        Unit::factory()->prodi($jurusan)->create();

        $user = User::factory()->create([
            'unit_id' => $jurusan->id,
            'jabatan' => 'Admin Jurusan per Prodi',
            'email_verified_at' => now(),
        ]);
        $user->syncRoles(['Admin Jurusan']);

        $service = Service::factory()->create([
            'is_active' => true,
        ]);
        ServiceWorkflow::factory()->create([
            'service_id' => $service->id,
            'require_prodi' => true,
            'require_jurusan' => false,
        ]);

        $this->actingAs($user)
            ->get(route('student.dashboard'))
            ->assertOk();

        $this->actingAs($user)
            ->post(route('student.requests.store'), [
                'service_id' => $service->id,
            ])
            ->assertRedirect();

        $requestItem = UltRequest::query()->latest('id')->firstOrFail();
        $this->assertSame((int) $user->id, (int) $requestItem->student_id);
        $this->assertSame((int) $service->id, (int) $requestItem->service_id);
    }

    public function test_dosen_sees_signer_inbox_button_on_student_portal(): void
    {
        $this->seedPermissions();
        $this->makeRole('Dosen');

        $unit = Unit::factory()->create();
        $user = User::factory()->create([
            'unit_id' => $unit->id,
            'jabatan' => 'Dosen',
            'email_verified_at' => now(),
        ]);
        $user->syncRoles(['Dosen']);

        $this->actingAs($user)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Signer Inbox');
    }
}

