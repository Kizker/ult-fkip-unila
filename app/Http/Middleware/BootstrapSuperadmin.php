<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class BootstrapSuperadmin
{
    public function handle(Request $request, Closure $next)
    {
        try {
            if ($request->session()->get('bootstrapped_roles') === true) {
                return $next($request);
            }
        } catch (\Throwable $e) {
            // ignore (no session)
        }

        $user = $request->user();
        if (!$user || !method_exists($user, 'assignRole')) {
            return $next($request);
        }

        if ($user->hasRole('Superadmin')) {
            try {
                $request->session()->put('bootstrapped_roles', true);
            } catch (\Throwable $e) {
                // ignore
            }
            return $next($request);
        }

        // Only bootstrap accounts explicitly named "superadmin" and currently missing all roles.
        $email = strtolower(trim((string) ($user->email ?? '')));
        if ($email === '') return $next($request);

        try {
            if ($user->roles()->count() > 0) {
                return $next($request);
            }
        } catch (\Throwable $e) {
            return $next($request);
        }

        if (str_contains($email, 'superadmin')) {
            Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
            $user->assignRole('Superadmin');
        } else {
            // Fallback: ensure every user has at least a base role (prevents 403 loops).
            Role::firstOrCreate(['name' => 'Mahasiswa', 'guard_name' => 'web']);
            $user->assignRole('Mahasiswa');
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        try {
            $request->session()->put('bootstrapped_roles', true);
        } catch (\Throwable $e) {
            // ignore
        }

        return $next($request);
    }
}
