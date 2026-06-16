@foreach($guides as $guide)
    @php
        $title = $isEn ? ($guide->title_en ?: $guide->title_id) : $guide->title_id;
        $summary = $isEn ? ($guide->summary_en ?: $guide->summary_id) : $guide->summary_id;
        $summary = filled($summary)
            ? \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', (string) $summary)), 160, '...')
            : ($guide->isVideo()
                ? ($isEn ? 'Video tutorial guide.' : 'Panduan dalam bentuk video tutorial.')
                : ($isEn ? 'PDF user guide document.' : 'Dokumen panduan pengguna dalam format PDF.'));
        $roles = $guide->roles->pluck('name')->values()->all();
        $detailUrl = $guide->isVideo() ? route('user_guides.show', $guide->slug) : route('user_guides.file', $guide->slug);
        $searchText = trim(
            implode(' ', array_filter([
                $guide->title_id,
                $guide->title_en,
                $guide->summary_id,
                $guide->summary_en,
                $guide->video_url,
                $guide->isVideo() ? 'video youtube tutorial' : 'pdf dokumen file',
                implode(' ', $roles),
                $guide->is_public ? 'umum public guest' : 'role login',
            ])),
        );
    @endphp
    <x-card class="card-item services-catalog-card user-guide-card" data-services-reveal data-realtime-search-item
        data-realtime-search-text="{{ $searchText }}">
        <div class="services-catalog-card__head">
            <span class="services-catalog-card__category">{{ $guide->isVideo() ? ($isEn ? 'Video Guide' : 'Panduan Video') : ($isEn ? 'User Guide' : 'Panduan') }}</span>
            @if ($guide->is_public)
                <span class="services-catalog-card__index">{{ $isEn ? 'Public' : 'Umum' }}</span>
            @else
                <span class="services-catalog-card__index">{{ $isEn ? 'Role-based' : 'Berdasarkan role' }}</span>
            @endif
        </div>
        <h3 class="services-catalog-card__title">
            <a href="{{ $detailUrl }}" target="_blank">{{ $title }}</a>
        </h3>
        <p class="services-catalog-card__desc">{{ $summary }}</p>
        @if (!empty($roles))
            <div class="user-guide-card__roles">
                @foreach ($roles as $roleName)
                    <span class="user-guide-card__role">{{ $roleName }}</span>
                @endforeach
            </div>
        @endif
        <div class="services-catalog-card__actions user-guide-card__actions">
            <x-button
                variant="secondary"
                class="services-catalog-card__detail-btn"
                href="{{ $detailUrl }}"
                target="_blank"
            >
                {{ $guide->isVideo() ? ($isEn ? 'Watch guide' : 'Tonton panduan') : ($isEn ? 'Open guide' : 'Buka panduan') }}
            </x-button>
            @if ($guide->isPdf())
                <x-button
                    variant="ghost"
                    class="services-catalog-card__detail-btn"
                    href="{{ route('user_guides.download', $guide->slug) }}"
                >
                    {{ $isEn ? 'Download' : 'Unduh' }}
                </x-button>
            @else
                <x-button
                    variant="ghost"
                    class="services-catalog-card__detail-btn"
                    href="{{ $guide->videoWatchUrl() }}"
                    target="_blank"
                >
                    {{ $isEn ? 'Open YouTube' : 'Buka YouTube' }}
                </x-button>
            @endif
        </div>
    </x-card>
@endforeach
