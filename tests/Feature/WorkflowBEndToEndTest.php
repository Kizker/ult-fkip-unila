<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Models\Request as UltRequest;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkflowBEndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_workflow_b_end_to_end_issues_number_and_completes(): void
    {
        Storage::fake('local');
        $this->seed();

        $service = Service::where('slug', 'surat-keterangan-aktif-kuliah')->firstOrFail();
        $mhs = User::where('email', 'mahasiswa@demo.test')->firstOrFail();
        $adminJurusan = User::where('email', 'jurusan.prodi@demo.test')->firstOrFail();
        $ult = User::where('email', 'ult@demo.test')->firstOrFail();
        $dekan = User::where('email', 'dekan@demo.test')->firstOrFail();

        // 1) Mahasiswa submit
        $resp = $this->actingAs($mhs)->post(route('requests.store'), [
            'service_id' => $service->id,
            'field' => [
                'nim' => '2215060000',
                'keperluan' => 'Pengajuan beasiswa',
            ],
            'file' => [
                'ktm' => UploadedFile::fake()->create('ktm.pdf', 80, 'application/pdf'),
            ],
        ]);
        $resp->assertRedirect();

        $req = UltRequest::where('service_id', $service->id)->where('student_id', $mhs->id)->latest('id')->firstOrFail();
        $this->assertEquals(RequestStatus::DIAJUKAN->value, $req->current_status->value ?? $req->current_status);
        $this->assertEquals('prodi_verify', $req->current_step_key);

        // 2) Admin Jurusan (per prodi) verify -> REVIEW_ULT
        $this->actingAs($adminJurusan)
            ->post(route('admin.requests.action', $req), ['action' => 'verify', 'note' => 'OK prodi'])
            ->assertRedirect();
        $req->refresh();
        $this->assertEquals(RequestStatus::REVIEW_ULT->value, $req->current_status->value ?? $req->current_status);
        $this->assertEquals('ult_review', $req->current_step_key);

        // 3) ULT review -> MENUNGGU_TTD_FAKULTAS
        $this->actingAs($ult)
            ->post(route('admin.requests.action', $req), ['action' => 'review', 'note' => 'OK ult'])
            ->assertRedirect();
        $req->refresh();
        $this->assertEquals(RequestStatus::MENUNGGU_TTD_FAKULTAS->value, $req->current_status->value ?? $req->current_status);
        $this->assertEquals('faculty_sign', $req->current_step_key);

        // 4) Admin Fakultas sign -> ult_issue
        $this->actingAs($dekan)
            ->post(route('admin.requests.action', $req), ['action' => 'sign', 'note' => 'Disetujui'])
            ->assertRedirect();
        $req->refresh();
        $this->assertEquals('ult_issue', $req->current_step_key);

        // 5) ULT issue number -> output
        $this->actingAs($ult)
            ->post(route('admin.requests.action', $req), ['action' => 'issue_number', 'note' => 'Terbit'])
            ->assertRedirect();
        $req->refresh();
        $this->assertNotNull($req->documentNumber);
        $this->assertEquals('output', $req->current_step_key);

        // 6) ULT complete -> done
        $this->actingAs($ult)
            ->post(route('admin.requests.action', $req), ['action' => 'complete', 'note' => 'Selesai'])
            ->assertRedirect();
        $req->refresh();
        $this->assertEquals(RequestStatus::SELESAI->value, $req->current_status->value ?? $req->current_status);
    }
}
