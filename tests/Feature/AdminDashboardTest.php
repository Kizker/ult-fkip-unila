<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Request as UltRequest;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup permissions
        $this->seed(\Database\Seeders\RbacSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Superadmin');
    }

    public function test_admin_can_view_dashboard()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertViewHasAll(['summary', 'trendData', 'statusData', 'kpi', 'queue', 'transactions']);
    }

    public function test_dashboard_displays_correct_summary_counts()
    {
        $service = Service::factory()->create();
        $studentUser = User::factory()->create();

        // Create requests with different statuses
        UltRequest::factory()->count(2)->create(['current_status' => 'SELESAI', 'service_id' => $service->id, 'student_id' => $studentUser->id]);
        UltRequest::factory()->count(1)->create(['current_status' => 'DITOLAK', 'service_id' => $service->id, 'student_id' => $studentUser->id]);
        UltRequest::factory()->count(3)->create(['current_status' => 'DIAJUKAN', 'service_id' => $service->id, 'student_id' => $studentUser->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $summary = $response->viewData('summary');
        
        $this->assertEquals(6, $summary['total']);
        $this->assertEquals(2, $summary['completed']);
        $this->assertEquals(1, $summary['rejected']);
        $this->assertEquals(3, $summary['pending']);
    }

    public function test_dashboard_export_csv()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard.export', ['format' => 'csv']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_dashboard_export_excel()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard.export', ['format' => 'excel']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_dashboard_chart_data_endpoint()
    {
        $response = $this->actingAs($this->admin)->getJson(route('admin.dashboard.chart_data'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'summary' => ['total', 'completed', 'pending', 'rejected'],
            'trend',
            'status',
            'kpi',
            'label'
        ]);
    }
}
