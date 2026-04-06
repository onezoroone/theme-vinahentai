@extends('theme-vinahentai::layout.main')

@section('body')
    <div class="container-page mx-auto px-4 py-6">
        <div class="mb-4 flex flex-col items-start gap-3 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="relative h-[15px] w-[15px]"><img src="{{ asset('vendor/theme-vinahentai/images/multi-star.svg') }}" alt="" class="absolute top-0 left-[4.62px] h-4"></div>
                <h1 class="text-txt-primary text-2xl font-semibold uppercase">Danh sách truyện</h1>
            </div>
            @isset($sortOptions)
                <div class="w-full max-w-xs md:max-w-sm">
                    <div class="relative" data-tax-sort-dropdown-root>
                        <button
                            type="button"
                            aria-haspopup="listbox"
                            aria-expanded="false"
                            data-tax-sort-dropdown-trigger
                            class="bg-bgc-layer2 border-bd-default flex w-full items-center justify-between rounded-xl border px-3 py-2.5 font-sans text-base font-medium transition-colors focus:outline-none text-txt-primary focus:border-lav-500 hover:border-txt-secondary"
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

        <div class="grid grid-cols-1 gap-6 md:grid-cols-[minmax(0,1fr)_360px]">
            <section class="min-w-0">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-4">
                    @foreach ($mangas as $manga)
                        @include('theme-vinahentai::components.item')
                    @endforeach
                </div>

                <div class="mt-8 flex justify-center">
                    {{ $mangas->links('theme-vinahentai::components.pagination') }}
                </div>
            </section>

            <aside class="w-full md:justify-self-end">
                <div class="bg-bgc-layer1 border-bd-default mb-6 rounded-2xl border p-4" id="status-filter-root">
                    <h2 class="text-txt-primary mb-3 text-lg font-semibold">Tình trạng</h2>
                    <div class="mb-4 space-y-3"><label class="flex items-center gap-2 text-sm text-txt-primary"><input id="f-status-ongoing" type="checkbox" class="accent-fuchsia-400">Chưa hoàn thành</label><label class="flex items-center gap-2 text-sm text-txt-primary"><input id="f-status-completed" type="checkbox" class="accent-fuchsia-400">Đã hoàn thành</label></div><button id="f-status-apply" type="button" class="w-full rounded-xl bg-bgc-layer2 px-4 py-2 text-sm font-semibold text-white hover:bg-white/10">Lọc</button>
                </div>
                <div class="hidden lg:block">
                    <div class="text-txt-primary mb-3 text-lg font-semibold">Thể loại</div>
                    <div class="grid grid-cols-2 gap-x-6 gap-y-3">
                        @foreach ($genres as $genre)
                        <a href="{{ $genre->getUrl() }}" title="{{ $genre->name }}" class="text-txt-primary text-sm hover:text-txt-focus">{{ $genre->name }}</a>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const root = document.getElementById('status-filter-root');
            const ongoingCheckbox = document.getElementById('f-status-ongoing');
            const completedCheckbox = document.getElementById('f-status-completed');
            const applyButton = document.getElementById('f-status-apply');

            if (!root || !ongoingCheckbox || !completedCheckbox || !applyButton) {
                return;
            }

            const url = new URL(window.location.href);
            const selectedStatuses = (url.searchParams.get('status') ?? '')
                .split(',')
                .map((value) => value.trim().toLowerCase())
                .filter(Boolean);

            ongoingCheckbox.checked = selectedStatuses.includes('ongoing');
            completedCheckbox.checked = selectedStatuses.includes('completed');

            const applyStatusFilter = () => {
                const nextUrl = new URL(window.location.href);
                const statuses = [];

                if (ongoingCheckbox.checked) {
                    statuses.push('ongoing');
                }

                if (completedCheckbox.checked) {
                    statuses.push('completed');
                }

                if (statuses.length > 0) {
                    nextUrl.searchParams.set('status', statuses.join(','));
                } else {
                    nextUrl.searchParams.delete('status');
                }

                nextUrl.searchParams.delete('page');
                window.location.assign(nextUrl.toString());
            };

            applyButton.addEventListener('click', applyStatusFilter);
        })();
    </script>
@endpush
