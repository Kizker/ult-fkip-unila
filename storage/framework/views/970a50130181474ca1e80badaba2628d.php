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

        <section class="section ult-announcements" id="homeAnnouncements" aria-labelledby="ann-title">
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

        <section class="section ult-services" id="homeServices" aria-labelledby="services-title">
            <div class="section-head ult-head">
                <div class="section-heading">
                    <h2 id="services-title" class="section-title"><?php echo e($servicesTitle); ?></h2>
                    <p class="section-subtitle"><?php echo e($servicesSubtitle); ?></p>
                </div>
                <a class="section-link"
                    href="<?php echo e(route('services.index')); ?>"><?php echo e($isEn ? 'All services' : 'Semua layanan'); ?> &rarr;</a>
            </div>

            <div class="ult-service-marquee" data-service-marquee>
                <?php $__empty_1 = true; $__currentLoopData = $serviceRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rowIndex => $rowItems): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $rowDuration = max(18, $rowItems->count() * 7);
                    ?>

                    <div class="ult-service-row <?php echo e($rowIndex % 2 === 1 ? 'is-reverse' : ''); ?>">
                        <div class="ult-service-track" style="--ult-service-marquee-duration: <?php echo e($rowDuration); ?>s;">
                            <?php $__currentLoopData = $rowItems->concat($rowItems); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $serviceTitle = $isEn ? $service->title_en ?? $service->title_id : $service->title_id;
                                    $serviceSummary = $isEn ? $service->summary_en ?? $service->summary_id : $service->summary_id;
                                    $serviceSummary = filled($serviceSummary)
                                        ? $serviceSummary
                                        : ($isEn
                                            ? 'Official digital service for academic administration.'
                                            : 'Layanan digital resmi untuk kebutuhan administrasi akademik.');

                                    $serviceCategory = $isEn
                                        ? $service->category?->name_en ?? $service->category?->name_id
                                        : $service->category?->name_id;
                                    $serviceCategory = $serviceCategory ?: ($isEn ? 'General service' : 'Layanan umum');

                                    $serviceFormat = $service->usesRequestPptxSource() ? 'PPTX' : 'DOCX';
                                ?>

                                <a href="<?php echo e(route('services.show', $service)); ?>"
                                    class="ult-service-ticker-card ult-reveal"
                                    aria-label="<?php echo e($serviceTitle); ?>">
                                    <div class="ult-service-ticker-card__content">
                                        <div class="ult-service-ticker-card__topline">
                                            <span class="ult-service-ticker-card__chip">
                                                <iconify-icon icon="heroicons:folder-solid"></iconify-icon>
                                                <?php echo e($serviceCategory); ?>

                                            </span>
                                            <span class="ult-service-ticker-card__format">
                                                <iconify-icon icon="heroicons:document-text-solid"></iconify-icon>
                                                <?php echo e($serviceFormat); ?>

                                            </span>
                                        </div>
                                        <h3 class="ult-service-ticker-card__title"><?php echo e($serviceTitle); ?></h3>
                                        <p class="ult-service-ticker-card__meta">
                                            <?php echo e(\Illuminate\Support\Str::limit($serviceSummary, 92, '...')); ?></p>
                                    </div>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="empty">
                        <?php echo e($isEn ? 'No services are available at the moment.' : 'Belum ada layanan yang tersedia saat ini.'); ?>

                    </div>
                <?php endif; ?>
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