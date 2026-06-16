@extends('layouts.public')

@section('title', app()->getLocale() === 'en' ? ($service->title_en ?? $service->title_id) : $service->title_id)

@section('content')
    @php
        $isEn = app()->getLocale() === 'en';
        $serviceTitle = $isEn ? ($service->title_en ?? $service->title_id) : $service->title_id;
        $serviceSummary = $isEn ? ($service->summary_en ?? $service->summary_id) : $service->summary_id;
        $serviceSummary = filled($serviceSummary)
            ? $serviceSummary
            : ($isEn
                ? 'Digital service available for online submission.'
                : 'Layanan digital yang dapat diajukan secara online.');
        $heroImage = filled($heroBanner?->image_path ?? null)
            ? asset('storage/' . ltrim((string) $heroBanner->image_path, '/'))
            : asset('assets/images/blog/blog-details.png');
    @endphp

    <div class="page-services-show page-services-index services-v2" id="serviceShowPage">
        <header class="services-v2-hero services-v2-hero--show" style="--services-hero-image:url('{{ $heroImage }}');">
            <div class="services-v2-hero__bg" aria-hidden="true"></div>
            <div class="services-v2-hero__veil" aria-hidden="true"></div>
            <div class="services-v2-hero__mesh" aria-hidden="true"></div>

            <div class="services-v2-hero__inner">
                <div class="services-v2-hero__copy">
                    <div class="services-v2-hero__kicker">{{ $isEn ? 'Service Detail' : 'Detail Layanan' }}</div>
                    <h1 class="services-v2-hero__title">{{ $serviceTitle }}</h1>
                    <p class="services-v2-hero__subtitle">{{ $serviceSummary }}</p>
                </div>
            </div>

            <a href="#service-show-content"
                class="services-v2-hero__scroll"
                data-scroll-to="#service-show-content">
                {{ $isEn ? 'View details' : 'Lihat detail' }}
            </a>
        </header>

        <section id="service-show-content" class="service-show-main" aria-label="{{ $isEn ? 'Service details' : 'Detail layanan' }}">
            <div class="service-show-main__back page-back" data-service-show-reveal>
                <a class="back-link" href="{{ route('services.index') }}">
                    <span class="service-show-back-link__icon" aria-hidden="true">&larr;</span>
                    <span class="service-show-back-link__text">{{ $isEn ? 'Back to services' : 'Kembali ke layanan' }}</span>
                </a>
            </div>

            <x-card class="content-card service-show-shell" data-service-show-reveal>
                <div class="service-show-head">
                    <div class="service-show-head__copy">
                        <div class="card-meta">{{ $isEn ? 'Service Overview' : 'Ringkasan Layanan' }}</div>
                        <h2 class="page-title page-title--compact">{{ $serviceTitle }}</h2>
                        <p class="page-subtitle page-subtitle--compact">{{ $serviceSummary }}</p>
                    </div>
                    <div class="service-show-head__chips" aria-label="{{ $isEn ? 'Service highlights' : 'Ringkasan layanan' }}">
                        @if ((int) ($service->sla_days ?? 0) > 0)
                            <span class="service-show-chip">{{ $isEn ? 'Est. ' . (int) $service->sla_days . ' days' : 'Estimasi ' . (int) $service->sla_days . ' hari' }}</span>
                        @endif
                        @if ($hasDocumentTemplate)
                            <span class="service-show-chip">{{ $isEn ? 'PDF template available' : 'Template PDF tersedia' }}</span>
                        @endif
                    </div>
                </div>

                <div class="content-grid" aria-label="{{ $isEn ? 'Service details' : 'Detail layanan' }}">
                    <section class="content-panel" data-service-show-reveal
                        aria-label="{{ $isEn ? 'Requirements' : 'Persyaratan' }}">
                        <div class="content-panel__title">{{ $isEn ? 'Requirements' : 'Persyaratan' }}</div>
                        <div class="prose prose-sm dark:prose-invert content-prose">
                            {!! $isEn ? ($service->requirements_html_en ?? $service->requirements_html_id) : $service->requirements_html_id !!}
                        </div>
                    </section>

                    <section class="content-panel" data-service-show-reveal aria-label="SOP">
                        <div class="content-panel__title">{{ $isEn ? 'Procedure (SOP)' : 'SOP' }}</div>
                        <div class="prose prose-sm dark:prose-invert content-prose">
                            {!! $isEn ? ($service->sop_html_en ?? $service->sop_html_id) : $service->sop_html_id !!}
                        </div>
                    </section>

                    @if ($hasDocumentTemplate && $documentPreviewUrl)
                        <section class="content-panel content-panel--doc-preview" data-service-show-reveal
                            aria-label="{{ $isEn ? 'Document preview' : 'Preview dokumen' }}">
                            <div class="service-doc-preview__top">
                                <div>
                                    <div class="content-panel__title">{{ $isEn ? 'Document Preview (PDF)' : 'Preview Dokumen (PDF)' }}</div>
                                    <div class="service-doc-preview__hint">
                                        {{ $isEn ? 'Service document template in PDF format.' : 'Template dokumen layanan dalam bentuk PDF.' }}
                                    </div>
                                </div>
                                <a class="service-doc-preview__open" href="{{ $documentPreviewUrl }}" target="_blank"
                                    rel="noopener noreferrer">
                                    {{ $isEn ? 'Open in new tab' : 'Buka tab baru' }}
                                </a>
                            </div>

                            <div class="service-doc-preview__wrap" role="region" aria-label="PDF preview area">
                                <iframe class="service-doc-preview__frame" src="{{ $documentPreviewUrl }}"
                                    title="Preview Dokumen Layanan" loading="lazy"></iframe>
                            </div>

                            <div class="service-doc-preview__fallback">
                                {{ $isEn ? 'If preview is not available on your device, use the Open in new tab button.' : 'Jika preview tidak tampil di perangkat Anda, gunakan tombol Buka tab baru.' }}
                            </div>
                        </section>
                    @endif

                    @if (!empty($isCertificateService))
                        <section class="content-panel content-panel--doc-preview content-panel--certificate" data-service-show-reveal
                            aria-label="Pedoman Sertifikat Piagam">
                            <div class="content-panel__title">{{ $isEn ? 'Certificate Guide' : 'Pedoman Sertifikat/Piagam' }}</div>
                            <div class="service-doc-preview__hint service-cert-guide__intro">
                                {{ $isEn ? 'This section is specific to services with a .pptx source document from applicant.' : 'Bagian ini khusus layanan dengan dokumen sumber .pptx dari pemohon.' }}
                            </div>
                            <ol class="as-help service-cert-guide__list mt-3">
                                <li class="service-cert-guide__item">
                                    <div class="service-cert-guide__copy">
                                        <p class="service-cert-guide__text">
                                            Siapkan dan upload dokumen sumber <span class="font-mono">.pptx</span>.
                                        </p>
                                    </div>
                                </li>
                                <li class="service-cert-guide__item">
                                    <div class="service-cert-guide__copy">
                                        <p class="service-cert-guide__text">
                                            Tentukan daftar signer sejak awal (internal/pemohon/custom).
                                        </p>
                                    </div>
                                </li>
                                <li class="service-cert-guide__item">
                                    <div class="service-cert-guide__copy">
                                        <p class="service-cert-guide__text">Pastikan token wajib ada:</p>
                                        <div class="service-cert-guide__chips">
                                            <span class="font-mono">{{ '{' }}{{ '{' }}nomor_surat{{ '}' }}{{ '}' }}</span>
                                            <span class="font-mono">{{ '{' }}{{ '{' }}tanggal_ttd{{ '}' }}{{ '}' }}</span>
                                            <span class="font-mono">i</span>
                                        </div>
                                        <p class="service-cert-guide__text">serta token signer per indeks.</p>
                                    </div>
                                </li>
                                <li class="service-cert-guide__item">
                                    <div class="service-cert-guide__copy">
                                        <p class="service-cert-guide__text">
                                            Tempatkan token <span class="font-mono">{{ '{' }}{{ '{' }}ttd_i{{ '}' }}{{ '}' }}</span> di shape TTD yang <strong>transparan</strong> (tanpa fill dan tanpa outline). Samakan tinggi shape antar signer, lalu sistem akan menghitung lebar TTD dari tinggi shape (bukan tinggi menyesuaikan lebar).
                                        </p>
                                    </div>
                                </li>
                                <li class="service-cert-guide__item">
                                    <div class="service-cert-guide__copy">
                                        <p class="service-cert-guide__text">Token opsional diproses jika ada di template:</p>
                                        <div class="service-cert-guide__chips">
                                            <span class="font-mono">{{ '{' }}{{ '{' }}jabatan_penandatangan_i{{ '}' }}{{ '}' }}</span>
                                            <span class="font-mono">{{ '{' }}{{ '{' }}nama_penerima{{ '}' }}{{ '}' }}</span>
                                        </div>
                                    </div>
                                </li>
                                <li class="service-cert-guide__item">
                                    <div class="service-cert-guide__copy">
                                        <p class="service-cert-guide__text">Jangan pakai token:</p>
                                        <div class="service-cert-guide__chips">
                                            <span class="font-mono">{{ '{' }}{{ '{' }}kota_ttd{{ '}' }}{{ '}' }}</span>
                                            <span class="service-cert-guide__chip-sep">dan</span>
                                            <span class="font-mono">{{ '{' }}{{ '{' }}tanggal_surat{{ '}' }}{{ '}' }}</span>
                                        </div>
                                    </div>
                                </li>
                                <li class="service-cert-guide__item">
                                    <div class="service-cert-guide__copy">
                                        <p class="service-cert-guide__text">Rekomendasi font yang paling aman dipakai di server:</p>
                                        <div class="service-cert-guide__chips">
                                            <span><strong>Serif:</strong> <span class="font-mono">Times New Roman</span>, <span class="font-mono">Cambria</span>, <span class="font-mono">Georgia</span></span>
                                        </div>
                                        <div class="service-cert-guide__chips">
                                            <span><strong>Serif tambahan:</strong> <span class="font-mono">Garamond</span>, <span class="font-mono">Palatino Linotype</span>, <span class="font-mono">Book Antiqua</span>, <span class="font-mono">Constantia</span></span>
                                        </div>
                                        <div class="service-cert-guide__chips">
                                            <span><strong>Sans Serif:</strong> <span class="font-mono">Arial</span>, <span class="font-mono">Calibri</span>, <span class="font-mono">Segoe UI</span>, <span class="font-mono">Verdana</span></span>
                                        </div>
                                        <div class="service-cert-guide__chips">
                                            <span><strong>Sans Serif tambahan:</strong> <span class="font-mono">Tahoma</span>, <span class="font-mono">Trebuchet MS</span>, <span class="font-mono">Corbel</span>, <span class="font-mono">Candara</span></span>
                                        </div>
                                        <div class="service-cert-guide__chips">
                                            <span><strong>Monospace (opsional):</strong> <span class="font-mono">Consolas</span>, <span class="font-mono">Courier New</span></span>
                                        </div>
                                        <p class="service-cert-guide__text">Hindari font theme seperti <span class="font-mono">+mn-lt</span>/<span class="font-mono">+mj-lt</span>. Jika perlu, jalankan <strong>Replace Fonts</strong> di PowerPoint.</p>
                                        <p class="service-cert-guide__text">Daftar font bisa berbeda di setiap server. Admin dapat mengecek font terpasang dengan perintah <span class="font-mono">fc-list</span>.</p>
                                    </div>
                                </li>
                                <li class="service-cert-guide__item">
                                    <div class="service-cert-guide__copy">
                                        <p class="service-cert-guide__text">Gunakan font regular. Jika ingin tebal/miring/garis bawah, gunakan tools <strong>Bold</strong>, <strong>Italic</strong>, dan <strong>Underline</strong> di PowerPoint.</p>
                                    </div>
                                </li>
                                <li class="service-cert-guide__item">
                                    <div class="service-cert-guide__copy">
                                        <p class="service-cert-guide__text">Hindari elemen <strong>mirror/flip</strong> atau dekoratif kompleks. Jika tetap diperlukan, ubah dulu menjadi gambar final (PNG).</p>
                                    </div>
                                </li>
                                <li class="service-cert-guide__item">
                                    <div class="service-cert-guide__copy">
                                        <p class="service-cert-guide__text">Sebelum submit, cek kembali file template yang akan diupload. Setelah submit, gunakan <strong>Preview Dokumen</strong> di detail permohonan untuk memastikan hasil sesuai.</p>
                                    </div>
                                </li>
                            </ol>
                            <div class="service-cert-example">
                                <div class="service-doc-preview__hint service-cert-example__label">Contoh layout sertifikat/piagam:</div>
                                <a class="service-cert-example__link" href="{{ asset('example/CONTOH SERTIFIKAT_page-0001.jpg') }}"
                                    target="_blank" rel="noopener noreferrer">
                                    <img class="service-cert-example__image"
                                        src="{{ asset('example/CONTOH SERTIFIKAT_page-0001.jpg') }}"
                                        alt="Contoh sertifikat piagam untuk acuan placeholder" loading="lazy">
                                </a>
                                <div class="service-cert-example__caption">Klik gambar untuk membuka ukuran penuh.</div>
                            </div>
                            <div class="service-doc-preview__fallback service-cert-note mt-2">
                                Catatan: <span class="font-mono">{{ '{' }}{{ '{' }}tanggal_ttd{{ '}' }}{{ '}' }}</span> mengikuti waktu signer terakhir. Jumlah signer harus cocok dengan token indeks (<span class="font-mono">ttd_1..ttd_n</span>).
                            </div>
                        </section>
                    @endif
                </div>

                <div class="page-actions" data-service-show-reveal>
                    @auth
                        <x-button class="service-show__apply-btn"
                            href="{{ route('student.requests.create', $service) }}">{{ $isEn ? 'Apply service' : 'Ajukan layanan' }}</x-button>
                    @else
                        <x-button href="{{ route('login') }}">{{ $isEn ? 'Login to apply' : 'Masuk untuk mengajukan' }}</x-button>
                    @endauth
                </div>
            </x-card>
        </section>
    </div>
@endsection

