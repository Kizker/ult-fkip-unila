<?php

namespace Tests\Feature;

use App\Enums\RequestSignoffStatus;
use App\Enums\RequestStatus;
use App\Models\Request as UltRequest;
use App\Models\RequestSignoff;
use App\Models\Service;
use App\Models\ServiceSigner;
use App\Models\ServiceTemplate;
use App\Models\ServiceWorkflow;
use App\Models\Unit;
use App\Models\User;
use App\Services\Documents\DateFormatter;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesRolesPermissions;
use Tests\TestCase;

class DocumentModuleTanggalSuratAutoTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRolesPermissions;

    public function test_tanggal_surat_autofilled_on_last_required_approval(): void
    {
        Storage::fake('private');
        config(['ult.private_disk' => 'private']);

        Carbon::setTestNow(Carbon::parse('2026-01-12 10:00:00'));

        $this->seedPermissions();
        $this->makeRole('Staf ULT', ['doc_signoffs.decide']);

        $unit = Unit::factory()->create();

        $signer = User::factory()->create(['unit_id' => $unit->id]);
        $signer->assignRole('Staf ULT');

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
            'uploaded_by' => $signer->id,
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
            'current_status' => RequestStatus::IN_SIGNING,
            'current_unit_id' => $unit->id,
            'nomor_surat' => 'X/ULT/2026',
            'current_signer_order_index' => 1,
        ]);
        RequestSignoff::create([
            'request_id' => $req->id,
            'signer_role' => 'Staf ULT',
            'order_index' => 1,
            'is_required' => true,
            'status' => RequestSignoffStatus::PENDING,
        ]);

        $this->actingAs($signer)
            ->post(route('signer.requests.decide', $req), [
                'decision' => 'APPROVE',
                'note' => 'OK',
            ])->assertRedirect();

        $req->refresh();
        $this->assertSame(RequestStatus::READY_FOR_FINAL->value, $req->current_status->value ?? $req->current_status);
        $this->assertSame('2026-01-12', $req->tanggal_surat?->toDateString());
        $this->assertSame('12 Januari 2026', DateFormatter::formatDateToDoc($req->tanggal_surat->toDateString(), 'id'));
    }
}
