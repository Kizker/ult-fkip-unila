<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroBanner;
use App\Services\AuditLogger;
use App\Services\Uploads\UniqueUploadNamer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CmsHeroController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly UniqueUploadNamer $uploadNamer,
    ) {}

    public function edit()
    {
        $hero = HeroBanner::query()->orderByDesc('id')->first();
        return view('admin.cms.hero_edit', compact('hero'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'title_id' => ['required','string','max:190'],
            'title_en' => ['nullable','string','max:190'],
            'subtitle_id' => ['nullable','string','max:190'],
            'subtitle_en' => ['nullable','string','max:190'],
            'cta_label_id' => ['nullable','string','max:190'],
            'cta_label_en' => ['nullable','string','max:190'],
            'cta_url' => ['nullable','string','max:255'],
            'is_active' => ['nullable','boolean'],
            'image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        $hero = HeroBanner::query()->orderByDesc('id')->first() ?? new HeroBanner();

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $disk = 'public';
            $path = $this->uploadNamer->makePathForUploadedFile(
                $disk,
                'cms/hero',
                'cms_hero',
                $file,
            );
            $stream = fopen($file->getRealPath(), 'rb');
            Storage::disk($disk)->put($path, $stream);
            if (is_resource($stream)) fclose($stream);
            $data['image_path'] = $path;

            // delete old (best-effort)
            if ($hero->image_path) {
                Storage::disk($disk)->delete($hero->image_path);
            }
        }

        $hero->fill([
            'title_id' => $data['title_id'],
            'title_en' => $data['title_en'] ?? null,
            'subtitle_id' => $data['subtitle_id'] ?? null,
            'subtitle_en' => $data['subtitle_en'] ?? null,
            'cta_label_id' => $data['cta_label_id'] ?? null,
            'cta_label_en' => $data['cta_label_en'] ?? null,
            'cta_url' => $data['cta_url'] ?? null,
            'image_path' => $data['image_path'] ?? $hero->image_path,
            'is_active' => (bool)($data['is_active'] ?? false),
        ]);

        $hero->save();

        $this->audit->log('cms.hero_updated', 'hero_banners', (string)$hero->id, [
            'is_active' => $hero->is_active,
        ]);

        return redirect()->route('admin.cms.index')->with('status', __('app.saved'));
    }
}
