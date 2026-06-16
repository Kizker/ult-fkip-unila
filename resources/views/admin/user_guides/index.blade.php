@extends('layouts.app')
@section('section', 'Panduan Pengguna')

@section('content')
    @php
        $total = method_exists($items, 'total') ? (int) $items->total() : (int) count($items);
        $shown = (int) $items->count();
        $publishedCount = (int) collect($items->items())->where('is_published', true)->count();
        $publicCount = (int) collect($items->items())->where('is_public', true)->count();
        $formatBytes = static function (int $bytes): string {
            if ($bytes < 1024) {
                return $bytes . ' B';
            }

            if ($bytes < 1048576) {
                return number_format($bytes / 1024, 1) . ' KB';
            }

            return number_format($bytes / 1048576, 2) . ' MB';
        };
    @endphp

    <div class="page-admin-user-guides page-admin-user-guides-index">
        <header class="admin-page-header">
            <div class="admin-page-heading">
                <div class="admin-page-kicker">Dokumen publik</div>
                <h1 class="admin-page-title">Panduan Pengguna</h1>
                <p class="admin-page-subtitle">
                    Kelola panduan PDF dan video tutorial, atur visibilitas per role, dan opsi akses umum tanpa login.
                </p>
            </div>
            <div class="admin-page-actions">
                <x-button href="{{ route('user_guides.index') }}" variant="ghost">Lihat halaman publik</x-button>
                <x-button href="{{ route('admin.user_guides.create') }}">Tambah Panduan</x-button>
            </div>
        </header>

        <section class="ug-stats-grid" aria-label="Ringkasan panduan pengguna">
            <article class="ug-stat-card">
                <div class="ug-stat-card__label">Total</div>
                <div class="ug-stat-card__value">{{ $total }}</div>
                <div class="ug-stat-card__caption">Semua data panduan</div>
            </article>
            <article class="ug-stat-card">
                <div class="ug-stat-card__label">Publish (halaman ini)</div>
                <div class="ug-stat-card__value">{{ $publishedCount }}</div>
                <div class="ug-stat-card__caption">Siap tampil di publik</div>
            </article>
            <article class="ug-stat-card">
                <div class="ug-stat-card__label">Akses umum</div>
                <div class="ug-stat-card__value">{{ $publicCount }}</div>
                <div class="ug-stat-card__caption">Bisa diakses tanpa login</div>
            </article>
        </section>

        <x-card class="ug-card admin-search-card" data-admin-search-card>
            <div class="admin-search" role="search" aria-label="Pencarian panduan pengguna">
                <div class="admin-search__toolbar">
                    <div class="admin-search__field">
                        <label for="admin-user-guides-live-search" class="sr-only">Cari panduan</label>
                        <div class="admin-search__input-wrap">
                            <input
                                id="admin-user-guides-live-search"
                                type="text"
                                class="admin-search__input"
                                placeholder="Cari..."
                                data-realtime-search-input
                                data-realtime-search-mode="filter"
                                data-realtime-search-scope=".ug-table-wrap"
                                data-realtime-search-item-selector="tbody tr[data-realtime-search-item]"
                                data-realtime-search-empty-selector="tbody tr[data-realtime-search-empty]"
                                data-realtime-search-count-selector="[data-realtime-search-count]"
                            >
                            <button type="button" class="admin-search__clear admin-search__clear--disabled" aria-label="Hapus pencarian" data-admin-search-clear data-reset-url="{{ route('admin.user_guides.index') }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M6 6l12 12M18 6l-12 12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-search-resultbar">
                <div class="admin-search-resultbar__count admin-search-resultbar__count--live" data-realtime-search-count data-default-count-text="Menampilkan {{ $shown }} dari {{ $total }} panduan">Menampilkan {{ $shown }} dari {{ $total }} panduan</div>
            </div>

            <div class="ug-table-wrap mt-4 overflow-x-auto">
                <table class="w-full text-sm ug-table">
                    <thead class="text-muted">
                        <tr>
                            <th class="text-left py-2">Judul</th>
                            <th class="text-left py-2">Akses</th>
                            <th class="text-left py-2">Role</th>
                            <th class="text-left py-2">File</th>
                            <th class="text-left py-2">Status</th>
                            <th class="text-right py-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $it)
                            @php
                                $roleText = $it->roles->pluck('name')->implode(', ');
                                $typeText = $it->isVideo() ? 'video youtube tutorial' : 'pdf dokumen file';
                                $searchText = trim(
                                    implode(' ', array_filter([
                                        $it->title_id,
                                        $it->title_en,
                                        $it->slug,
                                        $it->original_name,
                                        $it->video_url,
                                        $typeText,
                                        $roleText,
                                        $it->is_public ? 'public umum' : 'role login',
                                        $it->is_published ? 'published publish aktif' : 'draft nonaktif',
                                    ])),
                                );
                            @endphp
                            <tr class="border-t border-[rgb(var(--c-border))]" data-realtime-search-item
                                data-realtime-search-text="{{ $searchText }}">
                                <td class="py-3 align-top" data-label="Judul">
                                    <div class="font-semibold">{{ $it->title_id }}</div>
                                    @if (filled($it->title_en))
                                        <div class="text-xs text-muted mt-1">{{ $it->title_en }}</div>
                                    @endif
                                    <div class="text-xs text-muted mt-1">{{ $it->slug }}</div>
                                </td>
                                <td class="py-3 align-top" data-label="Akses">
                                    @if ($it->is_public)
                                        <x-badge variant="success">Umum</x-badge>
                                    @else
                                        <x-badge variant="warning">Login + Role</x-badge>
                                    @endif
                                </td>
                                <td class="py-3 align-top" data-label="Role">
                                    @if ($it->roles->isNotEmpty())
                                        <div class="ug-role-chips">
                                            @foreach ($it->roles as $role)
                                                <span class="ug-role-chip">{{ $role->name }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-muted">-</span>
                                    @endif
                                </td>
                                <td class="py-3 align-top" data-label="File">
                                    <div class="inline-flex flex-wrap gap-2 items-center">
                                        <x-badge :variant="$it->isVideo() ? 'warning' : 'secondary'">
                                            {{ $it->isVideo() ? 'Video' : 'PDF' }}
                                        </x-badge>
                                        <div class="font-medium">
                                            {{ $it->isPdf() ? $it->original_name : 'Tautan YouTube' }}
                                        </div>
                                    </div>
                                    <div class="text-xs text-muted">
                                        {{ $it->isPdf() ? $formatBytes((int) $it->size) : \Illuminate\Support\Str::limit((string) $it->video_url, 60, '...') }}
                                    </div>
                                </td>
                                <td class="py-3 align-top" data-label="Status">
                                    <x-badge :variant="$it->is_published ? 'success' : 'warning'">
                                        {{ $it->is_published ? 'Published' : 'Draft' }}
                                    </x-badge>
                                </td>
                                <td class="py-3 text-right align-top" data-label="Aksi">
                                    <div class="inline-flex flex-wrap justify-end gap-2">
                                        <x-button href="{{ $it->isPdf() ? route('user_guides.file', $it->slug) : route('user_guides.show', $it->slug) }}" variant="ghost" target="_blank">Preview</x-button>
                                        <x-button href="{{ route('admin.user_guides.edit', $it) }}" variant="secondary">Edit</x-button>
                                        <form method="POST" action="{{ route('admin.user_guides.destroy', $it) }}">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" variant="danger" data-confirm="Hapus panduan ini?">Hapus</x-button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="border-t border-[rgb(var(--c-border))]">
                                <td class="py-6 text-center text-muted" colspan="6">
                                    Belum ada panduan pengguna.
                                </td>
                            </tr>
                        @endforelse
                        @if($items->count() > 0)
                            <tr class="border-t border-[rgb(var(--c-border))] hidden" data-realtime-search-empty>
                                <td class="py-6 text-center text-muted" colspan="6">
                                    Tidak ada panduan yang cocok dengan pencarian.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $items->onEachSide(1)->links('components.public.pagination') }}
            </div>
        </x-card>
    </div>
@endsection
