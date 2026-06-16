@extends('layouts.public')
@section('title', 'Reset Password')
@section('content')
@php
  $isEn = app()->getLocale() === 'en';
@endphp
<div class="page-auth-login page-auth-reset-password">
  <div class="auth-shell auth-shell--single auth-shell--login-single">
    <x-card class="auth-card auth-card--login-single">
      <div class="auth-card__header auth-card__header--login">
        <h1 class="auth-title">{{ $isEn ? 'Create New Password' : 'Buat Password Baru' }}</h1>
        <p class="auth-subtitle">{{ $isEn ? 'Use a strong password that is still easy for you to remember.' : 'Gunakan password yang kuat dan mudah diingat.' }}</p>
      </div>

      <form class="auth-form" method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-input name="email" type="email" label="Email" value="{{ old('email') }}" required />
        <div class="auth-password-field">
          <label class="text-sm font-medium" for="password">
            {{ $isEn ? 'New Password' : 'Password baru' }}<span class="text-[rgb(var(--c-danger))]" aria-hidden="true"> *</span>
          </label>
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
              aria-label="{{ $isEn ? 'Show password' : 'Tampilkan password' }}"
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
            <span class="auth-toolbtn__text">{{ $isEn ? 'Generate password' : 'Password otomatis' }}</span>
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
            <span class="auth-toolbtn__text" data-auth-copy-text>{{ $isEn ? 'Copy' : 'Salin' }}</span>
          </button>

          <div class="auth-pass-hint" data-auth-generated-hint hidden>{{ $isEn ? 'Password generated and filled automatically.' : 'Password dibuat dan diisikan otomatis.' }}</div>
        </div>
        <div class="auth-password-field">
          <label class="text-sm font-medium" for="password_confirmation">
            {{ $isEn ? 'Confirm Password' : 'Konfirmasi' }}<span class="text-[rgb(var(--c-danger))]" aria-hidden="true"> *</span>
          </label>
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
              aria-label="{{ $isEn ? 'Show password' : 'Tampilkan password' }}"
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
          <x-button type="submit" class="w-full justify-center">{{ $isEn ? 'Save' : 'Simpan' }}</x-button>
        </div>
      </form>

      <div class="auth-login-register">
        <a class="auth-link auth-link--muted" href="{{ route('login') }}">{{ $isEn ? 'Back to login' : 'Kembali ke login' }}</a>
      </div>
    </x-card>
  </div>
</div>
@endsection
