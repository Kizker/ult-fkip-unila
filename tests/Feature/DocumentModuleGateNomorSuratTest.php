<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Models\Request as UltRequest;
use App\Models\RequestSignoff;
use App\Models\Service;
use App\Models\ServiceSigner;
use App\Models\ServiceTemplate;
use App\Models\ServiceWorkflow;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesRolesPermissions;
use Tests\TestCase;

class DocumentModuleGateNomorSuratTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRolesPermissions;

    public function test_start_signing_blocked_until_nomor_surat_present(): void
    {
        Storage::fake('private');
        config(['ult.private_disk' => 'private']);

        $this->seedPermissions();
        $this->makeRole('Admin Jurusan', ['requests.view_unit','requests.process_unit','doc_requests.gate']);

        $unit = Unit::factory()->create();
        $admin = User::factory()->create(['unit_id' => $unit->id]);
        $admin->assignRole('Admin Jurusan');

        $service = Service::factory()->create();
        ServiceWorkflow::factory()->create([
            'service_id' => $service->id,
            'gate_enabled' => true,
            'gate_role' => 'Admin Jurusan',
            'gate_steps_json' => ['VERIFY_INITIAL','INPUT_NOMOR_SURAT'],
        ]);
        ServiceTemplate::create([
            'service_id' => $service->id,
            'type' => \App\Enums\ServiceTemplateType::MAIN_DOCX,
            'file_path' => 'services/'.$service->id.'/templates/dummy.docx',
            'original_filename' => 'dummy.docx',
            'uploaded_by' => $admin->id,
            'created_at' => now(),
        ]);
        ServiceSigner::create([
            'service_id' => $service->id,
            'role' => 'Staf ULT',
            'order_index' => 1,
            'is_required' => true,
            'requires_signature_upload' => false,
        ]);

        $student = User::factory()->create(['unit_id' => $unit->id]);
        $req = UltRequest::factory()->create([
            'service_id' => $service->id,
            'student_id' => $student->id,
            'current_status' => RequestStatus::GATE_VERIFIED,
            'current_unit_id' => $unit->id,
            'nomor_surat' => null,
        ]);
        RequestSignoff::create([
            'request_id' => $req->id,
            'signer_role' => 'Staf ULT',
            'order_index' => 1,
            'is_required' => true,
            'status' => \App\Enums\RequestSignoffStatus::PENDING,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.doc_requests.start_signing', $req))
            ->assertStatus(422);

        $req->refresh();
        $this->assertNotSame(RequestStatus::IN_SIGNING->value, $req->current_status->value ?? $req->current_status);

        $this->actingAs($admin)
            ->post(route('admin.doc_requests.gate.nomor_surat', $req), ['nomor_surat' => '123/ULT/2026'])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.doc_requests.start_signing', $req))
            ->assertRedirect();

        $req->refresh();
        $this->assertSame(RequestStatus::IN_SIGNING->value, $req->current_status->value ?? $req->current_status);
        $this->assertSame(1, (int) $req->current_signer_order_index);
    }
}
