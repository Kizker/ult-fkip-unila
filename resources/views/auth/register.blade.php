@extends('layouts.public')
@section('title', __('app.register'))
@section('content')
@php
  $isEn = app()->getLocale() === 'en';
@endphp
<div class="page-auth-login page-auth-register">
  <div class="auth-shell auth-shell--single auth-shell--login-single">
    <x-card class="auth-card auth-card--login-single">
      <div class="auth-card__header auth-card__header--login">
        <h1 class="auth-title">{{ __('app.register') }}</h1>
        <p class="auth-subtitle">
          {{ $isEn
              ? 'Create a student or lecturer account. New accounts must verify email before use.'
              : 'Buat akun Mahasiswa atau Dosen. Akun baru perlu verifikasi email sebelum digunakan.' }}
        </p>
      </div>

      <form class="auth-form" method="POST" action="{{ route('register') }}" enctype="multipart/form-data"
        x-data="{ ...profilePhotoUploader({ initialUrl: null, maxBytes: 2097152 }), accountRole: @js(old('account_role', 'Mahasiswa')) }"
        x-on:submit="handleSubmit($event)">
        @csrf
        <x-input name="name" label="Nama" value="{{ old('name') }}" required />
        <x-input name="email" type="email" label="Email" value="{{ old('email') }}" required />
        <x-select name="account_role" label="Jenis Akun" required x-model="accountRole">
          <option value="Mahasiswa" @selected(old('account_role', 'Mahasiswa') === 'Mahasiswa')>Mahasiswa</option>
          <option value="Dosen" @selected(old('account_role') === 'Dosen')>Dosen</option>
        </x-select>
        <div class="space-y-1">
          <label class="text-sm font-medium" for="student_number">
            <span x-text="accountRole === 'Dosen' ? 'NIP' : 'NPM'"></span>
            <span class="text-[rgb(var(--c-danger))]" aria-hidden="true"> *</span>
          </label>
          <input
            id="student_number"
            name="student_number"
            type="text"
            value="{{ old('student_number') }}"
            required
            class="h-11 w-full rounded-xl border bg-white/70 px-4 text-sm leading-5 focus:outline-none focus-visible:ring-2 focus-visible:ring-[rgb(var(--focus-ring))] dark:bg-zinc-900 {{ $errors->has('student_number') ? 'border-[rgb(var(--c-danger))] ring-2 ring-[rgb(var(--c-danger)/.15)]' : 'border-[rgb(var(--c-border))]' }}"
            x-bind:placeholder="accountRole === 'Dosen' ? 'Masukkan NIP' : 'Masukkan NPM'"
          />
          <p class="text-xs text-muted" x-text="accountRole === 'Dosen' ? 'Gunakan NIP dosen yang aktif.' : 'Gunakan NPM mahasiswa yang aktif.'"></p>
          @error('student_number')
            <p class="text-sm text-[rgb(var(--c-danger))]">{{ $message }}</p>
          @enderror
        </div>
        @php
          $linkedProdiOptions = $prodiOptions->map(function ($prodi) {
              return [
                  'id' => (string) $prodi->id,
                  'name' => $prodi->name,
                  'parent_id' => (string) $prodi->parent_id,
              ];
          })->values()->all();
        @endphp
        <div
          class="space-y-4"
          data-linked-jurusan-prodi
          data-selected-prodi="{{ (string) old('prodi_id', '') }}"
          data-prodi-options="{{ \Illuminate\Support\Js::encode($linkedProdiOptions) }}"
        >
          <div>
            <x-select
              name="jurusan_id"
              label="Jurusan"
              class="bg-white/70 dark:bg-zinc-900"
              required
            >
              <option value="">Pilih jurusan</option>
              @foreach($jurusanOptions as $jurusan)
                <option value="{{ $jurusan->id }}" @selected((string) old('jurusan_id') === (string) $jurusan->id)>{{ $jurusan->name }}</option>
              @endforeach
            </x-select>
          </div>
          <div>
            <x-select
              name="prodi_id"
              label="Program Studi"
              required
            >
              <option value="">Pilih jurusan terlebih dahulu</option>
            </x-select>
            @error('prodi_id')
              <p class="mt-1 text-sm text-[rgb(var(--c-danger))]">{{ $message }}</p>
            @enderror
          </div>
        </div>
        <div class="space-y-3">
          <label class="text-sm font-medium" for="profile_photo">Foto Profil</label>
          <div class="auth-register-photo rounded-3xl border border-[rgb(var(--c-border))] bg-zinc-50/90 p-4 shadow-sm dark:bg-zinc-900/85">
            <div class="auth-register-photo__row flex items-center gap-4">
              <div class="auth-register-photo__preview h-24 w-24 shrink-0 overflow-hidden rounded-2xl border border-[rgb(var(--c-border))] bg-white shadow-sm dark:bg-zinc-950">
              <template x-if="previewUrl">
                <img :src="previewUrl" alt="Preview foto profil" class="h-full w-full object-cover" />
              </template>
              <template x-if="!previewUrl">
                <div class="flex h-full w-full flex-col items-center justify-center gap-1 text-zinc-500 dark:text-zinc-400">
                  <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M4 7a3 3 0 0 1 3-3h10a3 3 0 0 1 3 3v10a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V7Z"></path>
                    <path d="M8.5 10.5h.01"></path>
                    <path d="m7 16 3.5-3.5 2.5 2.5 2-2 2.5 3"></path>
                  </svg>
                  <span class="text-[11px] font-medium">Preview 1:1</span>
                </div>
              </template>
              </div>

              <div class="auth-register-photo__body min-w-0 flex-1 space-y-3">
                <div class="auth-register-photo__picker rounded-2xl border border-[rgb(var(--c-border))] bg-white p-3 shadow-sm dark:bg-zinc-950">
                  <x-file-input
                    id="profile_photo"
                    name="profile_photo"
                    accept="image/png,image/jpeg,image/webp"
                    required
                    x-on:change="handleChange($event)"
                  />
                </div>

                <div class="auth-register-photo__hint space-y-1 px-1">
                  <p class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Foto akan dipotong otomatis menjadi persegi</p>
                  <p class="mt-1 text-xs leading-5 text-zinc-500 dark:text-zinc-400">Format PNG, JPG, atau WEBP. Sistem akan menyesuaikan foto agar tetap maksimal 2MB sebelum dikirim.</p>
                  <p class="text-xs leading-5 text-[rgb(var(--c-danger))]" x-show="uploadError" x-text="uploadError"></p>
                  <p class="text-xs leading-5 text-zinc-500 dark:text-zinc-400" x-show="isProcessing">Sedang memproses foto...</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="auth-password-field">
          <label class="text-sm font-medium" for="password">Password</label>
          <div class="auth-password-control">
            <input
              id="password"
              name="password"
              type="password"
              class="auth-password-input w-full rounded-xl border border-[rgb(var(--c-border))] bg-white/70 px-4 text-sm leading-5 focus:outline-none focus-visible:ring-2 focus-visible:ring-[rgb(var(--focus-ring))] dark:bg-zinc-900"
              required
            />
            <button
              type="button"
              class="auth-password-toggle"
              data-auth-action="toggle-password"
              data-target="password"
              aria-pressed="false"
              aria-label="Tampilkan password"
            >
              <span class="auth-toolbtn__icon" aria-hidden="true">
                <svg class="auth-icon auth-icon--eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                  <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"></path>
                </svg>
                <svg class="auth-icon auth-icon--eyeoff" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M10.58 10.58A3 3 0 0 0 12 15a3 3 0 0 0 2.12-.88"></path>
                  <path d="M16.42 16.42C14.98 17.4 13.28 18 12 18 5.5 18 2 12 2 12a18.76 18.76 0 0 1 5.58-6.42"></path>
                  <path d="M9.88 5.12C10.57 4.86 11.28 4.7 12 4.7c6.5 0 10 7.3 10 7.3a18.56 18.56 0 0 1-3.17 4.67"></path>
                  <path d="M3 3l18 18"></path>
                </svg>
              </span>
            </button>
          </div>
          @error('password')
            <p class="text-sm text-[rgb(var(--c-danger))]">{{ $message }}</p>
          @enderror
        </div>
        <div class="auth-field-tools" data-auth-tools-for="password">
          <button
            type="button"
            class="auth-toolbtn auth-toolbtn--primary"
            data-auth-action="generate-password"
            data-target="password"
          >
            <span class="auth-toolbtn__icon" aria-hidden="true">
              <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2l1.2 4.4L18 8l-4.8 1.6L12 14l-1.2-4.4L6 8l4.8-1.6L12 2Z"></path>
                <path d="M5 14l.7 2.6L8 17.3l-2.3.7L5 20l-.7-2.6L2 16.6l2.3-.7L5 14Z"></path>
              </svg>
            </span>
            <span class="auth-toolbtn__text">Password otomatis</span>
          </button>

          <button
            type="button"
            class="auth-toolbtn auth-toolbtn--ghost"
            data-auth-action="copy-password"
            data-target="password"
          >
            <span class="auth-toolbtn__icon" aria-hidden="true">
              <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 9h10v10H9z"></path>
                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
              </svg>
            </span>
            <span class="auth-toolbtn__text" data-auth-copy-text>Salin</span>
          </button>

          <div class="auth-pass-hint" data-auth-generated-hint hidden>Password dibuat dan diisikan otomatis.</div>
        </div>
        <div class="auth-password-field">
          <label class="text-sm font-medium" for="password_confirmation">Konfirmasi Password</label>
          <div class="auth-password-control">
            <input
              id="password_confirmation"
              name="password_confirmation"
              type="password"
              class="auth-password-input w-full rounded-xl border border-[rgb(var(--c-border))] bg-white/70 px-4 text-sm leading-5 focus:outline-none focus-visible:ring-2 focus-visible:ring-[rgb(var(--focus-ring))] dark:bg-zinc-900"
              required
            />
            <button
              type="button"
              class="auth-password-toggle"
              data-auth-action="toggle-password"
              data-target="password_confirmation"
              aria-pressed="false"
              aria-label="Tampilkan password"
            >
              <span class="auth-toolbtn__icon" aria-hidden="true">
                <svg class="auth-icon auth-icon--eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                  <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"></path>
                </svg>
                <svg class="auth-icon auth-icon--eyeoff" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M10.58 10.58A3 3 0 0 0 12 15a3 3 0 0 0 2.12-.88"></path>
                  <path d="M16.42 16.42C14.98 17.4 13.28 18 12 18 5.5 18 2 12 2 12a18.76 18.76 0 0 1 5.58-6.42"></path>
                  <path d="M9.88 5.12C10.57 4.86 11.28 4.7 12 4.7c6.5 0 10 7.3 10 7.3a18.56 18.56 0 0 1-3.17 4.67"></path>
                  <path d="M3 3l18 18"></path>
                </svg>
              </span>
            </button>
          </div>
          @error('password_confirmation')
            <p class="text-sm text-[rgb(var(--c-danger))]">{{ $message }}</p>
          @enderror
        </div>

        <div class="auth-actions auth-actions--stack">
          <x-button type="submit" class="w-full justify-center">{{ __('app.register') }}</x-button>
        </div>
      </form>

      <div class="auth-login-register">
        <span>{{ $isEn ? 'Already have an account?' : 'Sudah punya akun?' }}</span>
        <a class="auth-link" href="{{ route('login') }}">{{ $isEn ? 'Login' : 'Masuk' }}</a>
      </div>
    </x-card>
  </div>
</div>
@endsection
