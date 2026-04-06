@props([
    'paginator' => null,
    'current' => null,
    'last' => null,
    'resolveUrl' => null,
    'commentsMode' => false,
])

@php
    if ($paginator instanceof \Illuminate\Pagination\LengthAwarePaginator) {
        $cp = (int) $paginator->currentPage();
        $lp = max(1, (int) $paginator->lastPage());
    } else {
        $cp = max(1, (int) ($current ?? 1));
        $lp = max(1, (int) ($last ?? 1));
    }
    if ($cp > $lp) {
        $cp = $lp;
    }
    $windowStart = max(1, $cp - 2);
    $windowEnd = min($lp, $cp + 2);
    $showLeftEllipsis = $windowStart > 2;
    $showRightEllipsis = $windowEnd < $lp - 1;
    // Phân trang full page (tax, danh sách): Laravel chỉ truyền $paginator — không có resolveUrl → cần href từ paginator.
    $serverSideLinks = $paginator instanceof \Illuminate\Pagination\LengthAwarePaginator
        && ($resolveUrl === null || ! is_callable($resolveUrl))
        && ! $commentsMode;
    $pageParam = $paginator instanceof \Illuminate\Pagination\AbstractPaginator ? $paginator->getPageName() : 'page';
@endphp

<div
    class="flex flex-col items-center gap-2"
    @if ($commentsMode) data-comments-pagination @endif
