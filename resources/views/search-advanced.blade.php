@extends('theme-vinahentai::layout.main')

@section('body')
    <div class="container-page mx-auto px-4 py-6 pb-48 md:pb-64" data-adv-search-root>
        <h1 class="text-txt-primary mb-3 text-3xl font-semibold">Tìm kiếm nâng cao</h1>
        <div class="mb-6 h-1.5 w-20 bg-fuchsia-400"></div>

        <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center">
            <input
                type="search"
                placeholder="Nhập tên truyện (không bắt buộc)"
                class="bg-bgc-layer2 text-txt-primary w-full max-w-xl rounded-xl px-3 py-2 outline-none placeholder:text-txt-secondary"
                value="{{ $q }}"
                data-adv-q
                autocomplete="off"
            >
        </div>

        <div class="mb-4 flex flex-wrap items-center gap-4">
            <label class="flex cursor-pointer items-center gap-2 text-sm text-txt-primary">
                <input
                    type="checkbox"
                    class="accent-fuchsia-400"
                    data-adv-status="ongoing"
                    @checked(in_array('ongoing', $selectedStatuses, true))
                >
                Đang tiến hành
            </label>
            <label class="flex cursor-pointer items-center gap-2 text-sm text-txt-primary">
                <input
                    type="checkbox"
                    class="accent-fuchsia-400"
                    data-adv-status="completed"
                    @checked(in_array('completed', $selectedStatuses, true))
                >
                Đã hoàn thành
            </label>
            <label class="flex cursor-pointer items-center gap-2 text-sm text-txt-primary">
                <input
                    type="checkbox"
                    class="accent-fuchsia-400"
                    data-adv-status="oneshot"
                    @checked(in_array('oneshot', $selectedStatuses, true))
                >
                Oneshot
            </label>
        </div>

        <div class="mb-4 space-y-4">
            <div class="rounded-2xl border border-white/5 bg-bgc-layer2/70 p-3 shadow-lg backdrop-blur">
                <div class="mb-2 text-sm font-semibold text-txt-primary">Thể loại (bao gồm)</div>
                @include('theme-vinahentai::partials.search-advanced-genre-stack', [
                    'genresByLetter' => $genresByLetter,
                    'mode' => 'include',
                    'selectedSlugs' => $selectedIncludeSlugs,
                ])
            </div>

            <div class="rounded-2xl border border-rose-500/20 bg-rose-600/15 p-3 shadow-lg backdrop-blur">
                <button
                    type="button"
                    class="flex w-full items-center justify-between rounded-xl border border-rose-500/30 bg-rose-500/20 px-4 py-3 text-sm font-semibold text-rose-100 shadow transition hover:bg-rose-500/30"
                    aria-expanded="{{ count($selectedExcludeSlugs) > 0 ? 'true' : 'false' }}"
                    aria-controls="exclude-genres-panel"
                    data-adv-exclude-toggle
                >
                    <span>Chọn thể loại muốn loại trừ</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down h-4 w-4 transition-transform duration-200 {{ count($selectedExcludeSlugs) > 0 ? 'rotate-180' : '' }}" aria-hidden="true" data-adv-exclude-chevron>
                        <path d="m6 9 6 6 6-6"></path>
                    </svg>
                </button>
                <div
                    id="exclude-genres-panel"
                    class="grid transition-all duration-300 ease-out {{ count($selectedExcludeSlugs) > 0 ? 'adv-exclude-open grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0' }}"
                    data-adv-exclude-panel
                >
                    <div class="overflow-hidden">
                        <div class="mb-2 mt-3 text-sm font-semibold text-rose-100">Thể loại (loại trừ)</div>
                        @include('theme-vinahentai::partials.search-advanced-genre-stack', [
                            'genresByLetter' => $genresByLetter,
                            'mode' => 'exclude',
                            'selectedSlugs' => $selectedExcludeSlugs,
                        ])
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-8">
            <button
                type="button"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] px-6 py-3 text-sm font-semibold text-black shadow-lg transition-opacity hover:opacity-90"
                data-adv-apply
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search h-4 w-4" aria-hidden="true">
                    <path d="m21 21-4.34-4.34"></path>
                    <circle cx="11" cy="11" r="8"></circle>
                </svg>
                Áp dụng bộ lọc
            </button>
            <p class="text-txt-secondary mt-2 text-xs">Bấm &quot;Áp dụng bộ lọc&quot; để cập nhật URL và tải lại trang với tham số lọc.</p>
        </div>

        <div class="mb-3 mt-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-txt-primary text-xl font-semibold">Kết quả</h2>
            <div class="flex items-center gap-2">
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
        </div>

        @if (! $hasActiveSearch)
            <p class="text-txt-secondary mb-6 text-sm">Chọn bộ lọc và bấm &quot;Áp dụng bộ lọc&quot; để xem kết quả.</p>
        @elseif ($mangas->total() === 0)
            <p class="text-txt-secondary mb-6 text-sm">Không có truyện phù hợp.</p>
        @endif

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ($mangas as $manga)
                @include('theme-vinahentai::components.item', [
                    'manga' => $manga,
                ])
            @endforeach
        </div>

        @if ($hasActiveSearch && $mangas instanceof \Illuminate\Contracts\Pagination\Paginator && $mangas->hasPages())
            <div class="mt-8 flex justify-center">
                {{ $mangas->links('theme-vinahentai::components.pagination') }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const root = document.querySelector('[data-adv-search-root]');
            if (!root) {
                return;
            }

            const norm = (s) => (s || '').toString().trim().toLowerCase();

            const wireGenreFilter = (inputSel, gridSel) => {
                const input = root.querySelector(inputSel);
                const grid = root.querySelector(gridSel);
                if (!input || !grid) {
                    return;
                }
                input.addEventListener('input', () => {
                    const q = norm(input.value);
                    grid.querySelectorAll('.adv-genre-label').forEach((label) => {
                        const t = norm(label.textContent);
                        label.classList.toggle('hidden', Boolean(q) && !t.includes(q));
                    });
                    grid.querySelectorAll('[data-adv-letter-group], [data-adv-letter-group-ex]').forEach((section) => {
                        const any = [...section.querySelectorAll('.adv-genre-label')].some((l) => !l.classList.contains('hidden'));
                        section.classList.toggle('hidden', !any);
                    });
                });
            };

            wireGenreFilter('[data-adv-filter-include]', '[data-adv-include-grid]');
            wireGenreFilter('[data-adv-filter-exclude]', '[data-adv-exclude-grid]');

            const toggleBtn = root.querySelector('[data-adv-exclude-toggle]');
            const panel = root.querySelector('[data-adv-exclude-panel]');
            const chevron = root.querySelector('[data-adv-exclude-chevron]');
            toggleBtn?.addEventListener('click', () => {
                const open = panel?.classList.contains('adv-exclude-open');
                if (open) {
                    panel.classList.remove('adv-exclude-open', 'grid-rows-[1fr]', 'opacity-100');
                    panel.classList.add('grid-rows-[0fr]', 'opacity-0');
                    toggleBtn.setAttribute('aria-expanded', 'false');
                    chevron?.classList.remove('rotate-180');
                } else {
                    panel?.classList.add('adv-exclude-open', 'grid-rows-[1fr]', 'opacity-100');
                    panel?.classList.remove('grid-rows-[0fr]', 'opacity-0');
                    toggleBtn.setAttribute('aria-expanded', 'true');
                    chevron?.classList.add('rotate-180');
                }
            });

            root.querySelector('[data-adv-apply]')?.addEventListener('click', () => {
                const qInput = root.querySelector('[data-adv-q]');
                const statuses = [...root.querySelectorAll('[data-adv-status]:checked')]
                    .map((el) => el.getAttribute('data-adv-status'))
                    .filter(Boolean);
                const include = [...root.querySelectorAll('[data-adv-include]:checked')]
                    .map((el) => el.value)
                    .filter(Boolean);
                const exclude = [...root.querySelectorAll('[data-adv-exclude]:checked')]
                    .map((el) => el.value)
                    .filter(Boolean);

                // Thứ tự tham số giống ví dụ: apply → exclude → include → page → q → status (dấu phẩy → %2C)
                const qRaw = (qInput?.value || '').toString().trim();

                const params = new URLSearchParams([
                    ['apply', '1'],
                    ['excludeGenres', exclude.join(',')],
                    ['includeGenres', include.join(',')],
                    ['page', '1'],
                    ['q', qRaw],
                    ['status', statuses.join(',')],
                ]);
                const sortCur = new URL(window.location.href).searchParams.get('sort');
                if (sortCur) {
                    params.set('sort', sortCur);
                }

                const url = window.location.pathname + '?' + params.toString();
                window.location.assign(url);
            });
        })();
    </script>
@endpush
