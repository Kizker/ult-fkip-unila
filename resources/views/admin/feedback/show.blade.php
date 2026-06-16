@extends('layouts.app')
@section('section', 'Kritik dan Saran')

@section('content')
    @php
        $statusLabels = [
            \App\Models\FeedbackMessage::STATUS_BARU => 'Baru',
            \App\Models\FeedbackMessage::STATUS_DIPROSES => 'Diproses',
            \App\Models\FeedbackMessage::STATUS_SELESAI => 'Selesai',
        ];

        $categoryLabels = [
            \App\Models\FeedbackMessage::CATEGORY_MASUKAN => 'Masukan',
            \App\Models\FeedbackMessage::CATEGORY_SARAN => 'Saran',
            \App\Models\FeedbackMessage::CATEGORY_KOMPLAIN => 'Komplain',
        ];

        $statusVariant = match ($feedback->status) {
            \App\Models\FeedbackMessage::STATUS_SELESAI => 'success',
            \App\Models\FeedbackMessage::STATUS_DIPROSES => 'warning',
            default => 'primary',
        };
    @endphp

    <div class="page-admin-feedback-show">
        <header class="admin-page-header">
            <div class="admin-page-heading">
                <div class="admin-page-kicker">Detail Masukan</div>
                <h1 class="admin-page-title">{{ $feedback->name }}</h1>
                <p class="admin-page-subtitle">{{ $feedback->email }}</p>
            </div>
            <div class="admin-page-actions gap-2">
                <x-badge :variant="$statusVariant">{{ $statusLabels[$feedback->status] ?? $feedback->status }}</x-badge>
                <x-button href="{{ route('admin.feedback.index') }}" variant="ghost">&larr; Kembali</x-button>
            </div>
        </header>

        <div class="feedback-show-layout">
            <x-card class="feedback-show-main">
                <h2 class="text-lg font-semibold">Isi Pesan</h2>
                <dl class="feedback-show-meta">
                    <div class="feedback-show-meta__item">
                        <dt>Kategori</dt>
                        <dd><strong>{{ $categoryLabels[$feedback->category] ?? $feedback->category }}</strong></dd>
                    </div>
                    <div class="feedback-show-meta__item">
                        <dt>Dikirim</dt>
                        <dd>{{ optional($feedback->created_at)->format('d M Y H:i') }}</dd>
                    </div>
                    @if(filled($feedback->phone))
                        <div class="feedback-show-meta__item">
                            <dt>Nomor HP</dt>
                            <dd>{{ $feedback->phone }}</dd>
                        </div>
                    @endif
                    @if(filled($feedback->ip_address))
                        <div class="feedback-show-meta__item">
                            <dt>IP</dt>
                            <dd>{{ $feedback->ip_address }}</dd>
                        </div>
                    @endif
                </dl>

                <div class="feedback-show-message">
                    {{ $feedback->message }}
                </div>
            </x-card>

            <x-card class="feedback-show-side">
                <h2 class="text-lg font-semibold">Tindak Lanjut</h2>
                <p class="text-xs text-muted mt-1">Ubah status manual. Balasan admin opsional, jika diisi akan dikirim ke email pengirim.</p>

                <form method="POST" action="{{ route('admin.feedback.update', $feedback) }}" class="feedback-show-form">
                    @csrf

                    <x-select name="status" label="Status" required>
                        @foreach(\App\Models\FeedbackMessage::STATUSES as $statusValue)
                            <option value="{{ $statusValue }}" @selected(old('status', $feedback->status) === $statusValue)>
                                {{ $statusLabels[$statusValue] ?? $statusValue }}
                            </option>
                        @endforeach
                    </x-select>

                    <x-textarea name="admin_reply" label="Balasan Admin (opsional)" rows="8">{{ old('admin_reply', $feedback->admin_reply) }}</x-textarea>

                    @if(filled($feedback->admin_reply))
                        <div class="feedback-show-last-reply text-xs text-muted">
                            Balasan terakhir oleh:
                            {{ $feedback->repliedByUser?->name ?? 'Admin' }}
                            @if($feedback->replied_at)
                                pada {{ $feedback->replied_at->format('d M Y H:i') }}
                            @endif
                        </div>
                    @endif

                    <div class="feedback-show-form__actions">
                        <x-button type="submit">Simpan</x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection
