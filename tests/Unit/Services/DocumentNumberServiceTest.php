<?php

namespace Tests\Unit\Services;

use App\Models\Request as UltRequest;
use App\Models\Unit;
use App\Services\DocumentNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesRolesPermissions;
use Tests\TestCase;

class DocumentNumberServiceTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRolesPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    public function test_document_number_is_unique_and_idempotent_per_request(): void
    {
        $unit = Unit::factory()->create(['code' => 'FKIP']);

        $req = UltRequest::factory()->create(['current_unit_id' => $unit->id]);

        $svc = app(DocumentNumberService::class);

        $doc1 = $svc->issue($req, $unit, 'default');
        $doc2 = $svc->issue($req->fresh(), $unit, 'default');

        $this->assertEquals($doc1->id, $doc2->id);
        $this->assertEquals(1, \App\Models\DocumentNumber::count());
    }

    public function test_sequence_increments_for_same_unit_and_year(): void
    {
        $unit = Unit::factory()->create(['code' => 'FKIP']);

        $svc = app(DocumentNumberService::class);

        $req1 = UltRequest::factory()->create(['current_unit_id' => $unit->id]);
        $req2 = UltRequest::factory()->create(['current_unit_id' => $unit->id]);

        $doc1 = $svc->issue($req1, $unit, 'default');
        $doc2 = $svc->issue($req2, $unit, 'default');

        $this->assertEquals($doc1->number_seq + 1, $doc2->number_seq);
        $this->assertNotEmpty($svc->renderNumber($doc1));
        $this->assertNotEmpty($svc->renderNumber($doc2));
    }
}
