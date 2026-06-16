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

class WorkflowCEndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_workflow_c_end_to_end_until_legalization_then_complete(): void
    {
        Storage::fake('local');
        $this->seed();

        $service = Service::where('slug', 'legalisir-surat-aktif')->firstOrFail();
        $mhs = User::where('email', 'mahasiswa@demo.test')->firstOrFail();
        $jur = User::where('email', 'jurusan@demo.test')->firstOrFail();
        $ult = User::where('email', 'ult@demo.test')->firstOrFail();
        $dekan = User::where('email', 'dekan@demo.test')->firstOrFail();
        $legal = User::where('email', 'legalisir@demo.test')->firstOrFail();

        // 1) Student submits
        $payload = [
            'service_id' => $service->id,
            'field' => [
                // nim not required here, but provide anyway
                'nim' => '2215060000',
                'keperluan' => 'Legalisir untuk beasiswa',
            ],
            'file' => [
                'ktm_scan' => UploadedFile::fake()->create('ktm.pdf', 120, 'application/pdf'),
            ],
        ];

        $this->actingAs($mhs)
            ->post(route('requests.store'), $payload)
            ->assertRedirect();

        $req = UltRequest::where('service_id', $service->id)->latest('id')->firstOrFail();
        $this->assertEquals(RequestStatus::DIAJUKAN->value, $req->current_status->value ?? $req->current_status);
        $this->assertEquals('jurusan_verify', $req->current_step_key);

        // 2) Jurusan verifies -> ULT review
        $this->actingAs($jur)
            ->post(route('admin.requests.action', $req), ['action' => 'verify', 'note' => 'OK jurusan'])
            ->assertRedirect();
        $req->refresh();
        $this->assertEquals('ult_review', $req->current_step_key);
        $this->assertEquals(RequestStatus::REVIEW_ULT->value, $req->current_status->value ?? $req->current_status);

        // 3) ULT review -> faculty sign
        $this->actingAs($ult)
            ->post(route('admin.requests.action', $req), ['action' => 'review', 'note' => 'OK ULT'])
            ->assertRedirect();
        $req->refresh();
        $this->assertEquals('faculty_sign', $req->current_step_key);
        $this->assertEquals(RequestStatus::MENUNGGU_TTD_FAKULTAS->value, $req->current_status->value ?? $req->current_status);

        // 4) Faculty signs -> ult issue
        $this->actingAs($dekan)
            ->post(route('admin.requests.action', $req), ['action' => 'sign', 'note' => 'TTD'])
            ->assertRedirect();
        $req->refresh();
        $this->assertEquals('ult_issue', $req->current_step_key);

        // 5) ULT issues number -> legalization
        $this->actingAs($ult)
            ->post(route('admin.requests.action', $req), ['action' => 'issue_number', 'note' => 'Terbit'])
            ->assertRedirect();
        $req->refresh();
        $this->assertNotNull($req->documentNumber);
        $this->assertEquals('legalization', $req->current_step_key);
        $this->assertEquals(RequestStatus::MENUNGGU_LEGALISIR->value, $req->current_status->value ?? $req->current_status);

        // 6) Legalisir staff completes legalization step -> output
        $this->actingAs($legal)
            ->post(route('admin.requests.action', $req), ['action' => 'review', 'note' => 'Legalisir selesai'])
            ->assertRedirect();
        $req->refresh();
        $this->assertEquals('output', $req->current_step_key);

        // 7) ULT completes
        $this->actingAs($ult)
            ->post(route('admin.requests.action', $req), ['action' => 'complete', 'note' => 'Selesai'])
            ->assertRedirect();
        $req->refresh();
        $this->assertEquals(RequestStatus::SELESAI->value, $req->current_status->value ?? $req->current_status);
    }
}
