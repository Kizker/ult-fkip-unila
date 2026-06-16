<?php

namespace Tests\Unit;

use App\Enums\AttachmentKind;
use App\Enums\AttachmentVerifiedStatus;
use App\Enums\RequestStatus;
use App\Models\Attachment;
use App\Models\Request as UltRequest;
use App\Models\RequestNote;
use App\Models\RequestStatusHistory;
use App\Models\Service;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestOrderingNewestFirstTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_relations_are_sorted_newest_first(): void
    {
        $unit = Unit::factory()->create();
        $student = User::factory()->create(['unit_id' => $unit->id]);
        $service = Service::factory()->create();

        $request = UltRequest::factory()->create([
            'service_id' => $service->id,
            'student_id' => $student->id,
            'current_status' => RequestStatus::PERLU_PERBAIKAN,
            'current_unit_id' => $unit->id,
        ]);

        $oldHistory = RequestStatusHistory::create([
            'request_id' => $request->id,
            'from_status' => 'DIAJUKAN',
            'to_status' => 'PERLU_PERBAIKAN',
            'step_key' => 'step_old',
            'note' => 'old',
            'actor_id' => $student->id,
            'created_at' => now()->subMinutes(10),
        ]);
        $newHistory = RequestStatusHistory::create([
            'request_id' => $request->id,
            'from_status' => 'PERLU_PERBAIKAN',
            'to_status' => 'DIAJUKAN',
            'step_key' => 'step_new',
            'note' => 'new',
            'actor_id' => $student->id,
            'created_at' => now()->subMinutes(1),
        ]);

        $oldNote = RequestNote::create([
            'request_id' => $request->id,
            'actor_id' => $student->id,
            'body' => 'old note',
            'is_internal' => false,
            'created_at' => now()->subMinutes(8),
        ]);
        $newNote = RequestNote::create([
            'request_id' => $request->id,
            'actor_id' => $student->id,
            'body' => 'new note',
            'is_internal' => false,
            'created_at' => now()->subMinutes(2),
        ]);

        Carbon::setTestNow('2026-01-01 10:00:00');
        $attEditedLater = Attachment::create([
            'request_id' => $request->id,
            'uploaded_by' => $student->id,
            'kind' => AttachmentKind::input,
            'service_field_id' => null,
            'original_name' => 'old-file.pdf',
            'stored_path' => 'requests/'.$request->id.'/input/old-file.pdf',
            'mime' => 'application/pdf',
            'size' => 1200,
            'sha256' => hash('sha256', 'old-file'),
            'verified_status' => AttachmentVerifiedStatus::pending,
        ]);

        Carbon::setTestNow('2026-01-01 10:05:00');
        $attNewest = Attachment::create([
            'request_id' => $request->id,
            'uploaded_by' => $student->id,
            'kind' => AttachmentKind::input,
            'service_field_id' => null,
            'original_name' => 'new-file.pdf',
            'stored_path' => 'requests/'.$request->id.'/input/new-file.pdf',
            'mime' => 'application/pdf',
            'size' => 1300,
            'sha256' => hash('sha256', 'new-file'),
            'verified_status' => AttachmentVerifiedStatus::pending,
        ]);

        Carbon::setTestNow('2026-01-01 10:10:00');
        $attEditedLater->update([
            'verification_note' => 'edited later',
        ]);
        Carbon::setTestNow();

        $request->load(['histories', 'notes', 'attachments']);

        $this->assertSame([(int) $newHistory->id, (int) $oldHistory->id], $request->histories->pluck('id')->map(fn ($v) => (int) $v)->all());
        $this->assertSame([(int) $newNote->id, (int) $oldNote->id], $request->notes->pluck('id')->map(fn ($v) => (int) $v)->all());
        $this->assertSame([(int) $attEditedLater->id, (int) $attNewest->id], $request->attachments->pluck('id')->map(fn ($v) => (int) $v)->all());
    }
}
