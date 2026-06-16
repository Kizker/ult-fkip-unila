@extends('layouts.public')

@section('title', 'Kritik dan Saran')

@section('content')
    @php
        $user = auth()->user();
        $recaptchaEnabled = (bool) config('services.recaptcha.enabled', false);
        $recaptchaSiteKey = trim((string) config('services.recaptcha.site_key', ''));
        $isEn = app()->getLocale() === 'en';

        $categories = [
            \App\Models\FeedbackMessage::CATEGORY_MASUKAN => 'Masukan',
            \App\Models\FeedbackMessage::CATEGORY_SARAN => 'Saran',
            \App\Models\FeedbackMessage::CATEGORY_KOMPLAIN => 'Komplain',
        ];

        $heroTitle = $isEn ? 'Feedback and Suggestions' : 'Kritik dan Saran';
        $heroSubtitle = $isEn
            ? 'Use this form to submit feedback or report complaints. Every submission is reviewed by ULT admin.'
            : 'Gunakan form ini untuk menyampaikan masukan atau komplain. Setiap pesan akan ditinjau oleh admin ULT.';
        $heroImage = filled($heroBanner?->image_path ?? null)
            ? asset('storage/' . ltrim((string) $heroBanner->image_path, '/'))
            : asset('assets/images/blog/blog-details.png');
    @endphp

    <div class="page-public-feedback page-services-index services-v2" id="feedbackCreatePage">
        <header class="services-v2-hero" style="--services-hero-image:url('{{ $heroImage }}');">
            <div class="services-v2-hero__bg" aria-hidden="true"></div>
            <div class="services-v2-hero__veil" aria-hidden="true"></div>
            <div class="services-v2-hero__mesh" aria-hidden="true"></div>

            <div class="services-v2-hero__inner">
                <div class="services-v2-hero__copy" data-services-reveal>
                    <div class="services-v2-hero__kicker">{{ $isEn ? 'Feedback' : 'Masukan' }}</div>
                    <h1 class="services-v2-hero__title">{{ $heroTitle }}</h1>
                    <p class="services-v2-hero__subtitle">{{ $heroSubtitle }}</p>
                </div>
            </div>

            <a href="#feedback-form-section"
                class="services-v2-hero__scroll"
                data-scroll-to="#feedback-form-section">
                {{ $isEn ? 'Fill the form' : 'Isi formulir' }}
            </a>
        </header>

        <section id="feedback-form-section" class="services-v2-search feedback-public-panel" aria-label="{{ $isEn ? 'Feedback form' : 'Form kritik dan saran' }}" data-services-reveal>
                    <div class="services-v2-search__head">
                        <h2 class="services-v2-search__title">{{ $isEn ? 'Submit Your Message' : 'Sampaikan Pesan Anda' }}</h2>
                        <p class="services-v2-search__hint">
                            {{ $isEn ? 'Initial status is set to new and will be updated manually by admin.' : 'Status awal otomatis baru dan akan diperbarui manual oleh admin.' }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('feedback.store') }}" class="feedback-public-form space-y-5">
                        @csrf

                        <div class="feedback-public-form__grid feedback-public-form__grid--identity">
                            <x-input
                                name="name"
                                :label="$isEn ? 'Name' : 'Nama'"
                                value="{{ old('name', $user?->name) }}"
                                required
                                readonly
                            />
                            <x-input
                                name="email"
                                type="email"
                                :label="$isEn ? 'Email' : 'Email'"
                                value="{{ old('email', $user?->email) }}"
                                required
                                readonly
                            />
                        </div>

                        <div class="feedback-public-form__grid feedback-public-form__grid--meta">
                            <x-select name="category" :label="$isEn ? 'Category' : 'Kategori'" required>
                                <option value="">{{ $isEn ? 'Select category' : 'Pilih kategori' }}</option>
                                @foreach($categories as $value => $label)
                                    <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                                @endforeach
                            </x-select>

                            <x-input
                                name="phone"
                                :label="$isEn ? 'Phone number (optional)' : 'Nomor HP (opsional)'"
                                value="{{ old('phone') }}"
                                placeholder="08xxxxxxxxxx"
                            />
                        </div>

                        <x-textarea name="message" :label="$isEn ? 'Message' : 'Pesan'" rows="8" required>{{ old('message') }}</x-textarea>
                        <p class="feedback-public-note text-xs text-muted -mt-2">
                            {{ $isEn ? 'Minimum 20 characters, maximum 2000 characters.' : 'Minimal 20 karakter, maksimal 2000 karakter.' }}
                        </p>

                        <div class="hidden" aria-hidden="true">
                            <label for="website">Website</label>
                            <input type="text" id="website" name="website" value="{{ old('website') }}" tabindex="-1" autocomplete="off">
                        </div>

                        @if($recaptchaEnabled)
                            @if($recaptchaSiteKey !== '')
                                <div class="feedback-public-recaptcha space-y-1">
                                    <div class="g-recaptcha" data-sitekey="{{ $recaptchaSiteKey }}"></div>
                                    @error('g-recaptcha-response')
                                        <p class="text-sm text-[rgb(var(--c-danger))]">{{ $message }}</p>
                                    @enderror
                                </div>
                            @else
                                <div class="text-sm text-[rgb(var(--c-danger))]">
                                    reCAPTCHA aktif tetapi `RECAPTCHA_SITE_KEY` belum diatur.
                                </div>
                            @endif
                        @endif

                        <div class="feedback-public-actions flex flex-wrap gap-2 justify-end">
                            <x-button href="{{ route('home') }}" variant="ghost">{{ $isEn ? 'Back' : 'Kembali' }}</x-button>
                            <x-button type="submit">{{ $isEn ? 'Submit' : 'Kirim' }}</x-button>
                        </div>
                    </form>
        </section>
    </div>

    @if($recaptchaEnabled && $recaptchaSiteKey !== '')
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
@endsection
