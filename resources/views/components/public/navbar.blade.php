@php
  $publicNavItems = [
      [
          'label' => __('app.services'),
          'href' => route('services.index'),
          'active' => request()->routeIs('services.*'),
      ],
      [
          'label' => __('app.announcements'),
          'href' => route('announcements.index'),
          'active' => request()->routeIs('announcements.*'),
      ],
      [
          'label' => __('app.blog'),
          'href' => route('blog.index'),
          'active' => request()->routeIs('blog.*'),
      ],
      [
          'label' => __('app.user_guides'),
          'href' => route('user_guides.index'),
          'active' => request()->routeIs('user_guides.*'),
      ],
      [
          'label' => __('app.feedback'),
          'href' => route('feedback.create'),
          'active' => request()->routeIs('feedback.*'),
      ],
      [
          'label' => __('app.about'),
          'href' => route('about'),
          'active' => request()->routeIs('about'),
      ],
  ];
@endphp

<header class="public-header" data-public-nav>
  <div class="public-container public-header__inner">
    <a href="{{ route('home') }}" class="public-brand" aria-label="Beranda ULT FKIP Unila">
      <span class="public-brand__logos" style="display:inline-flex;align-items:center;flex-direction:row;flex-wrap:nowrap;gap:.42rem;white-space:nowrap;line-height:0;">
        <img
          src="{{ asset('icons/unila.png') }}"
          alt="Logo Universitas Lampung"
          class="public-brand__logo public-brand__logo--unila"
          style="display:block;"
          loading="lazy"
          decoding="async"
        />
        <img
          src="{{ asset('icons/logo.png') }}"
          alt="Logo FKIP Unila"
          class="public-brand__logo public-brand__logo--fkip"
          style="display:block;"
          loading="lazy"
          decoding="async"
        />
      </span>
      <span class="public-brand__mark">ULT</span>
      <span class="public-brand__name public-brand__brand-desktop">FKIP Unila</span>
    </a>

    <nav class="public-nav public-nav--desktop" aria-label="Navigasi utama">
      @foreach ($publicNavItems as $item)
        <a
          class="public-navlink {{ $item['active'] ? 'is-active' : '' }}"
          href="{{ $item['href'] }}"
          @if ($item['active']) aria-current="page" @endif
        >
          {{ $item['label'] }}
        </a>
      @endforeach
    </nav>

    <div class="public-actions public-actions--desktop" aria-label="Aksi cepat">
      <x-button class="pbtn" variant="{{ app()->getLocale() === 'id' ? 'primary' : 'ghost' }}" href="{{ route('locale.set','id') }}">ID</x-button>
      <x-button class="pbtn" variant="{{ app()->getLocale() === 'en' ? 'primary' : 'ghost' }}" href="{{ route('locale.set','en') }}">EN</x-button>

      <x-button
        class="pbtn public-theme-toggle"
        variant="ghost"
        type="button"
        data-action="toggle-dark"
        aria-label="Ubah tema"
      >
        <span class="public-theme-toggle__icon" aria-hidden="true">
          <svg class="public-icon public-icon--sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 18a6 6 0 1 0 0-12 6 6 0 0 0 0 12Z"></path>
            <path d="M12 2v2"></path><path d="M12 20v2"></path>
            <path d="M4.93 4.93l1.41 1.41"></path><path d="M17.66 17.66l1.41 1.41"></path>
            <path d="M2 12h2"></path><path d="M20 12h2"></path>
            <path d="M6.34 17.66l-1.41 1.41"></path><path d="M19.07 4.93l-1.41 1.41"></path>
          </svg>
          <svg class="public-icon public-icon--moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"></path>
          </svg>
        </span>
        <span class="sr-only" data-theme-label>Toggle theme</span>
      </x-button>
    </div>

    <div class="public-auth public-auth--desktop" aria-label="Akun">
      @auth
        @php
          $user = auth()->user();
          $adminPerms = [
            'requests.view_any','requests.view_unit','requests.review_ult','requests.process_unit',
            'approvals.unit.sign','approvals.faculty.sign','document_numbers.issue',
            'services.manage','cms.manage','site_settings.manage','academics.manage','users.manage','audit_logs.view',
            'doc_services.manage','doc_services.publish','doc_templates.upload','doc_placeholders.manage','doc_signers.manage','feedbacks.manage',
            'doc_requests.gate','doc_requests.assemble'
          ];
          $dashboardHref = route('home');
          if ($user?->hasRole('Superadmin')) {
            $dashboardHref = route('admin.dashboard');
          } elseif ($user?->can('requests.view_own')) {
            $dashboardHref = route('student.dashboard');
          } elseif ($user?->canAny($adminPerms)) {
            $dashboardHref = route('admin.dashboard');
          } elseif ($user?->can('doc_signoffs.decide')) {
            $dashboardHref = route('signer.requests.inbox');
          }
        @endphp
        <x-button class="pbtn public-auth__primary" href="{{ $dashboardHref }}">{{ __('app.dashboard') }}</x-button>
        <form action="{{ route('logout') }}" method="POST">
          @csrf
          <x-button class="pbtn" variant="secondary" type="submit">{{ __('app.logout') }}</x-button>
        </form>
      @else
        <x-button class="pbtn" variant="secondary" href="{{ route('login') }}">{{ __('app.login') }}</x-button>
        <x-button class="pbtn public-auth__primary" href="{{ route('register') }}">{{ __('app.register') }}</x-button>
      @endauth
    </div>

    <button
      class="public-menu-btn"
      type="button"
      data-action="nav-toggle"
      aria-expanded="false"
      aria-controls="public-nav-panel"
    >
      <span class="sr-only">Buka menu</span>
      <svg class="public-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path d="M4 6h16"></path>
        <path d="M4 12h16"></path>
        <path d="M4 18h16"></path>
      </svg>
    </button>
  </div>

  <div id="public-nav-panel" class="public-nav-panel" aria-hidden="true">
    <div class="public-container public-nav-panel__inner">
      <nav class="public-nav public-nav--mobile" aria-label="Navigasi utama">
        @foreach ($publicNavItems as $item)
          <a
            class="public-navlink {{ $item['active'] ? 'is-active' : '' }}"
            href="{{ $item['href'] }}"
            @if ($item['active']) aria-current="page" @endif
          >
            {{ $item['label'] }}
          </a>
        @endforeach
      </nav>

      <div class="public-divider" role="separator" aria-hidden="true"></div>

      <div class="public-actions public-actions--mobile" aria-label="Preferensi">
        <x-button class="pbtn" variant="{{ app()->getLocale() === 'id' ? 'primary' : 'ghost' }}" href="{{ route('locale.set','id') }}">ID</x-button>
        <x-button class="pbtn" variant="{{ app()->getLocale() === 'en' ? 'primary' : 'ghost' }}" href="{{ route('locale.set','en') }}">EN</x-button>
        <x-button
          class="pbtn public-theme-toggle"
          variant="ghost"
          type="button"
          data-action="toggle-dark"
          aria-label="Ubah tema"
        >
          <span class="public-theme-toggle__icon" aria-hidden="true">
            <svg class="public-icon public-icon--sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 18a6 6 0 1 0 0-12 6 6 0 0 0 0 12Z"></path>
              <path d="M12 2v2"></path><path d="M12 20v2"></path>
              <path d="M4.93 4.93l1.41 1.41"></path><path d="M17.66 17.66l1.41 1.41"></path>
              <path d="M2 12h2"></path><path d="M20 12h2"></path>
              <path d="M6.34 17.66l-1.41 1.41"></path><path d="M19.07 4.93l-1.41 1.41"></path>
            </svg>
            <svg class="public-icon public-icon--moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"></path>
            </svg>
          </span>
          <span class="sr-only" data-theme-label>Toggle theme</span>
        </x-button>
      </div>

      <div class="public-auth public-auth--mobile" aria-label="Akun">
        @auth
          @php
            $user = auth()->user();
            $adminPerms = [
              'requests.view_any','requests.view_unit','requests.review_ult','requests.process_unit',
              'approvals.unit.sign','approvals.faculty.sign','document_numbers.issue',
              'services.manage','cms.manage','site_settings.manage','academics.manage','users.manage','audit_logs.view',
              'doc_services.manage','doc_services.publish','doc_templates.upload','doc_placeholders.manage','doc_signers.manage','feedbacks.manage',
              'doc_requests.gate','doc_requests.assemble'
            ];
            $dashboardHref = route('home');
            if ($user?->hasRole('Superadmin')) {
              $dashboardHref = route('admin.dashboard');
            } elseif ($user?->can('requests.view_own')) {
              $dashboardHref = route('student.dashboard');
            } elseif ($user?->canAny($adminPerms)) {
              $dashboardHref = route('admin.dashboard');
            } elseif ($user?->can('doc_signoffs.decide')) {
              $dashboardHref = route('signer.requests.inbox');
            }
          @endphp
          <x-button class="pbtn public-auth__full public-auth__primary" href="{{ $dashboardHref }}">{{ __('app.dashboard') }}</x-button>
          <form action="{{ route('logout') }}" method="POST">
            @csrf
            <x-button class="pbtn public-auth__full" variant="secondary" type="submit">{{ __('app.logout') }}</x-button>
          </form>
        @else
          <x-button class="pbtn public-auth__full" variant="secondary" href="{{ route('login') }}">{{ __('app.login') }}</x-button>
          <x-button class="pbtn public-auth__full public-auth__primary" href="{{ route('register') }}">{{ __('app.register') }}</x-button>
        @endauth
      </div>
    </div>
  </div>
</header>
