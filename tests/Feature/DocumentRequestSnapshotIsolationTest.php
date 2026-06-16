<?php

namespace Tests\Feature;

use App\Enums\PlaceholderSourceType;
use App\Enums\RequestStatus;
use App\Enums\ServiceStatus;
use App\Models\Request as UltRequest;
use App\Models\RequestData;
use App\Models\Service;
use App\Models\ServiceWorkflow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesRolesPermissions;
use Tests\Support\DocxBuilder;
use Tests\TestCase;

class DocumentRequestSnapshotIsolationTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRolesPermissions;

    public function test_existing_request_gets_snapshot_before_template_is_replaced(): void
    {
        Storage::fake('private');
        config(['ult.private_disk' => 'private']);

        $this->seedPermissions();

        $admin = User::factory()->create();
        $student = User::factory()->create();

        $this->makeRole('Superadmin', [
            'doc_services.manage',
            'doc_templates.upload',
            'doc_placeholders.manage',
            'services.manage',
            'requests.create_own',
            'attachments.upload_own',
        ]);
        $admin->assignRole('Superadmin');
        $student->assignRole('Superadmin');

        $service = Service::factory()->create([
            'is_active' => true,
            'status' => ServiceStatus::PUBLISHED,
            'created_by' => $admin->id,
        ]);
        ServiceWorkflow::factory()->create([
            'service_id' => $service->id,
            'gate_enabled' => true,
            'gate_role' => 'Admin Jurusan',
            'gate_steps_json' => ['VERIFY_INITIAL', 'INPUT_NOMOR_SURAT'],
        ]);

        $firstUpload = new UploadedFile(
            DocxBuilder::makeTempDocx('Hello {{NOMOR_SURAT}} / {{TANGGAL_SURAT}} / {{NAMA_MHS}}'),
            'template-awal.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            null,
            true
        );

        $this->actingAs($admin)
            ->post(route('admin.layanan.dokumen.template', $service), [
                'file' => $firstUpload,
                'extract_placeholders' => 1,
            ])
            ->assertRedirect();

        $service->refresh()->load('placeholders');
        $items = $service->placeholders->map(function ($ph) {
            return [
                'placeholder_key' => $ph->placeholder_key,
                'source_type' => $ph->placeholder_key === 'NAMA_MHS'
                    ? PlaceholderSourceType::FORM->value
                    : ($ph->source_type?->value ?? PlaceholderSourceType::FORM->value),
                'source_ref' => null,
                'is_required' => true,
                'notes' => null,
            ];
        })->all();

        $this->actingAs($admin)
            ->put(route('admin.layanan.dokumen.placeholders', $service), ['items' => $items])
            ->assertRedirect();

        $request = UltRequest::factory()->create([
            'service_id' => $service->id,
            'student_id' => $student->id,
            'current_status' => RequestStatus::DIAJUKAN,
        ]);
        RequestData::query()->create([
            'request_id' => $request->id,
            'data_json' => ['nama_mhs' => 'Mahasiswa Lama'],
            'attachments_json' => [],
            'document_snapshot_json' => null,
        ]);

        $secondUpload = new UploadedFile(
            DocxBuilder::makeTempDocx('Updated {{NOMOR_SURAT}} / {{TANGGAL_SURAT}} / {{NOMOR_SK}}'),
            'template-baru.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            null,
            true
        );

        $this->actingAs($admin)
            ->post(route('admin.layanan.dokumen.template', $service), [
                'file' => $secondUpload,
                'extract_placeholders' => 1,
            ])
            ->assertRedirect();

        $requestData = RequestData::query()->where('request_id', $request->id)->firstOrFail();
        $snapshot = $requestData->document_snapshot_json;

        $this->assertIsArray($snapshot);
        $this->assertSame(
            'template-awal.docx',
            data_get($snapshot, 'template.original_filename')
        );
        $this->assertContains(
            'NAMA_MHS',
            collect(data_get($snapshot, 'placeholders', []))->pluck('placeholder_key')->all()
        );

        $service->refresh()->load('placeholders', 'fields');
        $this->assertNotContains('NAMA_MHS', $service->placeholders->pluck('placeholder_key')->all());
        $this->assertNotContains('nama_mhs', $service->fields->pluck('key')->all());
    }
}
