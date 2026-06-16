<?php

namespace Tests\Feature;

use App\Enums\RequestSignoffStatus;
use App\Enums\ServiceTemplateType;
use App\Models\Request as UltRequest;
use App\Models\RequestSignoff;
use App\Models\Service;
use App\Models\ServiceSigner;
use App\Models\ServiceTemplate;
use App\Models\ServiceWorkflow;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesRolesPermissions;
use Tests\TestCase;

class StudentPemohonSignatureUploadTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRolesPermissions;

    public function test_student_can_upload_pemohon_signature_on_submission(): void
    {
        Storage::fake('private');
        config(['ult.private_disk' => 'private']);

        $this->seedPermissions();

        $unit = Unit::factory()->create();
        $student = User::factory()->create(['unit_id' => $unit->id]);
        $student->givePermissionTo(['requests.view_own', 'requests.create_own']);

        $service = Service::factory()->create([
            'is_active' => true,
        ]);

        ServiceWorkflow::factory()->create([
            'service_id' => $service->id,
            'require_prodi' => true,
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
            'role' => 'PEMOHON',
            'order_index' => 1,
            'is_required' => true,
            // Intentionally false/null to verify fallback config still forces upload flow for PEMOHON.
            'requires_signature_upload' => false,
            'signature_file_types' => null,
            'signature_max_size_kb' => null,
        ]);

        $signature = UploadedFile::fake()->image('pemohon-signature.png', 300, 120);

        $this->actingAs($student)
            ->post(route('student.requests.store'), [
                'service_id' => $service->id,
                'pemohon_signatures' => [
                    1 => $signature,
                ],
            ])->assertRedirect();

        $req = UltRequest::query()->latest('id')->firstOrFail();
        $this->assertSame((int) $student->id, (int) $req->student_id);

        $signoff = RequestSignoff::query()
            ->where('request_id', $req->id)
            ->where('order_index', 1)
            ->where('signer_role', 'PEMOHON')
            ->first();

        $this->assertNotNull($signoff);
        $this->assertSame(
            RequestSignoffStatus::APPROVED->value,
            ($signoff->status instanceof RequestSignoffStatus) ? $signoff->status->value : (string) $signoff->status
        );
        $this->assertSame((int) $student->id, (int) $signoff->signer_user_id);
        $this->assertSame((int) $student->id, (int) $signoff->decided_by);
        $this->assertNotNull($signoff->decided_at);
        $this->assertNotEmpty($signoff->signature_file_path);
        Storage::disk('private')->assertExists((string) $signoff->signature_file_path);
    }
}

