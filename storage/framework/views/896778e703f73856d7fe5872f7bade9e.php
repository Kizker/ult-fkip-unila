<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'hasUnreadNotifications' => false,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'hasUnreadNotifications' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $isAdmin = request()->is('admin') || request()->is('admin/*') || request()->routeIs('admin.*');
    $locale = app()->getLocale();
    $user = auth()->user();
    $canViewStudentDashboard = auth()->check() && (bool) auth()->user()?->can('requests.view_own');
    $isProfilePage = request()->routeIs('profile.edit');
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
    $canAccessAdmin = (bool) $user?->canAny($adminPerms);
    $canAccessSigner = (bool) $user?->can('doc_signoffs.decide');
    $profileLinks = [];
    $dashboardHref = $canViewStudentDashboard ? route('student.dashboard') : null;
    $dashboardActive = $canViewStudentDashboard && request()->routeIs('student.dashboard');

    if ($canAccessAdmin) {
        $profileLinks[] = [
            'label' => 'Admin',
            'href' => route('admin.dashboard'),
            'active' => request()->routeIs('admin.*'),
        ];
    }

    if ($dashboardHref) {
        $profileLinks[] = [
            'label' => __('app.dashboard'),
            'href' => $dashboardHref,
            'active' => $dashboardActive,
        ];
    }

    if ($canAccessSigner) {
        $profileLinks[] = [
            'label' => 'Signer',
            'href' => route('signer.requests.inbox'),
            'active' => request()->routeIs('signer.requests.*'),
        ];
    }

    $profileLinks[] = [
        'label' => __('app.notifications'),
        'href' => route('notifications.index'),
        'active' => request()->routeIs('notifications.index'),
        'has_unread' => $hasUnreadNotifications,
    ];

    $profileLinks[] = [
        'label' => __('app.settings'),
        'href' => route('profile.edit'),
        'active' => request()->routeIs('profile.edit'),
    ];

    $profileLinks = collect($profileLinks)
        ->unique(fn ($link) => ($link['label'] ?? '').'|'.($link['href'] ?? ''))
        ->values()
        ->all();
?>

