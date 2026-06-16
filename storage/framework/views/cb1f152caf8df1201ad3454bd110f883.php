<?php $__env->startSection('title', __('app.about')); ?>

<?php $__env->startSection('content'); ?>
<?php
    $isEn = app()->getLocale() === 'en';
    $heroTitle = $isEn ? 'About ULT FKIP Unila' : 'Tentang ULT FKIP Unila';
    $heroSubtitle = $isEn
        ? 'Learn more about ULT FKIP Unila - our services, commitments, and how we help you.'
        : 'Mengenal lebih dekat ULT FKIP Unila - layanan, komitmen, dan bagaimana kami membantu Anda.';
    $contentKicker = $isEn ? 'About Profile' : 'Profil Layanan';
    $contentTitle = $isEn ? 'Information and Service Commitment' : 'Informasi dan Komitmen Layanan';
    $contentSubtitle = $isEn
        ? 'Find key information about ULT FKIP Unila and explore supporting links in one tidy section.'
        : 'Temukan informasi utama tentang ULT FKIP Unila dan akses tautan pendukung dalam satu section yang rapi.';
    $heroImage = filled($heroBanner?->image_path ?? null)
        ? asset('storage/' . ltrim((string) $heroBanner->image_path, '/'))
        : asset('assets/images/blog/blog-details.png');
?>

<div class="page-about page-services-index services-v2" id="aboutPage">
    <header class="services-v2-hero" data-services-hero-image="<?php echo e($heroImage); ?>">
        <div class="services-v2-hero__bg" aria-hidden="true"></div>
        <div class="services-v2-hero__veil" aria-hidden="true"></div>
        <div class="services-v2-hero__mesh" aria-hidden="true"></div>

        <div class="services-v2-hero__inner">
            <div class="services-v2-hero__copy" data-services-reveal>
                <div class="services-v2-hero__kicker"><?php echo e($isEn ? 'About' : 'Tentang'); ?></div>
                <h1 class="services-v2-hero__title"><?php echo e($heroTitle); ?></h1>
                <p class="services-v2-hero__subtitle"><?php echo e($heroSubtitle); ?></p>
            </div>
        </div>

        <a href="#about-content-section"
            class="services-v2-hero__scroll"
            data-scroll-to="#about-content-section">
            <?php echo e($isEn ? 'View content' : 'Lihat konten'); ?>

        </a>
    </header>

    <section id="about-content-section" class="about-content-section page-section" aria-label="<?php echo e(__('app.about')); ?>">
        <div class="about-content-shell">
            <div class="about-overview" data-services-reveal>
                <div class="about-overview__kicker"><?php echo e($contentKicker); ?></div>
                <h2 class="about-overview__title"><?php echo e($contentTitle); ?></h2>
                <p class="about-overview__subtitle"><?php echo e($contentSubtitle); ?></p>
                <div class="about-overview__chips" aria-label="<?php echo e($isEn ? 'About highlights' : 'Sorotan tentang ULT'); ?>">
                    <span class="about-overview__chip"><?php echo e($isEn ? 'Academic services' : 'Layanan akademik'); ?></span>
                    <span class="about-overview__chip"><?php echo e($isEn ? 'Student support' : 'Dukungan mahasiswa'); ?></span>
                    <span class="about-overview__chip"><?php echo e($isEn ? 'Integrated information' : 'Informasi terintegrasi'); ?></span>
                </div>
            </div>

            <div class="about-grid">
                <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'about-card','dataServicesReveal' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'about-card','data-services-reveal' => true]); ?>
                    <div class="prose prose-sm dark:prose-invert about-prose">
                        <?php echo $isEn ? ($aboutEn ?? $aboutId ?? '<p><strong>DATA TIDAK TERSEDIA: Konten Tentang ULT</strong></p>') : ($aboutId ?? '<p><strong>DATA TIDAK TERSEDIA: Konten Tentang ULT</strong></p>'); ?>

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

                <aside class="about-side" aria-label="<?php echo e($isEn ? 'Quick links' : 'Tautan cepat'); ?>" data-services-reveal>
                    <div class="about-side__panel">
                        <div class="about-side__title"><?php echo e($isEn ? 'Quick links' : 'Tautan cepat'); ?></div>
                        <div class="about-side__links">
                            <a class="about-link" href="<?php echo e(route('services.index')); ?>">
                                <span class="about-link__label"><?php echo e(__('app.services')); ?></span>
                                <span class="about-link__chev" aria-hidden="true">&rarr;</span>
                            </a>
                            <a class="about-link" href="<?php echo e(route('announcements.index')); ?>">
                                <span class="about-link__label"><?php echo e(__('app.announcements')); ?></span>
                                <span class="about-link__chev" aria-hidden="true">&rarr;</span>
                            </a>
                            <a class="about-link" href="<?php echo e(route('blog.index')); ?>">
                                <span class="about-link__label"><?php echo e(__('app.blog')); ?></span>
                                <span class="about-link__chev" aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/public/about.blade.php ENDPATH**/ ?>