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
use App\Notifications\RequestStatusChanged;
use App\Services\RequestWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Tests\Support\CreatesRolesPermissions;
use Tests\TestCase;

class RequestWorkflowNotificationTargetsTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRolesPermissions;

    public function test_transition_notifies_all_related_users_in_request_process(): void
    {
        Notification::fake();
        $this->seedPermissions();

        $this->makeRole('Mahasiswa', ['requests.view_own']);
        $this->makeRole('Superadmin', ['requests.view_any']);
        $this->makeRole('ADMIN_JURUSAN_NOTIFY', ['requests.view_unit', 'requests.process_unit']);
        $this->makeRole('STAF_ULT_NOTIFY', ['requests.review_ult']);
        $this->makeRole('KAPRODI', ['doc_signoffs.decide']);

        $fakultas = Unit::factory()->create();
        $jurusan = Unit::factory()->jurusan($fakultas)->create();
        $prodi = Unit::factory()->prodi($jurusan)->create();

        $service = Service::factory()->create();
        ServiceSigner::create([
            'service_id' => $service->id,
            'role' => 'KAPRODI',
            'order_index' => 1,
            'is_required' => true,
            'requires_signature_upload' => false,
            'signature_file_types' => null,
            'signature_max_size_kb' => null,
        ]);
        ServiceSigner::create([
            'service_id' => $service->id,
            'role' => 'CUSTOM',
            'custom_label' => 'Ketua Organisasi',
            'order_index' => 2,
            'is_required' => true,
            'requires_signature_upload' => false,
            'signature_file_types' => null,
            'signature_max_size_kb' => null,
        ]);

        $student = User::factory()->create(['unit_id' => $prodi->id]);
        $student->assignRole('Mahasiswa');

        $superadmin = User::factory()->create(['unit_id' => null]);
        $superadmin->assignRole('Superadmin');

        $adminJurusan = User::factory()->create(['unit_id' => $jurusan->id]);
        $adminJurusan->assignRole('ADMIN_JURUSAN_NOTIFY');

        $stafUlt = User::factory()->create(['unit_id' => $fakultas->id]);
        $stafUlt->assignRole('STAF_ULT_NOTIFY');

        $kaprodiSigner = User::factory()->create(['unit_id' => $prodi->id]);
        $kaprodiSigner->assignRole('KAPRODI');

        $customSigner = User::factory()->create(['unit_id' => null]);
        $unrelated = User::factory()->create(['unit_id' => $prodi->id]);

        $req = UltRequest::factory()->create([
            'service_id' => $service->id,
            'student_id' => $student->id,
            'current_status' => RequestStatus::DIAJUKAN,
            'current_unit_id' => $prodi->id,
        ]);

        RequestSignoff::create([
            'request_id' => $req->id,
            'signer_role' => 'KAPRODI',
            'order_index' => 1,
            'is_required' => true,
            'status' => RequestSignoffStatus::PENDING,
        ]);
        RequestSignoff::create([
            'request_id' => $req->id,
            'signer_role' => 'CUSTOM',
            'signer_user_id' => $customSigner->id,
            'order_index' => 2,
            'is_required' => true,
            'status' => RequestSignoffStatus::PENDING,
        ]);

        app(RequestWorkflowService::class)->transition(
            $req,
            RequestStatus::DIVERIFIKASI_UNIT,
            $superadmin,
            'Uji notifikasi',
            'prodi_verify',
            $prodi
        );

        Notification::assertSentTo($student, RequestStatusChanged::class);
        Notification::assertSentTo($superadmin, RequestStatusChanged::class);
        Notification::assertSentTo($adminJurusan, RequestStatusChanged::class);
        Notification::assertSentTo($stafUlt, RequestStatusChanged::class);
        Notification::assertSentTo($kaprodiSigner, RequestStatusChanged::class);
        Notification::assertSentTo($customSigner, RequestStatusChanged::class);
        Notification::assertNotSentTo($unrelated, RequestStatusChanged::class);
    }

    public function test_admin_jurusan_perprodi_still_gets_notification_when_request_moves_to_faculty_scope(): void
    {
        Notification::fake();
        $this->seedPermissions();

        $this->makeRole('Mahasiswa', ['requests.view_own']);
        $this->makeRole('Superadmin', ['requests.view_any']);
        $this->makeRole('ADMIN_JURUSAN_NOTIFY', ['requests.view_unit', 'requests.process_unit']);

        $fakultas = Unit::factory()->create();
        $jurusan = Unit::factory()->jurusan($fakultas)->create();
        $prodi = Unit::factory()->prodi($jurusan)->create();

        $service = Service::factory()->create();
        $student = User::factory()->create(['unit_id' => $prodi->id]);
        $student->assignRole('Mahasiswa');

        $superadmin = User::factory()->create(['unit_id' => null]);
        $superadmin->assignRole('Superadmin');

        $adminPerProdi = User::factory()->create(['unit_id' => $jurusan->id]);
        $adminPerProdi->assignRole('ADMIN_JURUSAN_NOTIFY');
        $adminPerProdi->unitScopes()->attach($prodi->id);

        $req = UltRequest::factory()->create([
            'service_id' => $service->id,
            'student_id' => $student->id,
            'current_status' => RequestStatus::REVIEW_ULT,
            'current_unit_id' => $fakultas->id,
        ]);

        $this->assertFalse(Gate::forUser($adminPerProdi)->allows('view', $req));

        app(RequestWorkflowService::class)->transition(
            $req,
            RequestStatus::MENUNGGU_TTD_FAKULTAS,
            $superadmin,
            'Uji notif per-prodi',
            'faculty_sign',
            $fakultas
        );

        Notification::assertSentTo($adminPerProdi, RequestStatusChanged::class);
    }
}
