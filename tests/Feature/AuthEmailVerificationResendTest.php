<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\VerificationEmailDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthEmailVerificationResendTest extends TestCase
{
    use RefreshDatabase;

    public function test_resend_verification_link_successfully_sets_status_flash(): void
    {
        $this->seed();

        $user = User::factory()->unverified()->create();
        $user->assignRole('Mahasiswa');
        $this->actingAs($user);

        $this->app->instance(VerificationEmailDispatcher::class, new class extends VerificationEmailDispatcher {
            public function send(User $user): array
            {
                return [
                    'sent' => true,
                    'error' => null,
                    'raw_error' => null,
                ];
            }
        });

        $resp = $this->post(route('verification.send'));
        $resp->assertRedirect();
        $resp->assertSessionHas('status', __('verification-link-sent'));
    }

    public function test_resend_verification_link_failure_sets_error_flash_instead_of_crashing(): void
    {
        $this->seed();

        $user = User::factory()->unverified()->create();
        $user->assignRole('Mahasiswa');
        $this->actingAs($user);

        $this->app->instance(VerificationEmailDispatcher::class, new class extends VerificationEmailDispatcher {
            public function send(User $user): array
            {
                return [
                    'sent' => false,
                    'error' => 'Email verifikasi gagal dikirim: SMTP membutuhkan autentikasi.',
                    'raw_error' => '530 5.7.1 Authentication required',
                ];
            }
        });

        $resp = $this->post(route('verification.send'));
        $resp->assertRedirect();
        $resp->assertSessionHas('error');
    }
}

