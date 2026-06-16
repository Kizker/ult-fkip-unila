<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Models\Request as UltRequest;
use App\Models\Service;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesRolesPermissions;
use Tests\TestCase;

class DocumentGateRevisionNoteTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRolesPermissions;

    public function test_revision_requires_note_and_records_it(): void
    {
        $this->seedPermissions();
        $this->makeRole('Admin Jurusan', ['requests.view_unit', 'requests.process_unit', 'doc_requests.gate']);

        $unit = Unit::factory()->create();
        $admin = User::factory()->create(['unit_id' => $unit->id]);
        $admin->assignRole('Admin Jurusan');

        $student = User::factory()->create(['unit_id' => $unit->id]);
        $service = Service::factory()->create();

        $req = UltRequest::factory()->create([
            'service_id' => $service->id,
            'student_id' => $student->id,
            'current_status' => RequestStatus::DIAJUKAN,
            'current_unit_id' => $unit->id,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.requests.show', $req))
            ->post(route('admin.doc_requests.gate.verify', $req), [
                'decision' => 'REVISION',
            ])
            ->assertSessionHasErrors('note');

        $req->refresh();
        $this->assertSame(RequestStatus::DIAJUKAN, $req->current_status);

        $note = 'Perbaiki lampiran pendukung dan sesuaikan nomor surat.';

        $this->actingAs($admin)
            ->post(route('admin.doc_requests.gate.verify', $req), [
                'decision' => 'REVISION',
                'note' => $note,
            ])
            ->assertRedirect();

        $req->refresh();
        $this->assertSame(RequestStatus::PERLU_PERBAIKAN, $req->current_status);
        $this->assertSame($note, $req->histories()->latest('id')->value('note'));
    }

    public function test_pass_requires_nomor_surat_as_validation_feedback(): void
    {
        $this->seedPermissions();
        $this->makeRole('Admin Jurusan', ['requests.view_unit', 'requests.process_unit', 'doc_requests.gate']);

        $unit = Unit::factory()->create();
        $admin = User::factory()->create(['unit_id' => $unit->id]);
        $admin->assignRole('Admin Jurusan');

        $student = User::factory()->create(['unit_id' => $unit->id]);
        $service = Service::factory()->create();

        $req = UltRequest::factory()->create([
            'service_id' => $service->id,
            'student_id' => $student->id,
            'current_status' => RequestStatus::DIAJUKAN,
            'current_unit_id' => $unit->id,
            'nomor_surat' => null,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.requests.show', $req))
            ->post(route('admin.doc_requests.gate.verify', $req), [
                'decision' => 'PASS',
            ])
            ->assertRedirect(route('admin.requests.show', $req))
            ->assertSessionHasErrors('nomor_surat');

        $req->refresh();
        $this->assertSame(RequestStatus::DIAJUKAN, $req->current_status);
    }
}
