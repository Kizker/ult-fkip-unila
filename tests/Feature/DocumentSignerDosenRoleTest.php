<?php

namespace Tests\Feature;

use App\Enums\CmsCategoryType;
use App\Models\CmsCategory;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesRolesPermissions;
use Tests\TestCase;

class DocumentSignerDosenRoleTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRolesPermissions;

    public function test_admin_service_form_shows_dosen_signer_option(): void
    {
        $this->seedPermissions();

        $admin = User::factory()->create();
        $this->makeRole('Superadmin');
        $admin->assignRole('Superadmin');

        $this->actingAs($admin)
            ->get(route('admin.layanan.create'))
            ->assertOk()
            ->assertSee('value="DOSEN"', false)
            ->assertSee('DOSEN - Dosen', false);
    }

    public function test_save_signers_persists_custom_label_for_dosen_role(): void
    {
        $this->seedPermissions();

        $admin = User::factory()->create();
        $this->makeRole('Superadmin');
        $admin->assignRole('Superadmin');

        $service = Service::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.layanan.dokumen.signers', $service), [
                'signers_json' => json_encode([
                    [
                        'role' => 'DOSEN',
                        'custom_label' => 'Pembimbing Akademik',
                        'order_index' => 1,
                        'is_required' => true,
                        'requires_signature_upload' => false,
                    ],
                ]),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('service_signers', [
            'service_id' => $service->id,
            'role' => 'DOSEN',
            'custom_label' => 'Pembimbing Akademik',
            'order_index' => 1,
        ]);
    }

    public function test_save_signers_allows_empty_custom_label_for_dosen_role(): void
    {
        $this->seedPermissions();

        $admin = User::factory()->create();
        $this->makeRole('Superadmin');
        $admin->assignRole('Superadmin');

        $service = Service::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.layanan.dokumen.signers', $service), [
                'signers_json' => json_encode([
                    [
                        'role' => 'DOSEN',
                        'order_index' => 1,
                        'is_required' => true,
                        'requires_signature_upload' => false,
                    ],
                ]),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('service_signers', [
            'service_id' => $service->id,
            'role' => 'DOSEN',
            'custom_label' => null,
            'order_index' => 1,
        ]);
    }

    public function test_save_signers_persists_custom_label_for_pemohon_role(): void
    {
        $this->seedPermissions();

        $admin = User::factory()->create();
        $this->makeRole('Superadmin');
        $admin->assignRole('Superadmin');

        $service = Service::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.layanan.dokumen.signers', $service), [
                'signers_json' => json_encode([
                    [
                        'role' => 'PEMOHON',
                        'custom_label' => 'Mahasiswa Aktif',
                        'order_index' => 1,
                        'is_required' => true,
                        'requires_signature_upload' => false,
                    ],
                ]),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('service_signers', [
            'service_id' => $service->id,
            'role' => 'PEMOHON',
            'custom_label' => 'Mahasiswa Aktif',
            'order_index' => 1,
        ]);
    }

    public function test_save_signers_allows_empty_custom_label_for_pemohon_role(): void
    {
        $this->seedPermissions();

        $admin = User::factory()->create();
        $this->makeRole('Superadmin');
        $admin->assignRole('Superadmin');

        $service = Service::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.layanan.dokumen.signers', $service), [
                'signers_json' => json_encode([
                    [
                        'role' => 'PEMOHON',
                        'order_index' => 1,
                        'is_required' => true,
                        'requires_signature_upload' => false,
                    ],
                ]),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('service_signers', [
            'service_id' => $service->id,
            'role' => 'PEMOHON',
            'custom_label' => null,
            'order_index' => 1,
        ]);
    }

    public function test_save_signers_uses_signer_labels_fallback_when_json_label_missing(): void
    {
        $this->seedPermissions();

        $admin = User::factory()->create();
        $this->makeRole('Superadmin');
        $admin->assignRole('Superadmin');

        $service = Service::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.layanan.dokumen.signers', $service), [
                'signers_json' => json_encode([
                    [
                        'role' => 'DOSEN',
                        // Simulate stale JS that does not send custom_label in signers_json.
                        'order_index' => 1,
                        'is_required' => true,
                        'requires_signature_upload' => false,
                    ],
                ]),
                'signer_labels' => ['Dosen Wali'],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('service_signers', [
            'service_id' => $service->id,
            'role' => 'DOSEN',
            'custom_label' => 'Dosen Wali',
            'order_index' => 1,
        ]);
    }

    public function test_service_create_uses_signer_labels_fallback_when_json_label_missing(): void
    {
        $this->seedPermissions();

        $admin = User::factory()->create();
        $this->makeRole('ServiceAdmin', [
            'services.manage',
            'doc_signers.manage',
        ]);
        $admin->assignRole('ServiceAdmin');

        $category = CmsCategory::query()->updateOrCreate(
            [
                'type' => CmsCategoryType::service,
                'slug' => 'akademik-dan-kerja-sama',
            ],
            [
                'name_id' => 'Akademik dan Kerja Sama',
                'name_en' => 'Academic and Partnerships',
            ],
        );

        $this->actingAs($admin)
            ->post(route('admin.layanan.store'), [
                'category_id' => $category->id,
                'title_id' => 'Layanan Uji Label',
                'title_en' => 'Label Test Service',
                'summary_id' => 'Ringkas',
                'summary_en' => 'Summary',
                'signers_json' => json_encode([
                    [
                        'role' => 'PEMOHON',
                        // Simulate stale JS that does not send custom_label in signers_json.
                        'order_index' => 1,
                        'is_required' => true,
                        'requires_signature_upload' => false,
                    ],
                ]),
                'signer_labels' => ['Pemohon Aktif'],
            ])
            ->assertRedirect();

        $service = Service::query()->where('title_id', 'Layanan Uji Label')->firstOrFail();

        $this->assertDatabaseHas('service_signers', [
            'service_id' => $service->id,
            'role' => 'PEMOHON',
            'custom_label' => 'Pemohon Aktif',
            'order_index' => 1,
        ]);
    }
}
