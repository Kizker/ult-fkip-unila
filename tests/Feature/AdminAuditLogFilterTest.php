<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesRolesPermissions;
use Tests\TestCase;

class AdminAuditLogFilterTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRolesPermissions;

    public function test_admin_can_filter_audit_logs_by_action_entity_and_date(): void
    {
        $this->seedPermissions();
        $this->makeRole('Superadmin', ['audit_logs.view']);

        $admin = User::factory()->create();
        $admin->assignRole('Superadmin');

        $actor = User::factory()->create(['name' => 'Actor A', 'email' => 'actor-a@example.test']);

        AuditLog::create([
            'actor_id' => $actor->id,
            'action' => 'request.created',
            'entity_type' => 'requests',
            'entity_id' => '1',
            'metadata' => ['k' => 'v'],
            'created_at' => '2026-01-01 10:00:00',
        ]);

        AuditLog::create([
            'actor_id' => $actor->id,
            'action' => 'request.status_changed',
            'entity_type' => 'requests',
            'entity_id' => '2',
            'metadata' => ['k' => 'v'],
            'created_at' => '2026-01-03 10:00:00',
        ]);

        AuditLog::create([
            'actor_id' => $actor->id,
            'action' => 'attachments.download',
            'entity_type' => 'attachments',
            'entity_id' => '3',
            'metadata' => ['k' => 'v'],
            'created_at' => '2026-01-04 10:00:00',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.audit.index', [
                'action' => 'request.status_changed',
                'entity_type' => 'requests',
                'from' => '2026-01-02',
                'to' => '2026-01-03',
            ]))
            ->assertOk()
            ->assertSee('requests #2')
            ->assertDontSee('requests #1')
            ->assertDontSee('attachments #3');
    }

    public function test_admin_can_search_audit_logs_by_actor_name(): void
    {
        $this->seedPermissions();
        $this->makeRole('Superadmin', ['audit_logs.view']);

        $admin = User::factory()->create();
        $admin->assignRole('Superadmin');

        $targetActor = User::factory()->create(['name' => 'Nama Khusus Audit', 'email' => 'khusus@example.test']);
        $otherActor = User::factory()->create(['name' => 'Pengguna Lain', 'email' => 'lain@example.test']);

        AuditLog::create([
            'actor_id' => $targetActor->id,
            'action' => 'request.created',
            'entity_type' => 'requests',
            'entity_id' => '10',
            'metadata' => ['k' => 'v'],
            'created_at' => '2026-01-05 10:00:00',
        ]);

        AuditLog::create([
            'actor_id' => $otherActor->id,
            'action' => 'attachments.download',
            'entity_type' => 'attachments',
            'entity_id' => '11',
            'metadata' => ['k' => 'v'],
            'created_at' => '2026-01-05 11:00:00',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.audit.index', ['q' => 'Nama Khusus Audit']))
            ->assertOk()
            ->assertSee('requests #10')
            ->assertDontSee('attachments #11');
    }
}
