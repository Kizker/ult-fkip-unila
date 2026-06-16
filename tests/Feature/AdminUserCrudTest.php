<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminUserCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_create_user_assign_role_and_upload_photo(): void
    {
        $this->seed();

        Storage::fake('public');

        $super = User::query()->where('email', 'superadmin@demo.test')->firstOrFail();
        $this->actingAs($super);

        $resp = $this->post(route('admin.users.store'), [
            'name' => 'User Baru',
            'email' => 'user.baru@demo.test',
            'password' => 'Password!2345',
            'role' => 'Mahasiswa',
            'profile_photo' => UploadedFile::fake()->image('avatar.jpg', 200, 200),
        ]);

        $created = User::query()->where('email', 'user.baru@demo.test')->firstOrFail();

        $resp->assertRedirect(route('admin.users.edit', $created));
        $this->assertTrue($created->hasRole('Mahasiswa'));
        $this->assertNotNull($created->profile_photo_path);
        Storage::disk('public')->assertExists($created->profile_photo_path);
    }

    public function test_non_superadmin_cannot_access_users_crud(): void
    {
        $this->seed();

        $adminJurusan = User::query()->where('email', 'jurusan@demo.test')->firstOrFail();
        $this->actingAs($adminJurusan);

        $this->get(route('admin.users.index'))->assertStatus(403);
        $this->get(route('admin.users.create'))->assertStatus(403);
    }

    public function test_user_form_shows_sekjur_role_option(): void
    {
        $this->seed();

        $super = User::query()->where('email', 'superadmin@demo.test')->firstOrFail();
        $this->actingAs($super);

        $this->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('value="SEKJUR"', false);
    }
}
