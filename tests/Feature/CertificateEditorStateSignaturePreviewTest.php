<?php

namespace Tests\Feature;

use App\Enums\DocumentSourceType;
use App\Enums\RequestSignoffStatus;
use App\Models\Request as UltRequest;
use App\Models\RequestData;
use App\Models\RequestSignoff;
use App\Models\Service;
use App\Models\Unit;
use App\Models\User;
use App\Services\Documents\CertificateDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateEditorStateSignaturePreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_state_includes_signature_preview_url_for_certificate_pemohon_and_custom_signers(): void
    {
        $unit = Unit::factory()->create();
        $student = User::factory()->create(['unit_id' => $unit->id]);
        $service = Service::factory()->create([
            'document_source_type' => DocumentSourceType::REQUEST_PPTX,
        ]);

        $request = UltRequest::factory()->create([
            'service_id' => $service->id,
            'student_id' => $student->id,
            'current_unit_id' => $unit->id,
        ]);

        RequestData::query()->create([
            'request_id' => $request->id,
            'data_json' => [
                'certificate' => [
                    'signers' => [
                        ['order_index' => 1, 'signer_type' => 'CUSTOM', 'name' => 'Custom 1', 'id_number' => 'ID-C1'],
                        ['order_index' => 2, 'signer_type' => 'INTERNAL', 'name' => 'Internal 2', 'id_number' => 'ID-I2'],
                        ['order_index' => 3, 'signer_type' => 'PEMOHON', 'name' => 'Pemohon 3', 'id_number' => 'ID-P3'],
                    ],
                ],
            ],
            'attachments_json' => [],
        ]);

        $customSignoff = RequestSignoff::query()->create([
            'request_id' => $request->id,
            'signer_role' => 'CERT_CUSTOM',
            'order_index' => 1,
            'is_required' => true,
            'status' => RequestSignoffStatus::APPROVED,
            'signature_file_path' => "requests/{$request->id}/signatures/custom.png",
        ]);
        RequestSignoff::query()->create([
            'request_id' => $request->id,
            'signer_role' => 'CERT_INTERNAL',
            'order_index' => 2,
            'is_required' => true,
            'status' => RequestSignoffStatus::PENDING,
            'signature_file_path' => null,
        ]);
        $pemohonSignoff = RequestSignoff::query()->create([
            'request_id' => $request->id,
            'signer_role' => 'CERT_PEMOHON',
            'order_index' => 3,
            'is_required' => true,
            'status' => RequestSignoffStatus::APPROVED,
            'signature_file_path' => "requests/{$request->id}/signatures/pemohon.png",
        ]);

        $state = app(CertificateDocumentService::class)->editorState(
            $request->fresh(['service', 'data', 'attachments', 'signoffs'])
        );

        $this->assertTrue((bool) ($state['is_certificate'] ?? false));
        $this->assertCount(3, $state['signers'] ?? []);
        $this->assertSame(
            route('student.requests.signature.preview', ['request' => $request, 'signoff' => $customSignoff]),
            (string) ($state['signers'][0]['signature_preview_url'] ?? '')
        );
        $this->assertSame('', (string) ($state['signers'][1]['signature_preview_url'] ?? ''));
        $this->assertSame(
            route('student.requests.signature.preview', ['request' => $request, 'signoff' => $pemohonSignoff]),
            (string) ($state['signers'][2]['signature_preview_url'] ?? '')
        );
    }
}

