@extends('layouts.public')

@section('content')
    @php
        $isEn = app()->getLocale() === 'en';

        $homeHeroSlides = collect($heroSlides ?? [])->values();
        if ($homeHeroSlides->isEmpty()) {
            $homeHeroSlides = collect([
                (object) [
                    'title_id' => 'ULT FKIP Unila',
                    'title_en' => 'ULT FKIP Unila',
                    'subtitle_id' => 'Portal layanan akademik untuk mahasiswa, dosen, dan tenaga kependidikan.',
                    'subtitle_en' => 'Academic services portal for students, lecturers, and staff.',
                    'cta_label_id' => 'Lihat Layanan',
                    'cta_label_en' => 'Explore Services',
                    'cta_url' => route('services.index'),
                    'image_path' => null,
                ],
            ]);
        }

        $annItems = collect($ann ?? [])->values();
        $serviceItems = collect($services ?? [])
            ->values()
            ->take(6);
        $serviceRows = collect();
        if ($serviceItems->isNotEmpty()) {
            $splitIndex = (int) ceil($serviceItems->count() / 2);
            $serviceRows = collect([
                $serviceItems->slice(0, $splitIndex)->values(),
                $serviceItems->slice($splitIndex)->values(),
            ])->filter(fn ($row) => $row->isNotEmpty())->values();
        }
        $blogItems = collect($blogs ?? [])
            ->values()
            ->take(4);

        $stripContent = static function (?string $html): string {
            $plain = strip_tags((string) ($html ?? ''));
            $plain = preg_replace('/\s+/u', ' ', $plain);

            return trim((string) $plain);
        };

        $excerpt = static function (?string $html, int $limit = 140) use ($stripContent): string {
            $plain = $stripContent($html);

            return \Illuminate\Support\Str::limit($plain, $limit, '...');
        };

        $extractImage = static function (?string $html): ?string {
            if (!filled($html)) {
                return null;
            }

            return preg_match('/<img[^>]+src=[\"\']([^\"\']+)[\"\']/i', $html, $matches) === 1 ? $matches[1] : null;
        };

        $normalizeImage = static function (?string $src): ?string {
            if (!filled($src)) {
                return null;
            }

            if (\Illuminate\Support\Str::startsWith($src, ['http://', 'https://', 'data:'])) {
                return $src;
            }

            return asset(ltrim($src, '/'));
        };

        $heroSecondaryLabel = __('app.login');
        $heroSecondaryHref = route('login');
        if (auth()->check()) {
            $user = auth()->user();
            $adminPerms = [
                'requests.view_any',
                'requests.view_unit',
                'requests.review_ult',
                'requests.process_unit',
                'approvals.unit.sign',
                'approvals.faculty.sign',
                'document_numbers.issue',
                'services.manage',
                'cms.manage',
                'site_settings.manage',
                'academics.manage',
                'users.manage',
                'audit_logs.view',
                'doc_services.manage',
                'doc_services.publish',
                'doc_templates.upload',
                'doc_placeholders.manage',
                'doc_signers.manage',
                'feedbacks.manage',
                'doc_requests.gate',
                'doc_requests.assemble',
            ];

            $heroSecondaryHref = route('home');
            if ($user?->hasRole('Superadmin')) {
                $heroSecondaryHref = route('admin.dashboard');
            } elseif ($user?->can('requests.view_own')) {
                $heroSecondaryHref = route('student.dashboard');
            } elseif ($user?->canAny($adminPerms)) {
                $heroSecondaryHref = route('admin.dashboard');
            } elseif ($user?->can('doc_signoffs.decide')) {
                $heroSecondaryHref = route('signer.requests.inbox');
            }

            $heroSecondaryLabel = __('app.dashboard');
        }

        $annTitle = $isEn ? 'Latest Announcements' : 'Pengumuman Terbaru';
        $annSubtitle = $isEn
            ? 'Important updates from ULT FKIP Unila. Swipe or use arrows to view more.'
            : 'Informasi penting terbaru dari ULT FKIP Unila. Geser atau gunakan panah untuk melihat lainnya.';

        $servicesTitle = $isEn ? 'Most Used Services' : 'Layanan Terpopuler';
        $servicesSubtitle = $isEn
            ? 'Fast access to services most frequently used by students and lecturers.'
            : 'Akses cepat ke layanan yang paling sering digunakan mahasiswa dan dosen.';

        $blogTitle = $isEn ? 'Latest Blog Posts' : 'Blog Terbaru';
        $blogSubtitle = $isEn
            ? 'Recent stories, activity reports, and academic insights.'
            : 'Artikel terbaru, laporan kegiatan, dan wawasan akademik terbaru.';

        $serviceTotal = (int) ($totalServices ?? $serviceItems->count());
        $announcementTotal = (int) ($totalAnnouncements ?? $annItems->count());
        $blogTotal = (int) ($totalBlogs ?? $blogItems->count());

        $heroStats = [
            [
                'value' => $serviceTotal,
                'label' => $isEn ? 'services' : 'Layanan',
            ],
            [
                'value' => $announcementTotal,
                'label' => $isEn ? 'announcements' : 'Pengumuman',
            ],
            [
                'value' => $blogTotal,
                'label' => $isEn ? 'articles' : 'Artikel',
            ],
        ];
    @endphp

    <div class="page-public-home ult-home" id="ultHome">
        <section class="ult-hero" id="ultHeroCarousel" aria-labelledby="home-title">
            <div class="ult-hero-slider">
                @foreach ($homeHeroSlides as $slide)
                    @php
                        $slideTitle = $isEn
                            ? $slide->title_en ?? $slide->title_id
                            : $slide->title_id ?? $slide->title_en;
                        $slideTitle = filled($slideTitle) ? $slideTitle : 'ULT FKIP Unila';

                        $slideSubtitle = $isEn
                            ? $slide->subtitle_en ?? $slide->subtitle_id
                            : $slide->subtitle_id ?? $slide->subtitle_en;
                        $slideSubtitle = filled($slideSubtitle)
                            ? $slideSubtitle
                            : ($isEn
                                ? 'Integrated digital services for the academic community.'
                                : 'Layanan digital terintegrasi untuk sivitas akademika.');

                        $slideCta = $isEn
                            ? $slide->cta_label_en ?? $slide->cta_label_id
                            : $slide->cta_label_id ?? $slide->cta_label_en;
                        $slideCta = filled($slideCta) ? $slideCta : ($isEn ? 'Explore Services' : 'Lihat Layanan');

                        $slideHref = filled($slide->cta_url ?? null) ? $slide->cta_url : route('services.index');
                        $slideImage = filled($slide->image_path ?? null)
                            ? asset('storage/' . ltrim($slide->image_path, '/'))
                            : null;
                    @endphp

                    <article class="ult-hero-slide {{ $loop->first ? 'is-active' : '' }}" data-hero-slide>
                        <div class="ult-hero-bg"
                            @if ($slideImage) style="background-image: url('{{ $slideImage }}');" @endif
                            aria-hidden="true"></div>
                        <div class="ult-hero-overlay" aria-hidden="true"></div>

                        <div class="ult-hero-content-wrap">
                            <div class="ult-hero-content ult-reveal">
                                @if ($loop->first)
                                    <h1 id="home-title" class="ult-hero-title">{{ $slideTitle }}</h1>
                                @else
                                    <h2 class="ult-hero-title">{{ $slideTitle }}</h2>
                                @endif

                                <p class="ult-hero-subtitle">{{ $slideSubtitle }}</p>

                                <div class="ult-hero-actions">
                                    <a class="btn btn-primary ult-hero-cta"
                                        href="{{ $slideHref }}">{{ $slideCta }}</a>
                                    <a class="btn btn-secondary ult-hero-cta-secondary"
                                        href="{{ $heroSecondaryHref }}">{{ $heroSecondaryLabel }}</a>
                                </div>

                                <div class="ult-hero-bottom" aria-label="{{ $isEn ? 'Quick summary' : 'Ringkasan cepat' }}">
                                    <div class="ult-hero-bottom__grid">
                                        @foreach ($heroStats as $heroStat)
                                            <article class="ult-hero-stat">
                                                <div class="ult-hero-stat__num">{{ $heroStat['value'] }}</div>
                                                <div class="ult-hero-stat__label">{{ $heroStat['label'] }}</div>
                                            </article>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($homeHeroSlides->count() > 1)
                <div class="ult-hero-dots" role="tablist" aria-label="{{ $isEn ? 'Hero slider' : 'Slider hero' }}">
                    @foreach ($homeHeroSlides as $slide)
                        <button type="button" class="ult-hero-dot {{ $loop->first ? 'is-active' : '' }}"
                            data-hero-dot="{{ $loop->index }}"
                            aria-label="{{ $isEn ? 'Slide' : 'Slide' }} {{ $loop->iteration }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}"></button>
                    @endforeach
                </div>
            @endif
        </section>

        <!-- Quick-Access Grid: Panduan & Layanan Terpopuler -->
        <section class="ult-quick-access" id="ultQuickAccess" aria-label="{{ $isEn ? 'Quick access' : 'Akses cepat' }}">
            <div class="ult-quick-access__grid">

                <!-- Kolom Kiri: Panduan & Tutorial -->
                <div class="ult-panduan-section" id="homePanduan">
                    <div class="ult-section-header">
                        <div class="ult-section-header__copy">
                            <h2 class="ult-section-header__title">{{ $isEn ? 'User Guides & Tutorials' : 'Panduan & Tutorial' }}</h2>
                            <p class="ult-section-header__subtitle">{{ $isEn ? 'Important guides and video tutorials to help you navigate the system.' : 'Buku panduan dan video tutorial untuk memudahkan pengajuan layanan Anda.' }}</p>
                        </div>
                        <a class="section-link" href="{{ route('user_guides.index') }}">{{ $isEn ? 'All guides' : 'Semua panduan' }} &rarr;</a>
                    </div>

                    <!-- Buku Panduan -->
                    @php
                        $bookTitle = '';
                        $bookDesc = '';
                        $bookUrl = route('user_guides.index');
                        if ($guideBook ?? false) {
                            $bookTitle = $isEn ? ($guideBook->title_en ?? $guideBook->title_id) : $guideBook->title_id;
                            $bookDesc = $isEn ? ($guideBook->summary_en ?? $guideBook->summary_id) : $guideBook->summary_id;
                            $bookUrl = route('user_guides.show', $guideBook);
                        }
                        $bookTitle = filled($bookTitle) ? $bookTitle : ($isEn ? 'ULT FKIP Unila User Guide' : 'Panduan Penggunaan ULT FKIP Unila');
                        $bookDesc = filled($bookDesc) ? $bookDesc : ($isEn ? 'Complete PDF guide covering service request steps, document tracking, and final output download.' : 'Dokumen PDF lengkap berisi langkah-langkah pengajuan layanan, pelacakan dokumen, hingga pengunduhan hasil akhir.');
                    @endphp
                    <a href="{{ $bookUrl }}" class="ult-guide-book-card ult-reveal" aria-label="{{ $bookTitle }}">
                        <div class="ult-guide-book-card__label">{{ $isEn ? 'Guide Book' : 'Buku Panduan' }}</div>
                        <h3 class="ult-guide-book-card__title">{{ $bookTitle }}</h3>
                        <p class="ult-guide-book-card__desc">{{ $bookDesc }}</p>
                        <span class="ult-guide-book-card__cta">{{ $isEn ? 'Open Guide' : 'Buka Panduan' }} <iconify-icon icon="heroicons:arrow-right" width="14"></iconify-icon></span>
                    </a>

                    <!-- Video Tutorials -->
                    <div class="ult-video-grid">
                        @forelse($guideVideos ?? [] as $video)
                            @php
                                $vidTitle = $isEn ? ($video->title_en ?? $video->title_id) : $video->title_id;
                                $vidThumb = null;
                                $ytId = null;
                                // Extract YouTube thumbnail
                                if ($video->isVideo()) {
                                    $ytId = null;
                                    $vUrl = trim((string) ($video->video_url ?? ''));
                                    if ($vUrl !== '') {
                                        $vParts = parse_url($vUrl);
                                        $vHost = strtolower($vParts['host'] ?? '');
                                        $vPath = trim($vParts['path'] ?? '', '/');
                                        if (in_array($vHost, ['youtu.be', 'www.youtu.be'])) {
                                            $ytId = preg_match('/^[A-Za-z0-9_-]{11}$/', $vPath) ? $vPath : null;
                                        } elseif (in_array($vHost, ['youtube.com', 'www.youtube.com', 'm.youtube.com'])) {
                                            if ($vPath === 'watch') {
                                                parse_str($vParts['query'] ?? '', $vQ);
                                                $ytId = preg_match('/^[A-Za-z0-9_-]{11}$/', $vQ['v'] ?? '') ? $vQ['v'] : null;
                                            } elseif (str_starts_with($vPath, 'embed/')) {
                                                $c = substr($vPath, 6);
                                                $ytId = preg_match('/^[A-Za-z0-9_-]{11}$/', $c) ? $c : null;
                                            } elseif (str_starts_with($vPath, 'shorts/')) {
                                                $c = substr($vPath, 7);
                                                $ytId = preg_match('/^[A-Za-z0-9_-]{11}$/', $c) ? $c : null;
                                            }
                                        }
                                    }
                                    $vidThumb = $ytId ? "https://img.youtube.com/vi/{$ytId}/mqdefault.jpg" : null;
                                }
                                $vidLink = ($video->isVideo() && $video->videoWatchUrl()) ? $video->videoWatchUrl() : route('user_guides.index');
                            @endphp
                            <a href="{{ $vidLink }}" class="ult-video-card ult-reveal"
                               @if($video->isVideo() && $video->videoWatchUrl()) target="_blank" rel="noopener noreferrer" @endif
                               aria-label="{{ $vidTitle }}">
                                <div class="ult-video-card__thumb">
                                    @if($vidThumb)
                                        <img src="{{ $vidThumb }}" alt="{{ $vidTitle }}" loading="lazy">
                                    @endif
                                    <div class="ult-video-card__play">
                                        <div class="ult-video-card__play-icon">
                                            <iconify-icon icon="heroicons:play-solid"></iconify-icon>
                                        </div>
                                    </div>
                                </div>
                                <div class="ult-video-card__info">
                                    <h4 class="ult-video-card__title" title="{{ $vidTitle }}">{{ $vidTitle }}</h4>
                                    <p class="ult-video-card__meta">Video Tutorial</p>
                                </div>
                            </a>
                        @empty
                            {{-- Mock data fallback --}}
                            @php
                                $mockVideos = [
                                    $isEn ? 'How to Submit a Service Request' : 'Cara Mengajukan Permohonan Layanan',
                                    $isEn ? 'How to Track Service Status' : 'Cara Melacak Status Layanan',
                                    $isEn ? 'How to Download Completed Documents' : 'Cara Mengunduh Dokumen Selesai',
                                ];
                            @endphp
                            @foreach($mockVideos as $mockTitle)
                                <a href="{{ route('user_guides.index') }}" class="ult-video-card ult-reveal" aria-label="{{ $mockTitle }}">
                                    <div class="ult-video-card__thumb">
                                        <div class="ult-video-card__play">
                                            <div class="ult-video-card__play-icon">
                                                <iconify-icon icon="heroicons:play-solid"></iconify-icon>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ult-video-card__info">
                                        <h4 class="ult-video-card__title">{{ $mockTitle }}</h4>
                                        <p class="ult-video-card__meta">Video Tutorial</p>
                                    </div>
                                </a>
                            @endforeach
                        @endforelse
                    </div>
                </div>

                <!-- Kolom Kanan: Layanan Terpopuler (Sidebar) -->
                <div class="ult-popular-services-section" id="homeServices">
                    <div class="ult-popular-panel">
                        <div class="ult-popular-panel__header">
                            <h2 class="ult-popular-panel__title">{{ $servicesTitle }}</h2>
                            <p class="ult-popular-panel__subtitle">{{ $isEn ? 'Quick access to our most requested services.' : 'Akses cepat ke layanan yang paling sering digunakan.' }}</p>
                        </div>

                        <div class="ult-popular-list" role="list">
                            @forelse ($services as $service)
                                @php
                                    $svcTitle = $isEn ? ($service->title_en ?? $service->title_id) : $service->title_id;
                                    $svcCategory = $isEn
                                        ? ($service->category?->name_en ?? $service->category?->name_id)
                                        : $service->category?->name_id;
                                    $svcCategory = $svcCategory ?: ($isEn ? 'General' : 'Umum');
                                @endphp
                                <a href="{{ route('services.show', $service) }}"
                                   class="ult-popular-item ult-reveal"
                                   role="listitem"
                                   aria-label="{{ $svcTitle }}">
                                    <div class="ult-popular-item__body">
                                        <div class="ult-popular-item__category">{{ $svcCategory }}</div>
                                        <h3 class="ult-popular-item__name" title="{{ $svcTitle }}">{{ $svcTitle }}</h3>
                                    </div>
                                    <div class="ult-popular-item__arrow">
                                        <iconify-icon icon="heroicons:chevron-right-20-solid"></iconify-icon>
                                    </div>
                                </a>
                            @empty
                                <div class="ult-popular-empty">
                                    <iconify-icon icon="heroicons:inbox" style="font-size:2rem;opacity:.5;margin-bottom:6px;display:block"></iconify-icon>
                                    <span>{{ $isEn ? 'No services available at the moment.' : 'Belum ada layanan yang tersedia.' }}</span>
                                </div>
                            @endforelse
                        </div>

                        <div class="ult-popular-panel__footer">
                            <a href="{{ route('services.index') }}" class="ult-popular-panel__viewall ult-popular-panel__viewall--solid">
                                {{ $isEn ? 'Explore All Services' : 'Lihat Semua Layanan' }}
                                <iconify-icon icon="heroicons:arrow-right-20-solid"></iconify-icon>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <section class="section ult-announcements" id="homeAnnouncements" aria-labelledby="ann-title" style="padding-top: 2rem;">
            <div class="section-head ult-head">
                <div class="section-heading">
                    <h2 id="ann-title" class="section-title">{{ $annTitle }}</h2>
                    <p class="section-subtitle">{{ $annSubtitle }}</p>
                </div>
                <a class="section-link"
                    href="{{ route('announcements.index') }}">{{ $isEn ? 'All announcements' : 'Semua pengumuman' }}
                    &rarr;</a>
            </div>

            <div class="ult-ann-carousel" data-ann-carousel>
                <button type="button" class="ult-ann-nav ult-ann-nav--prev" data-ann-prev
                    aria-label="{{ $isEn ? 'Previous announcements' : 'Pengumuman sebelumnya' }}">
                    &#10094;
                </button>

                <div class="ult-ann-track" data-ann-track>
                    @forelse ($annItems as $post)
                        @php
                            $postTitle = $isEn ? $post->title_en ?? $post->title_id : $post->title_id;
                            $postHtml = $isEn
                                ? $post->content_html_en ?? $post->content_html_id
                                : $post->content_html_id;
                            $postDesc = filled($postHtml)
                                ? $excerpt($postHtml, 160)
                                : ($isEn
                                    ? 'Official announcement from ULT FKIP Unila.'
                                    : 'Pengumuman resmi dari ULT FKIP Unila.');
                            $postImage = filled($post->image_path ?? null)
                                ? asset('storage/' . ltrim((string) $post->image_path, '/'))
                                : $normalizeImage($extractImage($postHtml));
                        @endphp

                        <article class="ult-ann-card ult-reveal">
                            <a class="ult-ann-card__link" href="{{ route('announcements.show', $post) }}">
                                <div class="ult-ann-card__media">
                                    @if ($postImage)
                                        <img src="{{ $postImage }}" alt="{{ $postTitle }}" loading="lazy">
                                    @else
                                        <span class="ult-ann-card__fallback"
                                            aria-hidden="true">{{ $isEn ? 'Announcement' : 'Pengumuman' }}</span>
                                    @endif
                                </div>

                                <div class="ult-ann-card__body">
                                    <p class="kicker">{{ optional($post->published_at)->format('d M Y') }}</p>
                                    <h3 class="ult-ann-card__title">{{ $postTitle }}</h3>
                                    <p class="ult-ann-card__desc">{{ $postDesc }}</p>
                                </div>
                            </a>
                        </article>
                    @empty
                        <div class="empty">
                            {{ $isEn ? 'No announcements available at the moment.' : 'Belum ada pengumuman terbaru saat ini.' }}
                        </div>
                    @endforelse
                </div>

                <button type="button" class="ult-ann-nav ult-ann-nav--next" data-ann-next
                    aria-label="{{ $isEn ? 'Next announcements' : 'Pengumuman berikutnya' }}">
                    &#10095;
                </button>
            </div>

            <div class="ult-ann-dots" data-ann-dots aria-label="{{ $isEn ? 'Announcement pages' : 'Halaman pengumuman' }}">
            </div>
        </section>



        <section class="section ult-blogs" id="homeBlogs" aria-labelledby="blog-title">
            <div class="section-head ult-head">
                <div class="section-heading">
                    <h2 id="blog-title" class="section-title">{{ $blogTitle }}</h2>
                    <p class="section-subtitle">{{ $blogSubtitle }}</p>
                </div>
                <a class="section-link" href="{{ route('blog.index') }}">{{ $isEn ? 'All blog posts' : 'Semua blog' }}
                    &rarr;</a>
            </div>

            <div class="ult-blog-grid">
                @forelse ($blogItems as $post)
                    @php
                        $postTitle = $isEn ? $post->title_en ?? $post->title_id : $post->title_id;
                        $postHtml = $isEn ? $post->content_html_en ?? $post->content_html_id : $post->content_html_id;
                        $postDesc = filled($postHtml)
                            ? $excerpt($postHtml, 132)
                            : ($isEn
                                ? 'Latest update from ULT FKIP Unila.'
                                : 'Pembaruan terbaru dari ULT FKIP Unila.');
                        $postImage = filled($post->image_path ?? null)
                            ? asset('storage/' . ltrim((string) $post->image_path, '/'))
                            : $normalizeImage($extractImage($postHtml));
                    @endphp

                    <a class="ult-blog-card ult-reveal" href="{{ route('blog.show', $post) }}"
                        aria-label="{{ $postTitle }}">
                        <div class="ult-blog-card__media">
                            @if ($postImage)
                                <img src="{{ $postImage }}" alt="{{ $postTitle }}" loading="lazy">
                            @else
                                <span class="ult-blog-card__fallback"
                                    aria-hidden="true">{{ $isEn ? 'Blog' : 'Blog' }}</span>
                            @endif
                        </div>
                        <div class="ult-blog-card__body">
                            <p class="kicker">{{ optional($post->published_at)->format('d M Y') }}</p>
                            <h3 class="ult-blog-card__title">{{ $postTitle }}</h3>
                            <p class="ult-blog-card__desc">{{ $postDesc }}</p>
                        </div>
                    </a>
                @empty
                    <div class="empty">
                        {{ $isEn ? 'No blog posts available at the moment.' : 'Belum ada blog terbaru saat ini.' }}</div>
                @endforelse
            </div>
        </section>
    </div>

@endsection
