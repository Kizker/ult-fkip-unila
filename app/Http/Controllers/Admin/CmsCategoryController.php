<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CmsCategoryType;
use App\Http\Controllers\Controller;
use App\Models\CmsCategory;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CmsCategoryController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index()
    {
        $items = CmsCategory::query()
            ->where('type', CmsCategoryType::service->value)
            ->orderBy('name_id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.cms.categories.index', compact('items'));
    }

    public function create()
    {
        return view('admin.cms.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_id' => ['required','string','max:190'],
            'name_en' => ['nullable','string','max:190'],
            'slug' => ['nullable','string','max:190'],
        ]);

        $slug = $data['slug'] ?: Str::slug($data['name_id']);
        $cat = CmsCategory::create([
            'type' => CmsCategoryType::service,
            'slug' => $slug,
            'name_id' => $data['name_id'],
            'name_en' => $data['name_en'] ?? null,
        ]);

        $this->audit->log('cms.category_created', 'cms_categories', (string)$cat->id, ['slug' => $cat->slug]);

        return redirect()->route('admin.cms.categories.index')->with('status', __('app.saved'));
    }

    public function edit(CmsCategory $category)
    {
        return view('admin.cms.categories.edit', ['item' => $category]);
    }

    public function update(Request $request, CmsCategory $category)
    {
        $data = $request->validate([
            'name_id' => ['required','string','max:190'],
            'name_en' => ['nullable','string','max:190'],
            'slug' => ['required','string','max:190','unique:cms_categories,slug,'.$category->id],
        ]);

        $category->fill([
            'type' => CmsCategoryType::service,
            'slug' => $data['slug'],
            'name_id' => $data['name_id'],
            'name_en' => $data['name_en'] ?? null,
        ])->save();

        $this->audit->log('cms.category_updated', 'cms_categories', (string)$category->id, ['slug' => $category->slug]);

        return redirect()->route('admin.cms.categories.index')->with('status', __('app.saved'));
    }

    public function destroy(CmsCategory $category)
    {
        if ($category->type?->value !== CmsCategoryType::service->value) {
            return back()->withErrors(['category' => 'Kategori ini bukan kategori layanan.']);
        }

        if ($category->services()->exists()) {
            return back()->withErrors(['category' => 'Kategori masih dipakai data layanan. Pindahkan layanan terlebih dahulu.']);
        }

        $id = $category->id;
        $category->delete();

        $this->audit->log('cms.category_deleted', 'cms_categories', (string)$id, []);

        return redirect()->route('admin.cms.categories.index')->with('status', __('app.deleted'));
    }
}
