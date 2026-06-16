<?php

namespace App\Http\Controllers;

use App\Models\HeroBanner;
use App\Models\UserGuide;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicUserGuideController extends Controller
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $heroBanner = $this->activeHeroBanner();

        $guides = UserGuide::query()
            ->with('roles:id,name')
            ->visibleTo($request->user())
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($where) use ($q) {
                    $where->where('title_id', 'like', "%{$q}%")
                        ->orWhere('title_en', 'like', "%{$q}%")
                        ->orWhere('summary_id', 'like', "%{$q}%")
                        ->orWhere('summary_en', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('published_at')
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        if ($request->boolean('_infinite')) {
            return response()->json([
                'html' => view('public.user_guides._items', [
                    'guides' => $guides,
                    'isEn' => app()->getLocale() === 'en',
                ])->render(),
                'next_page_url' => $guides->nextPageUrl(),
                'has_more' => $guides->hasMorePages(),
            ]);
        }

        return view('public.user_guides.index', compact('guides', 'q', 'heroBanner'));
    }

    public function show(Request $request, UserGuide $user_guide)
    {
        $denied = $this->ensureCanView($request, $user_guide);
        if ($denied !== null) {
            return $denied;
        }

        if ($user_guide->isPdf()) {
            return redirect()->route('user_guides.file', $user_guide->slug);
        }

        return view('public.user_guides.show', [
            'guide' => $user_guide->loadMissing('roles:id,name'),
            'heroBanner' => $this->activeHeroBanner(),
            'videoUrl' => $user_guide->videoWatchUrl(),
            'videoEmbedUrl' => $user_guide->videoEmbedUrl(),
        ]);
    }

    public function file(Request $request, UserGuide $user_guide)
    {
        $denied = $this->ensureCanView($request, $user_guide);
        if ($denied !== null) {
            return $denied;
        }

        if (!$user_guide->isPdf()) {
            return redirect()->route('user_guides.show', $user_guide->slug);
        }

        $disk = (string) config('ult.private_disk', 'private');
        if (!Storage::disk($disk)->exists((string) $user_guide->stored_path)) {
            abort(404);
        }

        $filename = $this->inlineFileName($user_guide);

        return response()->file(
            Storage::disk($disk)->path((string) $user_guide->stored_path),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
                'X-Frame-Options' => 'SAMEORIGIN',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]
        );
    }

    public function download(Request $request, UserGuide $user_guide)
    {
        $denied = $this->ensureCanView($request, $user_guide);
        if ($denied !== null) {
            return $denied;
        }

        if (!$user_guide->isPdf()) {
            return redirect()->route('user_guides.show', $user_guide->slug);
        }

        $disk = (string) config('ult.private_disk', 'private');
        if (!Storage::disk($disk)->exists((string) $user_guide->stored_path)) {
            abort(404);
        }

        $downloadName = (string) ($user_guide->original_name ?: $this->inlineFileName($user_guide));

        $this->audit->log('user_guides.downloaded', 'user_guides', (string) $user_guide->id, [
            'slug' => $user_guide->slug,
            'is_public' => $user_guide->is_public,
        ], $request);

        return Storage::disk($disk)->download((string) $user_guide->stored_path, $downloadName);
    }

    private function ensureCanView(Request $request, UserGuide $guide)
    {
        if ($guide->canBeViewedBy($request->user())) {
            return null;
        }

        if (!$guide->is_published) {
            abort(404);
        }

        if (!$request->user()) {
            return redirect()
                ->route('login')
                ->with('status', 'Silakan login untuk melihat panduan ini.');
        }

        abort(403);
    }

    private function inlineFileName(UserGuide $guide): string
    {
        $slug = trim((string) $guide->slug);
        if ($slug === '') {
            return 'panduan-pengguna.pdf';
        }

        return $slug . '.pdf';
    }

    private function activeHeroBanner(): ?HeroBanner
    {
        return HeroBanner::query()
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();
    }
}
