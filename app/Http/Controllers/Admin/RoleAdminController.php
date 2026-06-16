<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAdminController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(Request $request)
    {
        $q = trim((string) $request->string('q'));

        $roles = Role::query()
            ->with('permissions')
            ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $assignedCounts = DB::table('model_has_roles')
            ->selectRaw('role_id, count(*) as c')
            ->groupBy('role_id')
            ->pluck('c', 'role_id')
            ->all();

        $permissions = Permission::query()->orderBy('name')->get();

        return view('admin.roles.index', compact('roles', 'permissions', 'q', 'assignedCounts'));
    }

    public function create()
    {
        $permissions = Permission::query()->orderBy('name')->get();

        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        $perms = $data['permissions'] ?? [];
        $role->syncPermissions($perms);

        $this->audit->log('rbac.role_created', 'roles', (string) $role->id, [
            'name' => $role->name,
            'permissions' => $perms,
        ]);

        return redirect()->route('admin.roles.edit', $role)->with('status', __('app.saved'));
    }

    public function edit(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::query()->orderBy('name')->get();

        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:roles,name,' . $role->id],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        if ($role->name === 'Superadmin' && $data['name'] !== 'Superadmin') {
            return back()->withErrors(['name' => 'Role Superadmin tidak boleh diubah namanya.']);
        }

        $role->update(['name' => $data['name']]);

        $perms = $data['permissions'] ?? [];
        $role->syncPermissions($perms);

        $this->audit->log('rbac.role_updated', 'roles', (string) $role->id, [
            'name' => $role->name,
            'permissions' => $perms,
        ]);

        return back()->with('status', __('app.saved'));
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'Superadmin') {
            return back()->withErrors(['delete' => 'Role Superadmin tidak boleh dihapus.']);
        }

        $role->load('permissions');

        $assignedCount = (int) DB::table('model_has_roles')
            ->where('role_id', $role->id)
            ->count();

        if ($assignedCount > 0) {
            return back()->withErrors(['delete' => "Role ini masih dipakai oleh {$assignedCount} user."]);
        }

        $id = (string) $role->id;
        $name = $role->name;
        $perms = $role->permissions->pluck('name')->values()->all();

        $role->delete();

        $this->audit->log('rbac.role_deleted', 'roles', $id, [
            'name' => $name,
            'permissions' => $perms,
        ]);

        return redirect()->route('admin.roles.index')->with('status', __('app.deleted'));
    }
}
