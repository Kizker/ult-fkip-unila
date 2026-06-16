<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#7c3aed">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('icons/icon-192.png') }}" sizes="192x192">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <title>@yield('title', 'Web ULT FKIP Unila')</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    <style>
        @media (min-width: 1280px) {
            .page-app-shell.is-admin .app-shell-main {
                padding-left: 18rem;
            }
        }
    </style>
</head>
@php
  $isAdmin = request()->is('admin') || request()->is('admin/*') || request()->routeIs('admin.*');
  $hasUnreadNotifications = auth()->check() ? auth()->user()->unreadNotifications()->exists() : false;
  $isProfilePage = request()->routeIs('profile.edit');
@endphp
<body class="min-h-screen bg-white dark:bg-zinc-950">
    <div class="page-app-shell {{ ($isAdmin && auth()->check()) ? 'is-admin' : '' }}" data-app-shell x-data>
        @if($isAdmin && auth()->check())
            <div class="app-shell-admin flex">
                <x-app.sidebar :has-unread-notifications="$hasUnreadNotifications" />
                <div class="app-shell-main has-public-footer flex-1 min-w-0">
                    <x-app.topbar :has-unread-notifications="$hasUnreadNotifications" />
                    <div class="app-shell__container app-shell__content {{ $isProfilePage ? 'public-container' : '' }}">
                        <x-flash
                            :show-validation="($flashShowValidation ?? true)"
                            :show-validation-list="($flashShowValidationList ?? false)"
                        />

                        @yield('content')
                    </div>
                    <x-public.footer />
                </div>
            </div>
        @else
            <main class="app-shell-main has-public-footer">
                <x-app.topbar :has-unread-notifications="$hasUnreadNotifications" />
                <div class="app-shell__container app-shell__content {{ $isProfilePage ? 'public-container' : '' }}">
                    <x-flash
                        :show-validation="($flashShowValidation ?? true)"
                        :show-validation-list="($flashShowValidationList ?? false)"
                    />

                    @yield('content')
                </div>
                <x-public.footer />
            </main>
        @endif

        <x-toast />
    </div>
</body>
</html>
