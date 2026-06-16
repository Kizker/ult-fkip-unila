@extends('layouts.public')
@section('title', 'Forgot Password')
@section('content')
@php
  $isEn = app()->getLocale() === 'en';
@endphp
<div class="page-auth-login page-auth-forgot-password">
  <div class="auth-shell auth-shell--single auth-shell--login-single">
    <x-card class="auth-card auth-card--login-single">
      <div class="auth-card__header auth-card__header--login">
        <h1 class="auth-title">{{ $isEn ? 'Reset Password' : 'Reset Password' }}</h1>
        <p class="auth-subtitle">{{ $isEn ? 'Enter your email and we will send you a link to reset your password.' : 'Masukkan email, kami akan mengirim tautan untuk mengatur ulang password.' }}</p>
      </div>

      <form class="auth-form" method="POST" action="{{ route('password.email') }}">
        @csrf
        <x-input name="email" type="email" label="Email" value="{{ old('email') }}" required />
        <div class="auth-actions auth-actions--stack">
          <x-button type="submit" class="w-full justify-center">{{ $isEn ? 'Send reset link' : 'Kirim link' }}</x-button>
        </div>
      </form>

      <div class="auth-login-register">
        <a class="auth-link" href="{{ route('login') }}">&larr; {{ $isEn ? 'Back to login' : 'Kembali ke login' }}</a>
      </div>
    </x-card>
  </div>
</div>
@endsection
