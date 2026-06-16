<?php

namespace Tests\Feature;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentNumberFormatCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_jurusan_per_prodi_can_manage_own_unit_format_but_not_other_units(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'jurusan.prodi@demo.test')->firstOrFail();
        $otherUnit = Unit::query()->where('id', '!=', $admin->unit_id)->firstOrFail();

        $this->actingAs($admin);

        $resp = $this->post(route('admin.doc_formats.store'), [
            'unit_id' => $admin->unit_id,
            'format_key' => 'default',
            'name' => 'Prodi Format',
            'template' => '{SEQ:4}/ULT-FKIP/{UNIT_CODE}/{YYYY}',
            'seq_padding' => 4,
            'is_active' => 1,
        ]);
        $resp->assertRedirect(route('admin.doc_formats.index'));
        $this->assertDatabaseHas('document_number_formats', [
            'unit_id' => $admin->unit_id,
            'format_key' => 'default',
            'name' => 'Prodi Format',
        ]);

        $resp2 = $this->post(route('admin.doc_formats.store'), [
            'unit_id' => $otherUnit->id,
            'format_key' => 'default',
            'name' => 'Hack',
            'template' => '{SEQ}/X/{UNIT_CODE}/{YYYY}',
            'seq_padding' => 3,
            'is_active' => 1,
        ]);
        $resp2->assertStatus(403);
    }
}
