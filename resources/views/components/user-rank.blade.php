<div class="bg-bgc-layer1 border-bd-default overflow-hidden rounded-2xl border p-0">
    <div dir="ltr" class="w-full space-y-6" id="user-leaderboard" data-user-leaderboard>
        <div role="tablist" aria-orientation="horizontal" class="border-bd-default flex border-b" tabindex="0" data-orientation="horizontal" style="outline: none;">
            <button
                type="button"
                role="tab"
                aria-selected="true"
                aria-controls="user-leaderboard-weekly"
                data-user-rank-tab="weekly"
                data-state="active"
                class="data-[state=active]:border-lav-500 data-[state=active]:text-txt-primary text-txt-secondary hover:text-txt-primary flex-1 cursor-pointer bg-transparent px-3 py-3 text-sm font-medium transition-colors data-[state=active]:border-b-2 data-[state=active]:font-semibold"
            >
                Top tuần
            </button>
            <button
                type="button"
                role="tab"
                aria-selected="false"
                aria-controls="user-leaderboard-monthly"
                data-user-rank-tab="monthly"
                data-state="inactive"
                class="data-[state=active]:border-lav-500 data-[state=active]:text-txt-primary text-txt-secondary hover:text-txt-primary flex-1 cursor-pointer bg-transparent px-3 py-3 text-sm font-medium transition-colors data-[state=active]:border-b-2 data-[state=active]:font-semibold"
            >
                Top tháng
            </button>
        </div>

        <div
            data-user-panel="weekly"
            id="user-leaderboard-weekly"
            role="tabpanel"
            class="space-y-0 pb-4"
        >
            <div data-user-items="weekly"></div>
        </div>

        <div
            data-user-panel="monthly"
            id="user-leaderboard-monthly"
            role="tabpanel"
            class="space-y-0 pb-4 hidden"
        >
            <div data-user-items="monthly"></div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            const root = document.querySelector('[data-user-leaderboard]');
            if (!root) return;

            const apiBase = "/api/user-leaderboard";

            const formatNumber = (value) => {
                try {
                    return new Intl.NumberFormat('vi-VN').format(Number(value || 0));
                } catch (e) {
                    return String(value || 0);
                }
            };

            const viewsText = (views) => `${formatNumber(views)} lượt xem`;

            const followHtml = (points) => {
                return `
                    <span>${formatNumber(points)}</span>
                    <img class="h-3 w-3" alt="Follows" src="{{ asset('vendor/theme-vinahentai/images/gold-icon.png') }}">
                `;
            };

            const rankColorClass = (rank) => {
                if (rank === 1) return 'text-[#FFE133]';
                if (rank === 2) return 'text-[#5BD8FA]';
                if (rank === 3) return 'text-[#FF7158]';
                return 'text-txt-primary';
            };

            const renderUserItem = (item) => {
                const rank = Number(item.rank || 0);
                const name = item.title || '';
                const url = item.url || '#';
                const avatar = item.avatar || '';
                const views = item.views || 0;
                const points = item.points || 0;

                return `
                    <a href="${url}" class="flex items-center gap-3 p-3">
                        <span class="w-5 text-center text-base font-semibold ${rankColorClass(rank)}">${rank}</span>
                        <div class="relative h-14 w-14 flex-shrink-0 overflow-hidden rounded-full bg-[#121826] flex items-center justify-center">
                            ${
                                avatar
                                    ? `<img alt="${name}" class="absolute inset-0 h-full w-full object-cover" src="${avatar}">`
                                    : `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user h-7 w-7 text-txt-primary" aria-hidden="true">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                      </svg>`
                            }
                        </div>
                        <div class="flex-1 space-y-1">
                            <h3 class="text-txt-primary text-base leading-6 font-semibold">${name}</h3>
                            <div class="flex items-center gap-2 text-txt-secondary text-xs">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye h-3 w-3" aria-hidden="true">
                                    <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <span>${viewsText(views)}</span>
                                <span class="text-[10px] text-txt-secondary">•</span>
                                <span class="flex items-center gap-1">${followHtml(points)}</span>
                            </div>
                        </div>
                    </a>
                `;
            };

            const setPanelVisibility = (period) => {
                const weeklyPanel = root.querySelector('[data-user-panel="weekly"]');
                const monthlyPanel = root.querySelector('[data-user-panel="monthly"]');

                if (period === 'weekly') {
                    if (weeklyPanel) weeklyPanel.classList.remove('hidden');
                    if (monthlyPanel) monthlyPanel.classList.add('hidden');
                } else {
                    if (weeklyPanel) weeklyPanel.classList.add('hidden');
                    if (monthlyPanel) monthlyPanel.classList.remove('hidden');
                }

                root.querySelectorAll('[role="tab"]').forEach((btn) => {
                    const p = btn.getAttribute('data-user-rank-tab');
                    const isActive = p === period;
                    btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
                    btn.dataset.state = isActive ? 'active' : 'inactive';
                });
            };
            const loadUsers = async (period) => {
                const itemsEl = root.querySelector('[data-user-items="' + period + '"]');
                if (!itemsEl) return;

                itemsEl.innerHTML = `
                    <div class="p-3 text-txt-secondary text-sm">
                        Đang tải...
                    </div>
                `;

                const url = `${apiBase}/${period}?page=1`;
                const res = await fetch(url, { method: 'GET' });
                if (!res.ok) {
                    itemsEl.innerHTML = `
                        <div class="p-3 text-txt-secondary text-sm">
                            Không thể tải dữ liệu.
                        </div>
                    `;
                    return;
                }

                const payload = await res.json();
                const items = payload.data || [];

                if (items.length === 0) {
                    itemsEl.innerHTML = `
                        <div class="p-3 text-txt-secondary text-sm">
                            Không có dữ liệu.
                        </div>
                    `;
                    return;
                }

                itemsEl.innerHTML = items.map(renderUserItem).join('');
            };

            root.querySelectorAll('[data-user-rank-tab]').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    const period = btn.getAttribute('data-user-rank-tab');
                    if (!period) return;

                    setPanelVisibility(period);
                    await loadUsers(period);
                });
            });

            // Load mặc định: Top tuần trang 1.
            loadUsers('weekly');
        })();
    </script>
@endpush

