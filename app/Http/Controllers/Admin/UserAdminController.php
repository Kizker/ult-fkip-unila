<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\Uploads\UniqueUploadNamer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserAdminController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly UniqueUploadNamer $uploadNamer,
    ) {}

    private function allowsUnitScopes(string $role, ?string $jabatan): bool
    {
        $r = strtoupper(trim($role));
        if ($r === 'SUPERADMIN' || $r === 'ADMIN' || str_starts_with($r, 'ADMIN_') || str_starts_with($r, 'ADMIN ')) return true;

        $j = strtoupper(trim((string) ($jabatan ?? '')));
        return in_array($j, ['ADMIN JURUSAN', 'ADMIN JURUSAN PER PRODI'], true);
    }

    private function roleAllowsJabatan(string $role): bool
    {
        $r = strtoupper(trim($role));
        return $r !== 'MAHASISWA';
    }

    private function normalizeJabatan(?string $jabatan, ?string $jabatanOther): ?string
    {
        $j = trim((string) ($jabatan ?? ''));
        if ($j === '') return null;
        if ($j !== '__other__') return $j;

        $other = trim((string) ($jabatanOther ?? ''));
        return $other !== '' ? $other : null;
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->string('q'));
        $unitId = $request->string('unit_id')->toString();

        $items = User::query()
            ->with(['roles', 'unit'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('student_number', 'like', "%{$q}%");
                });
            })
            ->when($unitId !== '', fn ($query) => $query->where('unit_id', $unitId))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $units = Unit::query()->orderBy('name')->get();

        return view('admin.users.index', compact('items', 'q', 'unitId', 'units'));
    }

    public function create()
    {
        $roles = Role::query()
            ->whereNotIn('name', ['Approver Unit', 'Approver Fakultas', 'APPROVER_ULT'])
            ->orderBy('name')
            ->get();
        $units = Unit::query()->orderBy('name')->get();
        $fakultasUnits = Unit::query()->where('type', 'fakultas')->orderBy('name')->get();
        $jurusanUnits = Unit::query()->where('type', 'jurusan')->orderBy('name')->get();
        $prodiUnits = Unit::query()->where('type', 'prodi')->orderBy('name')->get();

        $prodiByJurusan = $prodiUnits
            ->groupBy('parent_id')
            ->map(fn ($items) => $items->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])->values()->all())
            ->all();

        return view('admin.users.create', compact('roles', 'units', 'fakultasUnits', 'jurusanUnits', 'prodiUnits', 'prodiByJurusan'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'email' => ['required', 'string', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:190'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'student_number' => ['nullable', 'string', 'max:50'],
            'jabatan' => ['nullable', 'string', 'max:190'],
            'jabatan_other' => ['nullable', 'string', 'max:190'],
            'scoped_unit_ids' => ['nullable', 'array'],
            'scoped_unit_ids.*' => [
                'integer',
                Rule::exists('units', 'id')->where('type', 'prodi'),
            ],
            'role' => ['required', 'string', 'exists:roles,name'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $role = (string) $data['role'];
        $jabatan = $this->roleAllowsJabatan($role)
            ? $this->normalizeJabatan($data['jabatan'] ?? null, $data['jabatan_other'] ?? null)
            : null;

        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => Hash::make($data['password']),
            'unit_id' => $data['unit_id'] ?? null,
            'student_number' => $data['student_number'] ?? null,
            'jabatan' => $jabatan,
            'email_verified_at' => now(),
        ]);

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $path = $this->uploadNamer->makePathForUploadedFile(
                'public',
                'avatars',
                "avatar_user_{$user->id}",
                $file,
            );
            $stream = fopen($file->getRealPath(), 'rb');
            Storage::disk('public')->put($path, $stream);
            if (is_resource($stream)) fclose($stream);
            $user->forceFill(['profile_photo_path' => $path])->save();
        }

        $user->syncRoles([$role]);

        $scopes = !empty($data['scoped_unit_ids'])
            ? array_values(array_unique(array_map('intval', $data['scoped_unit_ids'])))
            : [];
        $user->unitScopes()->sync($this->allowsUnitScopes($role, $jabatan) ? $scopes : []);

        $this->audit->log('users.created', 'users', (string) $user->id, [
            'role' => $role,
            'unit_id' => $user->unit_id,
        ]);

        return redirect()->route('admin.users.edit', $user)->with('status', __('app.saved'));
    }

    public function edit(User $user)
    {
        $user->load(['roles', 'unit', 'unitScopes']);

        $roles = Role::query()
            ->whereNotIn('name', ['Approver Unit', 'Approver Fakultas', 'APPROVER_ULT'])
            ->orderBy('name')
            ->get();
        $units = Unit::query()->orderBy('name')->get();
        $fakultasUnits = Unit::query()->where('type', 'fakultas')->orderBy('name')->get();
        $jurusanUnits = Unit::query()->where('type', 'jurusan')->orderBy('name')->get();
        $prodiUnits = Unit::query()->where('type', 'prodi')->orderBy('name')->get();

        $prodiByJurusan = $prodiUnits
            ->groupBy('parent_id')
            ->map(fn ($items) => $items->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])->values()->all())
            ->all();

        return view('admin.users.edit', compact('user', 'roles', 'units', 'fakultasUnits', 'jurusanUnits', 'prodiUnits', 'prodiByJurusan'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'email' => ['required', 'string', 'email', 'max:190', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'max:190'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'student_number' => ['nullable', 'string', 'max:50'],
            'jabatan' => ['nullable', 'string', 'max:190'],
            'jabatan_other' => ['nullable', 'string', 'max:190'],
            'scoped_unit_ids' => ['nullable', 'array'],
            'scoped_unit_ids.*' => [
                'integer',
                Rule::exists('units', 'id')->where('type', 'prodi'),
            ],
            'role' => ['required', 'string', 'exists:roles,name'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $role = (string) $data['role'];
        $jabatan = $this->roleAllowsJabatan($role)
            ? $this->normalizeJabatan($data['jabatan'] ?? null, $data['jabatan_other'] ?? null)
            : null;

        $emailChanged = $data['email'] !== $user->email;

        $user->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
            'unit_id' => $data['unit_id'] ?? null,
            'student_number' => $data['student_number'] ?? null,
            'jabatan' => $jabatan,
        ]);

        if (!empty($data['password'])) {
            $user->forceFill(['password' => Hash::make($data['password'])]);
        }

        if ($emailChanged) {
            $user->forceFill(['email_verified_at' => null]);
        }

        if ($request->hasFile('profile_photo')) {
            $this->deleteProfilePhoto($user);
            $file = $request->file('profile_photo');
            $path = $this->uploadNamer->makePathForUploadedFile(
                'public',
                'avatars',
                "avatar_user_{$user->id}",
                $file,
            );
            $stream = fopen($file->getRealPath(), 'rb');
            Storage::disk('public')->put($path, $stream);
            if (is_resource($stream)) fclose($stream);
            $user->forceFill(['profile_photo_path' => $path]);
        }

        $user->save();

        $user->syncRoles([$role]);

        $scopes = !empty($data['scoped_unit_ids'])
            ? array_values(array_unique(array_map('intval', $data['scoped_unit_ids'])))
            : [];
        $user->unitScopes()->sync($this->allowsUnitScopes($role, $jabatan) ? $scopes : []);

        $this->audit->log('users.updated', 'users', (string) $user->id, [
            'role' => $role,
            'email_changed' => $emailChanged,
            'unit_id' => $user->unit_id,
        ]);

        return back()->with('status', __('app.saved'));
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->withErrors(['delete' => 'Tidak bisa menghapus akun yang sedang login.']);
        }

        if ($user->hasRole('Superadmin')) {
            $superCount = User::query()->role('Superadmin')->count();
            if ($superCount <= 1) {
                return back()->withErrors(['delete' => 'Tidak bisa menghapus Superadmin terakhir.']);
            }
        }

        $this->deleteProfilePhoto($user);

        $id = (string) $user->id;
        $email = $user->email;
        $roles = $user->roles->pluck('name')->values()->all();

        $user->delete();

        $this->audit->log('users.deleted', 'users', $id, [
            'email' => $email,
            'roles' => $roles,
        ]);

        return redirect()->route('admin.users.index')->with('status', __('app.deleted'));
    }

    private function deleteProfilePhoto(User $user): void
    {
        $path = $user->profile_photo_path;
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
