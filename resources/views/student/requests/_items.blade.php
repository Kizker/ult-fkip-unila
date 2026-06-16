@foreach($items as $r)
    @php
        $currentStatus = $r->current_status->value ?? $r->current_status;
        $requestSearchText = trim(implode(' ', array_filter([
            (string) ($r->request_code ?? ''),
            (string) ($r->activity_title ?? ''),
            (string) ($r->display_title ?? ''),
            (string) ($r->service->title_id ?? ''),
            (string) ($r->service->title_en ?? ''),
            (string) $currentStatus,
            (string) str_replace('_', ' ', (string) $currentStatus),
            (string) $r->created_at->format('d M Y H:i'),
        ])));
    @endphp
    <x-card class="student-request-card student-index-card" data-realtime-search-item data-realtime-search-text="{{ $requestSearchText }}">
        <div class="student-request-card__row">
            <div class="student-request-card__meta">
                <div class="student-request-card__topline">
                    <div class="student-request-card__status">
                        <x-status-badge :status="$currentStatus" />
                    </div>
                    <div class="student-request-card__kicker">{{ $r->request_code ?? ('REQ-' . $r->id) }}</div>
                </div>
                <div class="student-request-card__title">
                    <a href="{{ route('student.requests.show', $r) }}">{{ $r->display_title }}</a>
                </div>
                <div class="student-request-card__time">Diajukan: {{ $r->created_at->format('d M Y H:i') }}</div>
            </div>
            <div class="student-request-card__actions">
                <x-button class="student-request-card__detail-btn" variant="secondary" href="{{ route('student.requests.show', $r) }}">Detail</x-button>
            </div>
        </div>
    </x-card>
@endforeach
