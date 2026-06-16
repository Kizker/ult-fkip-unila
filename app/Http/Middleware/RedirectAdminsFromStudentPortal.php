<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectAdminsFromStudentPortal
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        // Allow dual-role/admin users to keep using personal request portal
        // when they explicitly have own-request permissions.
        if ($user && $user->can('admin-access') && !$user->can('requests.create_own')) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
