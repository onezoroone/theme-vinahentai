@php
    // Cache có thể lưu sai kiểu; luôn đảm bảo là Collection model.
    $menus = Cache::rememberForever('theme_vinahentai.menu_tree_v1', function () {
        return \Backpack\MenuCRUD\app\Models\MenuItem::getTree();
    });
    if (!$menus instanceof \Illuminate\Support\Collection) {
        Cache::forget('theme_vinahentai.menu_tree_v1');
        $menus = \Backpack\MenuCRUD\app\Models\MenuItem::getTree();
    }
    $menus = $menus->filter(fn($row) => is_object($row) && $row instanceof \Backpack\MenuCRUD\app\Models\MenuItem);
    if ($menus->isEmpty()) {
        $menus = \Backpack\MenuCRUD\app\Models\MenuItem::getTree();
    }

    $normalizeMenuChildren = static function ($raw): \Illuminate\Support\Collection {
        if ($raw instanceof \Illuminate\Support\Collection) {
            return $raw;
        }
        if (is_array($raw)) {
            return collect($raw);
        }

        return collect();
    };

    $mobileMenuRandomItem = $menus->first(fn($row) => is_object($row) && $row->name === 'Random');
    $mobileMenuRandomUrl = $mobileMenuRandomItem ? $mobileMenuRandomItem->url() : '/random';
@endphp

