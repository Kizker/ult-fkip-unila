<?php

namespace Tests\Unit\Workflow;

use App\Enums\RequestStatus;
use App\Models\Request as UltRequest;
use App\Models\Service;
use App\Models\ServiceWorkflow;
use App\Models\Unit;
use App\Models\User;
use App\Services\RequestWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\CreatesRolesPermissions;
use Tests\TestCase;

class GatekeeperUltTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRolesPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    public function test_only_ult_staff_can_forward_to_faculty_when_required(): void
    {
        $fak = Unit::factory()->create(['type' => \App\Enums\UnitType::fakultas, 'code' => 'FKIP']);
        $service = Service::factory()->create();
        ServiceWorkflow::factory()->create([
            'service_id' => $service->id,
            'require_faculty_signature' => true,
            'require_ult_review' => true,
        ]);

        $req = UltRequest::factory()->create([
            'service_id' => $service->id,
            'current_status' => RequestStatus::REVIEW_ULT,
            'current_step_key' => 'ult_review',
            'current_unit_id' => $fak->id,
        ]);

        $notUlt = User::factory()->create();
        $notUlt->givePermissionTo(['requests.process_unit']); // but NOT requests.review_ult

        $ult = User::factory()->create();
        $ult->givePermissionTo(['requests.review_ult','requests.process_unit']);

        $svc = app(RequestWorkflowService::class);

        try {
            $svc->transition($req, RequestStatus::MENUNGGU_TTD_FAKULTAS, $notUlt, 'forward', 'ult_review', $fak);
            $this->fail('Expected HttpException (403) for non-ULT actor');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }

        $svc->transition($req->fresh(), RequestStatus::MENUNGGU_TTD_FAKULTAS, $ult, 'forward', 'ult_review', $fak);
        $this->assertEquals(RequestStatus::MENUNGGU_TTD_FAKULTAS, $req->fresh()->current_status);
    }
}
