<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role;

class UserGuide extends Model
{
    public const CONTENT_TYPE_PDF = 'pdf';
    public const CONTENT_TYPE_VIDEO = 'video';

    protected $fillable = [
        'slug',
        'title_id',
        'title_en',
        'summary_id',
        'summary_en',
        'content_type',
        'original_name',
        'stored_path',
        'mime',
        'size',
        'video_url',
        'is_public',
        'is_published',
        'published_at',
        'uploaded_by',
    ];

    protected $casts = [
        'size' => 'integer',
        'is_public' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function isPdf(): bool
    {
        return ($this->content_type ?: self::CONTENT_TYPE_PDF) === self::CONTENT_TYPE_PDF;
    }

    public function isVideo(): bool
    {
        return $this->content_type === self::CONTENT_TYPE_VIDEO;
    }

    public function videoProvider(): ?string
    {
        if (!$this->isVideo()) {
            return null;
        }

        return $this->youtubeVideoId() ? 'youtube' : null;
    }

    public function videoEmbedUrl(): ?string
    {
        $videoId = $this->youtubeVideoId();

        return $videoId ? 'https://www.youtube.com/embed/' . $videoId : null;
    }

    public function videoWatchUrl(): ?string
    {
        $videoId = $this->youtubeVideoId();
        if ($videoId) {
            return 'https://www.youtube.com/watch?v=' . $videoId;
        }

        return filled($this->video_url) ? (string) $this->video_url : null;
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_guide_role', 'user_guide_id', 'role_id')
            ->withTimestamps();
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        $query->where('is_published', true);

        if ($user?->hasRole('Superadmin')) {
            return $query;
        }

        if (!$user) {
            return $query->where('is_public', true);
        }

        $roleIds = $user->roles()->pluck('roles.id')->map(static fn ($id): int => (int) $id)->all();

        return $query->where(function (Builder $visibility) use ($roleIds) {
            $visibility->where('is_public', true);

            if (!empty($roleIds)) {
                $visibility->orWhereHas('roles', function (Builder $roleQuery) use ($roleIds) {
                    $roleQuery->whereIn('roles.id', $roleIds);
                });
            }
        });
    }

    public function canBeViewedBy(?User $user): bool
    {
        if ($user?->hasRole('Superadmin')) {
            return true;
        }

        if (!$this->is_published) {
            return false;
        }

        if ($this->is_public) {
            return true;
        }

        if (!$user) {
            return false;
        }

        $user->loadMissing('roles:id,name');
        $this->loadMissing('roles:id,name');

        $userRoleIds = $user->roles->pluck('id')->map(static fn ($id): int => (int) $id)->all();
        if (empty($userRoleIds)) {
            return false;
        }

        return $this->roles
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->intersect($userRoleIds)
            ->isNotEmpty();
    }

    private function youtubeVideoId(): ?string
    {
        $url = trim((string) $this->video_url);
        if ($url === '') {
            return null;
        }

        $parts = parse_url($url);
        if (!is_array($parts)) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = trim((string) ($parts['path'] ?? ''), '/');

        if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            return $this->sanitizeYoutubeId($path);
        }

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            if ($path === 'watch') {
                parse_str((string) ($parts['query'] ?? ''), $query);

                return $this->sanitizeYoutubeId($query['v'] ?? null);
            }

            if (str_starts_with($path, 'embed/')) {
                return $this->sanitizeYoutubeId(substr($path, 6));
            }

            if (str_starts_with($path, 'shorts/')) {
                return $this->sanitizeYoutubeId(substr($path, 7));
            }
        }

        return null;
    }

    private function sanitizeYoutubeId(mixed $value): ?string
    {
        $id = trim((string) $value);

        if ($id === '' || !preg_match('/^[A-Za-z0-9_-]{11}$/', $id)) {
            return null;
        }

        return $id;
    }
}
