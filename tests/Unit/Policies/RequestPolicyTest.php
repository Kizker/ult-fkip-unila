<?php

namespace Tests\Unit\Policies;

use App\Models\Request as UltRequest;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\Support\CreatesRolesPermissions;
use Tests\TestCase;

class RequestPolicyTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRolesPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    public function test_student_cannot_view_other_students_request(): void
    {
        $role = $this->makeRole('Mahasiswa', ['requests.view_own']);

        $studentA = User::factory()->create();
        $studentA->assignRole($role);

        $studentB = User::factory()->create();
        $studentB->assignRole($role);

        $unit = Unit::factory()->create();
        $req = UltRequest::factory()->create([
            'student_id' => $studentA->id,
            'current_unit_id' => $unit->id,
        ]);

        $this->assertTrue(Gate::forUser($studentA)->allows('view', $req));
        $this->assertFalse(Gate::forUser($studentB)->allows('view', $req));
    }

    public function test_unit_admin_can_only_view_requests_in_unit_scope(): void
    {
        $roleUnitAdmin = $this->makeRole('Admin Jurusan', ['requests.view_unit']);

        $fak = Unit::factory()->create(['type' => \App\Enums\UnitType::fakultas]);
        $jur = Unit::factory()->jurusan($fak)->create();
        $prodi = Unit::factory()->prodi($jur)->create();

        $admin = User::factory()->create(['unit_id' => $jur->id]);
        $admin->assignRole($roleUnitAdmin);

        $reqInScope = UltRequest::factory()->create(['current_unit_id' => $prodi->id]);
        $reqOutScope = UltRequest::factory()->create(['current_unit_id' => Unit::factory()->create()->id]);

        $this->assertTrue(Gate::forUser($admin)->allows('view', $reqInScope));
        $this->assertFalse(Gate::forUser($admin)->allows('view', $reqOutScope));

        // Listing scope also respects unit scope (unit + one-level children) 
        $ids = UltRequest::query()->forUser($admin)->pluck('id')->all();
        $this->assertContains($reqInScope->id, $ids);
        $this->assertNotContains($reqOutScope->id, $ids);
    }

    public function test_prodi_scoped_admin_can_view_jurusan_queue_only_for_scoped_prodi_students(): void
    {
        $roleUnitAdmin = $this->makeRole('Admin Jurusan', ['requests.view_unit', 'requests.process_unit']);

        $fak = Unit::factory()->create(['type' => \App\Enums\UnitType::fakultas]);
        $jur = Unit::factory()->jurusan($fak)->create();
        $prodiA = Unit::factory()->prodi($jur)->create();
        $prodiB = Unit::factory()->prodi($jur)->create();

        $admin = User::factory()->create(['unit_id' => $prodiA->id]);
        $admin->assignRole($roleUnitAdmin);
        $admin->unitScopes()->sync([$prodiA->id]);

        $studentA = User::factory()->create(['unit_id' => $prodiA->id]);
        $studentB = User::factory()->create(['unit_id' => $prodiB->id]);

        $reqScoped = UltRequest::factory()->create([
            'student_id' => $studentA->id,
            'current_unit_id' => $jur->id,
        ]);
        $reqOther = UltRequest::factory()->create([
            'student_id' => $studentB->id,
            'current_unit_id' => $jur->id,
        ]);

        $this->assertTrue(Gate::forUser($admin)->allows('view', $reqScoped));
        $this->assertTrue(Gate::forUser($admin)->allows('process', $reqScoped));
        $this->assertFalse(Gate::forUser($admin)->allows('view', $reqOther));
        $this->assertFalse(Gate::forUser($admin)->allows('process', $reqOther));

        $ids = UltRequest::query()->forUser($admin)->pluck('id')->all();
        $this->assertContains($reqScoped->id, $ids);
        $this->assertNotContains($reqOther->id, $ids);
    }
}
