@extends('theme-vinahentai::layout.main')

@section('body')
    <div class="container-page mx-auto px-4 py-6">
        <div class="mb-8 flex flex-col items-start justify-start gap-3.5">
            <div class="flex flex-col gap-2">
                <h1 class="text-txt-primary text-4xl leading-10 font-semibold">
                    {{ $sectionName }}
                </h1>
                {{-- @if ($type == 'translator')
                <div class="flex items-center gap-3"><span class="text-txt-secondary text-sm">2 người theo dõi</span><button type="button" class="border-lav-500 text-txt-focus hover:bg-lav-500/10 flex items-center justify-center gap-1 rounded-lg border px-3 py-2 text-sm font-semibold transition-colors cursor-pointer" aria-pressed="false" aria-label="Theo dõi">Theo dõi</button></div>
                @endif --}}
            </div>
            <div class="h-1.5 w-20 bg-fuchsia-400"></div>
            <p class="text-txt-primary text-sm leading-tight font-normal">
                {{ $sectionDescription }}
            </p>
            @isset($sortOptions)
                <div class="mt-2 flex items-center gap-3">
                    <span class="text-txt-secondary text-sm">Sắp xếp theo:</span>
                    <div class="w-64" data-tax-sort-dropdown-root>
                        <button
                            type="button"
                            aria-haspopup="listbox"
                            aria-expanded="false"
                            data-tax-sort-dropdown-trigger
                            class="bg-bgc-layer2 border-bd-default flex w-full cursor-pointer items-center justify-between rounded-xl border px-3 py-2.5 font-sans text-base font-medium text-txt-primary transition-colors hover:border-txt-secondary focus:border-lav-500 focus:outline-none"
                        >
                            <span class="truncate" data-tax-sort-dropdown-label>{{ $currentSortLabel }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down text-txt-secondary h-6 w-6 shrink-0 transition-transform" aria-hidden="true" data-tax-sort-dropdown-chevron>
                                <path d="m6 9 6 6 6-6"></path>
                            </svg>
                        </button>

                        <div data-dropdown-portal="true" class="hidden" data-tax-sort-dropdown-portal>
                            <div
                                role="listbox"
                                data-tax-sort-dropdown-list
                                class="bg-bgc-layer2 border-bd-default fixed z-[999] max-h-[320px] origin-top overflow-y-auto rounded-xl border shadow-lg"
                            >
                                @foreach ($sortOptions as $opt)
                                    <button
                                        role="option"
                                        type="button"
                                        aria-selected="{{ ($sort ?? '') === $opt['key'] ? 'true' : 'false' }}"
                                        data-tax-sort-option
                                        data-tax-sort-href="{{ $opt['url'] }}"
                                        title="{{ $opt['label'] }}"
                                        class="w-full px-3 py-2.5 text-left font-sans text-base font-medium transition-colors first:rounded-t-xl last:rounded-b-xl {{ ($sort ?? '') === $opt['key'] ? 'bg-bgc-layer-semi-purple text-txt-focus' : 'text-txt-primary hover:bg-bgc-layer-semi-neutral' }}"
                                    >
                                        {{ $opt['label'] }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endisset
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ($sectionItems as $item)
                @include('theme-vinahentai::components.item', [
                    'manga' => $item,
                ])
            @endforeach
        </div>

        @if ($sectionItems instanceof \Illuminate\Contracts\Pagination\Paginator && $sectionItems->hasPages())
            <div class="mt-8 flex justify-center">
                {{ $sectionItems->links('theme-vinahentai::components.pagination') }}
            </div>
        @endif
    </div>
@endsection
