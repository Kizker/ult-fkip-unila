@props([
    'hasUnreadNotifications' => false,
])

@php
    $requestOpsPerms = ['requests.view_any', 'requests.view_unit', 'requests.review_ult'];
    $menuGroups = [
        [
            'label' => 'Utama',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'can_any' => $requestOpsPerms],
                ['label' => 'Permohonan', 'route' => 'admin.requests.index', 'can_any' => $requestOpsPerms],
                ['label' => 'Kritik dan Saran', 'route' => 'admin.feedback.index', 'can' => 'feedbacks.manage'],
                ['label' => 'Signer Inbox', 'route' => 'signer.requests.inbox', 'can' => 'doc_signoffs.decide'],
            ],
        ],
        [
            'label' => 'Layanan Akademik',
            'items' => [
                ['label' => 'Layanan', 'route' => 'admin.layanan.index', 'can' => 'services.manage'],
                ['label' => 'Jurusan', 'route' => 'admin.jurusan.index', 'role' => 'Superadmin'],
                ['label' => 'Program Studi', 'route' => 'admin.prodi.index', 'role' => 'Superadmin'],
                [
                    'label' => 'Panduan Placeholder',
                    'route' => 'admin.layanan.placeholder_guide',
                    'can_any' => ['doc_services.manage', 'doc_placeholders.manage', 'doc_templates.upload'],
                ],
                [
                    'label' => 'Template Nomor Surat',
                    'route' => 'admin.letter_formats.index',
                    'can' => 'letter_numbers.manage_formats',
                ],
            ],
        ],
        [
            'label' => 'Konten Website',
            'items' => [
                [
                    'label' => 'Manajemen Konten',
                    'route' => 'admin.cms.index',
                    'can' => 'cms.manage',
                    'scope' => 'admin.cms.',
                ],
                ['label' => 'Blog', 'route' => 'admin.cms.blogs.index', 'can' => 'cms.manage', 'indent' => true],
                [
                    'label' => 'Pengumuman',
                    'route' => 'admin.cms.announcements.index',
                    'can' => 'cms.manage',
                    'indent' => true,
                ],
                [
                    'label' => 'Kategori Layanan',
                    'route' => 'admin.cms.categories.index',
                    'can' => 'cms.manage',
                    'indent' => true,
                ],
                ['label' => 'Banner Hero', 'route' => 'admin.cms.hero.edit', 'can' => 'cms.manage', 'indent' => true],
                [
                    'label' => 'Panduan Pengguna',
                    'route' => 'admin.user_guides.index',
                    'role' => 'Superadmin',
                ],
                [
                    'label' => 'Pengaturan Website',
                    'route' => 'admin.cms.settings.edit',
                    'can' => 'site_settings.manage',
                ],
            ],
        ],
        [
            'label' => 'Sistem & Keamanan',
            'items' => [
                ['label' => 'Pengguna', 'route' => 'admin.users.index', 'can' => 'users.manage'],
                ['label' => 'Peran', 'route' => 'admin.roles.index', 'can' => 'roles.manage', 'indent' => true],
                ['label' => 'Log Audit', 'route' => 'admin.audit.index', 'can' => 'audit_logs.view'],
            ],
        ],
    ];
    $locale = app()->getLocale();
@endphp

<!-- Mobile overlay -->
<div x-show="$store.ui.sidebarOpen" x-transition.opacity.duration.120ms x-cloak class="fixed inset-0 z-40 bg-black/40 xl:hidden"
    x-on:click="$store.ui.closeSidebar()" aria-hidden="true"></div>

