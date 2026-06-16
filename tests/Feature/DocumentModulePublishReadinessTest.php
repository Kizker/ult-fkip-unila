<?php

namespace Tests\Feature;

use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Models\ServiceWorkflow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesRolesPermissions;
use Tests\Support\DocxBuilder;
use Tests\TestCase;

class DocumentModulePublishReadinessTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRolesPermissions;

    public function test_publish_readiness_happy_path(): void
    {
        Storage::fake('private');
        config(['ult.private_disk' => 'private']);

        $this->seedPermissions();

        $super = User::factory()->create();
        $this->makeRole('Superadmin', [
            'doc_services.manage','doc_services.publish','doc_templates.upload','doc_placeholders.manage','doc_signers.manage',
            'services.manage',
        ]);
        $super->assignRole('Superadmin');

        $service = Service::factory()->create([
            'is_active' => false,
            'status' => ServiceStatus::DRAFT,
            'created_by' => $super->id,
        ]);
        ServiceWorkflow::factory()->create([
            'service_id' => $service->id,
            'gate_enabled' => true,
            'gate_role' => 'Admin Jurusan',
            'gate_steps_json' => ['VERIFY_INITIAL','INPUT_NOMOR_SURAT'],
        ]);

        // Upload docx template with required placeholders + 1 FORM placeholder
        $docxPath = DocxBuilder::makeTempDocx('Hello {{NOMOR_SURAT}} / {{TANGGAL_SURAT}} / {{NAMA_MHS}}');
        $upload = new UploadedFile(
            $docxPath,
            'template.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            null,
            true
        );

        $this->actingAs($super)
            ->post(route('admin.layanan.dokumen.template', $service), ['file' => $upload])
            ->assertRedirect();

        $this->actingAs($super)
            ->post(route('admin.layanan.dokumen.extract', $service))
            ->assertRedirect();

        // Map FORM placeholder
        $service->refresh()->load(['placeholders']);
        $items = $service->placeholders->map(function ($ph) {
            $src = $ph->source_type?->value;
            if ($ph->placeholder_key === 'NAMA_MHS') $src = 'FORM';
            return [
                'placeholder_key' => $ph->placeholder_key,
                'source_type' => $src ?: 'FORM',
                'source_ref' => null,
                'is_required' => true,
                'notes' => null,
            ];
        })->all();

        $this->actingAs($super)
            ->put(route('admin.layanan.dokumen.placeholders', $service), ['items' => $items])
            ->assertRedirect();

        // Create a service field mapping to NAMA_MHS
        $this->actingAs($super)
            ->post(route('admin.layanan.dokumen.fields.create', $service), [
                'key' => 'nama_mhs',
                'label_id' => 'Nama Mahasiswa',
                'type' => 'text',
                'required' => true,
                'rules_json' => '[]',
                'options_json' => '[]',
                'maps_to_placeholder_key' => 'NAMA_MHS',
                'sort_order' => 1,
            ])->assertRedirect();

        // Set signer chain
        $this->actingAs($super)
            ->put(route('admin.layanan.dokumen.signers', $service), [
                'signers_json' => json_encode([
                    [
                        'role' => 'Staf ULT',
                        'order_index' => 1,
                        'is_required' => true,
                        'requires_signature_upload' => false,
                    ],
                ]),
            ])->assertRedirect();

        // Publish
        $this->actingAs($super)
            ->post(route('admin.layanan.dokumen.publish', $service))
            ->assertRedirect();

        $service->refresh();
        $this->assertSame('PUBLISHED', $service->status?->value);
        $this->assertTrue((bool) $service->is_active);
    }
}
