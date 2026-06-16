<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index()
    {
        $users = User::query()->with(['roles','permissions','unit'])->paginate(20);
        $roles = Role::query()->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('name')->get();

        return view('admin.users_roles.index', compact('users','roles','permissions'));
    }

    public function syncRoles(Request $request, User $user)
    {
        $data = $request->validate(['roles' => ['array']]);
        $roles = $data['roles'] ?? [];
        $user->syncRoles($roles);

        $this->audit->log('rbac.roles_changed', 'users', (string) $user->id, ['roles' => $roles]);

        return back()->with('status', __('app.saved'));
    }

    public function syncPermissions(Request $request, User $user)
    {
        $data = $request->validate(['permissions' => ['array']]);
        $perms = $data['permissions'] ?? [];
        $user->syncPermissions($perms);

        $this->audit->log('rbac.permissions_changed', 'users', (string) $user->id, ['permissions' => $perms]);

        return back()->with('status', __('app.saved'));
    }
}
