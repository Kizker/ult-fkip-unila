<?php

namespace Tests\Feature;

use App\Enums\ServiceTemplateType;
use App\Models\Request as UltRequest;
use App\Models\Service;
use App\Models\ServiceSigner;
use App\Models\ServiceTemplate;
use App\Models\ServiceWorkflow;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesRolesPermissions;
use Tests\TestCase;

class StudentDosenSignerSelectionTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRolesPermissions;

    public function test_student_create_form_shows_dosen_signer_picker_with_filtered_users(): void
    {
        $this->seedPermissions();
        $this->makeRole('Dosen', ['doc_signoffs.decide']);
        $this->makeRole('DEKAN', ['doc_signoffs.decide']);
        $this->makeRole('SEKJUR', ['doc_signoffs.decide']);
        $this->makeRole('SEKRETARIS_ORG', ['doc_signoffs.decide']);
        $this->makeRole('Admin Jurusan');

        $fakultas = Unit::factory()->create();
        $jurusan = Unit::factory()->jurusan($fakultas)->create();
        $prodi = Unit::factory()->prodi($jurusan)->create();

        $student = User::factory()->create(['unit_id' => $prodi->id]);
        $student->givePermissionTo(['requests.view_own', 'requests.create_own']);

        $lecturer = User::factory()->create(['unit_id' => $prodi->id, 'jabatan' => 'Dosen']);
        $lecturer->syncRoles(['Dosen']);

        $dekan = User::factory()->create(['unit_id' => $fakultas->id, 'jabatan' => 'Dekan']);
        $dekan->syncRoles(['DEKAN']);

        $sekjur = User::factory()->create(['unit_id' => $jurusan->id, 'jabatan' => 'Sekretaris Jurusan']);
        $sekjur->syncRoles(['SEKJUR']);

        $orgSecretary = User::factory()->create(['unit_id' => $prodi->id, 'jabatan' => 'Sekretaris Organisasi']);
        $orgSecretary->syncRoles(['SEKRETARIS_ORG']);

        $nonEligible = User::factory()->create(['unit_id' => $jurusan->id, 'jabatan' => 'Admin Jurusan']);
        $nonEligible->syncRoles(['Admin Jurusan']);

        $service = Service::factory()->create(['is_active' => true]);
        ServiceSigner::create([
            'service_id' => $service->id,
            'role' => 'DOSEN',
            'custom_label' => 'Pembimbing Akademik',
            'order_index' => 1,
            'is_required' => true,
        ]);

        $this->actingAs($student)
            ->get(route('student.requests.create', $service))
            ->assertOk()
            ->assertSee('Pemilihan dosen penandatangan')
            ->assertSee($lecturer->name)
            ->assertSee($dekan->name)
            ->assertSee($sekjur->name)
            ->assertDontSee($orgSecretary->name)
            ->assertDontSee($nonEligible->name);
    }

    public function test_student_create_form_uses_custom_label_for_pemohon_signature_block(): void
    {
        $this->seedPermissions();

        $fakultas = Unit::factory()->create();
        $jurusan = Unit::factory()->jurusan($fakultas)->create();
        $prodi = Unit::factory()->prodi($jurusan)->create();

        $student = User::factory()->create(['unit_id' => $prodi->id]);
        $student->givePermissionTo(['requests.view_own', 'requests.create_own']);

        $service = Service::factory()->create(['is_active' => true]);
        ServiceSigner::create([
            'service_id' => $service->id,
            'role' => 'PEMOHON',
            'custom_label' => 'Mahasiswa Pemohon',
            'order_index' => 1,
            'is_required' => true,
            'requires_signature_upload' => true,
            'signature_file_types' => ['image/png'],
            'signature_max_size_kb' => 256,
        ]);

        $this->actingAs($student)
            ->get(route('student.requests.create', $service))
            ->assertOk()
            ->assertSee('Tanda tangan Mahasiswa Pemohon');
    }

    public function test_submit_request_assigns_selected_dosen_to_signoff(): void
    {
        $this->seedPermissions();
        $this->makeRole('Dosen', ['doc_signoffs.decide']);

        $fakultas = Unit::factory()->create();
        $jurusan = Unit::factory()->jurusan($fakultas)->create();
        $prodi = Unit::factory()->prodi($jurusan)->create();

        $student = User::factory()->create(['unit_id' => $prodi->id]);
        $student->givePermissionTo(['requests.view_own', 'requests.create_own']);

        $lecturer = User::factory()->create(['unit_id' => $prodi->id, 'jabatan' => 'Dosen']);
        $lecturer->syncRoles(['Dosen']);

        $service = Service::factory()->create(['is_active' => true]);
        ServiceWorkflow::factory()->create([
            'service_id' => $service->id,
            'require_prodi' => true,
            'require_jurusan' => false,
        ]);
        ServiceTemplate::create([
            'service_id' => $service->id,
            'type' => ServiceTemplateType::MAIN_DOCX,
            'file_path' => 'services/'.$service->id.'/templates/dummy.docx',
            'original_filename' => 'dummy.docx',
            'uploaded_by' => $student->id,
            'created_at' => now(),
        ]);
        ServiceSigner::create([
            'service_id' => $service->id,
            'role' => 'DOSEN',
            'custom_label' => 'Pembimbing Akademik',
            'order_index' => 1,
            'is_required' => true,
        ]);

        $this->actingAs($student)
            ->post(route('student.requests.store'), [
                'service_id' => $service->id,
                'dosen_signers' => [
                    1 => $lecturer->id,
                ],
            ])
            ->assertRedirect();

        $req = UltRequest::query()->latest('id')->firstOrFail();
        $this->assertDatabaseHas('request_signoffs', [
            'request_id' => $req->id,
            'signer_role' => 'DOSEN',
            'order_index' => 1,
            'signer_user_id' => $lecturer->id,
        ]);
    }

    public function test_submit_request_assigns_sekjur_scope_based_on_applicant_jurusan(): void
    {
        $this->seedPermissions();
        $this->makeRole('Dosen', ['doc_signoffs.decide']);

        $fakultas = Unit::factory()->create();
        $jurusan = Unit::factory()->jurusan($fakultas)->create();
        $prodi = Unit::factory()->prodi($jurusan)->create();

        $student = User::factory()->create(['unit_id' => $prodi->id]);
        $student->givePermissionTo(['requests.view_own', 'requests.create_own']);

        $sekjur = User::factory()->create([
            'unit_id' => $jurusan->id,
            'jabatan' => 'Sekretaris Jurusan',
        ]);
        $sekjur->syncRoles(['Dosen']);

        $service = Service::factory()->create(['is_active' => true]);
        ServiceWorkflow::factory()->create([
            'service_id' => $service->id,
            'require_prodi' => true,
            'require_jurusan' => false,
        ]);
        ServiceTemplate::create([
            'service_id' => $service->id,
            'type' => ServiceTemplateType::MAIN_DOCX,
            'file_path' => 'services/'.$service->id.'/templates/dummy.docx',
            'original_filename' => 'dummy.docx',
            'uploaded_by' => $student->id,
            'created_at' => now(),
        ]);
        ServiceSigner::create([
            'service_id' => $service->id,
            'role' => 'SEKJUR_SCOPE',
            'order_index' => 1,
            'is_required' => true,
        ]);

        $this->actingAs($student)
            ->post(route('student.requests.store'), [
                'service_id' => $service->id,
            ])
            ->assertRedirect();

        $req = UltRequest::query()->latest('id')->firstOrFail();
        $this->assertDatabaseHas('request_signoffs', [
            'request_id' => $req->id,
            'signer_role' => 'SEKJUR_SCOPE',
            'order_index' => 1,
            'signer_user_id' => $sekjur->id,
        ]);
    }

    public function test_submit_request_assigns_sekjur_scope_from_sekjur_role(): void
    {
        $this->seedPermissions();
        $this->makeRole('SEKJUR', ['doc_signoffs.decide']);

        $fakultas = Unit::factory()->create();
        $jurusan = Unit::factory()->jurusan($fakultas)->create();
        $prodi = Unit::factory()->prodi($jurusan)->create();

        $student = User::factory()->create(['unit_id' => $prodi->id]);
        $student->givePermissionTo(['requests.view_own', 'requests.create_own']);

        $sekjur = User::factory()->create([
            'unit_id' => $jurusan->id,
            'jabatan' => null,
        ]);
        $sekjur->syncRoles(['SEKJUR']);

        $service = Service::factory()->create(['is_active' => true]);
        ServiceWorkflow::factory()->create([
            'service_id' => $service->id,
            'require_prodi' => true,
            'require_jurusan' => false,
        ]);
        ServiceTemplate::create([
            'service_id' => $service->id,
            'type' => ServiceTemplateType::MAIN_DOCX,
            'file_path' => 'services/'.$service->id.'/templates/dummy.docx',
            'original_filename' => 'dummy.docx',
            'uploaded_by' => $student->id,
            'created_at' => now(),
        ]);
        ServiceSigner::create([
            'service_id' => $service->id,
            'role' => 'SEKJUR_SCOPE',
            'order_index' => 1,
            'is_required' => true,
        ]);

        $this->actingAs($student)
            ->post(route('student.requests.store'), [
                'service_id' => $service->id,
            ])
            ->assertRedirect();

        $req = UltRequest::query()->latest('id')->firstOrFail();
        $this->assertDatabaseHas('request_signoffs', [
            'request_id' => $req->id,
            'signer_role' => 'SEKJUR_SCOPE',
            'order_index' => 1,
            'signer_user_id' => $sekjur->id,
        ]);
    }
}
