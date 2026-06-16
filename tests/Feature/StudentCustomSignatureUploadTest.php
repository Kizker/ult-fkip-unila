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

class StudentCustomSignatureUploadTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRolesPermissions;

    public function test_student_can_upload_labeled_custom_signatures_without_email_mapping(): void
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
            'role' => 'CUSTOM',
            'custom_label' => 'Ketua Organisasi',
            'order_index' => 1,
            'is_required' => true,
            // Intentionally false/null to verify fallback config still forces upload flow for CUSTOM.
            'requires_signature_upload' => false,
            'signature_file_types' => null,
            'signature_max_size_kb' => null,
        ]);
        ServiceSigner::create([
            'service_id' => $service->id,
            'role' => 'CUSTOM',
            'custom_label' => 'Sekretaris Organisasi',
            'order_index' => 2,
            'is_required' => true,
            'requires_signature_upload' => false,
            'signature_file_types' => null,
            'signature_max_size_kb' => null,
        ]);

        $signature1 = UploadedFile::fake()->image('custom-signature-1.png', 300, 120);
        $signature2 = UploadedFile::fake()->image('custom-signature-2.png', 300, 120);

        $this->actingAs($student)
            ->post(route('student.requests.store'), [
                'service_id' => $service->id,
                'custom_signatures' => [
                    1 => $signature1,
                    2 => $signature2,
                ],
            ])->assertRedirect();

        $req = UltRequest::query()->latest('id')->firstOrFail();
        $signoff1 = RequestSignoff::query()
            ->where('request_id', $req->id)
            ->where('order_index', 1)
            ->where('signer_role', 'CUSTOM')
            ->first();
        $signoff2 = RequestSignoff::query()
            ->where('request_id', $req->id)
            ->where('order_index', 2)
            ->where('signer_role', 'CUSTOM')
            ->first();

        $this->assertNotNull($signoff1);
        $this->assertNotNull($signoff2);
        $this->assertNull($signoff1->signer_user_id);
        $this->assertNull($signoff2->signer_user_id);
        $this->assertSame(
            RequestSignoffStatus::APPROVED->value,
            ($signoff1->status instanceof RequestSignoffStatus) ? $signoff1->status->value : (string) $signoff1->status
        );
        $this->assertSame(
            RequestSignoffStatus::APPROVED->value,
            ($signoff2->status instanceof RequestSignoffStatus) ? $signoff2->status->value : (string) $signoff2->status
        );
        $this->assertStringContainsString('Ketua Organisasi', (string) $signoff1->note);
        $this->assertStringContainsString('Sekretaris Organisasi', (string) $signoff2->note);
        $this->assertNotEmpty($signoff1->signature_file_path);
        $this->assertNotEmpty($signoff2->signature_file_path);
        Storage::disk('private')->exists((string) $signoff1->signature_file_path);
        Storage::disk('private')->exists((string) $signoff2->signature_file_path);
    }
}
