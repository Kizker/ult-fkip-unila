<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Enums\ServiceTemplateType;
use App\Models\LetterNumber;
use App\Models\LetterNumberFormat;
use App\Models\Request as UltRequest;
use App\Models\Service;
use App\Models\ServiceTemplate;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesRolesPermissions;
use Tests\TestCase;

class LetterNumberTemplateIssueTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRolesPermissions;

    public function test_admin_jurusan_can_generate_nomor_surat_from_template_and_sequence_increments(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-02-05 09:00:00'));

        $this->seedPermissions();
        $this->makeRole('Admin Jurusan', ['requests.view_unit', 'requests.process_unit', 'doc_requests.gate', 'letter_numbers.manage_formats']);

        $fak = Unit::factory()->create(['code' => 'FKIP']);
        $jur = Unit::factory()->jurusan($fak)->create(['code' => 'JUR']);
        $prodi = Unit::factory()->prodi($jur)->create(['code' => 'PRO']);

        $admin = User::factory()->create(['unit_id' => $jur->id]);
        $admin->assignRole('Admin Jurusan');

        $student = User::factory()->create(['unit_id' => $prodi->id]);

        $service = Service::factory()->create();
        ServiceTemplate::create([
            'service_id' => $service->id,
            'type' => ServiceTemplateType::MAIN_DOCX,
            'file_path' => 'services/'.$service->id.'/templates/dummy.docx',
            'original_filename' => 'dummy.docx',
            'uploaded_by' => $admin->id,
            'created_at' => now(),
        ]);

        $format = LetterNumberFormat::create([
            'unit_id' => $jur->id,
            'format_key' => 'sk',
            'name' => 'Surat Keterangan',
            'template' => '{SEQ:3}/SK/{UNIT_CODE}/{YYYY}',
            'seq_padding' => 3,
            'is_active' => true,
        ]);

        $r1 = UltRequest::factory()->create([
            'service_id' => $service->id,
            'student_id' => $student->id,
            'current_status' => RequestStatus::DIAJUKAN,
            'current_unit_id' => $prodi->id,
            'nomor_surat' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.doc_requests.gate.nomor_surat', $r1), [
                'letter_format_id' => $format->id,
            ])
            ->assertRedirect();

        $r1->refresh();
        $this->assertSame('001/SK/JUR/2026', $r1->nomor_surat);
        $this->assertDatabaseHas('letter_numbers', [
            'request_id' => $r1->id,
            'format_id' => $format->id,
            'year' => 2026,
            'number_seq' => 1,
            'number_text' => '001/SK/JUR/2026',
        ]);

        $r2 = UltRequest::factory()->create([
            'service_id' => $service->id,
            'student_id' => $student->id,
            'current_status' => RequestStatus::DIAJUKAN,
            'current_unit_id' => $prodi->id,
            'nomor_surat' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.doc_requests.gate.nomor_surat', $r2), [
                'letter_format_id' => $format->id,
            ])
            ->assertRedirect();

        $r2->refresh();
        $this->assertSame('002/SK/JUR/2026', $r2->nomor_surat);

        $r3 = UltRequest::factory()->create([
            'service_id' => $service->id,
            'student_id' => $student->id,
            'current_status' => RequestStatus::DIAJUKAN,
            'current_unit_id' => $prodi->id,
            'nomor_surat' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.doc_requests.gate.nomor_surat', $r3), [
                'letter_format_id' => $format->id,
                'seq_override' => 10,
            ])
            ->assertRedirect();

        $r3->refresh();
        $this->assertSame('010/SK/JUR/2026', $r3->nomor_surat);

        $r4 = UltRequest::factory()->create([
            'service_id' => $service->id,
            'student_id' => $student->id,
            'current_status' => RequestStatus::DIAJUKAN,
            'current_unit_id' => $prodi->id,
            'nomor_surat' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.doc_requests.gate.nomor_surat', $r4), [
                'letter_format_id' => $format->id,
            ])
            ->assertRedirect();

        $r4->refresh();
        $this->assertSame('011/SK/JUR/2026', $r4->nomor_surat);

        $latest = LetterNumber::query()->where('request_id', $r4->id)->first();
        $this->assertSame(11, (int) $latest?->number_seq);
        $this->assertFalse((bool) $latest?->is_manual_override);
    }

    public function test_admin_jurusan_can_generate_nomor_surat_from_faculty_template_for_prodi_request(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-10 10:00:00'));

        $this->seedPermissions();
        $this->makeRole('Admin Jurusan', ['requests.view_unit', 'requests.process_unit', 'doc_requests.gate', 'letter_numbers.manage_formats']);

        $fak = Unit::factory()->create(['code' => 'FKIP']);
        $jur = Unit::factory()->jurusan($fak)->create(['code' => 'JUR-BIO']);
        $prodi = Unit::factory()->prodi($jur)->create(['code' => 'PRO-BIO']);

        $admin = User::factory()->create(['unit_id' => $jur->id]);
        $admin->assignRole('Admin Jurusan');

        $student = User::factory()->create(['unit_id' => $prodi->id]);

        $service = Service::factory()->create();
        ServiceTemplate::create([
            'service_id' => $service->id,
            'type' => ServiceTemplateType::MAIN_DOCX,
            'file_path' => 'services/'.$service->id.'/templates/dummy.docx',
            'original_filename' => 'dummy.docx',
            'uploaded_by' => $admin->id,
            'created_at' => now(),
        ]);

        $facultyFormat = LetterNumberFormat::create([
            'unit_id' => $fak->id,
            'format_key' => 'sk-fak',
            'name' => 'Surat Fakultas',
            'template' => '{SEQ:3}/SK/{UNIT_CODE}/{YYYY}',
            'seq_padding' => 3,
            'is_active' => true,
        ]);

        $requestItem = UltRequest::factory()->create([
            'service_id' => $service->id,
            'student_id' => $student->id,
            'current_status' => RequestStatus::DIAJUKAN,
            'current_unit_id' => $prodi->id,
            'nomor_surat' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.doc_requests.gate.nomor_surat', $requestItem), [
                'letter_format_id' => $facultyFormat->id,
            ])
            ->assertRedirect();

        $requestItem->refresh();
        $this->assertSame('001/SK/FKIP/2026', $requestItem->nomor_surat);
        $this->assertDatabaseHas('letter_numbers', [
            'request_id' => $requestItem->id,
            'format_id' => $facultyFormat->id,
            'unit_id' => $fak->id,
            'number_text' => '001/SK/FKIP/2026',
        ]);
    }

    public function test_request_show_lists_jurusan_and_fakultas_templates_for_prodi_request(): void
    {
        $this->seedPermissions();
        $this->makeRole('Admin Jurusan', ['requests.view_unit', 'requests.process_unit', 'doc_requests.gate', 'letter_numbers.manage_formats']);

        $fak = Unit::factory()->create(['code' => 'FKIP']);
        $jur = Unit::factory()->jurusan($fak)->create(['code' => 'JUR-MTK']);
        $prodi = Unit::factory()->prodi($jur)->create(['code' => 'PRO-MTK']);

        $admin = User::factory()->create(['unit_id' => $jur->id]);
        $admin->assignRole('Admin Jurusan');

        $student = User::factory()->create(['unit_id' => $prodi->id]);

        $service = Service::factory()->create();
        ServiceTemplate::create([
            'service_id' => $service->id,
            'type' => ServiceTemplateType::MAIN_DOCX,
            'file_path' => 'services/'.$service->id.'/templates/dummy.docx',
            'original_filename' => 'dummy.docx',
            'uploaded_by' => $admin->id,
            'created_at' => now(),
        ]);

        LetterNumberFormat::create([
            'unit_id' => $jur->id,
            'format_key' => 'sk-jur',
            'name' => 'Surat Jurusan',
            'template' => '{SEQ:3}/JUR/{UNIT_CODE}/{YYYY}',
            'seq_padding' => 3,
            'is_active' => true,
        ]);

        LetterNumberFormat::create([
            'unit_id' => $fak->id,
            'format_key' => 'sk-fak',
            'name' => 'Surat Fakultas',
            'template' => '{SEQ:3}/FAK/{UNIT_CODE}/{YYYY}',
            'seq_padding' => 3,
            'is_active' => true,
        ]);

        $requestItem = UltRequest::factory()->create([
            'service_id' => $service->id,
            'student_id' => $student->id,
            'current_status' => RequestStatus::DIAJUKAN,
            'current_unit_id' => $prodi->id,
            'nomor_surat' => null,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.requests.show', $requestItem))
            ->assertOk()
            ->assertSee('Surat Jurusan')
            ->assertSee('Surat Fakultas');
    }

    public function test_letter_format_index_filter_prodi_includes_ancestor_templates(): void
    {
        $this->seedPermissions();
        $this->makeRole('Admin Jurusan', ['requests.view_unit', 'letter_numbers.manage_formats']);

        $fak = Unit::factory()->create(['code' => 'FKIP']);
        $jur = Unit::factory()->jurusan($fak)->create(['code' => 'JUR-MTK']);
        $prodi = Unit::factory()->prodi($jur)->create(['code' => 'PRO-MTK']);

        $admin = User::factory()->create(['unit_id' => $jur->id]);
        $admin->assignRole('Admin Jurusan');

        LetterNumberFormat::create([
            'unit_id' => $fak->id,
            'format_key' => 'sk-fak',
            'name' => 'Surat Fakultas',
            'template' => '{SEQ:3}/FAK/{UNIT_CODE}/{YYYY}',
            'seq_padding' => 3,
            'is_active' => true,
        ]);

        LetterNumberFormat::create([
            'unit_id' => $jur->id,
            'format_key' => 'sk-jur',
            'name' => 'Surat Jurusan',
            'template' => '{SEQ:3}/JUR/{UNIT_CODE}/{YYYY}',
            'seq_padding' => 3,
            'is_active' => true,
        ]);

        LetterNumberFormat::create([
            'unit_id' => $prodi->id,
            'format_key' => 'sk-pro',
            'name' => 'Surat Prodi',
            'template' => '{SEQ:3}/PRO/{UNIT_CODE}/{YYYY}',
            'seq_padding' => 3,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.letter_formats.index', ['unit_id' => $prodi->id]))
            ->assertOk()
            ->assertSee('Surat Prodi')
            ->assertSee('Surat Jurusan')
            ->assertSee('Surat Fakultas');
    }

    public function test_letter_format_index_default_all_includes_inherited_templates_for_prodi_scope_user(): void
    {
        $this->seedPermissions();
        $this->makeRole('Admin Jurusan', ['requests.view_unit', 'letter_numbers.manage_formats']);

        $fak = Unit::factory()->create(['code' => 'FKIP']);
        $jur = Unit::factory()->jurusan($fak)->create(['code' => 'JUR-MTK']);
        $prodi = Unit::factory()->prodi($jur)->create(['code' => 'PRO-MTK']);

        $admin = User::factory()->create(['unit_id' => $prodi->id]);
        $admin->assignRole('Admin Jurusan');

        LetterNumberFormat::create([
            'unit_id' => $fak->id,
            'format_key' => 'sk-fak',
            'name' => 'Surat Fakultas',
            'template' => '{SEQ:3}/FAK/{UNIT_CODE}/{YYYY}',
            'seq_padding' => 3,
            'is_active' => true,
        ]);

        LetterNumberFormat::create([
            'unit_id' => $jur->id,
            'format_key' => 'sk-jur',
            'name' => 'Surat Jurusan',
            'template' => '{SEQ:3}/JUR/{UNIT_CODE}/{YYYY}',
            'seq_padding' => 3,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.letter_formats.index'))
            ->assertOk()
            ->assertSee('Surat Jurusan')
            ->assertSee('Surat Fakultas');
    }

    public function test_prodi_scope_admin_can_open_history_for_inherited_template(): void
    {
        $this->seedPermissions();
        $this->makeRole('Admin Jurusan', ['requests.view_unit', 'letter_numbers.manage_formats']);

        $fak = Unit::factory()->create(['code' => 'FKIP']);
        $jur = Unit::factory()->jurusan($fak)->create(['code' => 'JUR-MTK']);
        $prodi = Unit::factory()->prodi($jur)->create(['code' => 'PRO-MTK']);

        $admin = User::factory()->create(['unit_id' => $prodi->id]);
        $admin->assignRole('Admin Jurusan');

        $inheritedFormat = LetterNumberFormat::create([
            'unit_id' => $jur->id,
            'format_key' => 'sk-jur',
            'name' => 'Surat Jurusan',
            'template' => '{SEQ:3}/JUR/{UNIT_CODE}/{YYYY}',
            'seq_padding' => 3,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.letter_formats.show', $inheritedFormat))
            ->assertOk()
            ->assertSee('History Template')
            ->assertSee('Read-only (turunan)');
    }

    public function test_index_shows_history_action_for_inherited_template(): void
    {
        $this->seedPermissions();
        $this->makeRole('Admin Jurusan', ['requests.view_unit', 'letter_numbers.manage_formats']);

        $fak = Unit::factory()->create(['code' => 'FKIP']);
        $jur = Unit::factory()->jurusan($fak)->create(['code' => 'JUR-MTK']);
        $prodi = Unit::factory()->prodi($jur)->create(['code' => 'PRO-MTK']);

        $admin = User::factory()->create(['unit_id' => $prodi->id]);
        $admin->assignRole('Admin Jurusan');

        $inheritedFormat = LetterNumberFormat::create([
            'unit_id' => $jur->id,
            'format_key' => 'sk-jur',
            'name' => 'Surat Jurusan',
            'template' => '{SEQ:3}/JUR/{UNIT_CODE}/{YYYY}',
            'seq_padding' => 3,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.letter_formats.index'))
            ->assertOk()
            ->assertSee(route('admin.letter_formats.show', $inheritedFormat), false)
            ->assertSee('Read-only (turunan)');
    }
}
