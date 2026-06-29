<?php $__env->startSection('title', __('app.user_guides')); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $isEn = app()->getLocale() === 'en';
        $heroTitle = $isEn ? 'User Guide Library' : 'Perpustakaan Panduan Pengguna';
        $heroSubtitle = $isEn
            ? 'Browse PDF guides and video tutorials in one library.'
            : 'Temukan berbagai panduan pengguna dalam format PDF dan video tutorial dalam satu perpustakaan.';
        $searchTitle = $isEn ? 'Search Guides' : 'Cari Panduan';
        $searchPlaceholder = $isEn ? 'Search...' : 'Cari...';
        $searchCountTemplate = $isEn ? 'Showing :shown of :total guides' : 'Menampilkan :shown dari :total panduan';
        $total = method_exists($guides, 'total') ? (int) $guides->total() : (int) count($guides);
        $resultCountText = $isEn
            ? "Showing {$guides->count()} of {$total} guides"
            : "Menampilkan {$guides->count()} dari {$total} panduan";
        $heroImage = filled($heroBanner?->image_path ?? null)
            ? asset('storage/' . ltrim((string) $heroBanner->image_path, '/'))
            : asset('assets/images/blog/blog-details.png');
    ?>

    <div class="page-user-guides-index page-services-index services-v2" id="userGuidesIndexPage">
        <header class="services-v2-hero" style="--services-hero-image:url('<?php echo e($heroImage); ?>');">
            <div class="services-v2-hero__bg" aria-hidden="true"></div>
            <div class="services-v2-hero__veil" aria-hidden="true"></div>
            <div class="services-v2-hero__mesh" aria-hidden="true"></div>

            <div class="services-v2-hero__inner">
                <div class="services-v2-hero__copy" data-services-reveal>
                    <div class="services-v2-hero__kicker"><?php echo e($isEn ? 'Guides' : 'Panduan'); ?></div>
                    <h1 class="services-v2-hero__title"><?php echo e($heroTitle); ?></h1>
                    <p class="services-v2-hero__subtitle"><?php echo e($heroSubtitle); ?></p>
                </div>
            </div>

            <a href="#guides-catalog-section" class="services-v2-hero__scroll" data-scroll-to="#guides-catalog-section">
                <?php echo e($isEn ? 'View guides' : 'Lihat panduan'); ?>

            </a>
        </header>

        <section class="services-v2-search" aria-label="<?php echo e($isEn ? 'Search guides' : 'Cari panduan'); ?>" data-services-reveal>
            <div class="services-v2-search__head">
                <h2 class="services-v2-search__title"><?php echo e($searchTitle); ?></h2>
            </div>

            <form class="services-v2-search__form" role="search" aria-label="<?php echo e($isEn ? 'Search guides' : 'Cari panduan'); ?>">
                <div class="services-v2-search__toolbar services-v2-search__toolbar--solo">
                    <div class="services-v2-search__field services-v2-search__field--keyword">
                        <label class="services-v2-search__label sr-only" for="guides-q"><?php echo e($isEn ? 'Keyword' : 'Kata kunci'); ?></label>
                        <div class="services-v2-search__input-wrap">
                            <input id="guides-q" class="services-v2-input" name="q" value="<?php echo e($q); ?>"
                                placeholder="<?php echo e($searchPlaceholder); ?>"
                                data-realtime-search-input
                                data-realtime-search-mode="filter"
                                data-realtime-search-scope=".guides-card-grid"
                                data-realtime-search-item-selector="[data-realtime-search-item]"
                                data-realtime-search-empty-selector="[data-realtime-search-empty]"
                                data-realtime-search-count-selector="[data-realtime-search-count]"
                                data-realtime-search-count-template="<?php echo e($searchCountTemplate); ?>">
                            <button
                                type="button"
                                class="services-v2-search__clear <?php echo e(filled($q) ? '' : 'services-v2-search__clear--disabled'); ?>"
                                aria-label="<?php echo e($isEn ? 'Reset search' : 'Reset pencarian'); ?>"
                                data-services-clear-search
                                data-reset-url="<?php echo e(route('user_guides.index')); ?>">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M6 6l12 12M18 6l-12 12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </section>

        <section class="services-v2-catalog" aria-label="<?php echo e($isEn ? 'Guide result info' : 'Info hasil panduan'); ?>">
            <div class="services-v2-resultbar" data-services-reveal style="justify-content:flex-end;">
                <div class="services-v2-resultbar__count" data-realtime-search-count><?php echo e($resultCountText); ?></div>
            </div>
        </section>

        <section id="guides-catalog-section" class="page-section" aria-label="<?php echo e($isEn ? 'Guide list' : 'Daftar panduan'); ?>" data-services-reveal>
            <div class="card-grid guides-card-grid <?php echo e($guides->count() === 1 ? 'card-grid--single' : ''); ?>" data-infinite-container>
                <?php if($guides->count() > 0): ?>
                    <?php echo $__env->make('public.user_guides._items', compact('guides', 'isEn'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php else: ?>
                    <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'card-item','dataServicesReveal' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'card-item','data-services-reveal' => true]); ?>
                        <div class="card-title"><?php echo e($isEn ? 'No guides available' : 'Belum ada panduan tersedia'); ?></div>
                        <p class="card-desc">
                            <?php echo e($isEn ? 'No published guide can be shown for your account right now.' : 'Belum ada panduan terbit yang bisa ditampilkan untuk akun Anda saat ini.'); ?>

                        </p>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $attributes = $__attributesOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__attributesOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $component = $__componentOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__componentOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
                <?php endif; ?>
                <?php if($guides->count() > 0): ?>
                    <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'card-item hidden','dataRealtimeSearchEmpty' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'card-item hidden','data-realtime-search-empty' => true]); ?>
                        <div class="card-title"><?php echo e($isEn ? 'Guide not found' : 'Panduan tidak ditemukan'); ?></div>
                        <p class="card-desc">
                            <?php echo e($isEn ? 'Try another keyword for realtime search.' : 'Coba kata kunci lain untuk pencarian realtime.'); ?>

                        </p>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $attributes = $__attributesOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__attributesOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $component = $__componentOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__componentOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
                <?php endif; ?>
            </div>
        </section>

        <div class="page-pagination" data-services-reveal data-infinite-pagination>
            <?php echo e($guides->onEachSide(1)->links('components.public.pagination')); ?>

        </div>
        <?php if($guides->count() > 0): ?>
            <div class="public-infinite-load" data-infinite-list data-next-page-url="<?php echo e($guides->nextPageUrl() ?? ''); ?>"
                data-end-text="<?php echo e($isEn ? 'All guides have been loaded' : 'Semua panduan sudah dimuat'); ?>"
                data-load-more-text="<?php echo e($isEn ? 'Load more' : 'Muat lebih banyak'); ?>"
                data-loading-text="<?php echo e($isEn ? 'Loading...' : 'Memuat...'); ?>"
                data-error-text="<?php echo e($isEn ? 'Failed to load. Try again.' : 'Gagal memuat. Coba lagi.'); ?>">
                <button type="button" class="public-infinite-load__button" <?php if(!$guides->hasMorePages()): echo 'disabled'; endif; ?> data-infinite-load-more>
                    <?php echo e($isEn ? 'Load more' : 'Muat lebih banyak'); ?>

                </button>
                <div class="public-infinite-load__status" data-infinite-status aria-live="polite"></div>
                <div class="public-infinite-load__sentinel" data-infinite-sentinel aria-hidden="true"></div>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/public/user_guides/index.blade.php ENDPATH**/ ?>