>
    <div class="bg-bgc-layer1 border-bd-default inline-flex items-center justify-start gap-2 rounded-lg border px-2 py-1">
        @if ($resolveUrl !== null && is_callable($resolveUrl))
            <a
                href="{{ $cp <= 1 ? '#' : $resolveUrl(1) }}"
                class="{{ $cp <= 1 ? 'pointer-events-none opacity-50 ' : '' }}hover:bg-bgc-layer2 inline-flex h-10 w-9 cursor-pointer flex-col items-center justify-center rounded-lg p-2"
                aria-label="Về trang đầu"
                title="Về trang đầu"
                @if ($cp <= 1) aria-disabled="true" @endif
            >
                <div class="text-txt-secondary text-center font-sans text-sm font-semibold leading-tight">Đầu</div>
            </a>
        @elseif ($serverSideLinks)
            <a
                href="{{ $cp <= 1 ? '#' : $paginator->url(1) }}"
                class="{{ $cp <= 1 ? 'pointer-events-none opacity-50 ' : '' }}hover:bg-bgc-layer2 inline-flex h-10 w-9 cursor-pointer flex-col items-center justify-center rounded-lg p-2"
                aria-label="Về trang đầu"
                title="Về trang đầu"
                @if ($cp <= 1) aria-disabled="true" @endif
            >
                <div class="text-txt-secondary text-center font-sans text-sm font-semibold leading-tight">Đầu</div>
            </a>
        @else
            <button
                type="button"
                class="hover:bg-bgc-layer2 inline-flex h-10 w-9 cursor-pointer flex-col items-center justify-center rounded-lg p-2 disabled:cursor-not-allowed disabled:opacity-50"
                aria-label="Về trang đầu"
                title="Về trang đầu"
                data-pagination-first
                @disabled($cp <= 1)
            >
                <div class="text-txt-secondary text-center font-sans text-sm font-semibold leading-tight">Đầu</div>
            </button>
        @endif

        <div class="inline-flex items-center gap-1">
            @if ($showLeftEllipsis)
                <div>
                    <div class="inline-flex h-10 w-9 flex-col items-center justify-center rounded-lg p-2">
                        <div class="text-txt-primary text-center font-sans text-sm font-semibold leading-tight">...</div>
                    </div>
                </div>
            @endif

            @for ($n = $windowStart; $n <= $windowEnd; $n++)
                <div>
                    @if ($resolveUrl !== null && is_callable($resolveUrl))
                        @if ($n === $cp)
                            <span class="inline-flex h-10 w-9 cursor-default flex-col items-center justify-center rounded-lg bg-btn-primary p-2" aria-current="page" title="Trang {{ $n }}">
                                <span class="text-center font-sans text-sm font-semibold leading-tight text-bgc-layer1">{{ $n }}</span>
                            </span>
                        @else
                            <a href="{{ $resolveUrl($n) }}" class="hover:bg-bgc-layer2 inline-flex h-10 w-9 flex-col items-center justify-center rounded-lg p-2" title="Trang {{ $n }}">
                                <div class="text-center font-sans text-sm font-semibold leading-tight text-txt-primary">{{ $n }}</div>
                            </a>
                        @endif
                    @elseif ($serverSideLinks)
                        @if ($n === $cp)
                            <span class="inline-flex h-10 w-9 cursor-default flex-col items-center justify-center rounded-lg bg-btn-primary p-2" aria-current="page" title="Trang {{ $n }}">
                                <span class="text-center font-sans text-sm font-semibold leading-tight text-bgc-layer1">{{ $n }}</span>
                            </span>
                        @else
                            <a href="{{ $paginator->url($n) }}" class="hover:bg-bgc-layer2 inline-flex h-10 w-9 cursor-pointer flex-col items-center justify-center rounded-lg p-2" title="Trang {{ $n }}">
                                <div class="text-center font-sans text-sm font-semibold leading-tight text-txt-primary">{{ $n }}</div>
                            </a>
                        @endif
                    @else
                        @if ($n === $cp)
                            <button
                                type="button"
                                class="inline-flex h-10 w-9 cursor-default flex-col items-center justify-center rounded-lg bg-btn-primary p-2"
                                aria-current="page"
                                title="Trang {{ $n }}"
                                disabled
                            >
                                <span class="text-center font-sans text-sm font-semibold leading-tight text-bgc-layer1">{{ $n }}</span>
                            </button>
                        @else
                            <button
                                type="button"
                                class="hover:bg-bgc-layer2 inline-flex h-10 w-9 cursor-pointer flex-col items-center justify-center rounded-lg p-2"
                                title="Trang {{ $n }}"
                                data-pagination-page="{{ $n }}"
                            >
                                <div class="text-center font-sans text-sm font-semibold leading-tight text-txt-primary">{{ $n }}</div>
                            </button>
                        @endif
                    @endif
                </div>
            @endfor

            @if ($showRightEllipsis)
                <div>
                    <div class="inline-flex h-10 w-9 flex-col items-center justify-center rounded-lg p-2">
                        <div class="text-txt-primary text-center font-sans text-sm font-semibold leading-tight">...</div>
                    </div>
                </div>
            @endif
        </div>

        @if ($resolveUrl !== null && is_callable($resolveUrl))
            <a
                href="{{ $cp >= $lp ? '#' : $resolveUrl($lp) }}"
                class="{{ $cp >= $lp ? 'pointer-events-none opacity-50 ' : '' }}hover:bg-bgc-layer2 inline-flex h-10 w-9 cursor-pointer flex-col items-center justify-center rounded-lg p-2"
                aria-label="Tới trang cuối"
                title="Tới trang cuối"
                @if ($cp >= $lp) aria-disabled="true" @endif
            >
                <div class="text-txt-secondary text-center font-sans text-sm font-semibold leading-tight">Cuối</div>
            </a>
        @elseif ($serverSideLinks)
            <a
                href="{{ $cp >= $lp ? '#' : $paginator->url($lp) }}"
                class="{{ $cp >= $lp ? 'pointer-events-none opacity-50 ' : '' }}hover:bg-bgc-layer2 inline-flex h-10 w-9 cursor-pointer flex-col items-center justify-center rounded-lg p-2"
                aria-label="Tới trang cuối"
                title="Tới trang cuối"
                @if ($cp >= $lp) aria-disabled="true" @endif
            >
                <div class="text-txt-secondary text-center font-sans text-sm font-semibold leading-tight">Cuối</div>
            </a>
        @else
            <button
                type="button"
                class="hover:bg-bgc-layer2 inline-flex h-10 w-9 cursor-pointer flex-col items-center justify-center rounded-lg p-2 disabled:cursor-not-allowed disabled:opacity-50"
                aria-label="Tới trang cuối"
                title="Tới trang cuối"
                data-pagination-last
                @disabled($cp >= $lp)
            >
                <div class="text-txt-secondary text-center font-sans text-sm font-semibold leading-tight">Cuối</div>
            </button>
        @endif
    </div>

    @if ($serverSideLinks)
        <form method="get" action="{{ url()->current() }}" class="inline-flex items-center gap-2">
            @foreach (request()->except($pageParam) as $hiddenName => $hiddenValue)
                @if (is_array($hiddenValue))
                    @foreach ($hiddenValue as $hiddenSubKey => $hiddenSubValue)
                        <input type="hidden" name="{{ $hiddenName }}[{{ $hiddenSubKey }}]" value="{{ $hiddenSubValue }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $hiddenName }}" value="{{ $hiddenValue }}">
                @endif
            @endforeach
            <input
                type="number"
                name="{{ $pageParam }}"
                inputmode="numeric"
                min="1"
                max="{{ $lp }}"
                placeholder="Trang"
                class="h-10 w-20 rounded-lg border border-bd-default bg-bgc-layer1 px-2 text-center font-sans text-sm font-semibold text-txt-primary focus:outline-none focus:ring-2 focus:ring-btn-primary"
                aria-label="Số trang"
            />
            <button
                type="submit"
                class="inline-flex h-10 min-w-12 cursor-pointer items-center justify-center rounded-lg bg-btn-primary px-3 font-sans text-sm font-semibold leading-tight text-bgc-layer1 hover:opacity-95"
                title="Đi tới trang"
            >
                Đi
            </button>
        </form>
    @else
        <form class="inline-flex items-center gap-2" data-pagination-jump-form @if ($commentsMode) data-comments-pagination-jump @endif>
            <input
                type="number"
                inputmode="numeric"
                min="1"
                max="{{ $lp }}"
                placeholder="Trang"
                class="h-10 w-20 rounded-lg border border-bd-default bg-bgc-layer1 px-2 text-center font-sans text-sm font-semibold text-txt-primary focus:outline-none focus:ring-2 focus:ring-btn-primary"
                value=""
                data-pagination-jump-input
                aria-label="Số trang"
            />
            <button
                type="submit"
                disabled
                class="inline-flex h-10 min-w-12 cursor-pointer items-center justify-center rounded-lg bg-bgc-layer2 px-3 font-sans text-sm font-semibold leading-tight text-txt-secondary opacity-50 disabled:cursor-not-allowed disabled:opacity-50 enabled:cursor-pointer enabled:bg-btn-primary enabled:text-txt-primary enabled:opacity-100"
                title="Nhập trang hợp lệ"
                data-pagination-jump-submit
            >
                Đi
            </button>
        </form>
    @endif
</div>