<header class="app-topbar" aria-label="Topbar">
    <div class="app-shell__container app-topbar__inner <?php echo e($isProfilePage ? 'public-container' : ''); ?>"
        x-data="{ profileOpen: false }" x-on:keydown.escape.window="profileOpen = false">
        <div class="app-topbar__left">
            <?php if($isAdmin && auth()->check()): ?>
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'ghost','class' => 'app-topbar__iconbtn xl:hidden','ariaLabel' => 'Toggle sidebar','xOn:click' => '$store.ui.toggleSidebar()']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','class' => 'app-topbar__iconbtn xl:hidden','aria-label' => 'Toggle sidebar','x-on:click' => '$store.ui.toggleSidebar()']); ?>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" class="h-5 w-5">
                        <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" />
                    </svg>
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
            <?php endif; ?>

            <a href="<?php echo e(route('home')); ?>" class="app-topbar__brand">
                <span class="app-topbar__logos" style="display:inline-flex;align-items:center;flex-direction:row;flex-wrap:nowrap;gap:.42rem;white-space:nowrap;line-height:0;">
                    <img
                        src="<?php echo e(asset('icons/unila.png')); ?>"
                        alt="Logo Universitas Lampung"
                        class="app-topbar__logo app-topbar__logo--unila"
                        style="display:block;"
                        loading="lazy"
                        decoding="async"
                    />
                    <img
                        src="<?php echo e(asset('icons/logo.png')); ?>"
                        alt="Logo FKIP Unila"
                        class="app-topbar__logo app-topbar__logo--fkip"
                        style="display:block;"
                        loading="lazy"
                        decoding="async"
                    />
                </span>
                <span class="app-topbar__mark">ULT</span>
                <span class="app-topbar__brandtext app-topbar__brand-desktop">
                    <span class="app-topbar__name">FKIP Unila</span>
                </span>
            </a>
        </div>

        <div class="app-topbar__right">
            <?php if (! ($isAdmin && auth()->check())): ?>
                <!-- Mobile: compact actions -->
                <div class="app-topbar__mobile xl:hidden">
                    <div class="app-topbar__profile">
                        <button type="button"
                            class="app-topbar__profile-trigger inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-full border border-[rgb(var(--c-border))] bg-white/80 px-2.5 py-1.5 text-[rgb(var(--c-fg))] shadow-sm dark:bg-zinc-900/75"
                            x-on:click="profileOpen = !profileOpen"
                            x-bind:aria-expanded="profileOpen ? 'true' : 'false'" aria-haspopup="menu">
                            <span class="app-topbar__profile-avatarwrap inline-flex items-center justify-center relative shrink-0">
                                <?php if (isset($component)) { $__componentOriginal7b28afafefdb45b1c735ce7c940afb50 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7b28afafefdb45b1c735ce7c940afb50 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.user.avatar','data' => ['user' => $user,'size' => '28','class' => 'app-topbar__profile-avatar block rounded-full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('user.avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'size' => '28','class' => 'app-topbar__profile-avatar block rounded-full']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7b28afafefdb45b1c735ce7c940afb50)): ?>
<?php $attributes = $__attributesOriginal7b28afafefdb45b1c735ce7c940afb50; ?>
<?php unset($__attributesOriginal7b28afafefdb45b1c735ce7c940afb50); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7b28afafefdb45b1c735ce7c940afb50)): ?>
<?php $component = $__componentOriginal7b28afafefdb45b1c735ce7c940afb50; ?>
<?php unset($__componentOriginal7b28afafefdb45b1c735ce7c940afb50); ?>
<?php endif; ?>
                                <?php if($hasUnreadNotifications): ?>
                                    <span class="app-topbar__profile-dot" aria-hidden="true"></span>
                                <?php endif; ?>
                            </span>
                            <span class="app-topbar__profile-arrow inline-flex h-[18px] w-[18px] items-center justify-center shrink-0 transition-transform duration-200"
                                aria-hidden="true" x-bind:class="profileOpen ? 'is-open rotate-180' : ''">
                                <svg viewBox="0 0 20 20" fill="none" class="block h-[18px] w-[18px]">
                                    <path d="M5 7.5 10 12.5 15 7.5" stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Desktop: full actions -->
            <div class="app-topbar__desktop hidden xl:flex items-center gap-2 justify-end">
                <div class="app-topbar__profile" x-on:click.outside="profileOpen = false">
                    <button type="button"
                        class="app-topbar__profile-trigger inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-full border border-[rgb(var(--c-border))] bg-white/80 px-2.5 py-1.5 text-[rgb(var(--c-fg))] shadow-sm dark:bg-zinc-900/75"
                        x-on:click="profileOpen = !profileOpen"
                        x-bind:aria-expanded="profileOpen ? 'true' : 'false'" aria-haspopup="menu">
                        <span class="app-topbar__profile-avatarwrap inline-flex items-center justify-center relative shrink-0">
                            <?php if (isset($component)) { $__componentOriginal7b28afafefdb45b1c735ce7c940afb50 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7b28afafefdb45b1c735ce7c940afb50 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.user.avatar','data' => ['user' => $user,'size' => '26','class' => 'app-topbar__profile-avatar block rounded-full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('user.avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'size' => '26','class' => 'app-topbar__profile-avatar block rounded-full']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7b28afafefdb45b1c735ce7c940afb50)): ?>
<?php $attributes = $__attributesOriginal7b28afafefdb45b1c735ce7c940afb50; ?>
<?php unset($__attributesOriginal7b28afafefdb45b1c735ce7c940afb50); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7b28afafefdb45b1c735ce7c940afb50)): ?>
<?php $component = $__componentOriginal7b28afafefdb45b1c735ce7c940afb50; ?>
<?php unset($__componentOriginal7b28afafefdb45b1c735ce7c940afb50); ?>
<?php endif; ?>
                            <?php if($hasUnreadNotifications): ?>
                                <span class="app-topbar__profile-dot" aria-hidden="true"></span>
                            <?php endif; ?>
                        </span>
                        <span class="app-topbar__profile-arrow inline-flex h-[18px] w-[18px] items-center justify-center shrink-0 transition-transform duration-200"
                            aria-hidden="true" x-bind:class="profileOpen ? 'is-open rotate-180' : ''">
                            <svg viewBox="0 0 20 20" fill="none" class="block h-[18px] w-[18px]">
                                <path d="M5 7.5 10 12.5 15 7.5" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </button>

                    <div class="app-topbar__menu app-topbar__menu--profile" x-show="profileOpen" x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                        aria-label="Menu akun">
                        <div class="app-topbar__menu-card">
                            <div class="app-topbar__profile-summary flex items-center justify-between gap-3 border-b border-[rgb(var(--c-border))/0.75] px-3 py-3">
                                <div class="app-topbar__profile-copy min-w-0 flex-1">
                                    <div class="app-topbar__profile-summary-name truncate text-sm font-semibold"><?php echo e($user?->name ?? 'Akun'); ?></div>
                                    <div class="app-topbar__profile-summary-email truncate text-xs text-muted"><?php echo e($user?->email ?? ''); ?></div>
                                </div>
                                <?php if (isset($component)) { $__componentOriginal7b28afafefdb45b1c735ce7c940afb50 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7b28afafefdb45b1c735ce7c940afb50 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.user.avatar','data' => ['user' => $user,'size' => '40','class' => 'app-topbar__profile-avatar block rounded-full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('user.avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'size' => '40','class' => 'app-topbar__profile-avatar block rounded-full']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7b28afafefdb45b1c735ce7c940afb50)): ?>
<?php $attributes = $__attributesOriginal7b28afafefdb45b1c735ce7c940afb50; ?>
<?php unset($__attributesOriginal7b28afafefdb45b1c735ce7c940afb50); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7b28afafefdb45b1c735ce7c940afb50)): ?>
<?php $component = $__componentOriginal7b28afafefdb45b1c735ce7c940afb50; ?>
<?php unset($__componentOriginal7b28afafefdb45b1c735ce7c940afb50); ?>
<?php endif; ?>
                            </div>

                            <div class="app-topbar__menu-group app-topbar__menu-group--split" aria-label="Preferensi">
                                <div class="app-topbar__menu-stack">
                                    <div class="app-topbar__menu-label">Bahasa</div>
                                    <div class="app-topbar__menu-group app-topbar__menu-group--language" aria-label="Bahasa">
                                        <a class="app-topbar__menu-item <?php echo e($locale === 'id' ? 'is-active' : ''); ?>"
                                            href="<?php echo e(route('locale.set', 'id')); ?>">ID</a>
                                        <a class="app-topbar__menu-item <?php echo e($locale === 'en' ? 'is-active' : ''); ?>"
                                            href="<?php echo e(route('locale.set', 'en')); ?>">EN</a>
                                    </div>
                                </div>
                                <div class="app-topbar__menu-stack app-topbar__menu-stack--theme">
                                    <div class="app-topbar__menu-label">Mode</div>
                                    <button type="button" class="pbtn public-theme-toggle app-topbar__menu-themebtn"
                                        x-on:click="$store.ui.toggleDark()" aria-label="Toggle theme">
                                        <span class="public-theme-toggle__icon" aria-hidden="true">
                                            <svg class="public-icon public-icon--sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M12 18a6 6 0 1 0 0-12 6 6 0 0 0 0 12Z"></path>
                                                    <path d="M12 2v2"></path><path d="M12 20v2"></path>
                                                    <path d="M4.93 4.93l1.41 1.41"></path><path d="M17.66 17.66l1.41 1.41"></path>
                                                    <path d="M2 12h2"></path><path d="M20 12h2"></path>
                                                    <path d="M6.34 17.66l-1.41 1.41"></path><path d="M19.07 4.93l-1.41 1.41"></path>
                                            </svg>
                                            <svg class="public-icon public-icon--moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"></path>
                                            </svg>
                                        </span>
                                        <span class="sr-only" data-theme-label>Toggle theme</span>
                                    </button>
                                </div>
                            </div>

                            <?php $__currentLoopData = $profileLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a class="app-topbar__menu-link <?php echo e($link['active'] ? 'is-active' : ''); ?>"
                                    href="<?php echo e($link['href']); ?>" x-on:click="profileOpen = false">
                                    <span><?php echo e($link['label']); ?></span>
                                    <?php if(!empty($link['has_unread'])): ?>
                                        <span class="app-notif-dot" aria-hidden="true"></span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            <form action="<?php echo e(route('logout')); ?>" method="POST" class="app-topbar__menu-form">
                                <?php echo csrf_field(); ?>
                                <button type="submit"
                                    class="app-topbar__menu-link app-topbar__menu-link--danger"><?php echo e(__('app.logout')); ?></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (! ($isAdmin && auth()->check())): ?>
            <div class="app-topbar__mobile-sheet xl:hidden" x-show="profileOpen" x-cloak x-transition.opacity.duration.120ms>
                <button type="button" class="app-topbar__mobile-backdrop" x-on:click="profileOpen = false"
                    aria-label="Tutup menu akun"></button>
                <div class="app-topbar__mobile-panel"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                    aria-label="Menu akun">
                    <div class="app-topbar__menu-card">
                        <div class="app-topbar__profile-summary flex items-center justify-between gap-3 border-b border-[rgb(var(--c-border))/0.75] px-3 py-3">
                            <div class="app-topbar__profile-copy min-w-0 flex-1">
                                <div class="app-topbar__profile-summary-name truncate text-sm font-semibold"><?php echo e($user?->name ?? 'Akun'); ?></div>
                                <div class="app-topbar__profile-summary-email truncate text-xs text-muted"><?php echo e($user?->email ?? ''); ?></div>
                            </div>
                            <?php if (isset($component)) { $__componentOriginal7b28afafefdb45b1c735ce7c940afb50 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7b28afafefdb45b1c735ce7c940afb50 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.user.avatar','data' => ['user' => $user,'size' => '38','class' => 'app-topbar__profile-avatar block rounded-full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('user.avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'size' => '38','class' => 'app-topbar__profile-avatar block rounded-full']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7b28afafefdb45b1c735ce7c940afb50)): ?>
<?php $attributes = $__attributesOriginal7b28afafefdb45b1c735ce7c940afb50; ?>
<?php unset($__attributesOriginal7b28afafefdb45b1c735ce7c940afb50); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7b28afafefdb45b1c735ce7c940afb50)): ?>
<?php $component = $__componentOriginal7b28afafefdb45b1c735ce7c940afb50; ?>
<?php unset($__componentOriginal7b28afafefdb45b1c735ce7c940afb50); ?>
<?php endif; ?>
                        </div>

                        <div class="app-topbar__menu-group app-topbar__menu-group--split" aria-label="Preferensi">
                            <div class="app-topbar__menu-stack">
                                <div class="app-topbar__menu-label">Bahasa</div>
                                <div class="app-topbar__menu-group app-topbar__menu-group--language" aria-label="Bahasa">
                                    <a class="app-topbar__menu-item <?php echo e($locale === 'id' ? 'is-active' : ''); ?>"
                                        href="<?php echo e(route('locale.set', 'id')); ?>">ID</a>
                                    <a class="app-topbar__menu-item <?php echo e($locale === 'en' ? 'is-active' : ''); ?>"
                                        href="<?php echo e(route('locale.set', 'en')); ?>">EN</a>
                                </div>
                            </div>
                            <div class="app-topbar__menu-stack app-topbar__menu-stack--theme">
                                <div class="app-topbar__menu-label">Mode</div>
                                <button type="button" class="pbtn public-theme-toggle app-topbar__menu-themebtn"
                                    x-on:click="$store.ui.toggleDark()" aria-label="Toggle theme">
                                    <span class="public-theme-toggle__icon" aria-hidden="true">
                                        <svg class="public-icon public-icon--sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 18a6 6 0 1 0 0-12 6 6 0 0 0 0 12Z"></path>
                                                <path d="M12 2v2"></path><path d="M12 20v2"></path>
                                                <path d="M4.93 4.93l1.41 1.41"></path><path d="M17.66 17.66l1.41 1.41"></path>
                                                <path d="M2 12h2"></path><path d="M20 12h2"></path>
                                                <path d="M6.34 17.66l-1.41 1.41"></path><path d="M19.07 4.93l-1.41 1.41"></path>
                                        </svg>
                                        <svg class="public-icon public-icon--moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"></path>
                                        </svg>
                                    </span>
                                    <span class="sr-only" data-theme-label>Toggle theme</span>
                                </button>
                            </div>
                        </div>

                        <?php $__currentLoopData = $profileLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a class="app-topbar__menu-link <?php echo e($link['active'] ? 'is-active' : ''); ?>"
                                href="<?php echo e($link['href']); ?>" x-on:click="profileOpen = false">
                                <span><?php echo e($link['label']); ?></span>
                                <?php if(!empty($link['has_unread'])): ?>
                                    <span class="app-notif-dot" aria-hidden="true"></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <form action="<?php echo e(route('logout')); ?>" method="POST" class="app-topbar__menu-form">
                            <?php echo csrf_field(); ?>
                            <button type="submit"
                                class="app-topbar__menu-link app-topbar__menu-link--danger"><?php echo e(__('app.logout')); ?></button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</header>
<?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/components/app/topbar.blade.php ENDPATH**/ ?>