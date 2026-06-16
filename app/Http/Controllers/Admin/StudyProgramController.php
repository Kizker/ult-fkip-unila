<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UnitType;
use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudyProgramController extends Controller
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

    public function index(Request $request)
    {
        $departmentId = $request->query('jurusan_id');
        $faculty = $this->resolveDefaultFaculty();

        $departments = Unit::query()
            ->where('type', UnitType::jurusan->value)
            ->where('parent_id', $faculty->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $departmentIds = $departments->pluck('id');
        $departmentIdInt = (int) $departmentId;
        $departmentId = $departmentIds->contains($departmentIdInt) ? (string) $departmentIdInt : null;

        $items = Unit::query()
            ->where('type', UnitType::prodi->value)
            ->whereIn('parent_id', $departmentIds)
            ->with('parent')
            ->withCount(['users as users_count'])
            ->when($departmentId, fn ($q) => $q->where('parent_id', $departmentId))
            ->orderBy('name')
            ->paginate(15);

        return view('admin.academics.study_programs.index', [
            'items' => $items->appends(['jurusan_id' => $departmentId]),
            'departments' => $departments,
            'departmentId' => $departmentId,
        ]);
    }

    public function create()
    {
        $faculty = $this->resolveDefaultFaculty();

        $departments = Unit::query()
            ->where('type', UnitType::jurusan->value)
            ->where('parent_id', $faculty->id)
            ->orderBy('name')
            ->get();

        return view('admin.academics.study_programs.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $faculty = $this->resolveDefaultFaculty();

        $data = $request->validate([
            'parent_id' => [
                'required',
                'integer',
                Rule::exists('units', 'id')->where(fn ($q) => $q
                    ->where('type', UnitType::jurusan->value)
                    ->where('parent_id', $faculty->id)),
            ],
            'code' => ['required', 'string', 'max:50', 'unique:units,code'],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        Unit::create([
            'type' => UnitType::prodi->value,
            'parent_id' => $data['parent_id'],
            'code' => $data['code'],
            'name' => $data['name'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('admin.prodi.index')->with('status', __('app.saved'));
    }

    public function edit(Unit $prodi)
    {
        $this->assertType($prodi, UnitType::prodi);
        $faculty = $this->resolveDefaultFaculty();

        $departments = Unit::query()
            ->where('type', UnitType::jurusan->value)
            ->where('parent_id', $faculty->id)
            ->orderBy('name')
            ->get();

        return view('admin.academics.study_programs.edit', compact('prodi', 'departments'));
    }

    public function update(Request $request, Unit $prodi)
    {
        $this->assertType($prodi, UnitType::prodi);
        $faculty = $this->resolveDefaultFaculty();

        $data = $request->validate([
            'parent_id' => [
                'required',
                'integer',
                Rule::exists('units', 'id')->where(fn ($q) => $q
                    ->where('type', UnitType::jurusan->value)
                    ->where('parent_id', $faculty->id)),
            ],
            'code' => ['required', 'string', 'max:50', Rule::unique('units', 'code')->ignore($prodi->id)],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $prodi->update([
            'parent_id' => $data['parent_id'],
            'code' => $data['code'],
            'name' => $data['name'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return back()->with('status', __('app.saved'));
    }

    public function destroy(Unit $prodi)
    {
        $this->assertType($prodi, UnitType::prodi);

        if ($prodi->users()->exists()) {
            return back()->with('warning', 'Tidak bisa menghapus prodi karena masih dipakai oleh user.');
        }

        $prodi->delete();

        return redirect()->route('admin.prodi.index')->with('status', __('app.deleted'));
    }
}
