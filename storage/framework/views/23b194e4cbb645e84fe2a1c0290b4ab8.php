<?php $__env->startSection('title', __('app.services')); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $total = method_exists($services, 'total') ? (int) $services->total() : (int) count($services);
        $currentCategory = collect($categories ?? [])->firstWhere('id', (int) $category);
        $hasFilter = filled($q) || filled($category);
        $isEn = app()->getLocale() === 'en';

        $heroTitle = $isEn ? 'Find and Apply Services' : 'Temukan dan Ajukan Layanan';

        $heroSubtitle = $isEn
            ? 'Browse categories, review requirements, and submit requests online in minutes.'
            : 'Telusuri kategori, cek persyaratan, lalu ajukan layanan secara online dalam hitungan menit.';

        $heroImage = filled($heroBanner?->image_path ?? null)
            ? asset('storage/' . ltrim((string) $heroBanner->image_path, '/'))
            : asset('assets/images/blog/blog-details.png');

        $categoryLabel = $currentCategory
            ? ($isEn
                ? $currentCategory->name_en ?? $currentCategory->name_id
                : $currentCategory->name_id)
            : ($isEn
                ? 'All categories'
                : 'Semua kategori');
    ?>

    <div class="page-services-index services-v2" id="servicesIndexPage">
        <header class="services-v2-hero" style="--services-hero-image:url('<?php echo e($heroImage); ?>');">
            <div class="services-v2-hero__bg" aria-hidden="true"></div>
            <div class="services-v2-hero__veil" aria-hidden="true"></div>
            <div class="services-v2-hero__mesh" aria-hidden="true"></div>

            <div class="services-v2-hero__inner">
                <div class="services-v2-hero__copy" data-services-reveal>
                    <div class="services-v2-hero__kicker"><?php echo e($isEn ? 'Services' : 'Layanan'); ?></div>
                    <h1 class="services-v2-hero__title"><?php echo e($heroTitle); ?></h1>
                    <p class="services-v2-hero__subtitle"><?php echo e($heroSubtitle); ?></p>

                </div>
            </div>

            <a href="#services-catalog-section"
                class="services-v2-hero__scroll"
                data-scroll-to="#services-catalog-section">
                <?php echo e($isEn ? 'Find services' : 'Cari layanan'); ?>

            </a>
        </header>

        <section id="services-search-panel" class="services-v2-search" aria-label="Filter layanan" data-services-reveal>
            <div class="services-v2-search__head">
                <h2 class="services-v2-search__title"><?php echo e($isEn ? 'Search & Filter Services' : 'Cari dan Filter Layanan'); ?>

                </h2>
            </div>

            <form class="services-v2-search__form" method="GET" role="search" aria-label="Cari layanan">
                <div class="services-v2-search__toolbar">
                    <div class="services-v2-search__field services-v2-search__field--keyword">
                        <label class="services-v2-search__label sr-only" for="services-q"><?php echo e($isEn ? 'Keyword' : 'Kata kunci'); ?></label>
                        <div class="services-v2-search__input-wrap">
                            <input id="services-q" class="services-v2-input" name="q" value="<?php echo e($q); ?>"
                                placeholder="<?php echo e($isEn ? 'Search...' : 'Cari...'); ?>"
                                data-realtime-search-input data-realtime-search-mode="filter"
                                data-realtime-search-scope=".services-catalog-grid"
                                data-realtime-search-item-selector="[data-realtime-search-item]"
                                data-realtime-search-empty-selector="[data-realtime-search-empty]"
                                data-realtime-search-count-selector="[data-realtime-search-count]">
                            <button
                                type="button"
                                class="services-v2-search__clear <?php echo e($hasFilter ? '' : 'services-v2-search__clear--disabled'); ?>"
                                aria-label="<?php echo e($isEn ? 'Reset search and filters' : 'Reset pencarian dan filter'); ?>"
                                data-services-clear-search
                                data-reset-url="<?php echo e(route('services.index')); ?>">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M6 6l12 12M18 6l-12 12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <details class="services-v2-filter-menu">
                        <summary class="services-v2-filter-menu__toggle" aria-label="<?php echo e($isEn ? 'Open filters' : 'Buka filter'); ?>">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M4 7h16M7 12h10M10 17h4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
                            </svg>
                        </summary>

                        <div class="services-v2-filter-menu__panel">
                            <div class="services-v2-search__field services-v2-search__field--category">
                                <label class="services-v2-search__label"
                                    for="services-category"><?php echo e($isEn ? 'Category' : 'Kategori'); ?></label>
                                <select id="services-category" name="category" class="services-v2-input" onchange="this.form.requestSubmit()">
                                    <option value=""><?php echo e($isEn ? 'All services' : 'Semua layanan'); ?></option>
                                    <?php $__currentLoopData = $categories ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($cat->id); ?>" <?php if((string) $category === (string) $cat->id): echo 'selected'; endif; ?>>
                                            <?php echo e($isEn ? $cat->name_en ?? $cat->name_id : $cat->name_id); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <?php if($hasFilter): ?>
                                <a class="services-v2-filter-menu__reset" href="<?php echo e(route('services.index')); ?>">
                                    <?php echo e($isEn ? 'Reset filters' : 'Reset filter'); ?>

                                </a>
                            <?php endif; ?>
                        </div>
                    </details>
                </div>
            </form>
        </section>

        <section id="services-catalog-section" class="services-v2-catalog" aria-label="Daftar layanan">
            <div class="services-v2-resultbar" data-services-reveal style="justify-content:flex-end;">
                <div class="services-v2-resultbar__count" data-realtime-search-count>
                    <?php echo e($isEn ? "Showing {$services->count()} of {$total} services" : "Menampilkan {$services->count()} dari {$total} layanan"); ?>

                </div>
                <?php if($hasFilter): ?>
                    <div class="services-v2-resultbar__chips">
                        <?php if(filled($q)): ?>
                            <span class="services-v2-chip"><?php echo e($isEn ? 'Query' : 'Pencarian'); ?>:
                                "<?php echo e($q); ?>"</span>
                        <?php endif; ?>
                        <?php if($currentCategory): ?>
                            <span class="services-v2-chip"><?php echo e($categoryLabel); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="services-catalog-grid" data-infinite-container>
                <?php if($services->count() > 0): ?>
                    <?php echo $__env->make('public.services._items', ['services' => $services, 'isEn' => $isEn, 'loopOffset' => 0], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php else: ?>
                    <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'services-v2-empty','dataServicesReveal' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'services-v2-empty','data-services-reveal' => true]); ?>
                        <div class="services-v2-empty__title"><?php echo e($isEn ? 'Service not found' : 'Layanan tidak ditemukan'); ?>

                        </div>
                        <div class="services-v2-empty__text">
                            <?php echo e($isEn ? 'Try another keyword or choose a different category.' : 'Coba ubah kata kunci atau pilih kategori lain.'); ?>

                        </div>
                        <?php if($hasFilter): ?>
                            <div class="services-v2-empty__action">
                                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('services.index')).'','variant' => 'secondary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('services.index')).'','variant' => 'secondary']); ?>
                                    <?php echo e($isEn ? 'Show all services' : 'Lihat semua layanan'); ?>

                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
                            </div>
                        <?php endif; ?>
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
                <?php if($services->count() > 0): ?>
                    <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'services-v2-empty hidden','dataRealtimeSearchEmpty' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'services-v2-empty hidden','data-realtime-search-empty' => true]); ?>
                        <div class="services-v2-empty__title"><?php echo e($isEn ? 'Service not found' : 'Layanan tidak ditemukan'); ?>

                        </div>
                        <div class="services-v2-empty__text">
                            <?php echo e($isEn ? 'Try another keyword for realtime search.' : 'Coba kata kunci lain untuk pencarian realtime.'); ?>

                        </div>
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

        <div class="page-pagination services-v2-pagination" data-services-reveal data-infinite-pagination>
            <?php echo e($services->onEachSide(1)->links('components.public.pagination')); ?>

        </div>
        <?php if($services->count() > 0): ?>
            <div class="public-infinite-load" data-infinite-list data-next-page-url="<?php echo e($services->nextPageUrl() ?? ''); ?>"
                data-end-text="<?php echo e($isEn ? 'All services have been loaded' : 'Semua layanan sudah dimuat'); ?>"
                data-load-more-text="<?php echo e($isEn ? 'Load more' : 'Muat lebih banyak'); ?>"
                data-loading-text="<?php echo e($isEn ? 'Loading...' : 'Memuat...'); ?>"
                data-error-text="<?php echo e($isEn ? 'Failed to load. Try again.' : 'Gagal memuat. Coba lagi.'); ?>">
                <button type="button" class="public-infinite-load__button" <?php if(!$services->hasMorePages()): echo 'disabled'; endif; ?> data-infinite-load-more>
                    <?php echo e($isEn ? 'Load more' : 'Muat lebih banyak'); ?>

                </button>
                <div class="public-infinite-load__status" data-infinite-status aria-live="polite"></div>
                <div class="public-infinite-load__sentinel" data-infinite-sentinel aria-hidden="true"></div>
            </div>
        <?php endif; ?>
    </div>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/public/services/index.blade.php ENDPATH**/ ?>