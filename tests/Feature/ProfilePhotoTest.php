<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_and_remove_profile_photo(): void
    {
        $this->seed();

        Storage::fake('public');

        $user = User::query()->where('email', 'mahasiswa@demo.test')->firstOrFail();
        $this->actingAs($user);

        $resp = $this->post(route('profile.update'), [
            'name' => 'Mahasiswa Demo',
            'profile_photo' => UploadedFile::fake()->image('me.webp', 300, 300),
        ]);
        $resp->assertRedirect();

        $user->refresh();
        $this->assertNotNull($user->profile_photo_path);
        Storage::disk('public')->assertExists($user->profile_photo_path);

        $oldPath = $user->profile_photo_path;
        $resp2 = $this->post(route('profile.update'), [
            'name' => 'Mahasiswa Demo',
            'remove_photo' => 1,
        ]);
        $resp2->assertRedirect();

        $user->refresh();
        $this->assertNull($user->profile_photo_path);
        Storage::disk('public')->assertMissing($oldPath);
    }
}

