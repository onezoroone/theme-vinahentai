<div class="space-y-6 scroll-mt-[calc(var(--site-header-height)+12px)]" id="home-leaderboard">
    <div class="flex items-center gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trophy h-6 w-6 text-lav-500" aria-hidden="true">
            <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path>
            <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path>
            <path d="M4 22h16"></path>
            <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path>
            <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path>
            <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path>
        </svg>
        <h2 class="text-txt-primary text-xl font-semibold uppercase">bảng xếp hạng</h2>
    </div>

    <div class="bg-bgc-layer1 border-bd-default overflow-hidden rounded-2xl border p-0">
        <div class="w-full" data-orientation="horizontal" dir="ltr">
            <div
                role="tablist"
                aria-orientation="horizontal"
                class="border-bd-default flex border-b"
                tabindex="0"
                data-orientation="horizontal"
                style="outline: none;"
            >
                <button
                    type="button"
                    role="tab"
                    data-rank-tab="weekly"
                    aria-selected="true"
                    data-state="active"
                    class="data-[state=active]:border-lav-500 data-[state=active]:text-txt-primary text-txt-secondary hover:text-txt-primary flex-1 cursor-pointer bg-transparent px-3 py-3 text-base font-medium transition-colors data-[state=active]:border-b-2 data-[state=active]:font-semibold"
                >
                    Top tuần
                </button>
                <button
                    type="button"
                    role="tab"
                    data-rank-tab="monthly"
                    aria-selected="false"
                    data-state="inactive"
                    class="data-[state=active]:border-lav-500 data-[state=active]:text-txt-primary text-txt-secondary hover:text-txt-primary flex-1 cursor-pointer bg-transparent px-3 py-3 text-base font-medium transition-colors data-[state=active]:border-b-2 data-[state=active]:font-semibold"
                    tabindex="-1"
                >
                    Top tháng
                </button>
            </div>

            <div role="tabpanel" data-rank-panel="weekly" class="space-y-0 pb-4" style="">
                <div data-rank-items="weekly" class="space-y-0"></div>

                <div class="px-3 pb-2 pt-1">
                    <div class="flex gap-2">
                        <button
                            type="button"
                            data-rank-page-period="weekly"
                            data-rank-page="1"
                            class="flex-1 w-full rounded-md bg-white/5 py-2 text-sm font-medium text-white hover:bg-white/10 disabled:opacity-60 disabled:cursor-not-allowed"
                            disabled
                        >
                            Trang 1
                        </button>
                        <button
                            type="button"
                            data-rank-page-period="weekly"
                            data-rank-page="2"
                            class="flex-1 w-full rounded-md bg-white/5 py-2 text-sm font-medium text-white hover:bg-white/10"
                        >
                            Trang 2 ▸
                        </button>
                    </div>
                </div>
            </div>

            <div role="tabpanel" data-rank-panel="monthly" class="space-y-0 pb-4" hidden>
                <div data-rank-items="monthly" class="space-y-0"></div>

                <div class="px-3 pb-2 pt-1">
                    <div class="flex gap-2">
                        <button
                            type="button"
                            data-rank-page-period="monthly"
                            data-rank-page="1"
                            class="flex-1 w-full rounded-md bg-white/5 py-2 text-sm font-medium text-white hover:bg-white/10"
                        >
                            Trang 1
                        </button>
                        <button
                            type="button"
                            data-rank-page-period="monthly"
                            data-rank-page="2"
                            class="flex-1 w-full rounded-md bg-white/5 py-2 text-sm font-medium text-white hover:bg-white/10"
                            disabled
                        >
                            Trang 2 ▸
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            const root = document.getElementById('home-leaderboard');
            if (!root) return;

            const apiBase = @json(url('/api/leaderboard'));

            const state = {
                weekly: 1,
                monthly: 1
            };
            let activeRequestId = 0;

            const formatViews = (n) => {
                try {
                    return new Intl.NumberFormat('vi-VN').format(Number(n) || 0);
                } catch (e) {
                    return String(Number(n) || 0);
                }
            };

            const setTab = (period) => {
                const weeklyBtn = root.querySelector('[data-rank-tab="weekly"]');
                const monthlyBtn = root.querySelector('[data-rank-tab="monthly"]');

                const weeklyPanel = root.querySelector('[data-rank-panel="weekly"]');
                const monthlyPanel = root.querySelector('[data-rank-panel="monthly"]');

                if (period === 'weekly') {
                    weeklyBtn?.setAttribute('data-state', 'active');
                    weeklyBtn?.setAttribute('aria-selected', 'true');
                    monthlyBtn?.setAttribute('data-state', 'inactive');
                    monthlyBtn?.setAttribute('aria-selected', 'false');
                    monthlyBtn?.setAttribute('tabindex', '-1');

                    weeklyPanel?.removeAttribute('hidden');
                    monthlyPanel?.setAttribute('hidden', '');
                } else {
                    monthlyBtn?.setAttribute('data-state', 'active');
                    monthlyBtn?.setAttribute('aria-selected', 'true');
                    weeklyBtn?.setAttribute('data-state', 'inactive');
                    weeklyBtn?.setAttribute('aria-selected', 'false');
                    weeklyBtn?.setAttribute('tabindex', '-1');

                    monthlyPanel?.removeAttribute('hidden');
                    weeklyPanel?.setAttribute('hidden', '');
                }

                updatePagination(period);
            };

            const updatePagination = (period) => {
                const page = state[period] || 1;
                const page1Btn = root.querySelector('[data-rank-page-period="' + period + '"][data-rank-page="1"]');
                const page2Btn = root.querySelector('[data-rank-page-period="' + period + '"][data-rank-page="2"]');

                if (page1Btn) {
                    page1Btn.disabled = page === 1;
                }
                if (page2Btn) {
                    page2Btn.disabled = page === 2;
                }
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
                imgWrap.className = 'flex-shrink-0 overflow-hidden bg-black/20 rounded-lg aspect-[2/3]';
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
                h3.className = 'text-txt-primary line-clamp-1 text-base leading-6 font-semibold';
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
                const itemsEl = root.querySelector('[data-rank-items="' + period + '"]');
                if (!itemsEl) return;

                const requestId = ++activeRequestId;
                itemsEl.innerHTML = `
                    <div class="p-3 text-txt-secondary text-sm">
                        Đang tải...
                    </div>
                `;

                try {
                    const res = await fetch(`${apiBase}/${period}?page=${page}`, { method: 'GET' });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const json = await res.json();
                    if (requestId !== activeRequestId) return;

                    const items = json?.data || [];
                    itemsEl.innerHTML = '';

                    if (items.length === 0) {
                        itemsEl.innerHTML = `
                            <div class="p-3 text-txt-secondary text-sm">
                                Không có dữ liệu
                            </div>
                        `;
                        return;
                    }

                    items.forEach((it) => {
                        itemsEl.appendChild(renderItem(it));
                    });
                } catch (e) {
                    if (requestId !== activeRequestId) return;
                    console.error(e);
                    itemsEl.innerHTML = `
                        <div class="p-3 text-txt-secondary text-sm">
                            Không tải được dữ liệu
                        </div>
                    `;
                }
            };

            // Load mặc định: tuần page 1
            setTab('weekly');
            load('weekly', 1);

            // Tab click
            root.querySelectorAll('[data-rank-tab]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const period = btn.getAttribute('data-rank-tab');
                    if (!period) return;
                    state[period] = state[period] || 1;
                    setTab(period);
                    load(period, state[period]);
                });
            });

            // Pagination click
            root.querySelectorAll('[data-rank-page]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const period = btn.getAttribute('data-rank-page-period');
                    const page = Number(btn.getAttribute('data-rank-page')) || 1;
                    if (!period) return;
                    state[period] = page;
                    setTab(period);
                    load(period, page);
                });
            });
        })();
    </script>
@endpush
