<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UnitType;
use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    private function resolveDefaultFaculty(): Unit
    {
        $faculty = Unit::query()
            ->where('type', UnitType::fakultas->value)
            ->where(fn ($q) => $q
                ->where('code', 'FKIP')
                ->orWhere('name', 'Fakultas Keguruan dan Ilmu Pendidikan'))
            ->orderByRaw("CASE WHEN code = 'FKIP' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->first();

        abort_if(!$faculty, 422, 'Fakultas FKIP belum tersedia. Tambahkan unit fakultas terlebih dahulu.');

        return $faculty;
    }

    private function assertType(Unit $unit, UnitType $type): void
    {
        abort_unless($unit->type === $type, 404);
    }

    public function index()
    {
        $items = Unit::query()
            ->where('type', UnitType::jurusan->value)
            ->with('parent')
            ->withCount([
                'children as prodi_count' => fn ($q) => $q->where('type', UnitType::prodi->value),
                'users as users_count',
            ])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.academics.departments.index', compact('items'));
    }

    public function create()
    {
        $faculty = $this->resolveDefaultFaculty();

        return view('admin.academics.departments.create', compact('faculty'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:units,code'],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);
        $faculty = $this->resolveDefaultFaculty();

        Unit::create([
            'type' => UnitType::jurusan->value,
            'parent_id' => $faculty->id,
            'code' => $data['code'],
            'name' => $data['name'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('admin.jurusan.index')->with('status', __('app.saved'));
    }

    public function edit(Unit $jurusan)
    {
        $this->assertType($jurusan, UnitType::jurusan);

        $faculty = $this->resolveDefaultFaculty();

        return view('admin.academics.departments.edit', compact('jurusan', 'faculty'));
    }

    public function update(Request $request, Unit $jurusan)
    {
        $this->assertType($jurusan, UnitType::jurusan);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('units', 'code')->ignore($jurusan->id)],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);
        $faculty = $this->resolveDefaultFaculty();

        $jurusan->update([
            'parent_id' => $faculty->id,
            'code' => $data['code'],
            'name' => $data['name'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return back()->with('status', __('app.saved'));
    }

    public function destroy(Unit $jurusan)
    {
        $this->assertType($jurusan, UnitType::jurusan);

        $hasProdi = $jurusan->children()->where('type', UnitType::prodi->value)->exists();
        if ($hasProdi) {
            return back()->with('warning', 'Tidak bisa menghapus jurusan karena masih memiliki program studi.');
        }

        if ($jurusan->users()->exists()) {
            return back()->with('warning', 'Tidak bisa menghapus jurusan karena masih dipakai oleh user.');
        }

        $jurusan->delete();

        return redirect()->route('admin.jurusan.index')->with('status', __('app.deleted'));
    }
}
