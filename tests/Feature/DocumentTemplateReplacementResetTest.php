<?php

namespace Tests\Feature;

use App\Enums\PlaceholderSourceType;
use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Models\ServiceField;
use App\Models\ServiceWorkflow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesRolesPermissions;
use Tests\Support\DocxBuilder;
use Tests\TestCase;

class DocumentTemplateReplacementResetTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRolesPermissions;

    public function test_replacing_template_resets_placeholders_and_field_mappings(): void
    {
        Storage::fake('private');
        config(['ult.private_disk' => 'private']);

        $this->seedPermissions();

        $admin = User::factory()->create();
        $this->makeRole('Superadmin', [
            'doc_services.manage',
            'doc_templates.upload',
            'doc_placeholders.manage',
            'services.manage',
        ]);
        $admin->assignRole('Superadmin');

        $service = Service::factory()->create([
            'is_active' => false,
            'status' => ServiceStatus::DRAFT,
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

        $field = ServiceField::query()->where('service_id', $service->id)->where('key', 'nama_mhs')->first();
        $this->assertNotNull($field);
        $this->assertSame('NAMA_MHS', $field->maps_to_placeholder_key);

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

        $service->refresh()->load('placeholders');
        $this->assertSameCanonicalizing(
            ['NOMOR_SURAT', 'TANGGAL_SURAT', 'NOMOR_SK'],
            $service->placeholders->pluck('placeholder_key')->all()
        );
        $this->assertNull(
            $service->placeholders->firstWhere('placeholder_key', 'NOMOR_SK')?->source_type
        );
        $this->assertNull(
            $service->placeholders->firstWhere('placeholder_key', 'NOMOR_SK')?->source_ref
        );
        $this->assertNull(
            $service->placeholders->firstWhere('placeholder_key', 'NAMA_MHS')
        );

        $field->refresh();
        $this->assertNull($field->maps_to_placeholder_key);
    }
}
