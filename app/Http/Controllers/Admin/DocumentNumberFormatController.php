<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentNumberFormatRequest;
use App\Models\DocumentNumberFormat;
use App\Models\Unit;
use Illuminate\Http\Request;

class DocumentNumberFormatController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:document_numbers.manage_formats');
    }

    private function scopedUnits(): \Illuminate\Support\Collection
    {
        $user = auth()->user();
        if ($user->can('requests.view_any')) {
            return Unit::query()->orderBy('type')->orderBy('name')->get();
        }
        $unit = $user->unit;
        if (!$unit) return collect();

        $ids = array_merge([$unit->id], $unit->descendantIdsCached(600));
        return Unit::query()->whereIn('id', $ids)->orderBy('type')->orderBy('name')->get();
    }

    public function index(Request $request)
    {
        $units = $this->scopedUnits();
        $unitId = $request->integer('unit_id');
        $q = DocumentNumberFormat::query()->with('unit');

        if ($unitId) {
            $q->where('unit_id', $unitId);
        } else {
            $q->whereIn('unit_id', $units->pluck('id')->all());
        }

        $formats = $q->orderBy('unit_id')->orderBy('format_key')->paginate(20)->withQueryString();

        return view('admin/document_numbers/formats/index', [
            'formats' => $formats,
            'units' => $units,
            'unitId' => $unitId,
        ]);
    }

    public function create()
    {
        $units = $this->scopedUnits();
        return view('admin/document_numbers/formats/create', [
            'units' => $units,
            'defaults' => [
                'format_key' => 'default',
                'name' => 'Default',
                'template' => '{SEQ:3}/ULT-FKIP/{UNIT_CODE}/{YYYY}',
                'seq_padding' => 3,
                'is_active' => true,
            ],
        ]);
    }

    public function store(StoreDocumentNumberFormatRequest $request)
    {
        $data = $request->validated();

        $this->assertTemplateSafe($data['template'] ?? '');
        $this->assertUnitInScope((int) $data['unit_id']);

        $data['seq_padding'] = (int) ($data['seq_padding'] ?? 3);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        DocumentNumberFormat::updateOrCreate(
            ['unit_id' => $data['unit_id'], 'format_key' => $data['format_key']],
            $data
        );

        return redirect()->route('admin.doc_formats.index')->with('success', 'Format nomor disimpan.');
    }

    public function edit(DocumentNumberFormat $doc_format)
    {
        $this->authorize('update', $doc_format);
        $units = $this->scopedUnits();

        return view('admin/document_numbers/formats/edit', [
            'format' => $doc_format,
            'units' => $units,
        ]);
    }

    public function show(DocumentNumberFormat $doc_format)
    {
        $this->authorize('view', $doc_format);

        return redirect()
            ->route('admin.doc_formats.edit', $doc_format)
            ->with('status', 'Mode detail diarahkan ke halaman edit.');
    }

    public function update(StoreDocumentNumberFormatRequest $request, DocumentNumberFormat $doc_format)
    {
        $this->authorize('update', $doc_format);

        $data = $request->validated();
        $this->assertTemplateSafe($data['template'] ?? '');
        $this->assertUnitInScope((int) $data['unit_id']);

        $data['seq_padding'] = (int) ($data['seq_padding'] ?? 3);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        // If unit_id/format_key changes, enforce uniqueness by upsert
        if ((int) $doc_format->unit_id !== (int) $data['unit_id'] || $doc_format->format_key !== $data['format_key']) {
            $doc_format->delete();
            DocumentNumberFormat::updateOrCreate(
                ['unit_id' => $data['unit_id'], 'format_key' => $data['format_key']],
                $data
            );
        } else {
            $doc_format->fill($data)->save();
        }

        return redirect()->route('admin.doc_formats.index')->with('success', 'Format nomor diperbarui.');
    }

    public function destroy(DocumentNumberFormat $doc_format)
    {
        $this->authorize('delete', $doc_format);
        $doc_format->delete();
        return redirect()->route('admin.doc_formats.index')->with('success', 'Format nomor dihapus.');
    }

    private function assertUnitInScope(int $unitId): void
    {
        $user = auth()->user();
        if ($user->can('requests.view_any')) return;

        $unit = $user->unit;
        if (!$unit) abort(403);

        $allowed = array_merge([$unit->id], $unit->descendantIdsCached(600));
        if (!in_array($unitId, $allowed, true)) {
            abort(403);
        }
    }

    private function assertTemplateSafe(string $template): void
    {
        // Must include {SEQ} (optionally {SEQ:n})
        if (!preg_match('/\{SEQ(?::\d+)?\}/', $template)) {
            abort(422, 'Template wajib memiliki placeholder {SEQ} atau {SEQ:n}.');
        }

        // Restrict suspicious constructs: php tags, script tags, backticks.
        if (preg_match('/<\?php|<script|`/i', $template)) {
            abort(422, 'Template mengandung karakter yang tidak diizinkan.');
        }

        // Allowed placeholders
        $allowed = ['SEQ','UNIT_CODE','UNIT','YYYY','MM'];
        preg_match_all('/\{([A-Z_]+)(?::\d+)?\}/', $template, $m);
        foreach ($m[1] as $ph) {
            if (!in_array($ph, $allowed, true)) {
                abort(422, 'Placeholder tidak dikenal: {'.$ph.'}.');
            }
        }
    }
}
