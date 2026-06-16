<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsAnnouncement;
use App\Services\AuditLogger;
use App\Services\HtmlSanitizer;
use App\Services\Uploads\UniqueUploadNamer;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CmsAnnouncementController extends Controller
{
    public function __construct(
        private readonly HtmlSanitizer $sanitizer,
        private readonly AuditLogger $audit,
        private readonly UniqueUploadNamer $uploadNamer,
    ) {}

    public function index(Request $request)
    {
        $q = (string) $request->query('q', '');

        $items = CmsAnnouncement::query()
            ->when($q, fn($qr) => $qr->where(function ($w) use ($q) {
                $w->where('title_id', 'like', "%{$q}%")->orWhere('title_en', 'like', "%{$q}%");
            }))
            ->orderByDesc('published_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.cms.announcements.index', compact('items', 'q'));
    }

    public function create()
    {
        return view('admin.cms.announcements.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title_id' => ['required', 'string', 'max:190'],
            'title_en' => ['nullable', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', 'unique:cms_announcements,slug'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'content_html_id' => ['nullable', 'string'],
            'content_html_en' => ['nullable', 'string'],
            'published_at' => ['nullable', 'date'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->storeCoverImage($request->file('image'));
        }

        $announcement = CmsAnnouncement::create([
            'slug' => $this->resolveSlug($data['title_id'], $data['slug'] ?? null),
            'title_id' => $data['title_id'],
            'title_en' => $data['title_en'] ?? null,
            'image_path' => $imagePath,
            'content_html_id' => $this->sanitizer->clean($data['content_html_id'] ?? ''),
            'content_html_en' => $this->sanitizer->clean($data['content_html_en'] ?? ''),
            'published_at' => $data['published_at'] ?? null,
            'is_published' => (bool) ($data['is_published'] ?? false),
        ]);

        $this->audit->log('cms.announcement_created', 'cms_announcements', (string) $announcement->id, [
            'slug' => $announcement->slug,
            'is_published' => $announcement->is_published,
        ]);

        return redirect()->route('admin.cms.announcements.index')->with('status', __('app.saved'));
    }

    public function edit(CmsAnnouncement $announcement)
    {
        return view('admin.cms.announcements.edit', ['item' => $announcement]);
    }

    /**
     * Autosave draft content (sanitized). Intended for frequent calls from editor.
     */
    public function autosave(Request $request, CmsAnnouncement $announcement)
    {
        $data = $request->validate([
            'title_id' => ['nullable', 'string', 'max:190'],
            'title_en' => ['nullable', 'string', 'max:190'],
            'content_html_id' => ['nullable', 'string'],
            'content_html_en' => ['nullable', 'string'],
        ]);

        $announcement->fill([
            'title_id' => $data['title_id'] ?? $announcement->title_id,
            'title_en' => array_key_exists('title_en', $data) ? ($data['title_en'] ?: null) : $announcement->title_en,
            'content_html_id' => $this->sanitizer->clean($data['content_html_id'] ?? $announcement->content_html_id ?? ''),
            'content_html_en' => $this->sanitizer->clean($data['content_html_en'] ?? $announcement->content_html_en ?? ''),
        ])->save();

        $this->audit->log('cms.announcement_autosaved', 'cms_announcements', (string) $announcement->id, []);

        return response()->json([
            'ok' => true,
            'saved_at' => now()->toISOString(),
        ]);
    }

    /**
     * Redirect helper: allow editors to open signed preview route.
     */
    public function previewRedirect(CmsAnnouncement $announcement)
    {
        $url = URL::temporarySignedRoute('preview.announcements.show', now()->addMinutes(20), ['announcement' => $announcement->id]);

        return redirect($url);
    }

    public function update(Request $request, CmsAnnouncement $announcement)
    {
        $data = $request->validate([
            'title_id' => ['required', 'string', 'max:190'],
            'title_en' => ['nullable', 'string', 'max:190'],
            'slug' => ['required', 'string', 'max:190', 'unique:cms_announcements,slug,' . $announcement->id],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
            'content_html_id' => ['nullable', 'string'],
            'content_html_en' => ['nullable', 'string'],
            'published_at' => ['nullable', 'date'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $imagePath = $announcement->image_path;
        if (!empty($data['remove_image'])) {
            $this->deleteCoverImage($imagePath);
            $imagePath = null;
        }

        if ($request->hasFile('image')) {
            $this->deleteCoverImage($imagePath);
            $imagePath = $this->storeCoverImage($request->file('image'));
        }

        $announcement->fill([
            'slug' => $this->resolveSlug($data['title_id'], $data['slug'], $announcement->id),
            'title_id' => $data['title_id'],
            'title_en' => $data['title_en'] ?? null,
            'image_path' => $imagePath,
            'content_html_id' => $this->sanitizer->clean($data['content_html_id'] ?? ''),
            'content_html_en' => $this->sanitizer->clean($data['content_html_en'] ?? ''),
            'published_at' => $data['published_at'] ?? null,
            'is_published' => (bool) ($data['is_published'] ?? false),
        ])->save();

        $this->audit->log('cms.announcement_updated', 'cms_announcements', (string) $announcement->id, [
            'slug' => $announcement->slug,
            'is_published' => $announcement->is_published,
        ]);

        return redirect()->route('admin.cms.announcements.index')->with('status', __('app.saved'));
    }

    public function destroy(CmsAnnouncement $announcement)
    {
        $id = $announcement->id;
        $this->deleteCoverImage($announcement->image_path);
        $announcement->delete();

        $this->audit->log('cms.announcement_deleted', 'cms_announcements', (string) $id, []);

        return redirect()->route('admin.cms.announcements.index')->with('status', __('app.deleted'));
    }

    private function storeCoverImage(UploadedFile $file): string
    {
        $disk = 'public';
        $path = $this->uploadNamer->makePathForUploadedFile(
            $disk,
            'cms/announcements',
            'cms_announcement_cover',
            $file,
        );
        $stream = fopen($file->getRealPath(), 'rb');
        Storage::disk($disk)->put($path, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        return $path;
    }

    private function deleteCoverImage(?string $path): void
    {
        if (!$path) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function resolveSlug(string $title, ?string $slugInput = null, ?int $ignoreId = null): string
    {
        $base = Str::slug($slugInput ?: $title);
        if ($base === '') {
            $base = Str::random(10);
        }

        $slug = $base;
        $counter = 1;

        while (CmsAnnouncement::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
