<!doctype html>
<html lang="<?php echo e(app()->getLocale()); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#7c3aed">
    <link rel="icon" href="<?php echo e(asset('favicon.ico')); ?>" sizes="any">
    <link rel="icon" type="image/png" href="<?php echo e(asset('icons/icon-192.png')); ?>" sizes="192x192">
    <link rel="apple-touch-icon" href="<?php echo e(asset('icons/icon-192.png')); ?>">
    <title><?php echo $__env->yieldContent('title', 'Web ULT FKIP Unila'); ?></title>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css','resources/js/app.js']); ?>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-slate-900">
    <div class="page-public-site">
        <a class="public-skip-link" href="#main-content">Lewati ke konten utama</a>

        <?php if (isset($component)) { $__componentOriginala5f0778b7952fba1b2aaf8a771fcd23b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala5f0778b7952fba1b2aaf8a771fcd23b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.navbar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.navbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala5f0778b7952fba1b2aaf8a771fcd23b)): ?>
<?php $attributes = $__attributesOriginala5f0778b7952fba1b2aaf8a771fcd23b; ?>
<?php unset($__attributesOriginala5f0778b7952fba1b2aaf8a771fcd23b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala5f0778b7952fba1b2aaf8a771fcd23b)): ?>
<?php $component = $__componentOriginala5f0778b7952fba1b2aaf8a771fcd23b; ?>
<?php unset($__componentOriginala5f0778b7952fba1b2aaf8a771fcd23b); ?>
<?php endif; ?>

        <main id="main-content" class="public-main" role="main">
            <div class="public-container">
                <div class="public-flash">
                    <?php if (isset($component)) { $__componentOriginal5168fdb0c14fd91c6598264bc4be63f2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5168fdb0c14fd91c6598264bc4be63f2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.flash','data' => ['showValidation' => ($flashShowValidation ?? true),'showValidationList' => ($flashShowValidationList ?? false)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flash'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['show-validation' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($flashShowValidation ?? true)),'show-validation-list' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($flashShowValidationList ?? false))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5168fdb0c14fd91c6598264bc4be63f2)): ?>
<?php $attributes = $__attributesOriginal5168fdb0c14fd91c6598264bc4be63f2; ?>
<?php unset($__attributesOriginal5168fdb0c14fd91c6598264bc4be63f2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5168fdb0c14fd91c6598264bc4be63f2)): ?>
<?php $component = $__componentOriginal5168fdb0c14fd91c6598264bc4be63f2; ?>
<?php unset($__componentOriginal5168fdb0c14fd91c6598264bc4be63f2); ?>
<?php endif; ?>
                </div>

                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </main>

        <?php if (isset($component)) { $__componentOriginalbb84be681bbe94cc31d6257779433433 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbb84be681bbe94cc31d6257779433433 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.footer','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbb84be681bbe94cc31d6257779433433)): ?>
<?php $attributes = $__attributesOriginalbb84be681bbe94cc31d6257779433433; ?>
<?php unset($__attributesOriginalbb84be681bbe94cc31d6257779433433); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbb84be681bbe94cc31d6257779433433)): ?>
<?php $component = $__componentOriginalbb84be681bbe94cc31d6257779433433; ?>
<?php unset($__componentOriginalbb84be681bbe94cc31d6257779433433); ?>
<?php endif; ?>

        <?php if (isset($component)) { $__componentOriginal7cfab914afdd05940201ca0b2cbc009b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7cfab914afdd05940201ca0b2cbc009b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.toast','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('toast'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7cfab914afdd05940201ca0b2cbc009b)): ?>
<?php $attributes = $__attributesOriginal7cfab914afdd05940201ca0b2cbc009b; ?>
<?php unset($__attributesOriginal7cfab914afdd05940201ca0b2cbc009b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7cfab914afdd05940201ca0b2cbc009b)): ?>
<?php $component = $__componentOriginal7cfab914afdd05940201ca0b2cbc009b; ?>
<?php unset($__componentOriginal7cfab914afdd05940201ca0b2cbc009b); ?>
<?php endif; ?>
        
        <button id="backToTopBtn" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="fixed bottom-6 right-6 p-3 rounded-full bg-primary text-white shadow-lg hover:opacity-90 transition-all duration-300 z-50 flex items-center justify-center transform opacity-0 pointer-events-none translate-y-4" aria-label="Kembali ke atas">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                <path fill-rule="evenodd" d="M11.47 2.47a.75.75 0 011.06 0l7.5 7.5a.75.75 0 11-1.06 1.06l-6.22-6.22V21a.75.75 0 01-1.5 0V4.81l-6.22 6.22a.75.75 0 11-1.06-1.06l7.5-7.5z" clip-rule="evenodd" />
            </svg>
        </button>

        <script>
            window.addEventListener('scroll', function() {
                const btn = document.getElementById('backToTopBtn');
                if (window.scrollY > 300) {
                    btn.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-4');
                    btn.classList.add('opacity-100', 'pointer-events-auto', 'translate-y-0');
                } else {
                    btn.classList.remove('opacity-100', 'pointer-events-auto', 'translate-y-0');
                    btn.classList.add('opacity-0', 'pointer-events-none', 'translate-y-4');
                }
            });
        </script>
    </div>
</body>
</html>
<?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/layouts/public.blade.php ENDPATH**/ ?>