<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsAnnouncement;
use App\Models\CmsBlog;
use App\Models\HeroBanner;
use Illuminate\Support\Facades\DB;

class CmsController extends Controller
{
    public function index()
    {
        $hero = HeroBanner::query()->orderByDesc('id')->first();

        $blogs = CmsBlog::query()
            ->selectRaw("id, slug, title_id, published_at, is_published, 'blog' as content_type");

        $announcements = CmsAnnouncement::query()
            ->selectRaw("id, slug, title_id, published_at, is_published, 'announcement' as content_type");

        $recentContents = DB::query()
            ->fromSub($blogs->unionAll($announcements), 'recent_contents')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.cms.index', compact('hero', 'recentContents'));
    }

    // TODO: split into dedicated controllers for Hero and Site Settings with full CRUD UI.
}
