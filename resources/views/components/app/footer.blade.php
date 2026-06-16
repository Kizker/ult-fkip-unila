<footer class="app-footer" aria-label="Footer">
  <div class="app-footer__inner">
    <div class="app-footer__left">
      <a href="{{ route('home') }}" class="app-footer__brand">
        <span class="app-footer__logos" style="display:inline-flex;align-items:center;flex-direction:row;flex-wrap:nowrap;gap:.42rem;white-space:nowrap;line-height:0;">
          <img
            src="{{ asset('icons/unila.png') }}"
            alt="Logo Universitas Lampung"
            class="app-footer__logo app-footer__logo--unila"
            style="display:block;"
            loading="lazy"
            decoding="async"
          />
          <img
            src="{{ asset('icons/logo.png') }}"
            alt="Logo FKIP Unila"
            class="app-footer__logo app-footer__logo--fkip"
            style="display:block;"
            loading="lazy"
            decoding="async"
          />
        </span>
        <span class="app-footer__mark">ULT</span>
        <span class="app-footer__name">FKIP Unila</span>
      </a>
      <div class="app-footer__tagline">{{ __('app.footer_tagline') }}</div>
    </div>

    <nav class="app-footer__right" aria-label="Tautan">
      <a class="app-footer__link" href="{{ route('services.index') }}">{{ __('app.services') }}</a>
      <a class="app-footer__link" href="{{ route('announcements.index') }}">{{ __('app.announcements') }}</a>
      <a class="app-footer__link" href="{{ route('about') }}">{{ __('app.about') }}</a>
    </nav>
  </div>

  <div class="app-footer__bottom">
    <div class="app-footer__copy">
      &copy; {{ now()->year }} Andricha Dea Mitra. All rights reserved.
    </div>
  </div>
</footer>
