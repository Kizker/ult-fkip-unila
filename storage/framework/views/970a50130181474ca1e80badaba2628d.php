<?php $__env->startSection('content'); ?>
    <?php
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
    ?>

    <div class="page-public-home ult-home" id="ultHome">
        <section class="ult-hero" id="ultHeroCarousel" aria-labelledby="home-title">
            <div class="ult-hero-slider">
                <?php $__currentLoopData = $homeHeroSlides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slide): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
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
                    ?>

                    <article class="ult-hero-slide <?php echo e($loop->first ? 'is-active' : ''); ?>" data-hero-slide>
                        <div class="ult-hero-bg"
                            <?php if($slideImage): ?> style="background-image: url('<?php echo e($slideImage); ?>');" <?php endif; ?>
                            aria-hidden="true"></div>
                        <div class="ult-hero-overlay" aria-hidden="true"></div>

                        <div class="ult-hero-content-wrap">
                            <div class="ult-hero-content ult-reveal">
                                <?php if($loop->first): ?>
                                    <h1 id="home-title" class="ult-hero-title"><?php echo e($slideTitle); ?></h1>
                                <?php else: ?>
                                    <h2 class="ult-hero-title"><?php echo e($slideTitle); ?></h2>
                                <?php endif; ?>

                                <p class="ult-hero-subtitle"><?php echo e($slideSubtitle); ?></p>

                                <div class="ult-hero-actions">
                                    <a class="btn btn-primary ult-hero-cta"
                                        href="<?php echo e($slideHref); ?>"><?php echo e($slideCta); ?></a>
                                    <a class="btn btn-secondary ult-hero-cta-secondary"
                                        href="<?php echo e($heroSecondaryHref); ?>"><?php echo e($heroSecondaryLabel); ?></a>
                                </div>

                                <div class="ult-hero-bottom" aria-label="<?php echo e($isEn ? 'Quick summary' : 'Ringkasan cepat'); ?>">
                                    <div class="ult-hero-bottom__grid">
                                        <?php $__currentLoopData = $heroStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $heroStat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <article class="ult-hero-stat">
                                                <div class="ult-hero-stat__num"><?php echo e($heroStat['value']); ?></div>
                                                <div class="ult-hero-stat__label"><?php echo e($heroStat['label']); ?></div>
                                            </article>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <?php if($homeHeroSlides->count() > 1): ?>
                <div class="ult-hero-dots" role="tablist" aria-label="<?php echo e($isEn ? 'Hero slider' : 'Slider hero'); ?>">
                    <?php $__currentLoopData = $homeHeroSlides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slide): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button type="button" class="ult-hero-dot <?php echo e($loop->first ? 'is-active' : ''); ?>"
                            data-hero-dot="<?php echo e($loop->index); ?>"
                            aria-label="<?php echo e($isEn ? 'Slide' : 'Slide'); ?> <?php echo e($loop->iteration); ?>"
                            aria-selected="<?php echo e($loop->first ? 'true' : 'false'); ?>"></button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Quick-Access Grid: Panduan & Layanan Terpopuler -->
        <section class="ult-quick-access" id="ultQuickAccess" aria-label="<?php echo e($isEn ? 'Quick access' : 'Akses cepat'); ?>">
            <div class="ult-quick-access__grid">

                <!-- Kolom Kiri: Panduan & Tutorial -->
                <div class="ult-panduan-section" id="homePanduan">
                    <div class="ult-section-header">
                        <div class="ult-section-header__copy">
                            <h2 class="ult-section-header__title"><?php echo e($isEn ? 'User Guides & Tutorials' : 'Panduan & Tutorial'); ?></h2>
                            <p class="ult-section-header__subtitle"><?php echo e($isEn ? 'Important guides and video tutorials to help you navigate the system.' : 'Buku panduan dan video tutorial untuk memudahkan pengajuan layanan Anda.'); ?></p>
                        </div>
                        <a class="section-link" href="<?php echo e(route('user_guides.index')); ?>"><?php echo e($isEn ? 'All guides' : 'Semua panduan'); ?> &rarr;</a>
                    </div>

                    <!-- Buku Panduan -->
                    <?php
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
                    ?>
                    <a href="<?php echo e($bookUrl); ?>" class="ult-guide-book-card ult-reveal" aria-label="<?php echo e($bookTitle); ?>">
                        <div class="ult-guide-book-card__label"><?php echo e($isEn ? 'Guide Book' : 'Buku Panduan'); ?></div>
                        <h3 class="ult-guide-book-card__title"><?php echo e($bookTitle); ?></h3>
                        <p class="ult-guide-book-card__desc"><?php echo e($bookDesc); ?></p>
                        <span class="ult-guide-book-card__cta"><?php echo e($isEn ? 'Open Guide' : 'Buka Panduan'); ?> <iconify-icon icon="heroicons:arrow-right" width="14"></iconify-icon></span>
                    </a>

                    <!-- Video Tutorials -->
                    <div class="ult-video-grid">
                        <?php $__empty_1 = true; $__currentLoopData = $guideVideos ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
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
                            ?>
                            <a href="<?php echo e($vidLink); ?>" class="ult-video-card ult-reveal"
                               <?php if($video->isVideo() && $video->videoWatchUrl()): ?> target="_blank" rel="noopener noreferrer" <?php endif; ?>
                               aria-label="<?php echo e($vidTitle); ?>">
                                <div class="ult-video-card__thumb">
                                    <?php if($vidThumb): ?>
                                        <img src="<?php echo e($vidThumb); ?>" alt="<?php echo e($vidTitle); ?>" loading="lazy">
                                    <?php endif; ?>
                                    <div class="ult-video-card__play">
                                        <div class="ult-video-card__play-icon">
                                            <iconify-icon icon="heroicons:play-solid"></iconify-icon>
                                        </div>
                                    </div>
                                </div>
                                <div class="ult-video-card__info">
                                    <h4 class="ult-video-card__title" title="<?php echo e($vidTitle); ?>"><?php echo e($vidTitle); ?></h4>
                                    <p class="ult-video-card__meta">Video Tutorial</p>
                                </div>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            
                            <?php
                                $mockVideos = [
                                    $isEn ? 'How to Submit a Service Request' : 'Cara Mengajukan Permohonan Layanan',
                                    $isEn ? 'How to Track Service Status' : 'Cara Melacak Status Layanan',
                                    $isEn ? 'How to Download Completed Documents' : 'Cara Mengunduh Dokumen Selesai',
                                ];
                            ?>
                            <?php $__currentLoopData = $mockVideos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mockTitle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e(route('user_guides.index')); ?>" class="ult-video-card ult-reveal" aria-label="<?php echo e($mockTitle); ?>">
                                    <div class="ult-video-card__thumb">
                                        <div class="ult-video-card__play">
                                            <div class="ult-video-card__play-icon">
                                                <iconify-icon icon="heroicons:play-solid"></iconify-icon>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ult-video-card__info">
                                        <h4 class="ult-video-card__title"><?php echo e($mockTitle); ?></h4>
                                        <p class="ult-video-card__meta">Video Tutorial</p>
                                    </div>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Kolom Kanan: Layanan Terpopuler (Sidebar) -->
                <div class="ult-popular-services-section" id="homeServices">
                    <div class="ult-popular-panel">
                        <div class="ult-popular-panel__header">
                            <h2 class="ult-popular-panel__title"><?php echo e($servicesTitle); ?></h2>
                            <p class="ult-popular-panel__subtitle"><?php echo e($isEn ? 'Quick access to our most requested services.' : 'Akses cepat ke layanan yang paling sering digunakan.'); ?></p>
                        </div>

                        <div class="ult-popular-list" role="list">
                            <?php $__empty_1 = true; $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $svcTitle = $isEn ? ($service->title_en ?? $service->title_id) : $service->title_id;
                                    $svcCategory = $isEn
                                        ? ($service->category?->name_en ?? $service->category?->name_id)
                                        : $service->category?->name_id;
                                    $svcCategory = $svcCategory ?: ($isEn ? 'General' : 'Umum');
                                ?>
                                <a href="<?php echo e(route('services.show', $service)); ?>"
                                   class="ult-popular-item ult-reveal"
                                   role="listitem"
                                   aria-label="<?php echo e($svcTitle); ?>">
                                    <div class="ult-popular-item__body">
                                        <div class="ult-popular-item__category"><?php echo e($svcCategory); ?></div>
                                        <h3 class="ult-popular-item__name" title="<?php echo e($svcTitle); ?>"><?php echo e($svcTitle); ?></h3>
                                    </div>
                                    <div class="ult-popular-item__arrow">
                                        <iconify-icon icon="heroicons:chevron-right-20-solid"></iconify-icon>
                                    </div>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="ult-popular-empty">
                                    <iconify-icon icon="heroicons:inbox" style="font-size:2rem;opacity:.5;margin-bottom:6px;display:block"></iconify-icon>
                                    <span><?php echo e($isEn ? 'No services available at the moment.' : 'Belum ada layanan yang tersedia.'); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="ult-popular-panel__footer">
                            <a href="<?php echo e(route('services.index')); ?>" class="ult-popular-panel__viewall ult-popular-panel__viewall--solid">
                                <?php echo e($isEn ? 'Explore All Services' : 'Lihat Semua Layanan'); ?>

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
                    <h2 id="ann-title" class="section-title"><?php echo e($annTitle); ?></h2>
                    <p class="section-subtitle"><?php echo e($annSubtitle); ?></p>
                </div>
                <a class="section-link"
                    href="<?php echo e(route('announcements.index')); ?>"><?php echo e($isEn ? 'All announcements' : 'Semua pengumuman'); ?>

                    &rarr;</a>
            </div>

            <div class="ult-ann-carousel" data-ann-carousel>
                <button type="button" class="ult-ann-nav ult-ann-nav--prev" data-ann-prev
                    aria-label="<?php echo e($isEn ? 'Previous announcements' : 'Pengumuman sebelumnya'); ?>">
                    &#10094;
                </button>

                <div class="ult-ann-track" data-ann-track>
                    <?php $__empty_1 = true; $__currentLoopData = $annItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
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
                        ?>

                        <article class="ult-ann-card ult-reveal">
                            <a class="ult-ann-card__link" href="<?php echo e(route('announcements.show', $post)); ?>">
                                <div class="ult-ann-card__media">
                                    <?php if($postImage): ?>
                                        <img src="<?php echo e($postImage); ?>" alt="<?php echo e($postTitle); ?>" loading="lazy">
                                    <?php else: ?>
                                        <span class="ult-ann-card__fallback"
                                            aria-hidden="true"><?php echo e($isEn ? 'Announcement' : 'Pengumuman'); ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="ult-ann-card__body">
                                    <p class="kicker"><?php echo e(optional($post->published_at)->format('d M Y')); ?></p>
                                    <h3 class="ult-ann-card__title"><?php echo e($postTitle); ?></h3>
                                    <p class="ult-ann-card__desc"><?php echo e($postDesc); ?></p>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="empty">
                            <?php echo e($isEn ? 'No announcements available at the moment.' : 'Belum ada pengumuman terbaru saat ini.'); ?>

                        </div>
                    <?php endif; ?>
                </div>

                <button type="button" class="ult-ann-nav ult-ann-nav--next" data-ann-next
                    aria-label="<?php echo e($isEn ? 'Next announcements' : 'Pengumuman berikutnya'); ?>">
                    &#10095;
                </button>
            </div>

            <div class="ult-ann-dots" data-ann-dots aria-label="<?php echo e($isEn ? 'Announcement pages' : 'Halaman pengumuman'); ?>">
            </div>
        </section>



        <section class="section ult-blogs" id="homeBlogs" aria-labelledby="blog-title">
            <div class="section-head ult-head">
                <div class="section-heading">
                    <h2 id="blog-title" class="section-title"><?php echo e($blogTitle); ?></h2>
                    <p class="section-subtitle"><?php echo e($blogSubtitle); ?></p>
                </div>
                <a class="section-link" href="<?php echo e(route('blog.index')); ?>"><?php echo e($isEn ? 'All blog posts' : 'Semua blog'); ?>

                    &rarr;</a>
            </div>

            <div class="ult-blog-grid">
                <?php $__empty_1 = true; $__currentLoopData = $blogItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
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
                    ?>

                    <a class="ult-blog-card ult-reveal" href="<?php echo e(route('blog.show', $post)); ?>"
                        aria-label="<?php echo e($postTitle); ?>">
                        <div class="ult-blog-card__media">
                            <?php if($postImage): ?>
                                <img src="<?php echo e($postImage); ?>" alt="<?php echo e($postTitle); ?>" loading="lazy">
                            <?php else: ?>
                                <span class="ult-blog-card__fallback"
                                    aria-hidden="true"><?php echo e($isEn ? 'Blog' : 'Blog'); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="ult-blog-card__body">
                            <p class="kicker"><?php echo e(optional($post->published_at)->format('d M Y')); ?></p>
                            <h3 class="ult-blog-card__title"><?php echo e($postTitle); ?></h3>
                            <p class="ult-blog-card__desc"><?php echo e($postDesc); ?></p>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="empty">
                        <?php echo e($isEn ? 'No blog posts available at the moment.' : 'Belum ada blog terbaru saat ini.'); ?></div>
                <?php endif; ?>
            </div>
        </section>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/public/home.blade.php ENDPATH**/ ?>