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
</head>
<body class="min-h-screen bg-slate-50 dark:bg-slate-900">
    <div class="page-public-site">
        <a class="public-skip-link" href="#main-content">Lewati ke konten utama</a>

        <x-public.navbar />

        <main id="main-content" class="public-main" role="main">
            <div class="public-container">
                <div class="public-flash">
                    <x-flash
                        :show-validation="($flashShowValidation ?? true)"
                        :show-validation-list="($flashShowValidationList ?? false)"
                    />
                </div>

                @yield('content')
            </div>
        </main>

        <x-public.footer />

        <x-toast />
        
        <button id="backToTopBtn" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="fixed bottom-6 right-6 p-3 rounded-full bg-primary text-white shadow-lg hover:opacity-90 transition-all duration-300 z-50 flex items-center justify-center transform opacity-0 pointer-events-none translate-y-4" aria-label="Kembali ke atas">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                <path fill-rule="evenodd" d="M11.47 2.47a.75.75 0 011.06 0l7.5 7.5a.75.75 0 11-1.06 1.06l-6.22-6.22V21a.75.75 0 01-1.5 0V4.81l-6.22 6.22a.75.75 0 11-1.06-1.06l7.5-7.5z" clip-rule="evenodd" />
            </svg>
        </button>

        <script>
            window.addEventListener('scroll', function() {
                const btn = document.getElementById('backToTopBtn');
                if (window.scrollY > 300) {
                    btn.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-4');
                    btn.classList.add('opacity-100', 'pointer-events-auto', 'translate-y-0');
                } else {
                    btn.classList.remove('opacity-100', 'pointer-events-auto', 'translate-y-0');
                    btn.classList.add('opacity-0', 'pointer-events-none', 'translate-y-4');
                }
            });
        </script>
    </div>
</body>
</html>
