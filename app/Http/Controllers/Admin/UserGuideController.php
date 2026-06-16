<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserGuide;
use App\Services\AuditLogger;
use App\Services\Uploads\UniqueUploadNamer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserGuideController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly UniqueUploadNamer $uploadNamer,
    ) {
        $this->middleware('role:Superadmin');
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $items = UserGuide::query()
            ->with(['roles:id,name', 'uploader:id,name'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($where) use ($q) {
                    $where->where('title_id', 'like', "%{$q}%")
                        ->orWhere('title_en', 'like', "%{$q}%")
                        ->orWhere('slug', 'like', "%{$q}%")
                        ->orWhere('original_name', 'like', "%{$q}%")
                        ->orWhere('video_url', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.user_guides.index', compact('items', 'q'));
    }

    public function create()
    {
        $roles = Role::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.user_guides.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateGuide($request);

        $roleIds = $this->validatedRoleIds($data['allowed_roles'] ?? []);
        $isPublic = (bool) ($data['is_public'] ?? false);
        $contentType = (string) ($data['content_type'] ?? UserGuide::CONTENT_TYPE_PDF);
        if (!$isPublic && count($roleIds) < 1) {
            return back()
                ->withErrors(['allowed_roles' => 'Pilih minimal 1 role untuk panduan yang tidak publik.'])
                ->withInput();
        }

        $path = '';
        $originalName = '';
        $mime = 'text/html';
        $size = 0;
        $videoUrl = null;

        if ($contentType === UserGuide::CONTENT_TYPE_PDF) {
            /** @var UploadedFile $file */
            $file = $data['pdf'];
            $path = $this->storePdf($file);
            $originalName = (string) $file->getClientOriginalName();
            $mime = (string) ($file->getMimeType() ?: 'application/pdf');
            $size = (int) ($file->getSize() ?: 0);
        } else {
            $videoUrl = $this->normalizeVideoUrl((string) $data['video_url']);
        }

        $guide = UserGuide::create([
            'slug' => $this->resolveSlug((string) $data['title_id'], $data['slug'] ?? null),
            'title_id' => (string) $data['title_id'],
            'title_en' => $data['title_en'] ?? null,
            'summary_id' => $data['summary_id'] ?? null,
            'summary_en' => $data['summary_en'] ?? null,
            'content_type' => $contentType,
            'original_name' => $originalName,
            'stored_path' => $path,
            'mime' => $mime,
            'size' => $size,
            'video_url' => $videoUrl,
            'is_public' => $isPublic,
            'is_published' => (bool) ($data['is_published'] ?? false),
            'published_at' => $data['published_at'] ?? null,
            'uploaded_by' => $request->user()?->id,
        ]);

        $guide->roles()->sync($roleIds);

        $this->audit->log('user_guides.created', 'user_guides', (string) $guide->id, [
            'slug' => $guide->slug,
            'content_type' => $guide->content_type,
            'is_public' => $guide->is_public,
            'is_published' => $guide->is_published,
            'role_ids' => $roleIds,
        ], $request);

        return redirect()->route('admin.user_guides.index')->with('status', __('app.saved'));
    }

    public function edit(UserGuide $user_guide)
    {
        $user_guide->load('roles:id,name');
        $roles = Role::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.user_guides.edit', [
            'item' => $user_guide,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, UserGuide $user_guide): RedirectResponse
    {
        $data = $this->validateGuide($request, $user_guide);

        $roleIds = $this->validatedRoleIds($data['allowed_roles'] ?? []);
        $isPublic = (bool) ($data['is_public'] ?? false);
        $contentType = (string) ($data['content_type'] ?? UserGuide::CONTENT_TYPE_PDF);
        if (!$isPublic && count($roleIds) < 1) {
            return back()
                ->withErrors(['allowed_roles' => 'Pilih minimal 1 role untuk panduan yang tidak publik.'])
                ->withInput();
        }

        $oldContentType = (string) ($user_guide->content_type ?: UserGuide::CONTENT_TYPE_PDF);
        $path = (string) $user_guide->stored_path;
        $originalName = (string) $user_guide->original_name;
        $mime = (string) ($user_guide->mime ?: 'application/pdf');
        $size = (int) ($user_guide->size ?: 0);
        $videoUrl = $user_guide->video_url;
        $fileReplaced = false;

        if ($contentType === UserGuide::CONTENT_TYPE_PDF) {
            if ($oldContentType !== UserGuide::CONTENT_TYPE_PDF) {
                $path = '';
                $originalName = '';
                $mime = 'application/pdf';
                $size = 0;
                $videoUrl = null;
            }

            if ($request->hasFile('pdf')) {
                /** @var UploadedFile $file */
                $file = $request->file('pdf');
                if ($oldContentType === UserGuide::CONTENT_TYPE_PDF) {
                    $this->deletePdf($path);
                }
                $path = $this->storePdf($file);
                $originalName = (string) $file->getClientOriginalName();
                $mime = (string) ($file->getMimeType() ?: 'application/pdf');
                $size = (int) ($file->getSize() ?: 0);
                $videoUrl = null;
                $fileReplaced = true;
            }
        } else {
            if ($oldContentType === UserGuide::CONTENT_TYPE_PDF) {
                $this->deletePdf($path);
            }

            $path = '';
            $originalName = '';
            $mime = 'text/html';
            $size = 0;
            $videoUrl = $this->normalizeVideoUrl((string) $data['video_url']);
        }

        $user_guide->fill([
            'slug' => $this->resolveSlug((string) $data['title_id'], $data['slug'] ?? null, (int) $user_guide->id),
            'title_id' => (string) $data['title_id'],
            'title_en' => $data['title_en'] ?? null,
            'summary_id' => $data['summary_id'] ?? null,
            'summary_en' => $data['summary_en'] ?? null,
            'content_type' => $contentType,
            'original_name' => $originalName,
            'stored_path' => $path,
            'mime' => $mime,
            'size' => $size,
            'video_url' => $videoUrl,
            'is_public' => $isPublic,
            'is_published' => (bool) ($data['is_published'] ?? false),
            'published_at' => $data['published_at'] ?? null,
        ])->save();

        $user_guide->roles()->sync($roleIds);

        $this->audit->log('user_guides.updated', 'user_guides', (string) $user_guide->id, [
            'slug' => $user_guide->slug,
            'content_type' => $user_guide->content_type,
            'is_public' => $user_guide->is_public,
            'is_published' => $user_guide->is_published,
            'role_ids' => $roleIds,
            'file_replaced' => $fileReplaced,
        ], $request);

        return redirect()->route('admin.user_guides.index')->with('status', __('app.saved'));
    }

    public function destroy(Request $request, UserGuide $user_guide): RedirectResponse
    {
        $id = (string) $user_guide->id;
        $slug = (string) $user_guide->slug;
        if ($user_guide->isPdf()) {
            $this->deletePdf((string) $user_guide->stored_path);
        }
        $user_guide->delete();

        $this->audit->log('user_guides.deleted', 'user_guides', $id, [
            'slug' => $slug,
        ], $request);

        return redirect()->route('admin.user_guides.index')->with('status', __('app.deleted'));
    }

    private function validateGuide(Request $request, ?UserGuide $guide = null): array
    {
        $contentType = (string) $request->input('content_type', UserGuide::CONTENT_TYPE_PDF);
        $requiresPdfUpload = $contentType === UserGuide::CONTENT_TYPE_PDF
            && (!$guide || !$guide->isPdf());
        $slugRule = $guide
            ? 'unique:user_guides,slug,' . $guide->id
            : 'unique:user_guides,slug';

        return $request->validate([
            'title_id' => ['required', 'string', 'max:190'],
            'title_en' => ['nullable', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', $slugRule],
            'summary_id' => ['nullable', 'string'],
            'summary_en' => ['nullable', 'string'],
            'content_type' => ['required', 'in:' . UserGuide::CONTENT_TYPE_PDF . ',' . UserGuide::CONTENT_TYPE_VIDEO],
            'pdf' => array_filter([
                $requiresPdfUpload ? 'required' : null,
                !$requiresPdfUpload ? 'nullable' : null,
                'file',
                'mimes:pdf',
                'mimetypes:application/pdf',
                'max:' . ($this->maxUploadKilobytes()),
            ]),
            'video_url' => [
                $contentType === UserGuide::CONTENT_TYPE_VIDEO ? 'required' : 'nullable',
                'string',
                'max:2000',
                function (string $attribute, mixed $value, \Closure $fail) use ($contentType): void {
                    if ($contentType !== UserGuide::CONTENT_TYPE_VIDEO) {
                        return;
                    }

                    if (!$this->isValidYoutubeUrl((string) $value)) {
                        $fail('Gunakan tautan YouTube yang valid.');
                    }
                },
            ],
            'is_public' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'allowed_roles' => ['nullable', 'array'],
            'allowed_roles.*' => ['integer', 'exists:roles,id'],
        ]);
    }

    private function maxUploadKilobytes(): int
    {
        $defaultKb = 10 * 1024;
        $configuredMb = (int) config('ult.upload.max_size_mb', 10);
        if ($configuredMb < 1) {
            return $defaultKb;
        }

        return $configuredMb * 1024;
    }

    /**
     * @param array<int,mixed> $roleIds
     * @return array<int,int>
     */
    private function validatedRoleIds(array $roleIds): array
    {
        return collect($roleIds)
            ->map(static fn ($id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function storePdf(UploadedFile $file): string
    {
        $disk = (string) config('ult.private_disk', 'private');
        $path = $this->uploadNamer->makePathForUploadedFile(
            $disk,
            'guides/user_guides',
            'user_guide_pdf',
            $file,
            'pdf'
        );

        $stream = fopen($file->getRealPath(), 'rb');
        Storage::disk($disk)->put($path, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        return $path;
    }

    private function deletePdf(?string $path): void
    {
        if (!filled($path)) {
            return;
        }

        Storage::disk((string) config('ult.private_disk', 'private'))->delete((string) $path);
    }

    private function resolveSlug(string $title, ?string $slugInput = null, ?int $ignoreId = null): string
    {
        $base = Str::slug($slugInput ?: $title);
        if ($base === '') {
            $base = Str::random(10);
        }

        $slug = $base;
        $counter = 1;

        while (UserGuide::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function isValidYoutubeUrl(string $url): bool
    {
        return $this->extractYoutubeVideoId($url) !== null;
    }

    private function normalizeVideoUrl(string $url): string
    {
        $videoId = $this->extractYoutubeVideoId($url);

        return $videoId ? 'https://www.youtube.com/watch?v=' . $videoId : trim($url);
    }

    private function extractYoutubeVideoId(string $url): ?string
    {
        $parts = parse_url(trim($url));
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
