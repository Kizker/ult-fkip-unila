<?php

namespace Tests\Feature;

use App\Enums\RequestSignoffStatus;
use App\Models\Request as UltRequest;
use App\Models\RequestData;
use App\Models\RequestSignoff;
use App\Models\Service;
use App\Models\Unit;
use App\Models\User;
use App\Services\Documents\CertificateDocumentService;
use App\Services\Documents\DateFormatter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CertificateTanggalTtdFollowsLatestApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_tanggal_ttd_uses_latest_approval_timestamp_not_signer_order(): void
    {
        $unit = Unit::factory()->create();
        $student = User::factory()->create(['unit_id' => $unit->id]);
        $service = Service::factory()->create();

        $request = UltRequest::factory()->create([
            'service_id' => $service->id,
            'student_id' => $student->id,
            'current_unit_id' => $unit->id,
            'nomor_surat' => '00001/UN26.14/PN.01.00/2026',
        ]);

        RequestData::query()->create([
            'request_id' => $request->id,
            'data_json' => [
                'certificate' => [
                    'signers' => [
                        ['order_index' => 1, 'name' => 'Signer 1', 'id_number' => 'ID-1', 'jabatan' => 'Jabatan 1'],
                        ['order_index' => 2, 'name' => 'Signer 2', 'id_number' => 'ID-2', 'jabatan' => 'Jabatan 2'],
                    ],
                ],
            ],
            'attachments_json' => [],
        ]);

        $older = Carbon::parse('2026-02-20 08:00:00');
        $latest = Carbon::parse('2026-02-20 10:30:00');

        // Higher order signer approved earlier.
        RequestSignoff::query()->create([
            'request_id' => $request->id,
            'signer_role' => 'CERT_INTERNAL',
            'order_index' => 2,
            'is_required' => true,
            'status' => RequestSignoffStatus::APPROVED,
            'decided_at' => $older,
        ]);

        // Lower order signer approved later.
        RequestSignoff::query()->create([
            'request_id' => $request->id,
            'signer_role' => 'CERT_CUSTOM',
            'order_index' => 1,
            'is_required' => true,
            'status' => RequestSignoffStatus::APPROVED,
            'decided_at' => $latest,
        ]);

        $svc = app(CertificateDocumentService::class);
        $method = new \ReflectionMethod($svc, 'buildTextValueMap');
        $method->setAccessible(true);

        /** @var array<string,string> $values */
        $values = $method->invoke($svc, $request->fresh(['signoffs', 'data']));

        $this->assertArrayHasKey('tanggal_ttd', $values);
        $this->assertSame(DateFormatter::formatDateToDoc($latest->toDateString(), 'id'), $values['tanggal_ttd']);
        $this->assertSame('00001/UN26.14/PN.01.00/2026', $values['nomor_surat']);
    }

    public function test_tanggal_ttd_empty_when_last_required_signature_not_approved(): void
    {
        $unit = Unit::factory()->create();
        $student = User::factory()->create(['unit_id' => $unit->id]);
        $service = Service::factory()->create();

        $request = UltRequest::factory()->create([
            'service_id' => $service->id,
            'student_id' => $student->id,
            'current_unit_id' => $unit->id,
            'nomor_surat' => '00002/UN26.14/PN.01.00/2026',
        ]);

        RequestData::query()->create([
            'request_id' => $request->id,
            'data_json' => [
                'certificate' => [
                    'signers' => [
                        ['order_index' => 1, 'name' => 'Signer 1', 'id_number' => 'ID-1', 'jabatan' => 'Jabatan 1'],
                        ['order_index' => 2, 'name' => 'Signer 2', 'id_number' => 'ID-2', 'jabatan' => 'Jabatan 2'],
                    ],
                ],
            ],
            'attachments_json' => [],
        ]);

        RequestSignoff::query()->create([
            'request_id' => $request->id,
            'signer_role' => 'CERT_INTERNAL',
            'order_index' => 1,
            'is_required' => true,
            'status' => RequestSignoffStatus::APPROVED,
            'decided_at' => Carbon::parse('2026-02-20 09:00:00'),
        ]);

        RequestSignoff::query()->create([
            'request_id' => $request->id,
            'signer_role' => 'CERT_CUSTOM',
            'order_index' => 2,
            'is_required' => true,
            'status' => RequestSignoffStatus::PENDING,
            'decided_at' => null,
        ]);

        $svc = app(CertificateDocumentService::class);
        $method = new \ReflectionMethod($svc, 'buildTextValueMap');
        $method->setAccessible(true);

        /** @var array<string,string> $values */
        $values = $method->invoke($svc, $request->fresh(['signoffs', 'data']));

        $this->assertArrayHasKey('tanggal_ttd', $values);
        $this->assertSame('', $values['tanggal_ttd']);
        $this->assertSame('00002/UN26.14/PN.01.00/2026', $values['nomor_surat']);
    }
}
