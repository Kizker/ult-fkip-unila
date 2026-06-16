@extends('layouts.public')
@section('title', __('app.login'))
@section('content')
@php
  $isEn = app()->getLocale() === 'en';
@endphp
<div class="page-auth-login">
  <div class="auth-shell auth-shell--single auth-shell--login-single">
    @php
      $googleEnabled = (bool) (config('services.google.client_id') && config('services.google.client_secret'));
    @endphp
    <x-card class="auth-card auth-card--login-single">
      <div class="auth-card__header auth-card__header--login">
        <h1 class="auth-title">{{ __('app.login') }}</h1>
        <p class="auth-subtitle">
          {{ $isEn
              ? 'Sign in to submit services, monitor statuses, and view your request history.'
              : 'Masuk untuk mengajukan layanan, memantau status, dan melihat riwayat pengajuan.' }}
        </p>
      </div>

      <form class="auth-form" method="POST" action="{{ route('login') }}">
        @csrf
        <x-input name="email" type="email" label="Email" value="{{ old('email') }}" required />
        <div class="auth-password-field">
          <label class="text-sm font-medium" for="password">{{ $isEn ? 'Password' : 'Password' }}</label>
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
        </div>

        <div class="auth-login-meta">
          <label class="auth-check">
            <input type="checkbox" name="remember" class="auth-check__box">
            <span>{{ $isEn ? 'Remember me' : 'Ingat saya' }}</span>
          </label>
          <a class="auth-link auth-link--muted" href="{{ route('password.request') }}">
            {{ $isEn ? 'Forgot password?' : 'Lupa password?' }}
          </a>
        </div>

        <div class="auth-actions auth-actions--stack">
          <x-button type="submit" class="w-full justify-center">{{ __('app.login') }}</x-button>

          <div class="auth-login-divider" aria-hidden="true">
            <span>{{ $isEn ? 'or' : 'atau' }}</span>
          </div>

          @if($googleEnabled)
            <x-button
              href="{{ route('auth.google.redirect') }}"
              variant="secondary"
              class="w-full justify-center"
            >
              <img src="{{ asset('icons/google.svg') }}" alt="" class="h-5 w-5" />
              <span>{{ $isEn ? 'Sign in with Google' : 'Masuk dengan Google' }}</span>
            </x-button>
          @else
            <x-button
              type="button"
              variant="secondary"
              class="w-full justify-center opacity-60"
              disabled
            >
              <img src="{{ asset('icons/google.svg') }}" alt="" class="h-5 w-5" />
              <span>{{ $isEn ? 'Sign in with Google' : 'Masuk dengan Google' }}</span>
            </x-button>
          @endif
        </div>
      </form>

      <div class="auth-login-register">
        <span>{{ $isEn ? "Don't have an account?" : 'Belum punya akun?' }}</span>
        <a class="auth-link" href="{{ route('register') }}">{{ $isEn ? 'Register' : 'Daftar' }}</a>
      </div>
    </x-card>
  </div>
</div>
@endsection
