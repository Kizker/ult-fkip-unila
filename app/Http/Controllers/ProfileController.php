<?php

namespace App\Http\Controllers;

use App\Enums\UnitType;
use App\Models\Unit;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\Uploads\UniqueUploadNamer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly UniqueUploadNamer $uploadNamer,
    ) {}

    public function edit(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $isStudentProfile = $user->can('requests.view_own');

        $jurusanOptions = Unit::query()
            ->where('type', UnitType::jurusan->value)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $prodiOptions = Unit::query()
            ->where('type', UnitType::prodi->value)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        $selectedProdiId = (int) old('prodi_id', (int) ($user->unit_id ?? 0));
        $selectedProdi = $prodiOptions->firstWhere('id', $selectedProdiId);
        $selectedJurusanId = (int) old('jurusan_id', (int) ($selectedProdi?->parent_id ?? 0));

        return view('profile.edit', [
            'user' => $user,
            'isStudentProfile' => $isStudentProfile,
            'jurusanOptions' => $jurusanOptions,
            'prodiOptions' => $prodiOptions,
            'selectedJurusanId' => $selectedJurusanId > 0 ? $selectedJurusanId : null,
            'selectedProdiId' => $selectedProdiId > 0 ? $selectedProdiId : null,
        ]);
    }

    public function update(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $mode = (string) $request->input('action', 'update_profile');
        $isStudentProfile = $user->can('requests.view_own');

        if ($mode === 'change_password') {
            $data = $request->validate([
                'current_password' => ['required', 'current_password'],
                'new_password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $user->forceFill([
                'password' => $data['new_password'],
            ])->save();

            $this->audit->log('users.password_changed', 'users', (string) $user->id, []);

            return back()->with('status', 'Kata sandi berhasil diubah.');
        }

        $rules = [
            'name' => ['required', 'string', 'max:190'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:ratio=1/1'],
        ];

        if ($isStudentProfile) {
            $rules['jurusan_id'] = [
                'required',
                'integer',
                Rule::exists('units', 'id')->where(fn ($q) => $q->where('type', UnitType::jurusan->value)->where('is_active', true)),
            ];
            $rules['prodi_id'] = [
                'required',
                'integer',
                Rule::exists('units', 'id')->where(fn ($q) => $q->where('type', UnitType::prodi->value)->where('is_active', true)),
            ];
            $rules['student_number'] = ['nullable', 'string', 'max:50'];
        }

        $data = $request->validate($rules, [
            'profile_photo.image' => 'File yang dipilih harus berupa gambar.',
            'profile_photo.mimes' => 'Foto profil harus berformat JPG, JPEG, PNG, atau WEBP.',
            'profile_photo.max' => 'Ukuran foto profil maksimal 2MB.',
            'profile_photo.dimensions' => 'Foto profil harus berbentuk persegi. Sistem biasanya memotong otomatis, jadi silakan pilih ulang jika masih gagal.',
        ]);

        if ($isStudentProfile) {
            $prodi = Unit::query()->find((int) $data['prodi_id']);
            if (!$prodi || (int) $prodi->parent_id !== (int) $data['jurusan_id']) {
                throw ValidationException::withMessages([
                    'prodi_id' => 'Program studi tidak sesuai dengan jurusan yang dipilih.',
                ]);
            }
        }

        $user->forceFill([
            'name' => $data['name'],
            'unit_id' => $isStudentProfile ? (int) $data['prodi_id'] : $user->unit_id,
            'student_number' => $isStudentProfile ? (filled($data['student_number'] ?? null) ? trim((string) $data['student_number']) : null) : $user->student_number,
        ]);

        if ($request->hasFile('profile_photo')) {
            $this->deleteProfilePhoto($user);
            $file = $request->file('profile_photo');
            $path = $this->uploadNamer->makePathForUploadedFile(
                'public',
                'avatars',
                "avatar_profile_{$user->id}",
                $file,
            );
            $stream = fopen($file->getRealPath(), 'rb');
            Storage::disk('public')->put($path, $stream);
            if (is_resource($stream)) fclose($stream);
            $user->forceFill(['profile_photo_path' => $path]);
        }

        $user->save();

        $this->audit->log('users.profile_updated', 'users', (string) $user->id, [
            'has_new_photo' => $request->hasFile('profile_photo'),
            'unit_id' => $user->unit_id,
        ]);

        return back()->with('status', __('app.saved'));
    }

    private function deleteProfilePhoto(User $user): void
    {
        $path = $user->profile_photo_path;
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
