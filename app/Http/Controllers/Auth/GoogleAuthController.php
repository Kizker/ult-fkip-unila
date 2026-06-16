<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class GoogleAuthController extends Controller
{
    private function googleDriver(bool $forceStateless = false)
    {
        $driver = Socialite::driver('google');
        if ($forceStateless || config('services.google.stateless')) {
            $driver->stateless();
        }

        return $driver;
    }

    private function resolveGoogleUser()
    {
        try {
            return $this->googleDriver()->user();
        } catch (InvalidStateException $e) {
            // Common on VPS/reverse proxy when session state cookie is not preserved.
            if (!config('services.google.stateless')) {
                return $this->googleDriver(forceStateless: true)->user();
            }
            throw $e;
        }
    }

    private function bootstrapMissingRoleIfNeeded(Request $request, ?User $user): void
    {
        if (!$user) return;
        if ($user->roles()->count() > 0) return;

        $email = strtolower(trim((string) $user->email));
        if ($email !== '' && str_contains($email, 'superadmin')) {
            Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
            $user->assignRole('Superadmin');
        } else {
            Role::firstOrCreate(['name' => 'Mahasiswa', 'guard_name' => 'web']);
            $user->assignRole('Mahasiswa');
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        try {
            $request->session()->flash('status', 'Role dipulihkan otomatis untuk akun ini.');
        } catch (\Throwable $e) {
            // ignore
        }
    }

    private function dashboardRouteFor(?User $user): string
    {
        if (!$user) return route('home');
        if ($user->hasRole('Superadmin')) return route('admin.dashboard');

        $adminPerms = [
            'requests.view_any',
            'requests.view_unit',
            'requests.review_ult',
            'requests.process_unit',
            'approvals.unit.sign',
            'approvals.faculty.sign',
            'document_numbers.issue',
            'services.manage',
            'cms.manage',
            'site_settings.manage',
            'academics.manage',
            'users.manage',
            'audit_logs.view',
            'doc_services.manage',
            'doc_services.publish',
            'doc_templates.upload',
            'doc_placeholders.manage',
            'doc_signers.manage',
            'feedbacks.manage',
            'doc_requests.gate',
            'doc_requests.assemble',
        ];

        if ($user->can('requests.view_own')) return route('student.dashboard');
        if ($user->canAny($adminPerms)) return route('admin.dashboard');
        if ($user->can('doc_signoffs.decide')) return route('signer.requests.inbox');

        return route('home');
    }

    public function redirect(): RedirectResponse
    {
        if (!config('services.google.client_id') || !config('services.google.client_secret')) {
            return redirect()
                ->route('login')
                ->with('error', 'Login Google belum dikonfigurasi. Hubungi admin.');
        }

        return $this->googleDriver()->redirect();
    }

    public function callback(Request $request, AuditLogger $audit): RedirectResponse
    {
        try {
            $googleUser = $this->resolveGoogleUser();
        } catch (\Throwable $e) {
            $audit->log('auth.oauth_error_google', 'users', null, ['error' => $e->getMessage()], $request);

            return redirect()
                ->route('login')
                ->with('error', 'Login Google gagal. Silakan coba lagi.');
        }

        $email = strtolower((string) $googleUser->getEmail());
        if ($email === '') {
            return redirect()
                ->route('login')
                ->with('error', 'Google tidak mengembalikan email. Pastikan izin email aktif.');
        }

        $raw = $googleUser->user ?? [];
        $emailVerified = (bool) ($raw['email_verified'] ?? $raw['verified_email'] ?? false);
        if (!$emailVerified) {
            return redirect()
                ->route('login')
                ->with('error', 'Email Google belum terverifikasi.');
        }

        $googleId = (string) $googleUser->getId();
        $user = User::query()
            ->where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();
        try {
            if (!$user) {
                $audit->log('auth.oauth_register_blocked_google', 'users', null, [
                    'email' => $email,
                    'google_id' => $googleId,
                ], $request);

                return redirect()
                    ->route('register')
                    ->with('error', 'Pendaftaran akun baru harus dilakukan manual. Silakan daftar dan unggah foto profil terlebih dahulu.');
            } else {
                $changes = [];

                if (!$user->google_id) {
                    $changes['google_id'] = $googleId;
                }

                if (!$user->email_verified_at && $emailVerified) {
                    $changes['email_verified_at'] = now();
                }

                if ($changes) {
                    $user->forceFill($changes)->save();
                    $audit->log('auth.oauth_link_google', 'users', (string) $user->id, $changes, $request, $user);
                } else {
                    $audit->log('auth.oauth_login_google', 'users', (string) $user->id, [], $request, $user);
                }
            }
        } catch (\Throwable $e) {
            $audit->log('auth.oauth_sync_error_google', 'users', $user?->id ? (string) $user->id : null, [
                'error' => $e->getMessage(),
            ], $request, $user);

            return redirect()
                ->route('login')
                ->with('error', 'Akun Google terverifikasi, tetapi sinkronisasi akun gagal. Silakan hubungi admin.');
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        $this->bootstrapMissingRoleIfNeeded($request, $user);
        $user = $request->user()?->fresh('roles') ?? $user;
        $target = $this->dashboardRouteFor($user);
        $isPrivilegedLanding = in_array($target, [
            route('admin.dashboard'),
            route('signer.requests.inbox'),
        ], true);

        if ($isPrivilegedLanding) {
            $request->session()->forget('url.intended');
            return redirect()->to($target);
        }

        return redirect()->intended($target);
    }
}