<aside
    class="app-sidebar fixed inset-y-0 left-0 z-50 w-72 -translate-x-full xl:translate-x-0 transition-transform duration-200 ease-out"
    :class="$store.ui.sidebarOpen ? 'translate-x-0' : '-translate-x-full'" x-cloak aria-label="Sidebar Admin">
    <div
        class="app-sidebar__inner h-full flex flex-col bg-white dark:bg-zinc-950 border-r border-[rgb(var(--c-border))] lg:h-screen">
        <div class="app-sidebar__top px-3 pb-3" style="padding-top: 0.75rem;">
            <div class="flex items-start justify-between gap-3">
                <a href="{{ route('admin.dashboard') }}"
                    class="app-sidebar__hero block flex-1 min-w-0 rounded-2xl p-4 bg-gradient-to-br from-violet-600 to-fuchsia-500 text-white shadow">
                    <div class="flex items-center gap-3 min-w-0">
                        <x-user.avatar :user="auth()->user()" size="44"
                            class="rounded-2xl shrink-0 ring-1 ring-white/35 shadow-sm" />
                        <div class="min-w-0">
                            <div class="text-xs opacity-90">Login sebagai</div>
                            <div class="text-lg font-semibold mt-1 leading-tight truncate">
                                {{ auth()->user()?->name ?? '-' }}</div>
                            <div class="text-xs opacity-90 mt-1 truncate">{{ auth()->user()?->email ?? '-' }}</div>
                        </div>
                    </div>
                </a>
                <x-button variant="ghost" class="app-sidebar__close xl:hidden" aria-label="Tutup sidebar"
                    x-on:click="$store.ui.closeSidebar()">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" class="h-5 w-5">
                        <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </x-button>
            </div>
        </div>

        <nav class="app-sidebar__nav px-3 pb-4 flex-1 overflow-y-auto">
            <div class="app-sidebar__list space-y-3">
                @foreach ($menuGroups as $group)
                    @php
                        $visibleItems = collect($group['items'] ?? [])
                            ->map(function ($item) {
                                $can = $item['can'] ?? null;
                                $canAny = $item['can_any'] ?? null;
                                $role = $item['role'] ?? null;
                                $indent = (bool) ($item['indent'] ?? false);
                                $scope = $item['scope'] ?? null;
                                $inScope = is_string($scope) ? request()->routeIs($scope . '*') : false;
                                $isAllowed = false;
                                if (is_string($role) && $role !== '') {
                                    $isAllowed = auth()->user()?->hasRole($role) ?? false;
                                } elseif (is_array($role) && !empty($role)) {
                                    $isAllowed = auth()->user()?->hasAnyRole($role) ?? false;
                                } elseif (is_string($can) && $can !== '') {
                                    $isAllowed = auth()->user()?->can($can) ?? false;
                                } elseif (is_array($canAny) && !empty($canAny)) {
                                    $isAllowed = auth()->user()?->canAny($canAny) ?? false;
                                }

                                $activeExact = request()->routeIs($item['route']);
                                $activeScope = false;
                                if (!$activeExact && $indent && str_ends_with($item['route'], '.index')) {
                                    $activeScope = request()->routeIs(str_replace('.index', '.*', $item['route']));
                                }
                                if (!$activeExact && !$indent && !$scope && str_ends_with($item['route'], '.index')) {
                                    $activeScope = request()->routeIs(str_replace('.index', '.*', $item['route']));
                                }

                                return array_merge($item, [
                                    'indent' => $indent,
                                    'is_allowed' => $isAllowed,
                                    'active_exact' => $activeExact,
                                    'active_scope' => $activeScope,
                                    'in_scope' => $inScope,
                                ]);
                            })
                            ->filter(fn($item) => $item['is_allowed'])
                            ->values();

                        $hasExactActive = $visibleItems->contains(fn($item) => $item['active_exact']);
                        $visibleItems = $visibleItems
                            ->map(function ($item) use ($hasExactActive) {
                                $active = (bool) ($item['active_exact'] || (!$hasExactActive && $item['active_scope']));
                                $open = !$active && !$item['indent'] && (bool) ($item['in_scope'] ?? false);

                                $item['active'] = $active;
                                $item['open'] = $open;

                                return $item;
                            })
                            ->values();

                        $hasActive = $visibleItems->contains(fn($item) => $item['active'] || $item['open']);
                    @endphp
                    @if ($visibleItems->isNotEmpty())
                        <section
                            class="app-sidebar__group rounded-2xl border border-[rgb(var(--c-border))] p-2 {{ $hasActive ? 'is-current bg-zinc-50/70 dark:bg-zinc-900/40' : 'bg-white/70 dark:bg-zinc-950/50' }}">
                            <div
                                class="app-sidebar__group-label px-2 pb-1 text-[11px] font-semibold tracking-wide uppercase text-muted">
                                {{ $group['label'] }}
                            </div>
                            <div class="app-sidebar__group-list space-y-1">
                                @foreach ($visibleItems as $item)
                                    <a href="{{ route($item['route']) }}"
                                        class="app-sidebar__link flex items-center gap-3 rounded-xl px-3 py-2 text-sm border border-transparent hover:bg-zinc-50 dark:hover:bg-zinc-900/60 hover:border-[rgb(var(--c-border))] transition
                    {{ $item['active'] ? 'is-active bg-zinc-50 dark:bg-zinc-900/60 border-[rgb(var(--c-border))]' : '' }}
                    {{ $item['open'] ? 'is-open' : '' }}
                    {{ $item['indent'] ? 'is-child ml-4' : '' }}">
                                        <span
                                            class="app-sidebar__dot h-2.5 w-2.5 rounded-full {{ $item['active'] ? 'bg-[rgb(var(--c-primary))]' : ($item['open'] ? 'bg-[rgb(var(--c-primary)/0.35)]' : 'bg-zinc-300 dark:bg-zinc-700') }}"></span>
                                        <span class="font-medium">{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif
                @endforeach
            </div>
        </nav>

        <!-- Mobile quick actions (move from crowded topbar) -->
        <div class="app-sidebar__mobile xl:hidden px-3 pb-3">
            <div
                class="app-sidebar__mobile-card rounded-xl p-3 bg-zinc-50 dark:bg-zinc-900/60 border border-[rgb(var(--c-border))]">
                <div class="text-xs font-semibold text-muted mb-2">Aksi cepat</div>

                <div class="app-sidebar__mobile-row">
                    <a class="app-sidebar__chip {{ $locale === 'id' ? 'is-active' : '' }}"
                        href="{{ route('locale.set', 'id') }}">ID</a>
                    <a class="app-sidebar__chip {{ $locale === 'en' ? 'is-active' : '' }}"
                        href="{{ route('locale.set', 'en') }}">EN</a>
                    <button type="button" class="app-sidebar__chip app-sidebar__chip--theme"
                        x-on:click="$store.ui.toggleDark()" aria-label="Toggle theme">
                        <span class="app-theme-switch" x-bind:class="$store.ui.dark ? 'is-dark' : ''">
                            <span class="app-theme-switch__icon app-theme-switch__icon--sun" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 18a6 6 0 1 0 0-12 6 6 0 0 0 0 12Z"></path>
                                    <path d="M12 2v2"></path>
                                    <path d="M12 20v2"></path>
                                    <path d="M4.93 4.93l1.41 1.41"></path>
                                    <path d="M17.66 17.66l1.41 1.41"></path>
                                    <path d="M2 12h2"></path>
                                    <path d="M20 12h2"></path>
                                    <path d="M6.34 17.66l-1.41 1.41"></path>
                                    <path d="M19.07 4.93l-1.41 1.41"></path>
                                </svg>
                            </span>
                            <span class="app-theme-switch__icon app-theme-switch__icon--moon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"></path>
                                </svg>
                            </span>
                            <span class="app-theme-switch__thumb" aria-hidden="true"></span>
                        </span>
                    </button>
                </div>

                <div class="mt-2 grid gap-2">
                    <a class="app-sidebar__action app-notif-trigger {{ $hasUnreadNotifications ? 'has-unread' : '' }}"
                        href="{{ route('notifications.index') }}">
                        <span>{{ __('app.notifications') }}</span>
                        @if ($hasUnreadNotifications)
                            <span class="app-notif-dot" aria-hidden="true"></span>
                        @endif
                    </a>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="app-sidebar__action app-sidebar__action--danger">{{ __('app.logout') }}</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</aside>
