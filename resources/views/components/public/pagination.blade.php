@if ($paginator->hasPages())
    @php
        $isEn = app()->getLocale() === 'en';
        $prevLabel = $isEn ? 'Previous' : 'Sebelumnya';
        $nextLabel = $isEn ? 'Next' : 'Berikutnya';
        $summaryTemplate = $isEn
            ? 'Showing :from-:to of :total items'
            : 'Menampilkan :from-:to dari :total data';

        $from = (int) ($paginator->firstItem() ?? 0);
        $to = (int) ($paginator->lastItem() ?? 0);
        $total = method_exists($paginator, 'total') ? (int) $paginator->total() : (int) $paginator->count();
        $summary = strtr($summaryTemplate, [
            ':from' => (string) $from,
            ':to' => (string) $to,
            ':total' => (string) $total,
        ]);
    @endphp

    <nav class="public-pagination" role="navigation" aria-label="{{ $isEn ? 'Pagination Navigation' : 'Navigasi halaman' }}">
        <div class="public-pagination__summary">{{ $summary }}</div>

        <ul class="public-pagination__list">
            @if ($paginator->onFirstPage())
                <li class="public-pagination__item public-pagination__item--disabled" aria-disabled="true" aria-label="{{ $prevLabel }}">
                    <span class="public-pagination__link">{{ $prevLabel }}</span>
                </li>
            @else
                <li class="public-pagination__item">
                    <a class="public-pagination__link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ $prevLabel }}">{{ $prevLabel }}</a>
                </li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="public-pagination__item public-pagination__item--dots" aria-disabled="true">
                        <span class="public-pagination__link">{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="public-pagination__item public-pagination__item--active" aria-current="page">
                                <span class="public-pagination__link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="public-pagination__item">
                                <a class="public-pagination__link" href="{{ $url }}" aria-label="{{ $isEn ? 'Page ' . $page : 'Halaman ' . $page }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li class="public-pagination__item">
                    <a class="public-pagination__link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ $nextLabel }}">{{ $nextLabel }}</a>
                </li>
            @else
                <li class="public-pagination__item public-pagination__item--disabled" aria-disabled="true" aria-label="{{ $nextLabel }}">
                    <span class="public-pagination__link">{{ $nextLabel }}</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
