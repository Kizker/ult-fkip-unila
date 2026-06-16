<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;
    use HasRoles {
        hasPermissionTo as protected spatieHasPermissionTo;
        hasAnyPermission as protected spatieHasAnyPermission;
    }

    /**
     * Fallback role maps to keep critical portals accessible even when RBAC seed/cache is stale.
     *
     * @var array<int,string>
     */
    private const SELF_SERVICE_ROLES = [
        'Mahasiswa',
        'Dosen',
        'KAPRODI',
        'KAJUR',
        'SEKJUR',
        'SEKRETARIS_ORG',
        'KETUA_ORG',
        'Admin Jurusan',
        'Admin Jurusan per Prodi',
        'ADMIN_JURUSAN',
        'ADMIN JURUSAN',
        'ADMIN_JURUSAN_PER_PRODI',
        'ADMIN JURUSAN PER PRODI',
    ];

    /**
     * @var array<int,string>
     */
    private const SIGNER_ROLES = [
        'Dosen',
        'KAPRODI',
        'KAJUR',
        'SEKJUR',
        'SEKRETARIS_ORG',
        'KETUA_ORG',
        'DEKAN',
        'WD_AKADEMIK',
        'WD_UMUM',
        'WD_KEMAHASISWAAN',
        'PEMBIMBING_AKADEMIK',
    ];

    /**
     * Lower number means higher signer position in dropdown ordering.
     *
     * @var array<string,int>
     */
    private const SIGNER_HIERARCHY_ORDER = [
        'DEKAN' => 10,
        'WDAKADEMIK' => 20,
        'WDUMUM' => 30,
        'WDKEMAHASISWAAN' => 40,
        'KAJUR' => 50,
        'SEKJUR' => 60,
        'KAPRODI' => 70,
        'PEMBIMBINGAKADEMIK' => 80,
        'DOSEN' => 90,
    ];

    protected $fillable = [
        'name',
        'email',
        'google_id',
        'password',
        'unit_id',
        //  Mahasiswa bisa punya NPM; optional untuk MVP. Dampak: tidak wajib untuk auth/ownership.
        'student_number',
        'jabatan',
        'profile_photo_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function unitScopes(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'user_unit_scopes', 'user_id', 'unit_id')->withTimestamps();
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        $path = trim(str_replace('\\', '/', (string) $this->profile_photo_path));

        if ($path === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $path) === 1) {
            return $path;
        }

        $path = ltrim($path, '/');
        $storagePath = str_starts_with($path, 'storage/')
            ? substr($path, strlen('storage/'))
            : $path;

        if ($storagePath === '' || !Storage::disk('public')->exists($storagePath)) {
            return null;
        }

        return '/storage/'.ltrim($storagePath, '/');
    }

    /**
     * Superadmin bypass for Spatie permission middleware.
     * This ensures "Superadmin" can access admin routes even if RBAC seed/cache is stale.
     *
     * @param  mixed  $permission
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if ($this->hasRole('Superadmin')) return true;
        $permissionName = $this->extractPermissionName($permission);
        if ($permissionName !== null && $this->hasFallbackPermission($permissionName)) return true;
        return $this->spatieHasPermissionTo($permission, $guardName);
    }

    /**
     * @param  mixed  $permissions
     */
    public function hasAnyPermission($permissions, $guardName = null): bool
    {
        if ($this->hasRole('Superadmin')) return true;

        $items = is_iterable($permissions) ? $permissions : [$permissions];
        foreach ($items as $permission) {
            $permissionName = $this->extractPermissionName($permission);
            if ($permissionName !== null && $this->hasFallbackPermission($permissionName)) {
                return true;
            }
        }

        return $this->spatieHasAnyPermission($permissions, $guardName);
    }

    private function extractPermissionName(mixed $permission): ?string
    {
        if (is_string($permission)) {
            return trim($permission) !== '' ? trim($permission) : null;
        }

        if (is_object($permission) && isset($permission->name) && is_string($permission->name)) {
            $name = trim($permission->name);
            return $name !== '' ? $name : null;
        }

        return null;
    }

    private function hasFallbackPermission(string $permission): bool
    {
        $selfServiceProfile = $this->isSelfServiceProfile();
        $signerProfile = $this->isSignerProfile();

        if (in_array($permission, [
            'requests.view_own',
            'requests.create_own',
            'requests.update_own',
            'attachments.upload_own',
            'attachments.download_private',
        ], true)) {
            return $selfServiceProfile;
        }

        if ($permission === 'doc_signoffs.decide') {
            return $signerProfile;
        }

        return false;
    }

    private function isSelfServiceProfile(): bool
    {
        if ($this->hasAnyRole(self::SELF_SERVICE_ROLES)) {
            return true;
        }

        $jabatan = strtoupper(trim((string) ($this->jabatan ?? '')));
        if ($jabatan === '') {
            return false;
        }

        return in_array($jabatan, [
            'ADMIN JURUSAN',
            'ADMIN JURUSAN PER PRODI',
            'DOSEN',
            'PEMBIMBING AKADEMIK',
            'KETUA JURUSAN',
            'SEKRETARIS JURUSAN',
            'KETUA PROGRAM STUDI',
        ], true);
    }

    private function isSignerProfile(): bool
    {
        if ($this->hasAnyRole(self::SIGNER_ROLES)) {
            return true;
        }

        $jabatan = strtoupper(trim((string) ($this->jabatan ?? '')));
        if ($jabatan === '') {
            return false;
        }

        return in_array($jabatan, [
            'DOSEN',
            'PEMBIMBING AKADEMIK',
            'KETUA JURUSAN',
            'SEKRETARIS JURUSAN',
            'KETUA PROGRAM STUDI',
        ], true);
    }

    public function matchesSignerRole(string $role): bool
    {
        $normalizedTarget = $this->normalizeRoleToken($role);
        if ($normalizedTarget === '') {
            return false;
        }

        $this->loadMissing('roles');
        foreach ($this->roles as $userRole) {
            $name = is_string($userRole->name ?? null) ? $userRole->name : '';
            if ($this->normalizeRoleToken($name) === $normalizedTarget) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int,string> $roles
     */
    public function matchesAnySignerRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->matchesSignerRole((string) $role)) {
                return true;
            }
        }

        return false;
    }

    public function signerHierarchyOrder(): int
    {
        $this->loadMissing('roles');

        $best = 9999;

        foreach ($this->roles as $userRole) {
            $best = min($best, $this->resolveSignerHierarchyOrder((string) ($userRole->name ?? '')));
        }

        $best = min($best, $this->resolveSignerHierarchyOrder((string) ($this->jabatan ?? '')));

        return $best;
    }

    public function signerHierarchySortKey(): string
    {
        $jabatan = mb_strtolower(trim((string) ($this->jabatan ?? '')), 'UTF-8');
        $name = mb_strtolower(trim((string) ($this->name ?? '')), 'UTF-8');

        return sprintf(
            '%04d|%s|%s|%010d',
            $this->signerHierarchyOrder(),
            $jabatan,
            $name,
            (int) $this->id
        );
    }

    private function normalizeRoleToken(string $role): string
    {
        $clean = trim($role);
        if ($clean === '') {
            return '';
        }

        return strtoupper((string) preg_replace('/[^A-Z0-9]+/', '', strtoupper($clean)));
    }

    private function resolveSignerHierarchyOrder(string $value): int
    {
        $token = $this->normalizeRoleToken($value);
        if ($token === '') {
            return 9999;
        }

        if (str_contains($token, 'WAKILDEKAN') || str_starts_with($token, 'WD')) {
            if (str_contains($token, 'AKADEMIK')) {
                return self::SIGNER_HIERARCHY_ORDER['WDAKADEMIK'];
            }

            if (str_contains($token, 'UMUM')) {
                return self::SIGNER_HIERARCHY_ORDER['WDUMUM'];
            }

            if (str_contains($token, 'KEMAHASISWAAN')) {
                return self::SIGNER_HIERARCHY_ORDER['WDKEMAHASISWAAN'];
            }
        }

        if (str_contains($token, 'DEKAN')) {
            return self::SIGNER_HIERARCHY_ORDER['DEKAN'];
        }

        if (str_contains($token, 'KETUAJURUSAN') || str_contains($token, 'KAJUR')) {
            return self::SIGNER_HIERARCHY_ORDER['KAJUR'];
        }

        if (str_contains($token, 'SEKRETARISJURUSAN') || str_contains($token, 'SEKJUR')) {
            return self::SIGNER_HIERARCHY_ORDER['SEKJUR'];
        }

        if (str_contains($token, 'KETUAPROGRAMSTUDI') || str_contains($token, 'KAPRODI')) {
            return self::SIGNER_HIERARCHY_ORDER['KAPRODI'];
        }

        if (str_contains($token, 'PEMBIMBINGAKADEMIK')) {
            return self::SIGNER_HIERARCHY_ORDER['PEMBIMBINGAKADEMIK'];
        }

        if (str_contains($token, 'DOSEN')) {
            return self::SIGNER_HIERARCHY_ORDER['DOSEN'];
        }

        return 9999;
    }
}
