@extends('theme-vinahentai::layout.main')

@section('body')
    <div class="container-page flex flex-col items-center justify-center gap-11 px-4 py-8 md:px-6 lg:px-0">
        @include('theme-vinahentai::leaderboard.layout', ['active' => 'manga'])

        <h1 class="w-full text-center text-4xl leading-10 font-semibold">TOP TRUYỆN HENTAI</h1>

        <div
            id="manga-leaderboard-page"
            class="w-full max-w-[750px] rounded-xl bg-slate-950 outline-1 outline-offset-[-1px] outline-slate-700"
        >
            <div dir="ltr" data-orientation="horizontal" class="w-full">
                <div
                    role="tablist"
                    aria-orientation="horizontal"
                    class="grid grid-cols-3 gap-2 p-2 sm:flex sm:gap-0 sm:border-b sm:border-slate-700 sm:p-0"
                    tabindex="0"
                    data-orientation="horizontal"
                    style="outline: none;"
                >
                    <button
                        type="button"
                        role="tab"
                        data-manga-rank-tab="monthly"
                        aria-selected="true"
                        data-state="active"
                        class="text-txt-secondary hover:text-txt-primary data-[state=active]:text-txt-primary data-[state=active]:border-lav-400 data-[state=active]:bg-lav-500/18 data-[state=active]:shadow-[0_10px_30px_rgba(211,115,255,0.16)] flex min-h-12 cursor-pointer items-center justify-center rounded-2xl border border-white/8 bg-white/4 px-3 py-3 text-sm font-semibold whitespace-nowrap transition-all duration-200 [@media(max-width:359px)]:min-h-11 [@media(max-width:359px)]:px-2 [@media(max-width:359px)]:text-[11px] sm:min-h-0 sm:flex-1 sm:rounded-none sm:border-0 sm:border-b sm:border-transparent sm:bg-transparent sm:px-3 sm:py-3 sm:text-base sm:font-medium sm:data-[state=active]:border-lav-500 sm:data-[state=active]:bg-transparent sm:data-[state=active]:shadow-none sm:data-[state=active]:font-semibold"
                    >
                        Top tháng
                    </button>
                    <button
                        type="button"
                        role="tab"
                        data-manga-rank-tab="weekly"
                        aria-selected="false"
                        data-state="inactive"
                        tabindex="-1"
                        class="text-txt-secondary hover:text-txt-primary data-[state=active]:text-txt-primary data-[state=active]:border-lav-400 data-[state=active]:bg-lav-500/18 data-[state=active]:shadow-[0_10px_30px_rgba(211,115,255,0.16)] flex min-h-12 cursor-pointer items-center justify-center rounded-2xl border border-white/8 bg-white/4 px-3 py-3 text-sm font-semibold whitespace-nowrap transition-all duration-200 [@media(max-width:359px)]:min-h-11 [@media(max-width:359px)]:px-2 [@media(max-width:359px)]:text-[11px] sm:min-h-0 sm:flex-1 sm:rounded-none sm:border-0 sm:border-b sm:border-transparent sm:bg-transparent sm:px-3 sm:py-3 sm:text-base sm:font-medium sm:data-[state=active]:border-lav-500 sm:data-[state=active]:bg-transparent sm:data-[state=active]:shadow-none sm:data-[state=active]:font-semibold"
                    >
                        Top tuần
                    </button>
                    <button
                        type="button"
                        role="tab"
                        data-manga-rank-tab="daily"
                        aria-selected="false"
                        data-state="inactive"
                        tabindex="-1"
                        class="text-txt-secondary hover:text-txt-primary data-[state=active]:text-txt-primary data-[state=active]:border-lav-400 data-[state=active]:bg-lav-500/18 data-[state=active]:shadow-[0_10px_30px_rgba(211,115,255,0.16)] flex min-h-12 cursor-pointer items-center justify-center rounded-2xl border border-white/8 bg-white/4 px-3 py-3 text-sm font-semibold whitespace-nowrap transition-all duration-200 [@media(max-width:359px)]:min-h-11 [@media(max-width:359px)]:px-2 [@media(max-width:359px)]:text-[11px] sm:min-h-0 sm:flex-1 sm:rounded-none sm:border-0 sm:border-b sm:border-transparent sm:bg-transparent sm:px-3 sm:py-3 sm:text-base sm:font-medium sm:data-[state=active]:border-lav-500 sm:data-[state=active]:bg-transparent sm:data-[state=active]:shadow-none sm:data-[state=active]:font-semibold"
                    >
                        Top ngày
                    </button>
                </div>

                @foreach (['monthly', 'weekly', 'daily'] as $period)
                    <div
                        role="tabpanel"
                        data-manga-rank-panel="{{ $period }}"
                        class="space-y-0 pb-4"
                        @if ($period !== 'monthly') hidden @endif
                    >
                        <div data-manga-rank-items="{{ $period }}" class="space-y-0"></div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const root = document.getElementById('manga-leaderboard-page');
            if (!root) return;

            const apiBase = @json(url('/api/leaderboard'));
            const perPage = 10;

            const state = { monthly: 1, weekly: 1, daily: 1 };
            let activeRequestId = 0;

            const formatViews = (n) => {
                try {
                    return new Intl.NumberFormat('vi-VN').format(Number(n) || 0);
                } catch (e) {
                    return String(Number(n) || 0);
                }
            };

            const setTab = (period) => {
                ['monthly', 'weekly', 'daily'].forEach((p) => {
                    const btn = root.querySelector('[data-manga-rank-tab="' + p + '"]');
                    const panel = root.querySelector('[data-manga-rank-panel="' + p + '"]');
                    const active = p === period;
                    if (btn) {
                        btn.setAttribute('data-state', active ? 'active' : 'inactive');
                        btn.setAttribute('aria-selected', active ? 'true' : 'false');
                        btn.setAttribute('tabindex', active ? '0' : '-1');
                    }
                    if (panel) {
                        if (active) panel.removeAttribute('hidden');
                        else panel.setAttribute('hidden', '');
                    }
                });
                updatePagination(period);
            };

            const updatePagination = (period) => {
                const page = state[period] || 1;
                root.querySelectorAll('[data-manga-rank-page-period="' + period + '"]').forEach((el) => {
                    const p = Number(el.getAttribute('data-manga-rank-page')) || 1;
                    el.disabled = p === page;
                });
            };

            const renderItem = (item) => {
                const rank = Number(item.rank) || 0;
                const poster = item.poster || '';
                const title = item.title || '';

                const a = document.createElement('a');
                a.href = item.url || '#';
                a.className = 'flex items-center gap-3 p-3';

                const span = document.createElement('span');
                span.className = 'w-5 text-center text-base font-semibold';
                if (rank === 1) span.classList.add('text-[#FFE133]');
                else if (rank === 2) span.classList.add('text-[#5BD8FA]');
                else if (rank === 3) span.classList.add('text-[#FF7158]');
                else span.classList.add('text-txt-primary');
                span.textContent = String(rank);

                const imgWrap = document.createElement('div');
                imgWrap.className =
                    'flex-shrink-0 overflow-hidden bg-black/20 rounded-lg aspect-[2/3]';
                imgWrap.style.width = '94.08px';

                const img = document.createElement('img');
                img.className = 'h-full w-full object-cover';
                img.loading = 'lazy';
                img.alt = title;
                if (poster) img.src = poster;
                imgWrap.appendChild(img);

                const content = document.createElement('div');
                content.className = 'flex-1 space-y-1';

                const h3 = document.createElement('h3');
                h3.className =
                    'text-txt-primary line-clamp-1 text-base leading-6 font-semibold';
                h3.textContent = title;

                const stats = document.createElement('div');
                stats.className = 'flex items-center justify-start gap-4';

                const viewsWrap = document.createElement('div');
                viewsWrap.className = 'flex items-center gap-1.5 backdrop-blur-md';
                viewsWrap.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye text-txt-primary h-3 w-3" aria-hidden="true">
                        <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <span class="text-txt-primary text-xs font-medium">${formatViews(item.views)} lượt xem</span>
                `;

                const followsWrap = document.createElement('div');
                followsWrap.className = 'flex items-center gap-1.5 backdrop-blur-md';
                followsWrap.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-thumbs-up text-txt-primary h-3 w-3" aria-hidden="true">
                        <path d="M7 10v12"></path>
                        <path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"></path>
                    </svg>
                    <span class="text-txt-primary text-xs font-medium">${formatViews(item.follows)}</span>
                `;

                stats.appendChild(viewsWrap);
                stats.appendChild(followsWrap);
                content.appendChild(h3);
                content.appendChild(stats);
                a.appendChild(span);
                a.appendChild(imgWrap);
                a.appendChild(content);
                return a;
            };

            const load = async (period, page) => {
                const itemsEl = root.querySelector('[data-manga-rank-items="' + period + '"]');
                if (!itemsEl) return;

                const requestId = ++activeRequestId;
                itemsEl.innerHTML =
                    '<div class="p-3 text-txt-secondary text-sm">Đang tải...</div>';

                try {
                    const url =
                        apiBase +
                        '/' +
                        period +
                        '?page=' +
                        page +
                        '&per_page=' +
                        perPage;
                    const res = await fetch(url, { method: 'GET' });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const json = await res.json();
                    if (requestId !== activeRequestId) return;

                    const items = json?.data || [];
                    itemsEl.innerHTML = '';

                    if (items.length === 0) {
                        itemsEl.innerHTML =
                            '<div class="p-3 text-txt-secondary text-sm">Không có dữ liệu</div>';
                        return;
                    }

                    items.forEach((it) => itemsEl.appendChild(renderItem(it)));
                } catch (e) {
                    if (requestId !== activeRequestId) return;
                    console.error(e);
                    itemsEl.innerHTML =
                        '<div class="p-3 text-txt-secondary text-sm">Không tải được dữ liệu</div>';
                }
            };

            setTab('monthly');
            load('monthly', 1);

            root.querySelectorAll('[data-manga-rank-tab]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const period = btn.getAttribute('data-manga-rank-tab');
                    if (!period) return;
                    state[period] = state[period] || 1;
                    setTab(period);
                    load(period, state[period]);
                });
            });

            root.querySelectorAll('[data-manga-rank-page]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const period = btn.getAttribute('data-manga-rank-page-period');
                    const page = Number(btn.getAttribute('data-manga-rank-page')) || 1;
                    if (!period) return;
                    state[period] = page;
                    setTab(period);
                    load(period, page);
                });
            });
        })();
    </script>
@endpush
