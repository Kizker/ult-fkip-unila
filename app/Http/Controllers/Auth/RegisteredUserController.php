<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UnitType;
use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\Uploads\UniqueUploadNamer;
use App\Support\VerificationEmailDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    public function create()
    {
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

        return view('auth.register', [
            'jurusanOptions' => $jurusanOptions,
            'prodiOptions' => $prodiOptions,
        ]);
    }

    public function store(
        Request $request,
        AuditLogger $audit,
        VerificationEmailDispatcher $verificationMail,
        UniqueUploadNamer $uploadNamer,
    ): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'email' => ['required','email','max:190','unique:users,email'],
            'password' => ['required','string','min:10','confirmed'],
            'account_role' => ['nullable', Rule::in(['Mahasiswa', 'Dosen'])],
            'student_number' => ['required', 'string', 'max:50'],
            'jurusan_id' => [
                'required',
                'integer',
                Rule::exists('units', 'id')->where(fn ($query) => $query
                    ->where('type', UnitType::jurusan->value)
                    ->where('is_active', true)),
            ],
            'prodi_id' => [
                'required',
                'integer',
                Rule::exists('units', 'id')->where(fn ($query) => $query
                    ->where('type', UnitType::prodi->value)
                    ->where('is_active', true)),
            ],
            'profile_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:ratio=1/1'],
        ], [
            'student_number.required' => 'NPM atau NIP wajib diisi.',
            'student_number.max' => 'NPM atau NIP maksimal 50 karakter.',
            'profile_photo.required' => 'Foto profil wajib diunggah.',
            'profile_photo.image' => 'File yang dipilih harus berupa gambar.',
            'profile_photo.mimes' => 'Foto profil harus berformat JPG, JPEG, PNG, atau WEBP.',
            'profile_photo.max' => 'Ukuran foto profil maksimal 2MB.',
            'profile_photo.dimensions' => 'Foto profil harus berbentuk persegi. Sistem biasanya memotong otomatis, jadi silakan pilih ulang jika masih gagal.',
        ]);

        $prodi = Unit::query()->find((int) $data['prodi_id']);
        if (!$prodi || (int) $prodi->parent_id !== (int) $data['jurusan_id']) {
            throw ValidationException::withMessages([
                'prodi_id' => 'Program studi tidak sesuai dengan jurusan yang dipilih.',
            ]);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => Hash::make($data['password']),
            'unit_id' => (int) $data['prodi_id'],
            'student_number' => trim((string) $data['student_number']),
        ]);

        $file = $request->file('profile_photo');
        $path = $uploadNamer->makePathForUploadedFile(
            'public',
            'avatars',
            "avatar_profile_{$user->id}",
            $file,
        );
        $stream = fopen($file->getRealPath(), 'rb');
        Storage::disk('public')->put($path, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }
        $user->forceFill(['profile_photo_path' => $path])->save();

        $selectedRole = (string) ($data['account_role'] ?? 'Mahasiswa');
        Role::firstOrCreate(['name' => $selectedRole, 'guard_name' => 'web']);
        $user->assignRole($selectedRole);

        $sendResult = $verificationMail->send($user);
        $verificationSent = (bool) ($sendResult['sent'] ?? false);
        $verificationError = $sendResult['raw_error'] ?? null;

        Auth::login($user);
        $request->session()->regenerate();

        $audit->log('auth.register', 'users', (string) $user->id, [
            'role' => $selectedRole,
            'verification_sent' => $verificationSent,
            'verification_error' => $verificationError,
        ], $request, $user);

        // After register, redirect to verification notice
        if (!$verificationSent) {
            return redirect()
                ->route('verification.notice')
                ->with('error', $sendResult['error'] ?? 'Akun dibuat, tapi email verifikasi gagal dikirim. Silakan klik "Kirim ulang" atau hubungi admin.');
        }

        return redirect()->route('verification.notice');
    }
}
