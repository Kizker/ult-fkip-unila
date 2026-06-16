@foreach($services as $s)
    @php
        $serviceSearchText = trim(
            implode(
                ' ',
                array_filter([
                    $s->title_id,
                    $s->title_en,
                    $s->slug,
                    $s->summary_id,
                    $s->summary_en,
                    $s->category?->name_id,
                    $s->category?->name_en,
                ]),
            ),
        );
        $serviceTitle = $isEn ? $s->title_en ?? $s->title_id : $s->title_id;
        $serviceSummary = $isEn ? $s->summary_en ?? $s->summary_id : $s->summary_id;
        $serviceSummary = filled($serviceSummary)
            ? $serviceSummary
            : ($isEn
                ? 'Digital service available for online submission.'
                : 'Layanan digital yang dapat diajukan secara online.');
        $serviceCategory = $isEn
            ? $s->category?->name_en ?? $s->category?->name_id
            : $s->category?->name_id;
    @endphp
    <x-card class="services-catalog-card" data-realtime-search-item
        data-realtime-search-text="{{ $serviceSearchText }}" data-services-reveal>
        <div class="services-catalog-card__head">
            <span class="services-catalog-card__category">{{ $serviceCategory ?? '-' }}</span>
            <span
                class="services-catalog-card__index">{{ str_pad((string) (($loopOffset ?? 0) + $loop->iteration), 2, '0', STR_PAD_LEFT) }}</span>
        </div>
        <h3 class="services-catalog-card__title">
            <a href="{{ route('services.show', $s) }}">{{ $serviceTitle }}</a>
        </h3>
        <p class="services-catalog-card__desc">{{ $serviceSummary }}</p>
        <div class="services-catalog-card__actions">
            <x-button variant="secondary" class="services-catalog-card__detail-btn"
                href="{{ route('services.show', $s) }}">{{ $isEn ? 'Details' : 'Detail' }}</x-button>
            @auth
                <x-button class="services-catalog-card__apply-btn"
                    href="{{ route('student.requests.create', $s) }}">{{ $isEn ? 'Apply' : 'Ajukan' }}</x-button>
            @endauth
        </div>
    </x-card>
@endforeach
