<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Models\Request as UltRequest;
use App\Models\Service;
use App\Models\ServiceWorkflow;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesRolesPermissions;
use Tests\TestCase;

class DocumentGateInitialRoleRestrictionTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRolesPermissions;

    public function test_ult_staff_cannot_run_initial_gate_actions_when_gate_role_is_admin_jurusan(): void
    {
        $this->seedPermissions();
        $this->makeRole('Admin Jurusan', ['requests.view_unit', 'requests.process_unit', 'doc_requests.gate']);
        $this->makeRole('Staf ULT', ['requests.view_any', 'requests.review_ult', 'requests.process_unit', 'doc_requests.gate']);

        $fak = Unit::factory()->create(['type' => \App\Enums\UnitType::fakultas]);
        $jur = Unit::factory()->jurusan($fak)->create();
        $prodi = Unit::factory()->prodi($jur)->create();

        $ult = User::factory()->create(['unit_id' => $fak->id]);
        $ult->assignRole('Staf ULT');

        $adminJur = User::factory()->create(['unit_id' => $jur->id]);
        $adminJur->assignRole('Admin Jurusan');

        $student = User::factory()->create(['unit_id' => $prodi->id]);
        $service = Service::factory()->create();
        ServiceWorkflow::factory()->create([
            'service_id' => $service->id,
            'gate_enabled' => true,
            'gate_role' => 'Admin Jurusan',
            'gate_steps_json' => ['VERIFY_INITIAL', 'INPUT_NOMOR_SURAT'],
        ]);

        $req = UltRequest::factory()->create([
            'service_id' => $service->id,
            'student_id' => $student->id,
            'current_status' => RequestStatus::DIAJUKAN,
            'current_unit_id' => $jur->id,
        ]);

        $this->actingAs($ult)
            ->post(route('admin.doc_requests.gate.nomor_surat', $req), [
                'nomor_surat' => '00001/UN26.14/PN.01.00/2026',
            ])
            ->assertStatus(403);

        $this->actingAs($ult)
            ->post(route('admin.doc_requests.gate.verify', $req), [
                'decision' => 'REJECT',
            ])
            ->assertStatus(403);

        $this->actingAs($adminJur)
            ->post(route('admin.doc_requests.gate.nomor_surat', $req), [
                'nomor_surat' => '00001/UN26.14/PN.01.00/2026',
            ])
            ->assertRedirect();

        $req->refresh();
        $this->assertSame('00001/UN26.14/PN.01.00/2026', (string) $req->nomor_surat);
    }

    public function test_ult_staff_can_run_initial_gate_actions_when_gate_role_is_staf_ult(): void
    {
        $this->seedPermissions();
        $this->makeRole('Admin Jurusan', ['requests.view_unit', 'requests.process_unit', 'doc_requests.gate']);
        $this->makeRole('Staf ULT', ['requests.view_any', 'requests.review_ult', 'requests.process_unit', 'doc_requests.gate']);

        $fak = Unit::factory()->create(['type' => \App\Enums\UnitType::fakultas]);
        $jur = Unit::factory()->jurusan($fak)->create();
        $prodi = Unit::factory()->prodi($jur)->create();

        $ult = User::factory()->create(['unit_id' => $fak->id]);
        $ult->assignRole('Staf ULT');

        $student = User::factory()->create(['unit_id' => $prodi->id]);
        $service = Service::factory()->create();
        ServiceWorkflow::factory()->create([
            'service_id' => $service->id,
            'gate_enabled' => true,
            'gate_role' => 'Staf ULT',
            'gate_steps_json' => ['VERIFY_INITIAL', 'INPUT_NOMOR_SURAT'],
        ]);

        $req = UltRequest::factory()->create([
            'service_id' => $service->id,
            'student_id' => $student->id,
            'current_status' => RequestStatus::DIAJUKAN,
            'current_unit_id' => $jur->id,
        ]);

        $this->actingAs($ult)
            ->post(route('admin.doc_requests.gate.nomor_surat', $req), [
                'nomor_surat' => '00077/UN26.14/PN.01.00/2026',
            ])
            ->assertRedirect();

        $this->actingAs($ult)
            ->post(route('admin.doc_requests.gate.verify', $req), [
                'decision' => 'PASS',
            ])
            ->assertRedirect();

        $req->refresh();
        $this->assertSame('00077/UN26.14/PN.01.00/2026', (string) $req->nomor_surat);
        $this->assertSame(RequestStatus::REVIEW_ULT, $req->current_status);
    }
}
