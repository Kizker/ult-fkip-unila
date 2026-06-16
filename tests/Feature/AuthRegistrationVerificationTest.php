<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthRegistrationVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_logs_in_and_sends_verification_email(): void
    {
        $this->seed();

        Notification::fake();

        $resp = $this->post(route('register'), [
            'name' => 'Mahasiswa Baru',
            'email' => 'mahasiswa.baru@demo.test',
            'password' => 'Password!2345',
            'password_confirmation' => 'Password!2345',
        ]);

        $resp->assertRedirect(route('verification.notice'));
        $this->assertAuthenticated();

        $user = User::query()->where('email', 'mahasiswa.baru@demo.test')->firstOrFail();
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_unverified_user_can_open_verification_notice(): void
    {
        $this->seed();

        $user = User::factory()->unverified()->create();
        $this->actingAs($user);

        $this->get(route('verification.notice'))->assertOk();
    }

    public function test_unverified_user_is_redirected_to_notice_from_verified_routes(): void
    {
        $this->seed();

        $user = User::factory()->unverified()->create();
        $user->assignRole('Mahasiswa');

        $this->actingAs($user);

        $this->get(route('student.dashboard'))->assertRedirect(route('verification.notice'));
    }
}

