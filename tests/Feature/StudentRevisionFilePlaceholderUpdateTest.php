<?php

namespace Tests\Feature;

use App\Enums\AttachmentKind;
use App\Enums\AttachmentVerifiedStatus;
use App\Enums\RequestStatus;
use App\Models\Attachment;
use App\Models\Request as UltRequest;
use App\Models\RequestData;
use App\Models\RequestFieldValue;
use App\Models\Service;
use App\Models\ServiceField;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesRolesPermissions;
use Tests\TestCase;

class StudentRevisionFilePlaceholderUpdateTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRolesPermissions;

    public function test_student_can_replace_file_field_during_revision(): void
    {
        Storage::fake('private');
        config(['ult.private_disk' => 'private']);

        $this->seedPermissions();
        $this->makeRole('Mahasiswa', ['requests.view_own', 'requests.update_own']);

        if (config('database.default') === 'sqlite') {
            DB::statement('PRAGMA ignore_check_constraints = ON');
        }

        $unit = Unit::factory()->create();
        $student = User::factory()->create(['unit_id' => $unit->id]);
        $student->assignRole('Mahasiswa');

        $service = Service::factory()->create();
        $fileField = ServiceField::create([
            'service_id' => $service->id,
            'key' => 'pasphoto',
            'maps_to_placeholder_key' => 'PASPHOTO',
            'label_id' => 'Pasphoto',
            'label_en' => 'Passport Photo',
            'type' => 'file',
            'required' => true,
            'rules_json' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'options_json' => null,
            'sort_order' => 1,
        ]);

        $req = UltRequest::factory()->create([
            'service_id' => $service->id,
            'student_id' => $student->id,
            'current_status' => RequestStatus::PERLU_PERBAIKAN,
            'current_unit_id' => $unit->id,
        ]);

        $oldPath = "requests/{$req->id}/input/old-passphoto.jpg";
        Storage::disk('private')->put($oldPath, 'old');
        $oldAttachment = Attachment::create([
            'request_id' => $req->id,
            'uploaded_by' => $student->id,
            'kind' => AttachmentKind::input,
            'service_field_id' => $fileField->id,
            'original_name' => 'old-passphoto.jpg',
            'stored_path' => $oldPath,
            'mime' => 'image/jpeg',
            'size' => 3,
            'sha256' => hash('sha256', 'old'),
            'verified_status' => AttachmentVerifiedStatus::pending,
        ]);

        RequestFieldValue::create([
            'request_id' => $req->id,
            'service_field_id' => $fileField->id,
            'value_json' => ['attachment_id' => $oldAttachment->id, 'original' => $oldAttachment->original_name],
        ]);

        RequestData::create([
            'request_id' => $req->id,
            'data_json' => [
                'pasphoto' => ['attachment_id' => $oldAttachment->id],
            ],
            'attachments_json' => [
                'pasphoto' => $oldAttachment->id,
            ],
        ]);

        $newFile = UploadedFile::fake()->image('pasphoto-baru.jpg', 300, 400);

        $this->actingAs($student)
            ->post(route('student.requests.data.update', $req), [
                'fields' => [
                    $fileField->id => $newFile,
                ],
            ])
            ->assertRedirect();

        $req->refresh();
        $fieldValue = RequestFieldValue::query()
            ->where('request_id', $req->id)
            ->where('service_field_id', $fileField->id)
            ->firstOrFail();

        $newAttachmentId = (int) ($fieldValue->value_json['attachment_id'] ?? 0);
        $this->assertTrue($newAttachmentId > 0);
        $this->assertNotSame((int) $oldAttachment->id, $newAttachmentId);

        $newAttachment = Attachment::query()->findOrFail($newAttachmentId);
        $this->assertSame((int) $fileField->id, (int) $newAttachment->service_field_id);
        $this->assertStringStartsWith("requests/{$req->id}/input/", (string) $newAttachment->stored_path);
        Storage::disk('private')->assertExists((string) $newAttachment->stored_path);

        $requestData = RequestData::query()->where('request_id', $req->id)->firstOrFail();
        $this->assertSame($newAttachmentId, (int) data_get($requestData->data_json, 'pasphoto.attachment_id'));
        $this->assertSame($newAttachmentId, (int) data_get($requestData->attachments_json, 'pasphoto'));
    }
}
