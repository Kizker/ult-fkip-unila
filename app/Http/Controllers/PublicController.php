<?php

namespace App\Http\Controllers;

use App\Enums\ServiceTemplateType;
use App\Models\CmsAnnouncement;
use App\Models\CmsBlog;
use App\Models\CmsCategory;
use App\Models\HeroBanner;
use App\Models\Request as RequestItem;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\UserGuide;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function home()
    {
        $heroSlides = HeroBanner::query()
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $baseServices = Service::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'PUBLISHED');
            });

        $totalServices = (clone $baseServices)->count();

        $baseServices = $baseServices->with('category');

        $popularServiceIds = RequestItem::query()
            ->whereNotNull('service_id')
            ->selectRaw('service_id, COUNT(*) as usage_total')
            ->groupBy('service_id')
            ->orderByDesc('usage_total')
            ->limit(4)
            ->pluck('service_id');

        $services = collect();
        if ($popularServiceIds->isNotEmpty()) {
            $services = (clone $baseServices)
                ->whereIn('id', $popularServiceIds->all())
                ->get()
                ->sortBy(fn (Service $service) => (int) $popularServiceIds->search($service->id))
                ->values();
        }

        if ($services->count() < 4) {
            $remaining = 4 - $services->count();
            $fallback = (clone $baseServices)
                ->whereNotIn('id', $services->pluck('id')->all())
                ->orderBy('title_id')
                ->limit($remaining)
                ->get();
            $services = $services->concat($fallback)->values();
        }

        $services = $services->take(4)->values();

        $totalAnnouncements = CmsAnnouncement::query()
            ->where('is_published', true)
            ->count();

        $ann = CmsAnnouncement::query()
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        $totalBlogs = CmsBlog::query()
            ->where('is_published', true)
            ->count();

        $blogs = CmsBlog::query()
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        $privateDisk = (string) config('ult.private_disk', 'private');
        $guideBook = UserGuide::visibleTo(request()->user())
            ->where(function ($q) {
                $q->where('content_type', UserGuide::CONTENT_TYPE_PDF)
                  ->orWhereNull('content_type');
            })
            ->whereNotNull('stored_path')
            ->latest('published_at')
            ->limit(5)
            ->get()
            ->first(fn (UserGuide $g) => \Storage::disk($privateDisk)->exists((string) $g->stored_path));

        $guideVideos = UserGuide::visibleTo(request()->user())
            ->where('content_type', UserGuide::CONTENT_TYPE_VIDEO)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('public.home', compact(
            'heroSlides',
            'services',
            'ann',
            'blogs',
            'guideBook',
            'guideVideos',
            'totalServices',
            'totalAnnouncements',
            'totalBlogs'
        ));
    }

    public function services(Request $request)
    {
        $perPage = 15;

        $q = (string) $request->query('q', '');
        $category = $request->query('category');
        $category = is_numeric($category) ? (int) $category : null;

        $heroBanner = HeroBanner::query()
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        $categoryMap = CmsCategory::query()
            ->where('type', 'service')
            ->whereIn('slug', [
                'akademik-dan-kerja-sama',
                'umum-dan-keuangan',
                'kemahasiswaan-dan-alumni',
                'lainnya',
            ])
            ->get()
            ->keyBy('id');

        if ($category && !$categoryMap->has($category)) {
            $category = null;
        }

        $services = Service::query()
            ->where('is_active', true)
            ->where(function ($qr) {
                $qr->whereNull('status')->orWhere('status', 'PUBLISHED');
            })
            ->when($q, fn($qr) => $qr->where(function ($w) use ($q) {
                $w->where('title_id', 'like', "%{$q}%")->orWhere('title_en', 'like', "%{$q}%");
            }))
            ->when($category, fn($qr) => $qr->where('category_id', $category))
            ->with('category')
            ->orderBy('title_id')
            ->paginate($perPage)
            ->withQueryString();

        $categories = $categoryMap->values();

        if ($request->boolean('_infinite')) {
            return response()->json([
                'html' => view('public.services._items', [
                    'services' => $services,
                    'isEn' => app()->getLocale() === 'en',
                    'loopOffset' => (($services->currentPage() - 1) * $services->perPage()),
                ])->render(),
                'next_page_url' => $services->nextPageUrl(),
                'has_more' => $services->hasMorePages(),
            ]);
        }

        return view('public.services.index', compact('services', 'q', 'category', 'categories', 'heroBanner'));
    }

    public function serviceShow(Service $service)
    {
        abort_unless($service->is_active, 404);
        abort_unless($service->status === null || $service->status?->value === 'PUBLISHED', 404);
        $service->loadMissing(['workflow', 'templates']);

        $heroBanner = HeroBanner::query()
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        $wf = $service->workflow;
        $isCertificateService = $service->usesRequestPptxSource();
        $hasDocumentTemplate = $service->usesMainDocxTemplateSource()
            && (bool) $service->templates->firstWhere('type', ServiceTemplateType::MAIN_DOCX);
        $documentPreviewUrl = $hasDocumentTemplate ? route('services.document_preview', $service) : null;

        return view('public.services.show', compact(
            'service',
            'wf',
            'hasDocumentTemplate',
            'documentPreviewUrl',
            'isCertificateService',
            'heroBanner',
        ));
    }

    public function about()
    {
        $heroBanner = HeroBanner::query()
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        $aboutId = SiteSetting::where('key','about_ult_html_id')->value('value');
        $aboutEn = SiteSetting::where('key','about_ult_html_en')->value('value');
        return view('public.about', compact('aboutId','aboutEn', 'heroBanner'));
    }

    public function blog()
    {
        $perPage = 15;

        $heroBanner = HeroBanner::query()
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        $posts = CmsBlog::query()
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->paginate($perPage)
            ->withQueryString();

        if (request()->boolean('_infinite')) {
            $isEn = app()->getLocale() === 'en';
            $stripContent = static function (?string $html): string {
                $plain = strip_tags((string) ($html ?? ''));
                $plain = preg_replace('/\s+/u', ' ', $plain);
                return trim((string) $plain);
            };
            $excerpt = static function (?string $html, int $limit = 140) use ($stripContent): string {
                return \Illuminate\Support\Str::limit($stripContent($html), $limit, '...');
            };
            $extractImage = static function (?string $html): ?string {
                if (!filled($html)) {
                    return null;
                }

                return preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches) === 1 ? $matches[1] : null;
            };
            $normalizeImage = static function (?string $src): ?string {
                if (!filled($src)) {
                    return null;
                }

                if (\Illuminate\Support\Str::startsWith($src, ['http://', 'https://', 'data:'])) {
                    return $src;
                }

                return asset(ltrim((string) $src, '/'));
            };

            return response()->json([
                'html' => view('public.blog._items', compact('posts', 'isEn', 'excerpt', 'extractImage', 'normalizeImage'))->render(),
                'next_page_url' => $posts->nextPageUrl(),
                'has_more' => $posts->hasMorePages(),
            ]);
        }

        return view('public.blog.index', compact('posts', 'heroBanner'));
    }

    public function blogShow(CmsBlog $blog)
    {
        abort_unless($blog->is_published, 404);
        $post = $blog;

        $heroBanner = HeroBanner::query()
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        return view('public.blog.show', compact('post', 'heroBanner'));
    }

    public function announcements()
    {
        $perPage = 15;

        $heroBanner = HeroBanner::query()
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        $posts = CmsAnnouncement::query()
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->paginate($perPage)
            ->withQueryString();

        if (request()->boolean('_infinite')) {
            $isEn = app()->getLocale() === 'en';
            $stripContent = static function (?string $html): string {
                $plain = strip_tags((string) ($html ?? ''));
                $plain = preg_replace('/\s+/u', ' ', $plain);
                return trim((string) $plain);
            };
            $excerpt = static function (?string $html, int $limit = 140) use ($stripContent): string {
                return \Illuminate\Support\Str::limit($stripContent($html), $limit, '...');
            };
            $extractImage = static function (?string $html): ?string {
                if (!filled($html)) {
                    return null;
                }

                return preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches) === 1 ? $matches[1] : null;
            };
            $normalizeImage = static function (?string $src): ?string {
                if (!filled($src)) {
                    return null;
                }

                if (\Illuminate\Support\Str::startsWith($src, ['http://', 'https://', 'data:'])) {
                    return $src;
                }

                return asset(ltrim((string) $src, '/'));
            };

            return response()->json([
                'html' => view('public.announcements._items', compact('posts', 'isEn', 'excerpt', 'extractImage', 'normalizeImage'))->render(),
                'next_page_url' => $posts->nextPageUrl(),
                'has_more' => $posts->hasMorePages(),
            ]);
        }

        return view('public.announcements.index', compact('posts', 'heroBanner'));
    }

    public function announcementShow(CmsAnnouncement $announcement)
    {
        abort_unless($announcement->is_published, 404);
        $post = $announcement;

        $heroBanner = HeroBanner::query()
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        return view('public.announcements.show', compact('post', 'heroBanner'));
    }

    /**
     * Signed preview for CMS editors: allows viewing unpublished post safely.
     * Route is protected by signed middleware + cms.manage permission.
     */
    public function previewBlog(CmsBlog $blog)
    {
        return view('public.preview-post', [
            'post' => $blog,
            'typeLabel' => 'blog',
            'editUrl' => route('admin.cms.blogs.edit', $blog),
        ]);
    }

    /**
     * Signed preview for CMS editors: allows viewing unpublished announcement safely.
     * Route is protected by signed middleware + cms.manage permission.
     */
    public function previewAnnouncement(CmsAnnouncement $announcement)
    {
        return view('public.preview-post', [
            'post' => $announcement,
            'typeLabel' => 'announcement',
            'editUrl' => route('admin.cms.announcements.edit', $announcement),
        ]);
    }

    public function notifications(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(20);
        $unreadTotal = (int) $request->user()->unreadNotifications()->count();
        return view('app.notifications.index', compact('notifications', 'unreadTotal'));
    }

    public function markNotificationRead(Request $request, string $id)
    {
        $n = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $n->markAsRead();
        return back();
    }

    public function markAllNotificationsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return back()->with('status', 'Semua notifikasi telah ditandai dibaca.');
    }

    public function deleteNotification(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();

        if (is_null($notification->read_at)) {
            return back()->with('status', 'Notifikasi belum dibaca, tandai dibaca terlebih dahulu sebelum menghapus.');
        }

        $notification->delete();

        return back()->with('status', 'Notifikasi berhasil dihapus.');
    }

    public function deleteAllNotifications(Request $request)
    {
        $deleted = $request->user()->notifications()->whereNotNull('read_at')->delete();

        if ($deleted < 1) {
            return back()->with('status', 'Tidak ada notifikasi terbaca yang dapat dihapus.');
        }

        return back()->with('status', 'Semua notifikasi terbaca berhasil dihapus.');
    }
}
