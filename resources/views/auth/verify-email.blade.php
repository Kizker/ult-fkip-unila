@extends('layouts.public')
@section('title', 'Verify Email')
@section('content')
@php
  $isEn = app()->getLocale() === 'en';
@endphp
<div class="page-auth-login page-auth-verify-email">
  <div class="auth-shell auth-shell--single auth-shell--login-single">
    <x-card class="auth-card auth-card--login-single">
      <div class="auth-card__header auth-card__header--login">
        <h1 class="auth-title">{{ $isEn ? 'Verify Email' : 'Verifikasi Email' }}</h1>
        <p class="auth-subtitle">{{ $isEn ? 'Check your inbox or spam folder, then click the verification link to activate your account.' : 'Cek inbox/spam email kamu dan klik tautan verifikasi agar akun aktif.' }}</p>
      </div>

      <div class="auth-actions auth-actions--stack">
        <form method="POST" action="{{ route('verification.send') }}">
          @csrf
          <x-button type="submit" variant="secondary" class="w-full justify-center">{{ $isEn ? 'Resend verification email' : 'Kirim ulang' }}</x-button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <x-button type="submit" variant="ghost" class="w-full justify-center">{{ $isEn ? 'Logout' : 'Logout' }}</x-button>
        </form>
      </div>
    </x-card>
  </div>
</div>
@endsection
