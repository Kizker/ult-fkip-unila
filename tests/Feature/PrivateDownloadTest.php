<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Request as UltRequest;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesRolesPermissions;
use Tests\TestCase;

class PrivateDownloadTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRolesPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
        Storage::fake('local');
    }

    public function test_unauthorized_download_is_forbidden(): void
    {
        $unit = Unit::factory()->create();
        $student = User::factory()->create();
        $other = User::factory()->create();

        $student->givePermissionTo(['requests.view_own','attachments.download_private']);

        $req = UltRequest::factory()->create(['student_id' => $student->id, 'current_unit_id' => $unit->id]);

        $att = Attachment::factory()->create([
            'request_id' => $req->id,
            'uploaded_by' => $student->id,
            'stored_path' => 'requests/'.$req->id.'/input/test.pdf',
        ]);

        Storage::disk('local')->put($att->stored_path, 'dummy');

        $other->givePermissionTo(['attachments.download_private']); // still not allowed due to policy (not owner)

        $this->actingAs($other)
            ->get(route('attachments.download', $att))
            ->assertStatus(403);
    }

    public function test_authorized_download_succeeds_and_audited(): void
    {
        $unit = Unit::factory()->create();
        $student = User::factory()->create();
        $student->givePermissionTo(['requests.view_own','attachments.download_private']);

        $req = UltRequest::factory()->create(['student_id' => $student->id, 'current_unit_id' => $unit->id]);

        $att = Attachment::factory()->create([
            'request_id' => $req->id,
            'uploaded_by' => $student->id,
            'stored_path' => 'requests/'.$req->id.'/input/test.pdf',
        ]);

        Storage::disk('local')->put($att->stored_path, 'dummy');

        $this->actingAs($student)
            ->get(route('attachments.download', $att))
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'attachments.download',
        ]);
    }
}