<header class="flex w-full flex-col" data-site-search-url="{{ route('search') }}" data-site-search-param="q">
    <div class="fixed left-0 right-0 z-50 transition-transform duration-300 will-change-transform translate-y-0"
        data-site-header
        style="top:0;height:var(--site-header-height)">
        <div
            class="relative flex items-center justify-between self-stretch overflow-hidden bg-[rgba(9,16,26,0.85)] px-0 pt-2.5 pb-0.5 shadow-lg backdrop-blur-sm md:bg-[rgba(9,16,26,0.9)] md:pb-2">
            <div class="hidden lg:block w-full">
                <div class="container-page mx-auto px-4 relative flex items-center justify-between">
                    <div class="flex items-center">
                        {!! active_theme_config('site_logo_html', '') !!}
                    </div>

                    <div
                        class="absolute left-1/2 flex w-full max-w-[calc(100vw-1rem)] -translate-x-1/2 transform items-center gap-2 px-2 sm:max-w-[520px] sm:px-0 lg:gap-3">
                        <div class="group bg-bgc-layer2 flex w-full items-center gap-2 rounded-xl px-3 py-1.5 sm:px-4">
                            <a href="{{ route('search') }}"
                                class="hover:text-txt-primary flex h-5 w-5 flex-shrink-0 items-center justify-center transition-colors text-txt-secondary"
                                aria-label="Tìm kiếm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search h-5 w-5"
                                    aria-hidden="true">
                                    <path d="m21 21-4.34-4.34"></path>
                                    <circle cx="11" cy="11" r="8"></circle>
                                </svg>
                            </a>
                            <input type="search" name="q" placeholder="Tìm truyện..."
                                class="text-txt-secondary placeholder:text-txt-secondary focus:text-txt-primary flex-1 bg-transparent leading-normal font-medium outline-none focus:outline-none"
                                value="" data-site-search-input autocomplete="off" enterkeyhint="search">
                        </div>
                        <a href="{{ route('search.advanced') }}"
                            class="inline-flex flex-shrink-0 items-center gap-1 rounded-full border border-[#C084FC] bg-bgc-layer2/80 px-3 py-1.5 text-xs font-semibold text-[#E0B2FF] transition hover:bg-bgc-layer2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C084FC]"
                            aria-label="Mở tìm kiếm nâng cao">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-search h-4 w-4 text-[#C084FC]"
                                aria-hidden="true">
                                <path d="m21 21-4.34-4.34"></path>
                                <circle cx="11" cy="11" r="8"></circle>
                            </svg>
                            <span>Nâng cao</span>
                        </a>
                    </div>

                    <div class="flex items-center justify-start gap-4">
                        @if (!Auth::check())
                            <a href="{{ route('login') }}"
                                class="outline-lav-500 flex items-center justify-center gap-2.5 rounded-xl px-4 py-3 outline outline-offset-[-1px]"><span
                                    class="text-txt-focus text-center text-sm leading-tight font-medium">Đăng
                                    nhập</span></a><a href="{{ route('register') }}"
                                class="flex items-center justify-center gap-2.5 rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] px-4 py-3 shadow-[0px_4px_9px_rgba(196,69,255,0.25)]"><span
                                    class="text-center text-sm leading-tight font-semibold text-black">Đăng
                                    ký</span></a>
                        @else
                            @php
                                $logoutAction = \Illuminate\Support\Facades\Route::has('logout')
                                    ? route('logout')
                                    : url('/logout');
                            @endphp
                            @include('theme-vinahentai::partials.header-notifications-dropdown', ['suffix' => 'lg'])

                            {{-- Dropdown tài khoản: data-nav-dropdown + căn end (main.entry.js) --}}
                            <div class="relative shrink-0" data-nav-dropdown data-nav-dropdown-align="end">
                                <button type="button" id="site-user-menu-lg-trigger"
                                    class="flex cursor-pointer items-center gap-2 rounded-lg bg-transparent text-left outline-none focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#D373FF] focus-visible:outline-offset-2"
                                    data-nav-dropdown-trigger data-state="closed" aria-haspopup="dialog"
                                    aria-expanded="false" aria-controls="site-user-menu-lg-panel">
                                    <div
                                        class="flex h-7 w-7 shrink-0 items-center justify-center overflow-hidden rounded-full bg-[#121826]">
                                        @if (Auth::user()->avatar)
                                            <img src="{{ Auth::user()->avatar }}" alt="" loading="lazy"
                                                decoding="async" class="block h-full w-full object-cover">
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-user h-5 w-5 text-txt-primary" aria-hidden="true">
                                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="flex min-w-0 items-center gap-2">
                                        <span
                                            class="text-txt-primary text-base font-medium">{{ Auth::user()->name }}</span>
                                        @if (Auth::user()->level)
                                            <img src="{{ asset(Auth::user()->level->image) }}" alt="" loading="lazy"
                                                decoding="async" class="block w-auto shrink-0 object-contain"
                                                style="height:90%;max-height:50px;max-width:7.5rem">
                                        @endif
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-menu h-5 w-5 shrink-0 text-[#D373FF]"
                                            aria-hidden="true">
                                            <path d="M4 12h16"></path>
                                            <path d="M4 18h16"></path>
                                            <path d="M4 6h16"></path>
                                        </svg>
                                    </div>
                                </button>

                                <div id="site-user-menu-lg-panel" role="dialog"
                                    aria-labelledby="site-user-menu-lg-trigger" tabindex="-1"
                                    class="border-bd-default bg-bgc-layer1 z-[99999] hidden w-72 min-w-[18rem] overflow-hidden rounded-lg border shadow-lg will-change-[transform,opacity]"
                                    data-nav-dropdown-panel data-state="closed">
                                    <div class="w-full px-0">
                                        <a href="{{ url('/user/blacklist-tags') }}"
                                            class="group flex w-full cursor-pointer items-center justify-start gap-3 border-b border-white/8 bg-[#0C1424] px-5 py-3.5 pb-5 text-left transition-colors last:border-b-0 hover:bg-bgc-layer2/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C084FC] focus-visible:ring-offset-0 active:bg-bgc-layer2 md:border-b-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-shield-ban h-5 w-5 text-[#BFC5E6] transition-colors group-hover:text-white"
                                                aria-hidden="true">
                                                <path
                                                    d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z">
                                                </path>
                                                <path d="m4.243 5.21 14.39 12.472"></path>
                                            </svg>
                                            <span
                                                class="text-base leading-6 font-semibold whitespace-nowrap text-[#D6DAE6] group-hover:text-white">Lọc
                                                thể loại không thích</span>
                                        </a>
                                        <a href="{{ route('profile', Auth::id()) }}#reading-history"
                                            class="group flex w-full cursor-pointer items-center justify-start gap-3 border-b border-white/8 bg-[#161E35] px-5 py-3.5 text-left transition-colors last:border-b-0 hover:bg-bgc-layer2/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C084FC] focus-visible:ring-offset-0 active:bg-bgc-layer2 md:border-b-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-history h-5 w-5 text-[#BFC5E6] transition-colors group-hover:text-white"
                                                aria-hidden="true">
                                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                                <path d="M3 3v5h5"></path>
                                                <path d="M12 7v5l4 2"></path>
                                            </svg>
                                            <span
                                                class="text-base leading-6 font-semibold whitespace-nowrap text-[#D6DAE6] group-hover:text-white">Lịch
                                                sử đọc</span>
                                        </a>
                                        <a href="{{ route('profile', Auth::id()) }}#saved-stories"
                                            class="group flex w-full cursor-pointer items-center justify-start gap-3 border-b border-white/8 bg-[#161E35] px-5 py-3.5 pb-5 text-left transition-colors last:border-b-0 hover:bg-bgc-layer2/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C084FC] focus-visible:ring-offset-0 active:bg-bgc-layer2 md:border-b-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-bookmark-check h-5 w-5 text-[#BFC5E6] transition-colors group-hover:text-white"
                                                aria-hidden="true">
                                                <path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2Z"></path>
                                                <path d="m9 10 2 2 4-4"></path>
                                            </svg>
                                            <span
                                                class="text-base leading-6 font-semibold whitespace-nowrap text-[#D6DAE6] group-hover:text-white">Truyện
                                                đang theo dõi</span>
                                        </a>
                                        <a href="{{ route('profile', Auth::user()->id) }}"
                                            class="group flex w-full cursor-pointer items-center justify-start gap-3 border-b border-white/8 bg-[#0C1424] px-5 py-3.5 text-left transition-colors last:border-b-0 hover:bg-bgc-layer2/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C084FC] focus-visible:ring-offset-0 active:bg-bgc-layer2 md:border-b-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-user-round h-5 w-5 text-[#BFC5E6] transition-colors group-hover:text-white"
                                                aria-hidden="true">
                                                <circle cx="12" cy="8" r="5"></circle>
                                                <path d="M20 21a8 8 0 0 0-16 0"></path>
                                            </svg>
                                            <span
                                                class="text-base leading-6 font-semibold whitespace-nowrap text-[#D6DAE6] group-hover:text-white">Trang cá nhân</span>
                                        </a>
                                        <a href="{{ route('user.manage-manga') }}"
                                            class="group flex w-full cursor-pointer items-center justify-start gap-3 border-b border-white/8 bg-[#0C1424] px-5 py-3.5 text-left transition-colors last:border-b-0 hover:bg-bgc-layer2/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C084FC] focus-visible:ring-offset-0 active:bg-bgc-layer2 md:border-b-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-layers h-5 w-5 text-[#BFC5E6] transition-colors group-hover:text-white"
                                                aria-hidden="true">
                                                <path
                                                    d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83z">
                                                </path>
                                                <path d="M2 12a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9A1 1 0 0 0 22 12">
                                                </path>
                                                <path d="M2 17a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9A1 1 0 0 0 22 17">
                                                </path>
                                            </svg>
                                            <span
                                                class="text-base leading-6 font-semibold whitespace-nowrap text-[#D6DAE6] group-hover:text-white">Quản
                                                lý / Đăng truyện</span>
                                        </a>
                                        <a href="{{ route('shop') }}"
                                            class="group flex w-full cursor-pointer items-center justify-start gap-3 border-b border-white/8 bg-[#0C1424] px-5 py-3.5 pb-5 text-left transition-colors last:border-b-0 hover:bg-bgc-layer2/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C084FC] focus-visible:ring-offset-0 active:bg-bgc-layer2 md:border-b-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-shopping-bag h-5 w-5 text-[#BFC5E6] transition-colors group-hover:text-white"
                                                aria-hidden="true">
                                                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                                                <path d="M3 6h18"></path>
                                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                                            </svg>
                                            <span
                                                class="text-base leading-6 font-semibold whitespace-nowrap text-[#D6DAE6] group-hover:text-white">Cửa
                                                hàng</span>
                                        </a>
                                        <form method="POST" action="{{ $logoutAction }}" class="m-0">
                                            @csrf
                                            <button type="submit"
                                                class="group flex w-full cursor-pointer items-center justify-start gap-3 border-b border-white/8 bg-[#161E35] px-5 py-3.5 text-left transition-colors hover:bg-bgc-layer2/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C084FC] focus-visible:ring-offset-0 active:bg-bgc-layer2 md:border-b-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="lucide lucide-power h-4 w-4 text-[#E03F46]" aria-hidden="true">
                                                    <path d="M12 2v10"></path>
                                                    <path d="M18.4 6.6a9 9 0 1 1-12.77.04"></path>
                                                </svg>
                                                <span
                                                    class="text-base leading-6 font-semibold whitespace-nowrap text-[#E03F46]">Đăng
                                                    xuất</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex w-full items-center justify-between px-4 lg:hidden sm:px-8">
                {!! active_theme_config('site_logo_html', '') !!}

                <div class="flex min-w-0 flex-1 items-center justify-end gap-2 sm:gap-3">
                    {{-- Bấm icon → thay bằng thanh tìm tại chỗ; X → trả lại icon (không overlay) --}}
                    <div class="flex min-w-0 flex-1 items-center justify-end" data-mobile-inline-search>
                        <button type="button"
                            class="flex shrink-0 items-center justify-center rounded-lg p-1 transition hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-lav-500"
                            data-mobile-search-open aria-label="Mở tìm kiếm" aria-expanded="false"
                            aria-controls="mobile-inline-search-panel">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-search text-txt-primary h-6 w-6" aria-hidden="true">
                                <path d="m21 21-4.34-4.34"></path>
                                <circle cx="11" cy="11" r="8"></circle>
                            </svg>
                        </button>
                        <div id="mobile-inline-search-panel"
                            class="hidden fixed inset-0 z-50 bg-[rgba(9,16,26,0.95)] backdrop-blur-sm"
                            data-mobile-search-panel>
                            <div class="flex w-full items-center gap-2.5 p-2.5">
                                <div
                                    class="bg-bgc-layer2 flex w-full min-w-0 items-center justify-start gap-2 rounded-xl px-3 py-1.5">
                                    <a href="{{ route('search') }}"
                                        class="hover:text-txt-primary flex h-5 w-5 flex-shrink-0 items-center justify-center transition-colors text-txt-secondary"
                                        aria-label="Tìm kiếm">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-search h-5 w-5" aria-hidden="true">
                                            <path d="m21 21-4.34-4.34"></path>
                                            <circle cx="11" cy="11" r="8"></circle>
                                        </svg>
                                    </a>
                                    <input type="search" name="q" placeholder="Tìm truyện..."
                                        class="text-txt-secondary placeholder:text-txt-secondary focus:text-txt-primary min-w-0 flex-1 bg-transparent leading-normal font-medium outline-none focus:outline-none"
                                        value="" data-mobile-search-input data-site-search-input
                                        autocomplete="off" enterkeyhint="search">
                                    <button type="button"
                                        class="text-txt-secondary hover:text-txt-primary flex shrink-0 items-center justify-center rounded-lg p-1 transition focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-lav-500"
                                        data-mobile-search-close aria-label="Đóng tìm kiếm">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-x h-4 w-4" aria-hidden="true">
                                            <path d="M18 6 6 18"></path>
                                            <path d="m6 6 12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @auth
                        @include('theme-vinahentai::partials.header-notifications-dropdown', ['suffix' => 'sm'])
                    @endauth
                    <button type="button"
                        class="rounded-lg p-1 transition hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-lav-500"
                        aria-label="Mở menu" data-mobile-menu-trigger aria-haspopup="dialog" aria-expanded="false"
                        aria-controls="mobile-menu-popover" data-state="closed">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-menu text-txt-primary h-7 w-7"
                            aria-hidden="true">
                            <path d="M4 12h16"></path>
                            <path d="M4 18h16"></path>
                            <path d="M4 6h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- justify-start + overflow-x-auto: tránh justify-center làm mất item đầu khi hàng tràn --}}
        <nav
            class="hidden items-center justify-center gap-8 overflow-x-auto bg-[rgba(9,16,26,0.8)] px-8 py-[0.55rem] lg:flex lg:w-full -mt-1.5">
            @foreach ($menus as $item)
                @php
                    $nameLower = mb_strtolower($item->name);
                    // Mega thể loại: từ DB (genresByLetter). Không dùng $item->children — đó là MenuItem con trong Backpack.
                    $isMegaGenreNav =
                        str_contains($nameLower, 'thể loại') ||
                        str_contains($nameLower, 'the loai') ||
                        str_contains($nameLower, 'genre');
                    $menuChildren = $normalizeMenuChildren($item->children ?? null);
                    $menuChildrenCount = $menuChildren->count();
                    $hasSubmenu = $isMegaGenreNav || $menuChildrenCount > 0;
                @endphp
                @if ($hasSubmenu)
                    <div class="max-[375px]:min-w-0 shrink-0 whitespace-nowrap" data-nav-dropdown>
                        <button type="button"
                            class="group flex items-center justify-start gap-1 text-sm lg:text-[17px] whitespace-nowrap touch-manipulation"
                            aria-haspopup="dialog" aria-expanded="false"
                            aria-controls="nav-popover-{{ $item->id }}-lg"
                            id="nav-popover-{{ $item->id }}-lg-trigger" data-nav-dropdown-trigger
                            data-state="closed">
                            <div
                                class="text-txt-primary text-sm lg:text-[17px] leading-tight font-semibold whitespace-nowrap text-outline-purple">
                                {{ $item->name }}</div>
                            <span class="inline-flex text-outline-purple-thin lg:text-outline-purple">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-chevron-down text-txt-primary h-3 w-3 transition-transform duration-200 group-data-[state=open]:rotate-180 lg:h-4 lg:w-4"
                                    aria-hidden="true" data-nav-dropdown-chevron>
                                    <path d="m6 9 6 6 6-6"></path>
                                </svg>
                            </span>
                        </button>

                        <div id="nav-popover-{{ $item->id }}-lg" role="dialog"
                            aria-labelledby="nav-popover-{{ $item->id }}-lg-trigger"
                            class="hidden fixed left-0 top-0 z-[99999] flex max-h-[80svh] w-[100vw] flex-col overflow-hidden rounded-xl border border-bd-default bg-bgc-layer1 p-4 shadow-lg will-change-[transform,opacity] sm:w-full sm:max-w-[95vw] md:w-[640px] md:max-w-[640px]"
                            tabindex="-1" data-nav-dropdown-panel data-state="closed">
                            @if ($isMegaGenreNav)
                                <div class="mb-3 flex min-w-0 items-center justify-between gap-2">
                                    <label class="relative min-w-0 flex-1">
                                        <svg aria-hidden="true" viewBox="0 0 24 24"
                                            class="pointer-events-none absolute left-2 top-1/2 h-4 w-4 -translate-y-1/2 opacity-70"
                                            fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="11" cy="11" r="7"></circle>
                                            <line x1="21" y1="21" x2="16.65" y2="16.65">
                                            </line>
                                        </svg>
                                        <input type="search"
                                            placeholder="Có thể nhập nhiều từ khóa cùng lúc… (vd: manhwa color series)"
                                            class="genre-mega-search w-full rounded-md border border-bd-default bg-bgc-layer2 py-2 pl-8 pr-3 text-base outline-none focus:ring-2 focus:ring-primary/40 md:text-sm"
                                            autocomplete="off" data-genre-mega-search aria-label="Lọc thể loại">
                                    </label>
                                    <button type="button"
                                        class="shrink-0 inline-flex h-8 items-center gap-1 rounded-md border border-bd-default bg-bgc-layer2 px-2 text-xs font-bold hover:bg-bgc-layer3"
                                        aria-label="Chuyển đổi ngôn ngữ">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="h-3.5 w-3.5" aria-hidden="true">
                                            <path d="m5 8 6 6"></path>
                                            <path d="m4 14 6-6 2-3"></path>
                                            <path d="M2 5h12"></path>
                                            <path d="M7 2h1"></path>
                                            <path d="m22 22-5-10-5 10"></path>
                                            <path d="M14 18h6"></path>
                                        </svg>
                                        <span>VI</span>
                                    </button>
                                    <button type="button"
                                        class="shrink-0 inline-flex h-8 w-8 items-center justify-center rounded-md border border-bd-default bg-bgc-layer2 hover:bg-bgc-layer3"
                                        aria-label="Đóng" data-nav-dropdown-close>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="h-4 w-4" aria-hidden="true">
                                            <path d="M18 6 6 18"></path>
                                            <path d="m6 6 12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex min-h-0 flex-1 flex-col overflow-y-auto pr-2 pb-2"
                                    data-genre-mega-scroll>
                                    <div class="space-y-4" data-genre-mega-list>
                                        @forelse ($genresByLetter as $letter => $genres)
                                            <div data-genre-letter-group>
                                                <div class="mb-2 flex items-center gap-2">
                                                    <div class="text-sm font-bold text-txt-focus">{{ $letter }}
                                                    </div>
                                                    <div class="h-px flex-1 bg-bd-default/60"></div>
                                                </div>
                                                <div class="grid grid-cols-3 gap-x-4 gap-y-2">
                                                    @foreach ($genres as $genre)
                                                        <a href="{{ $genre->getUrl() }}"
                                                            class="genre-mega-link block break-inside-avoid py-1 touch-manipulation [touch-action:manipulation]"
                                                            data-genre-search-text="{{ mb_strtolower($genre->name) }}"
                                                            @if (filled($genre->description)) title="{{ $genre->description }}" @endif>
                                                            <div
                                                                class="text-txt-primary hover:text-txt-focus text-xs [@media(min-width:427px)]:text-[15px] sm:text-[15px] font-medium">
                                                                @php
                                                                    $gn = $genre->name;
                                                                    $first = mb_substr($gn, 0, 1, 'UTF-8');
                                                                    $rest = mb_substr($gn, 1, null, 'UTF-8');
                                                                @endphp
                                                                <span
                                                                    class="font-bold">{{ $first }}</span>{{ $rest }}
                                                            </div>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-sm text-txt-secondary">Chưa có thể loại.</p>
                                        @endforelse
                                    </div>
                                </div>
                            @elseif ($menuChildrenCount > 0)
                                <div class="mb-2 flex items-center justify-end">
                                    <button type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-bd-default bg-bgc-layer2 hover:bg-bgc-layer3"
                                        aria-label="Đóng" data-nav-dropdown-close>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" class="h-4 w-4" aria-hidden="true">
                                            <path d="M18 6 6 18"></path>
                                            <path d="m6 6 12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <ul class="max-h-[60vh] min-w-[200px] space-y-1 overflow-y-auto py-1">
                                    @foreach ($menuChildren as $child)
                                        <li>
                                            <a href="{{ $child->url() }}"
                                                class="block rounded-md px-3 py-2 text-sm font-medium text-txt-primary hover:bg-bgc-layer2">
                                                {{ $child->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @else
                    @if ($item->name == 'Random')
                        <a href="{{ $item->url() }}"
                            class="inline-flex items-center justify-center px-2 py-1 rounded-md touch-manipulation text-sm lg:text-[17px] leading-tight font-semibold whitespace-nowrap text-center text-txt-primary text-outline-purple shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-shuffle h-4 w-4 mr-1" aria-hidden="true">
                                <path d="m18 14 4 4-4 4"></path>
                                <path d="m18 2 4 4-4 4"></path>
                                <path d="M2 18h1.973a4 4 0 0 0 3.3-1.7l5.454-8.6a4 4 0 0 1 3.3-1.7H22"></path>
                                <path d="M2 6h1.972a4 4 0 0 1 3.6 2.2"></path>
                                <path d="M22 18h-6.041a4 4 0 0 1-3.3-1.8l-.359-.45"></path>
                            </svg>{{ $item->name }}
                        </a>
                    @elseif ($item->name != 'Triệu hồi Waifu')
                        <a href="{{ $item->url() }}"
                            class="inline-flex items-center justify-center px-2 py-1 rounded-md touch-manipulation text-sm lg:text-[17px] leading-tight font-semibold whitespace-nowrap text-center text-txt-primary text-outline-purple shrink-0">
                            {{ $item->name }}
                        </a>
                    @else
                        <div
                            class="flex h-6 items-center justify-center whitespace-nowrap max-[375px]:min-w-0 shrink-0">
                            <img src="{{ asset('vendor/theme-vinahentai/images/waifu-icon.png') }}"
                                alt="{{ $item->name }}"
                                class="hidden lg:inline-block h-6 pointer-events-none select-none">
                            <a href="/waifu/summon"
                                class="inline-flex items-center justify-center px-2 py-1 rounded-md touch-manipulation text-sm lg:text-[17px] leading-tight font-semibold whitespace-nowrap text-center text-txt-primary text-outline-purple shrink-0">{{ $item->name }}</a>
                        </div>
                    @endif
                @endif
            @endforeach
        </nav>

        <nav
            class="relative z-30 flex w-full min-w-0 flex-row flex-nowrap items-center justify-start gap-4 overflow-x-auto overscroll-x-contain bg-[rgba(9,16,26,0.85)] px-4 pt-[0.15rem] pb-[0.6rem] lg:hidden no-scrollbar touch-pan-x md:bg-[rgba(9,16,26,0.8)] md:py-[0.6rem] max-[376px]:gap-2 max-[376px]:px-2">
            @foreach ($menus as $item)
                @php
                    $nameLower = mb_strtolower($item->name);
                    $isMegaGenreNav =
                        str_contains($nameLower, 'thể loại') ||
                        str_contains($nameLower, 'the loai') ||
                        str_contains($nameLower, 'genre');
                    $menuChildren = $normalizeMenuChildren($item->children ?? null);
                    $menuChildrenCount = $menuChildren->count();
                    $hasSubmenu = $isMegaGenreNav || $menuChildrenCount > 0;
                @endphp
                @if ($hasSubmenu)
                    <div class="max-[375px]:min-w-0 shrink-0 whitespace-nowrap" data-nav-dropdown>
                        <button type="button"
                            class="group flex items-center justify-start gap-1 text-sm lg:text-[17px] whitespace-nowrap touch-manipulation"
                            aria-haspopup="dialog" aria-expanded="false"
                            aria-controls="nav-popover-{{ $item->id }}-sm"
                            id="nav-popover-{{ $item->id }}-sm-trigger" data-nav-dropdown-trigger
                            data-state="closed">
                            <div
                                class="text-txt-primary text-sm lg:text-[17px] leading-tight font-semibold whitespace-nowrap text-outline-purple">
                                {{ $item->name }}</div>
                            <span class="inline-flex text-outline-purple-thin lg:text-outline-purple">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-chevron-down text-txt-primary h-3 w-3 transition-transform duration-200 group-data-[state=open]:rotate-180 lg:h-4 lg:w-4"
                                    aria-hidden="true" data-nav-dropdown-chevron>
                                    <path d="m6 9 6 6 6-6"></path>
                                </svg>
                            </span>
                        </button>

                        <div id="nav-popover-{{ $item->id }}-sm" role="dialog"
                            aria-labelledby="nav-popover-{{ $item->id }}-sm-trigger"
                            class="hidden fixed left-0 top-0 z-[99999] flex max-h-[80svh] w-[100vw] flex-col overflow-hidden rounded-xl border border-bd-default bg-bgc-layer1 p-4 shadow-lg will-change-[transform,opacity] sm:w-full sm:max-w-[95vw] md:w-[640px] md:max-w-[640px]"
                            tabindex="-1" data-nav-dropdown-panel data-state="closed">
                            @if ($isMegaGenreNav)
                                <div class="mb-3 flex min-w-0 items-center justify-between gap-2">
                                    <label class="relative min-w-0 flex-1">
                                        <svg aria-hidden="true" viewBox="0 0 24 24"
                                            class="pointer-events-none absolute left-2 top-1/2 h-4 w-4 -translate-y-1/2 opacity-70"
                                            fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="11" cy="11" r="7"></circle>
                                            <line x1="21" y1="21" x2="16.65" y2="16.65">
                                            </line>
                                        </svg>
                                        <input type="search"
                                            placeholder="Có thể nhập nhiều từ khóa cùng lúc… (vd: manhwa color series)"
                                            class="genre-mega-search w-full rounded-md border border-bd-default bg-bgc-layer2 py-2 pl-8 pr-3 text-base outline-none focus:ring-2 focus:ring-primary/40 md:text-sm"
                                            autocomplete="off" data-genre-mega-search aria-label="Lọc thể loại">
                                    </label>
                                    <button type="button"
                                        class="shrink-0 inline-flex h-8 items-center gap-1 rounded-md border border-bd-default bg-bgc-layer2 px-2 text-xs font-bold hover:bg-bgc-layer3"
                                        aria-label="Chuyển đổi ngôn ngữ">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="h-3.5 w-3.5" aria-hidden="true">
                                            <path d="m5 8 6 6"></path>
                                            <path d="m4 14 6-6 2-3"></path>
                                            <path d="M2 5h12"></path>
                                            <path d="M7 2h1"></path>
                                            <path d="m22 22-5-10-5 10"></path>
                                            <path d="M14 18h6"></path>
                                        </svg>
                                        <span>VI</span>
                                    </button>
                                    <button type="button"
                                        class="shrink-0 inline-flex h-8 w-8 items-center justify-center rounded-md border border-bd-default bg-bgc-layer2 hover:bg-bgc-layer3"
                                        aria-label="Đóng" data-nav-dropdown-close>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="h-4 w-4" aria-hidden="true">
                                            <path d="M18 6 6 18"></path>
                                            <path d="m6 6 12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex min-h-0 flex-1 flex-col overflow-y-auto pr-2 pb-2"
                                    data-genre-mega-scroll>
                                    <div class="space-y-4" data-genre-mega-list>
                                        @forelse ($genresByLetter as $letter => $genres)
                                            <div data-genre-letter-group>
                                                <div class="mb-2 flex items-center gap-2">
                                                    <div class="text-sm font-bold text-txt-focus">{{ $letter }}
                                                    </div>
                                                    <div class="h-px flex-1 bg-bd-default/60"></div>
                                                </div>
                                                <div class="grid grid-cols-3 gap-x-4 gap-y-2">
                                                    @foreach ($genres as $genre)
                                                        <a href="{{ $genre->getUrl() }}"
                                                            class="genre-mega-link block break-inside-avoid py-1 touch-manipulation [touch-action:manipulation]"
                                                            data-genre-search-text="{{ mb_strtolower($genre->name) }}"
                                                            @if (filled($genre->description)) title="{{ $genre->description }}" @endif>
                                                            <div
                                                                class="text-txt-primary hover:text-txt-focus text-xs [@media(min-width:427px)]:text-[15px] sm:text-[15px] font-medium">
                                                                @php
                                                                    $gn = $genre->name;
                                                                    $first = mb_substr($gn, 0, 1, 'UTF-8');
                                                                    $rest = mb_substr($gn, 1, null, 'UTF-8');
                                                                @endphp
                                                                <span
                                                                    class="font-bold">{{ $first }}</span>{{ $rest }}
                                                            </div>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-sm text-txt-secondary">Chưa có thể loại.</p>
                                        @endforelse
                                    </div>
                                </div>
                            @elseif ($menuChildrenCount > 0)
                                <div class="mb-2 flex items-center justify-end">
                                    <button type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-bd-default bg-bgc-layer2 hover:bg-bgc-layer3"
                                        aria-label="Đóng" data-nav-dropdown-close>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" class="h-4 w-4" aria-hidden="true">
                                            <path d="M18 6 6 18"></path>
                                            <path d="m6 6 12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <ul class="max-h-[60vh] min-w-[200px] space-y-1 overflow-y-auto py-1">
                                    @foreach ($menuChildren as $child)
                                        <li>
                                            <a href="{{ $child->url() }}"
                                                class="block rounded-md px-3 py-2 text-sm font-medium text-txt-primary hover:bg-bgc-layer2">
                                                {{ $child->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @else
                    @if ($item->name == 'Random')
                        <a href="{{ $item->url() }}"
                            class="inline-flex items-center justify-center px-2 py-1 rounded-md touch-manipulation text-sm leading-tight font-semibold whitespace-nowrap text-center text-txt-primary text-outline-purple-thin shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-shuffle h-4 w-4 mr-1" aria-hidden="true">
                                <path d="m18 14 4 4-4 4"></path>
                                <path d="m18 2 4 4-4 4"></path>
                                <path d="M2 18h1.973a4 4 0 0 0 3.3-1.7l5.454-8.6a4 4 0 0 1 3.3-1.7H22"></path>
                                <path d="M2 6h1.972a4 4 0 0 1 3.6 2.2"></path>
                                <path d="M22 18h-6.041a4 4 0 0 1-3.3-1.8l-.359-.45"></path>
                            </svg>{{ $item->name }}
                        </a>
                    @elseif ($item->name != 'Triệu hồi Waifu')
                        <a href="{{ $item->url() }}"
                            class="inline-flex items-center justify-center px-2 py-1 rounded-md touch-manipulation text-sm leading-tight font-semibold whitespace-nowrap text-center text-txt-primary text-outline-purple-thin shrink-0">
                            {{ $item->name }}
                        </a>
                    @else
                        <div
                            class="inline-flex items-center justify-center px-2 py-1 rounded-md touch-manipulation text-sm leading-tight font-semibold whitespace-nowrap text-center text-txt-primary text-outline-purple-thin shrink-0">
                            <img src="{{ asset('vendor/theme-vinahentai/images/waifu-icon.png') }}"
                                alt="{{ $item->name }}"
                                class="hidden lg:inline-block h-6 pointer-events-none select-none">
                            <a href="/waifu/summon"
                                class="inline-flex items-center justify-center px-2 py-1 rounded-md touch-manipulation text-sm lg:text-[17px] leading-tight font-semibold whitespace-nowrap text-center text-txt-primary text-outline-purple shrink-0">{{ $item->name }}</a>
                        </div>
                    @endif
                @endif
            @endforeach
        </nav>
        <div class="h-[1px] w-full bg-[rgba(255,255,255,0.06)]"></div>
    </div>
    <div style="height:var(--site-header-height, var(--site-header-height-base))"></div>
    {{-- Popover menu mobile: đặt ngoài overflow-hidden của thanh header để không bị clip; fixed bám viewport --}}
    <div data-mobile-menu-popover class="fixed z-[50] hidden min-w-max" aria-hidden="true">
        <div data-side="bottom" data-align="end" data-state="closed" role="dialog" id="mobile-menu-popover"
            class="bg-bgc-layer1 w-[calc(100vw-30px)] max-w-[320px] rounded-2xl border border-white/10 px-4 py-4 text-left shadow-xl"
            tabindex="-1">
            <div class="flex flex-col gap-2">
                <a href="{{ $mobileMenuRandomUrl }}" data-mobile-menu-item="true"
                    class="flex w-full items-center justify-between rounded-xl bg-gradient-to-r from-[#8B5CF6] to-[#C084FC] px-3 py-2 text-sm font-semibold text-white shadow-[0_6px_18px_rgba(123,97,255,0.35)] transition hover:opacity-95">
                    <span>Random</span>
                </a>
                <a href="{{ route('search.advanced') }}" data-mobile-menu-item="true"
                    class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold text-txt-primary transition hover:bg-bgc-layer2">
                    <span>Tìm kiếm nâng cao</span>
                </a>
                <a href="{{ route('login') }}?redirect={{ urlencode('/user/blacklist-tags') }}"
                    data-mobile-menu-item="true"
                    class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold text-txt-primary transition hover:bg-bgc-layer2">
                    <span>Lọc thể loại không thích</span>
                </a>
                <a href="{{ route('login') }}" data-mobile-menu-item="true"
                    class="flex w-full items-center justify-between rounded-xl border border-lav-500 px-3 py-2 text-sm font-semibold text-txt-focus transition hover:bg-bgc-layer2">
                    <span>Đăng nhập</span>
                </a>
                <a href="{{ route('register') }}" data-mobile-menu-item="true"
                    class="flex w-full items-center justify-between rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] px-3 py-2 text-sm font-semibold text-black">
                    <span>Đăng ký</span>
                </a>
            </div>
        </div>
    </div>
</header>
