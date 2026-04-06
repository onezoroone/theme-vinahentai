{{-- Dropdown chuông: class panel giống Radix Popover; data-nav-dropdown — main.entry.js. $suffix: lg | sm --}}
@php
    $suffix = $suffix ?? 'lg';
    $list = $headerInAppNotifications ?? collect();
    $unread = (int) ($headerInAppNotificationsUnread ?? 0);
@endphp
<div class="relative shrink-0" data-nav-dropdown data-nav-dropdown-align="end">
    <button type="button"
        id="site-notifications-{{ $suffix }}-trigger"
        class="relative cursor-pointer bg-transparent outline-none focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#D373FF] focus-visible:outline-offset-2"
        data-nav-dropdown-trigger
        data-state="closed"
        aria-haspopup="dialog"
        aria-expanded="false"
        aria-controls="site-notifications-{{ $suffix }}-panel"
        aria-label="Thông báo">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-bell text-txt-primary h-6 w-6" aria-hidden="true">
            <path d="M10.268 21a2 2 0 0 0 3.464 0"></path>
            <path
                d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326">
            </path>
        </svg>
    </button>

    <div id="site-notifications-{{ $suffix }}-panel"
        role="dialog"
        aria-labelledby="site-notifications-{{ $suffix }}-trigger"
        tabindex="-1"
        data-nav-dropdown-tailwind-size="true"
        class="outline-bd-default bg-bgc-layer1 z-50 hidden flex w-96 max-h-[80vh] flex-col overflow-hidden rounded-lg outline-1 outline-offset-[-1px] will-change-[transform,opacity] sm:w-80 sm:max-h-[70vh] md:w-96 md:max-h-[34rem]"
        data-nav-dropdown-panel
        data-state="closed">
        <div class="bg-bgc-layer1 flex items-center justify-between p-3">
            <div class="text-txt-primary font-sans text-base leading-normal font-semibold">Thông báo</div>
            <div class="text-txt-focus cursor-not-allowed font-sans text-sm leading-tight font-medium opacity-50">Xem tất cả</div>
        </div>
        <div class="flex flex-1 flex-col overflow-hidden">
            <div class="flex-1 overflow-y-auto px-1 pb-3">
                <div class="flex flex-col divide-y divide-bd-default/40">
                    @forelse ($list as $n)
                        @php
                            /** @var \App\Models\UserNotification $n */
                            $meta = $n->pointsMeta();
                            $ctaUrl = is_array($meta) ? ($meta['action_url'] ?? null) : null;
                            $ctaLabel = is_array($meta) ? ($meta['cta_label'] ?? 'Đi tới') : 'Đi tới';
                            $ctaUrl = is_string($ctaUrl) && $ctaUrl !== '' ? $ctaUrl : null;
                        @endphp
                        <div class="bg-bgc-layer1 flex flex-col gap-3 px-3 py-3 first:pt-3">
                            <div class="flex items-start gap-4">
                                <div class="mt-1 h-2 w-2 rounded-full bg-txt-secondary" aria-hidden="true"></div>
                                <div class="flex flex-col gap-1">
                                    <span
                                        class="text-txt-focus font-sans text-sm font-semibold leading-snug">{{ $n->title }}</span>
                                    @if (filled($n->message))
                                        <span
                                            class="text-txt-primary font-sans text-sm leading-snug">{{ $n->message }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span
                                    class="text-txt-primary font-sans text-xs font-medium uppercase tracking-wide opacity-70">{{ $n->created_at?->locale('vi')->diffForHumans() ?? '' }}</span>
                                @if ($ctaUrl)
                                    <div class="flex items-center gap-2">
                                        <a href="{{ $ctaUrl }}"
                                            class="text-txt-focus flex items-center gap-1 rounded-full border border-transparent px-3 py-1 text-xs font-semibold transition hover:border-txt-focus hover:bg-bgc-layer2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-arrow-up-right h-3.5 w-3.5" aria-hidden="true">
                                                <path d="M7 7h10v10"></path>
                                                <path d="M7 17 17 7"></path>
                                            </svg>
                                            {{ $ctaLabel }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-txt-secondary px-3 py-8 text-center font-sans text-sm font-medium">Chưa có thông báo.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
