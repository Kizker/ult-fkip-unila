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
    <style>
        @media (min-width: 1280px) {
            .page-app-shell.is-admin .app-shell-main {
                padding-left: 18rem;
            }
        }
    </style>
</head>
<?php
  $isAdmin = request()->is('admin') || request()->is('admin/*') || request()->routeIs('admin.*');
  $hasUnreadNotifications = auth()->check() ? auth()->user()->unreadNotifications()->exists() : false;
  $isProfilePage = request()->routeIs('profile.edit');
?>
<body class="min-h-screen bg-white dark:bg-zinc-950">
    <div class="page-app-shell <?php echo e(($isAdmin && auth()->check()) ? 'is-admin' : ''); ?>" data-app-shell x-data>
        <?php if($isAdmin && auth()->check()): ?>
            <div class="app-shell-admin flex">
                <?php if (isset($component)) { $__componentOriginal790df3a3003b05a46d3e5fdd59aeab47 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal790df3a3003b05a46d3e5fdd59aeab47 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app.sidebar','data' => ['hasUnreadNotifications' => $hasUnreadNotifications]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app.sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['has-unread-notifications' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hasUnreadNotifications)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal790df3a3003b05a46d3e5fdd59aeab47)): ?>
<?php $attributes = $__attributesOriginal790df3a3003b05a46d3e5fdd59aeab47; ?>
<?php unset($__attributesOriginal790df3a3003b05a46d3e5fdd59aeab47); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal790df3a3003b05a46d3e5fdd59aeab47)): ?>
<?php $component = $__componentOriginal790df3a3003b05a46d3e5fdd59aeab47; ?>
<?php unset($__componentOriginal790df3a3003b05a46d3e5fdd59aeab47); ?>
<?php endif; ?>
                <div class="app-shell-main has-public-footer flex-1 min-w-0">
                    <?php if (isset($component)) { $__componentOriginal9679e66420851475f061bd7197575bd5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9679e66420851475f061bd7197575bd5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app.topbar','data' => ['hasUnreadNotifications' => $hasUnreadNotifications]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app.topbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['has-unread-notifications' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hasUnreadNotifications)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9679e66420851475f061bd7197575bd5)): ?>
<?php $attributes = $__attributesOriginal9679e66420851475f061bd7197575bd5; ?>
<?php unset($__attributesOriginal9679e66420851475f061bd7197575bd5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9679e66420851475f061bd7197575bd5)): ?>
<?php $component = $__componentOriginal9679e66420851475f061bd7197575bd5; ?>
<?php unset($__componentOriginal9679e66420851475f061bd7197575bd5); ?>
<?php endif; ?>
                    <div class="app-shell__container app-shell__content <?php echo e($isProfilePage ? 'public-container' : ''); ?>">
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

                        <?php echo $__env->yieldContent('content'); ?>
                    </div>
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
                </div>
            </div>
        <?php else: ?>
            <main class="app-shell-main has-public-footer">
                <?php if (isset($component)) { $__componentOriginal9679e66420851475f061bd7197575bd5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9679e66420851475f061bd7197575bd5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app.topbar','data' => ['hasUnreadNotifications' => $hasUnreadNotifications]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app.topbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['has-unread-notifications' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hasUnreadNotifications)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9679e66420851475f061bd7197575bd5)): ?>
<?php $attributes = $__attributesOriginal9679e66420851475f061bd7197575bd5; ?>
<?php unset($__attributesOriginal9679e66420851475f061bd7197575bd5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9679e66420851475f061bd7197575bd5)): ?>
<?php $component = $__componentOriginal9679e66420851475f061bd7197575bd5; ?>
<?php unset($__componentOriginal9679e66420851475f061bd7197575bd5); ?>
<?php endif; ?>
                <div class="app-shell__container app-shell__content <?php echo e($isProfilePage ? 'public-container' : ''); ?>">
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

                    <?php echo $__env->yieldContent('content'); ?>
                </div>
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
            </main>
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
    </div>
</body>
</html>
<?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/layouts/app.blade.php ENDPATH**/ ?>