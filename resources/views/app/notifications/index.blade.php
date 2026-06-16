@extends('layouts.app')
@section('section', 'Notifikasi')
@section('content')
    @php
        $total = method_exists($notifications, 'total') ? (int) $notifications->total() : (int) count($notifications);
        $unread = (int) ($unreadTotal ?? 0);
        $read = max(0, $total - $unread);
    @endphp

    <div class="page-app-notifications-index">
        <header class="app-page-header">
            <div class="app-page-heading">
                <div class="app-page-kicker">Akun</div>
                <h1 class="app-page-title">Notifikasi</h1>
                <p class="app-page-subtitle">Update terkait proses layanan, perubahan status, dan informasi penting lainnya.
                </p>
            </div>

            <div class="app-page-actions">
                <div class="app-meta">
                    <div class="app-meta-pill" aria-label="Total notifikasi">
                        <div class="app-meta-pill__label">Total</div>
                        <div class="app-meta-pill__value">{{ $total }}</div>
                    </div>
                    @if ($unread > 0)
                        <div class="app-meta-pill app-meta-pill--accent" aria-label="Belum dibaca (di halaman ini)">
                            <div class="app-meta-pill__label">Unread</div>
                            <div class="app-meta-pill__value">{{ $unread }}</div>
                        </div>
                    @endif
                </div>

                @if ($unread > 0)
                    <form method="POST" action="{{ route('notifications.read_all') }}" class="notifs-mark-all">
                        @csrf
                        <x-button variant="secondary" class="notifs-mark-all__btn" type="submit">Tandai baca semua</x-button>
                    </form>
                @endif

                @if ($total > 0 && $unread === 0)
                    <form method="POST" action="{{ route('notifications.delete_all') }}" class="notifs-delete-all">
                        @csrf
                        <x-button variant="danger" class="notifs-delete-all__btn" type="submit">Hapus semua</x-button>
                    </form>
                @endif
            </div>
        </header>

        <div class="notifs-list" aria-label="Daftar notifikasi">
            @forelse($notifications as $n)
                @php
                    $title = $n->data['service_title'] ?? 'Update';
                    $status = $n->data['to_status'] ?? '';
                    $note = $n->data['note'] ?? '';
                    $isUnread = is_null($n->read_at);
                    $when = $n->created_at->diffForHumans();
                @endphp

                <x-card class="notif-item {{ $isUnread ? 'is-unread' : '' }}">
                    <article class="notif-row">
                        <div class="notif-main">
                            <div class="notif-meta">
                                <span class="notif-status-pill {{ $isUnread ? 'is-unread' : '' }}">
                                    {{ $isUnread ? 'Baru' : 'Terbaca' }}
                                </span>
                                <span class="notif-time">{{ $n->created_at->format('d M Y H:i') }}</span>
                                <span class="notif-time-hint">{{ $when }}</span>
                            </div>

                            <div class="notif-title">
                                <span class="notif-title__service">{{ $title }}</span>
                                @if ($status !== '')
                                    <span class="notif-sep" aria-hidden="true">&mdash;</span>
                                    <span class="notif-title__status">{{ $status }}</span>
                                @endif
                            </div>

                            @if (!empty($note))
                                <div class="notif-note">{{ $note }}</div>
                            @endif
                        </div>

                        <div class="notif-actions">
                            @if ($isUnread)
                                <form method="POST" action="{{ route('notifications.read', $n->id) }}"
                                    class="notif-action">
                                    @csrf
                                    <x-button variant="secondary" class="notif-action__btn" type="submit">Tandai dibaca</x-button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('notifications.delete', $n->id) }}"
                                    class="notif-action notif-action--delete">
                                    @csrf
                                    <x-button variant="danger" class="notif-delete-btn" type="submit">Hapus</x-button>
                                </form>
                            @endif
                        </div>
                    </article>
                </x-card>
            @empty
                <x-card class="notif-empty">
                    <div class="notif-empty__title">Tidak ada notifikasi</div>
                    <div class="notif-empty__subtitle">Saat ada update, notifikasi akan muncul di sini.</div>
                </x-card>
            @endforelse
        </div>

        <div class="notifs-pagination">
            {{ $notifications->onEachSide(1)->links('components.public.pagination') }}
        </div>
    </div>
@endsection
