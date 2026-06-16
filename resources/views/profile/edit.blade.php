@extends('layouts.app')
@section('section', __('app.settings'))
@section('content')
    <div class="page-profile-edit" data-profile-edit-page>
        @php
            $profilePhotoUrl = $user->profile_photo_url;
            $profileInitials = collect(explode(' ', trim((string) $user->name)))
                ->filter()
                ->map(fn($part) => mb_substr($part, 0, 1))
                ->take(2)
                ->join('');
        @endphp
        <header class="profile-page-hero">
            <div class="profile-page-hero__heading">
                <div class="profile-page-hero__kicker">Akun</div>
                <h1 class="profile-page-hero__title">{{ __('app.settings') }}</h1>
                <p class="profile-page-hero__subtitle">Kelola identitas akun Anda agar tetap rapi dan profesional.</p>
            </div>
            <div class="profile-page-hero__meta">
                <div class="profile-meta-pill">
                    <div class="profile-meta-pill__label">Email</div>
                    <div class="profile-meta-pill__value">{{ $user->email }}</div>
                </div>
            </div>
        </header>

        <div class="profile-edit-layout">
            <x-card class="profile-card profile-card--main">
                <div class="profile-card__head">
                    <h2 class="profile-card__title">Informasi Profil</h2>
                    <p class="profile-card__subtitle">Perbarui nama, foto profil, dan data akademik akun Anda.</p>
                </div>

                <form class="profile-form profile-form--account" method="POST" action="{{ route('profile.update') }}"
                    enctype="multipart/form-data"
                    x-data="profilePhotoUploader({ initialUrl: @js($profilePhotoUrl), maxBytes: 2097152 })"
                    x-on:submit="handleSubmit($event)">
                    @csrf
                    <input type="hidden" name="action" value="update_profile">

                    <section class="profile-identity">
                        <div class="profile-photo-uploader">
                            <div class="profile-photo-uploader__preview">
                                <img x-show="previewUrl && !previewLoadFailed" x-bind:src="previewUrl" alt="Preview foto profil"
                                    x-on:error="handlePreviewError()"
                                    class="profile-photo-uploader__image" />
                                <div x-show="!previewUrl || previewLoadFailed" class="profile-photo-uploader__fallback" aria-hidden="true">
                                    {{ $profileInitials ?: '?' }}
                                </div>
                            </div>
                            <div class="profile-photo-uploader__actions">
              <div class="profile-photo-uploader__title">Foto Profil <span class="text-[rgb(var(--c-danger))]">*</span></div>
                                <p class="profile-photo-uploader__copy">Pilih foto persegi agar tampil rapi di akun Anda.
                                </p>
                                <label for="profile_photo"
                                    class="profile-photo-uploader__button profile-photo-uploader__button--field">
                                    <span class="profile-photo-uploader__button-text">Pilih file</span>
                                    <span class="profile-photo-uploader__filename"
                                        x-text="selectedFileName || 'Belum ada file dipilih'"></span>
                                </label>
                                <input id="profile_photo" name="profile_photo" type="file"
                                    accept="image/png,image/jpeg,image/webp" class="sr-only"
                                    x-on:change="handleChange($event)" />
                                <p class="profile-field__hint">PNG, JPG, atau WEBP. Foto akan dipotong otomatis menjadi persegi dan dijaga tetap maksimal 2MB.</p>
                                <p class="profile-field__hint text-[rgb(var(--c-danger))]" x-show="uploadError" x-text="uploadError"></p>
                                <p class="profile-field__hint" x-show="isProcessing">Sedang memproses foto...</p>
                            </div>
                        </div>
                    </section>

                    @php
                        $identityNumberLabel = $user->hasRole('Mahasiswa') ? 'NPM' : 'NIP';
                    @endphp
                    <section class="profile-fields">
                        <div class="profile-field">
                            <x-input name="name" label="Nama" value="{{ old('name', $user->name) }}" required />
                        </div>

                        <div class="profile-field">
                            <x-input name="student_number" :label="$identityNumberLabel"
                                value="{{ old('student_number', $user->student_number) }}" />
                        </div>
                    </section>

                    @if ($isStudentProfile)
                        @php
                            $selectedJurusanValue = (string) old('jurusan_id', (string) ($selectedJurusanId ?? ''));
                            $selectedProdiValue = (string) old('prodi_id', (string) ($selectedProdiId ?? ''));
                            $linkedProdiOptions = $prodiOptions
                                ->map(function ($prodi) {
                                    return [
                                        'id' => (string) $prodi->id,
                                        'name' => $prodi->name,
                                        'parent_id' => (string) $prodi->parent_id,
                                    ];
                                })
                                ->values()
                                ->all();
                        @endphp
                        <section class="profile-fields profile-fields--academic" data-linked-jurusan-prodi
                            data-selected-prodi="{{ $selectedProdiValue }}"
                            data-prodi-options="{{ \Illuminate\Support\Js::encode($linkedProdiOptions) }}">
                            <div class="profile-field">
                                <x-select name="jurusan_id" label="Jurusan" required>
                                    <option value="">Pilih jurusan</option>
                                    @foreach ($jurusanOptions as $jurusan)
                                        <option value="{{ $jurusan->id }}" @selected($selectedJurusanValue === (string) $jurusan->id)>
                                            {{ $jurusan->name }}</option>
                                    @endforeach
                                </x-select>
                            </div>
                            <div class="profile-field">
                                <x-select name="prodi_id" label="Program Studi" required>
                                    <option value="">Pilih jurusan terlebih dahulu</option>
                                </x-select>
                                <p class="profile-field__hint">Pastikan program studi sesuai jurusan yang dipilih.</p>
                            </div>
                        </section>
                    @endif

                    <div class="profile-actions">
                        <x-button type="submit" class="profile-save-btn">Simpan Perubahan</x-button>
                    </div>
                </form>
            </x-card>

            <x-card class="profile-card profile-card--security">
                <div class="profile-card__head">
                    <h2 class="profile-card__title">Keamanan Akun</h2>
                    <p class="profile-card__subtitle">Ganti kata sandi secara berkala untuk menjaga keamanan akun.</p>
                </div>

                <form class="profile-form profile-form--security" method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    <input type="hidden" name="action" value="change_password">
                    <section class="profile-fields">
                        <div class="profile-field">
                            <x-input name="current_password" type="password" label="Kata Sandi Saat Ini" required />
                        </div>
                        <div class="profile-field">
                            <x-input name="new_password" type="password" label="Kata Sandi Baru" required />
                        </div>
                        <div class="profile-field">
                            <x-input name="new_password_confirmation" type="password" label="Konfirmasi Kata Sandi Baru"
                                required />
                            <p class="profile-field__hint">Minimal 8 karakter.</p>
                        </div>
                    </section>
                    <div class="profile-actions">
                        <x-button type="submit" variant="secondary" class="profile-secondary-btn">Ubah Kata
                            Sandi</x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection
