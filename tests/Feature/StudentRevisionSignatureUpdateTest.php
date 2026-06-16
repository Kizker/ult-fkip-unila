<?php

namespace Tests\Feature;

use App\Enums\RequestSignoffStatus;
use App\Enums\RequestStatus;
use App\Models\Request as UltRequest;
use App\Models\RequestSignoff;
use App\Models\Service;
use App\Models\ServiceSigner;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesRolesPermissions;
use Tests\TestCase;

class StudentRevisionSignatureUpdateTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRolesPermissions;

    public function test_student_can_update_pemohon_and_custom_signatures_during_revision(): void
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
        ServiceSigner::create([
            'service_id' => $service->id,
            'role' => 'CUSTOM',
            'custom_label' => 'Ketua Organisasi',
            'order_index' => 1,
            'is_required' => true,
            'requires_signature_upload' => false,
            'signature_file_types' => null,
            'signature_max_size_kb' => null,
        ]);
        ServiceSigner::create([
            'service_id' => $service->id,
            'role' => 'PEMOHON',
            'custom_label' => null,
            'order_index' => 2,
            'is_required' => true,
            'requires_signature_upload' => false,
            'signature_file_types' => null,
            'signature_max_size_kb' => null,
        ]);

        $req = UltRequest::factory()->create([
            'service_id' => $service->id,
            'student_id' => $student->id,
            'current_status' => RequestStatus::PERLU_PERBAIKAN,
            'current_unit_id' => $unit->id,
        ]);

        $oldCustomPath = "requests/{$req->id}/signatures/old_custom.png";
        $oldPemohonPath = "requests/{$req->id}/signatures/old_pemohon.png";
        Storage::disk('private')->put($oldCustomPath, 'old-custom');
        Storage::disk('private')->put($oldPemohonPath, 'old-pemohon');

        RequestSignoff::create([
            'request_id' => $req->id,
            'signer_role' => 'CUSTOM',
            'order_index' => 1,
            'is_required' => true,
            'status' => RequestSignoffStatus::APPROVED,
            'decided_by' => $student->id,
            'decided_at' => now()->subDay(),
            'note' => 'old custom',
            'signature_file_path' => $oldCustomPath,
        ]);
        RequestSignoff::create([
            'request_id' => $req->id,
            'signer_role' => 'PEMOHON',
            'signer_user_id' => $student->id,
            'order_index' => 2,
            'is_required' => true,
            'status' => RequestSignoffStatus::APPROVED,
            'decided_by' => $student->id,
            'decided_at' => now()->subDay(),
            'note' => 'old pemohon',
            'signature_file_path' => $oldPemohonPath,
        ]);

        $newCustom = UploadedFile::fake()->image('new-custom.png', 300, 120);
        $newPemohon = UploadedFile::fake()->image('new-pemohon.png', 300, 120);

        $this->actingAs($student)
            ->post(route('student.requests.data.update', $req), [
                'custom_signatures' => [
                    1 => $newCustom,
                ],
                'pemohon_signatures' => [
                    2 => $newPemohon,
                ],
            ])
            ->assertRedirect();

        $customSignoff = RequestSignoff::query()
            ->where('request_id', $req->id)
            ->where('signer_role', 'CUSTOM')
            ->where('order_index', 1)
            ->firstOrFail();
        $pemohonSignoff = RequestSignoff::query()
            ->where('request_id', $req->id)
            ->where('signer_role', 'PEMOHON')
            ->where('order_index', 2)
            ->firstOrFail();

        $this->assertNotSame($oldCustomPath, (string) $customSignoff->signature_file_path);
        $this->assertNotSame($oldPemohonPath, (string) $pemohonSignoff->signature_file_path);
        Storage::disk('private')->assertExists((string) $customSignoff->signature_file_path);
        Storage::disk('private')->assertExists((string) $pemohonSignoff->signature_file_path);

        $customStatus = ($customSignoff->status instanceof RequestSignoffStatus) ? $customSignoff->status->value : (string) $customSignoff->status;
        $pemohonStatus = ($pemohonSignoff->status instanceof RequestSignoffStatus) ? $pemohonSignoff->status->value : (string) $pemohonSignoff->status;
        $this->assertSame(RequestSignoffStatus::APPROVED->value, $customStatus);
        $this->assertSame(RequestSignoffStatus::APPROVED->value, $pemohonStatus);
        $this->assertSame((int) $student->id, (int) $pemohonSignoff->signer_user_id);
    }

    public function test_revision_form_shows_previous_signature_preview_and_endpoint_returns_image(): void
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
        ServiceSigner::create([
            'service_id' => $service->id,
            'role' => 'CUSTOM',
            'custom_label' => 'Ketua Organisasi',
            'order_index' => 1,
            'is_required' => true,
            'requires_signature_upload' => false,
            'signature_file_types' => null,
            'signature_max_size_kb' => null,
        ]);

        $req = UltRequest::factory()->create([
            'service_id' => $service->id,
            'student_id' => $student->id,
            'current_status' => RequestStatus::PERLU_PERBAIKAN,
            'current_unit_id' => $unit->id,
        ]);

        $img = UploadedFile::fake()->image('custom-preview.png', 320, 120);
        $path = $img->storeAs("requests/{$req->id}/signatures", 'custom-preview.png', 'private');

        $signoff = RequestSignoff::create([
            'request_id' => $req->id,
            'signer_role' => 'CUSTOM',
            'order_index' => 1,
            'is_required' => true,
            'status' => RequestSignoffStatus::APPROVED,
            'decided_by' => $student->id,
            'decided_at' => now(),
            'note' => 'preview custom',
            'signature_file_path' => $path,
        ]);

        $previewUrl = route('student.requests.signature.preview', ['request' => $req, 'signoff' => $signoff]);

        $this->actingAs($student)
            ->get(route('student.requests.show', $req))
            ->assertOk()
            ->assertSee('data-signature-live-preview-input', false)
            ->assertSee($previewUrl, false);

        $this->actingAs($student)
            ->get($previewUrl)
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }
}
