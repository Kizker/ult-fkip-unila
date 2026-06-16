<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AuthenticatedSessionController extends Controller
{
    private function bootstrapMissingRoleIfNeeded(Request $request, ?User $user): void
    {
        if (!$user) return;
        if ($user->roles()->count() > 0) return;

        $email = strtolower(trim((string) $user->email));
        if ($email !== '' && str_contains($email, 'superadmin')) {
            Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
            $user->assignRole('Superadmin');
        } else {
            // Default: treat unassigned users as Mahasiswa
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

    private function dashboardRouteFor(?\App\Models\User $user): string
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

    public function create()
    {
        if (app()->environment('local') && User::query()->count() === 0) {
            session()->flash(
                'warning',
                'Database belum memiliki user. Jalankan: php artisan db:seed (demo password: Password!2345)'
            );
        }

        return view('auth.login', [
            // Error login (auth.failed) sudah tampil di field email; hindari banner validasi yang menyesatkan.
            'flashShowValidation' => false,
        ]);
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            $audit->log('auth.login_failed', null, null, ['email' => strtolower($credentials['email'])], $request);
            return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
        }

        $request->session()->regenerate();
        $audit->log('auth.login_success', 'users', (string) Auth::id(), [], $request);

        $user = $request->user();
        $this->bootstrapMissingRoleIfNeeded($request, $user);
        $user = $request->user()?->fresh('roles') ?? $user;
        $target = $this->dashboardRouteFor($user);
        $isPrivilegedLanding = in_array($target, [
            route('admin.dashboard'),
            route('signer.requests.inbox'),
        ], true);

        // For non-student privileged users, ignore stale intended URL (e.g. student dashboard)
        // to prevent 403 loops after role/permission changes.
        if ($user && $isPrivilegedLanding) {
            $request->session()->forget('url.intended');
            return redirect()->to($target);
        }

        return redirect()->intended($target);
    }

    public function destroy(Request $request, AuditLogger $audit): RedirectResponse
    {
        $audit->log('auth.logout', 'users', (string) $request->user()->id, [], $request);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('home'));
    }
}
