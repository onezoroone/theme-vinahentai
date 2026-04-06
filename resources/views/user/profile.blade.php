@extends('theme-vinahentai::layout.main')

@push('header')
<link rel="stylesheet" href="{{ asset('vendor/theme-vinahentai/css/profile.css') }}">
@endpush

@section('body')
    @include('theme-vinahentai::partials.breakthrough-effects')

    <div class="mx-auto flex w-full max-w-[968px] flex-col items-center gap-6 p-4 lg:py-8" data-profile-tabs-root>
        @if (session('status'))
            <div class="border-success-success/40 bg-success-success/10 text-success-success w-full rounded-lg border px-4 py-2 text-sm font-medium {{ session('breakthrough_effect') === 'success' ? 'breakthrough-status-pop' : '' }}"
                role="status">{{ session('status') }}</div>
        @endif
        <div class="bg-bgc-layer1 border-bd-default relative flex w-full flex-col gap-6 overflow-hidden rounded-xl border p-4 lg:flex-row lg:p-6">
            <div class="relative z-[1] flex flex-1 gap-4 lg:max-w-[514px]">
                <div class="flex flex-col items-center gap-2">
                    <div class="h-16 w-16 lg:h-24 lg:w-24 rounded-full overflow-hidden flex items-center justify-center bg-[#121826]">
                        @if ($user->avatar)
                        <img class="block h-full w-full object-cover" src="{{ $user->avatar }}" alt="{{ $user->name }}">
                        @else
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user h-10 w-10 lg:h-14 lg:w-14 text-txt-primary" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        @endif
                    </div>
                    <a class="text-xs font-semibold text-success-success" href="{{ route('user.profile-edit') }}" data-discover="true">Chỉnh sửa hồ sơ</a>
                </div>
                <div class="flex flex-1 flex-col gap-3">
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center gap-3">
                            <h1 class="text-txt-primary font-sans text-lg font-semibold lg:text-xl">{{ $user->name }} - {{ $user->level->name }} {{ $user->level_stage_label }}</h1><span class="relative inline-flex h-6 lg:h-8 overflow-visible"><img class="block h-full w-auto object-contain" src="{{ asset($user->level->image) }}" alt="User Badge" style="transform:scale(2);transform-origin:center;margin-left:-2px;margin-right:-2px">
                                <style>
                                    @media (min-width: 1024px) {
                                        .relative.inline-flex.h-6.lg\:h-8.overflow-visible img {
                                            transform: scale(1.5);
                                        }
                                    }
                                </style>
                            </span>
                        </div>
                        <p class="text-txt-secondary text-xs font-medium">Ngày đăng ký: {{ $user->created_at->format('d/m/Y') }}</p>
                    </div>
                    <div class="flex flex-1 flex-col gap-1.5">
                        <div class="flex items-center justify-between"><span class="text-txt-primary text-xs font-medium">Kinh nghiệm</span><span class="text-txt-primary text-xs font-medium">{{ $user->experience_points }}/{{ $user->level->required_experience }}</span></div>
                        <div class="bg-bgc-layer2 h-2 overflow-hidden rounded">
                            <div class="via-lav-500 h-2 rounded bg-gradient-to-r from-[#3D1351] to-[#E8B5FF]" style="width:{{ $user->level_progress_percent }}%"></div>
                        </div>
                        @if (!empty($breakthroughPreview))
                            <div class="mt-2">
                                <a href="{{ route('user.level-breakthrough') }}"
                                    class="from-[#DD94FF] to-[#D373FF] text-txt-inverse inline-flex min-h-[40px] items-center justify-center rounded-lg bg-gradient-to-b px-4 py-2 font-sans text-xs font-semibold shadow-[0px_4px_9px_rgba(196,69,255,0.25)] transition-opacity hover:opacity-90 sm:text-sm">
                                    Đột phá cấp độ
                                </a>
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-col gap-[5px]">
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-semibold text-white">Giới thiệu</h2>
                        </div>
                        <p class="text-txt-secondary text-xs leading-none font-medium">{!! $user->bio !!}</p>
                    </div>
                </div>
            </div>

            <div class="relative z-[1] border-bd-default flex flex-1 flex-col gap-3 rounded-xl border p-2">
                <div class="flex">
                    <div class="flex flex-1 flex-col items-center">
                        <div class="text-txt-primary text-base font-semibold">{{ $user->current_level }}</div>
                        <div class="text-txt-secondary text-xs font-medium">Cấp bậc</div>
                    </div>
                    <div class="flex flex-1 flex-col items-center">
                        <div class="flex items-center gap-1.5">
                            <img class="h-5 w-6" src="{{ asset('vendor/theme-vinahentai/images/gold-icon.png') }}" alt="Dâm Ngọc">
                            <div class="text-txt-primary text-base font-semibold">{{ $user->points }}</div>
                            {{-- Hover mở popover (Floating UI trong main.js); aria-controls trỏ tới panel ngoài overflow-hidden --}}
                            <button type="button"
                                id="profile-points-help-trigger"
                                data-profile-points-help-trigger
                                class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-white/35 bg-white/5 text-[10px] font-bold leading-none text-white/80 transition-colors hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C084FC] sm:h-5 sm:w-5 sm:text-xs !h-auto !w-auto !rounded-md !px-1.5 !py-0.5 !border-red-500/40 !bg-red-500/10 !text-red-500"
                                aria-label="Thông tin về Dâm Ngọc"
                                aria-haspopup="dialog"
                                aria-expanded="false"
                                aria-controls="profile-points-help-dialog"
                                data-state="closed">
                                <span class="text-red-500 text-[9px] font-bold leading-none whitespace-nowrap">hướng dẫn mới</span>
                            </button>
                        </div>
                        <div class="text-txt-secondary text-xs font-medium">Dâm Ngọc</div>
                    </div>
                </div>

                <div class="flex">
                    <div class="flex flex-1 flex-col items-center">
                        <div class="text-txt-primary text-base font-semibold">{{ $user->readingHistories->count() }}</div>
                        <div class="text-txt-secondary text-xs font-medium">Chap đã đọc</div>
                    </div>
                    <div class="flex flex-1 flex-col items-center">
                        <div class="text-txt-primary text-base font-semibold">{{ $user->waifus->count() }}</div>
                        <div class="text-txt-secondary text-xs font-medium">Số Waifu</div>
                    </div>
                </div>

                <div class="flex">
                    <div class="flex flex-1 flex-col items-center">
                        <div class="text-txt-primary text-base font-semibold">{{ $user->mangas->count() }}</div>
                        <div class="text-txt-secondary text-xs font-medium">Truyện đã đăng</div>
                    </div>
                    <div class="flex flex-1 flex-col items-center">
                        <div class="text-txt-primary text-base font-semibold">{{ count(explode(',', $user->followed_manga_ids ?? '') ?? []) }}</div>
                        <div class="text-txt-secondary text-xs font-medium">Đang theo dõi</div>
                    </div>
                </div>
            </div>
        </div>

        <div data-profile-points-help-popover class="fixed z-[99999] hidden w-[min(92vw,420px)]" aria-hidden="true">
            <div id="profile-points-help-dialog"
                data-side="bottom"
                data-align="center"
                role="dialog"
                aria-labelledby="profile-points-help-trigger"
                tabindex="-1"
                data-state="closed"
                class="bg-bgc-layer1 border-bd-default relative z-[99999] rounded-xl border p-3 shadow-lg will-change-[transform,opacity]">
                <div class="text-txt-secondary text-xs leading-relaxed font-medium whitespace-pre-line">1. Cách nhận Dâm Ngọc

Dâm Ngọc rơi ngẫu nhiên trong quá trình đọc truyện khi dâm khí hội tụ.
Like truyện, comment đầu tiên trong ngày sẽ +1 dâm ngọc.
Hoặc có thể nhận Dâm Ngọc khi: Là người đầu tiên Báo Lỗi hợp lệ, tham gia event, boost server Discord VinaHentai nhận 50 Dâm Ngọc cho mỗi boost.

2. Hành vi không được tính

❌ Spam chương, cày chap,... sẽ không làm rơi Dâm Ngọc.

3. Mục đích sử dụng

Dâm Ngọc triệu hồi Waifu chỉ mang tính hấp dẫn bổ sung, dùng để tăng trải nghiệm bên cạnh việc đọc truyện.

🚫 Không nên tập trung quá mức vào việc farm Dâm Ngọc.

Trải nghiệm đọc truyện vẫn là mục tiêu chính.</div>
                <span class="pointer-events-none absolute left-1/2 top-0 -translate-x-1/2 -translate-y-full" aria-hidden="true">
                    <svg class="fill-bgc-layer1" width="20" height="8" viewBox="0 0 30 10" preserveAspectRatio="none" style="display:block">
                        <polygon points="0,10 30,10 15,0"></polygon>
                    </svg>
                </span>
            </div>
        </div>

        <div class="w-full">
            <div dir="ltr" data-orientation="horizontal" class="w-full">
                <div role="tablist" aria-orientation="horizontal" class="mb-4 grid w-full grid-cols-3 gap-2 rounded-2xl border border-bd-default bg-bgc-layer2 p-2 sm:flex sm:items-center sm:gap-2 sm:rounded-xl sm:p-1" tabindex="0" data-orientation="horizontal" style="outline:none"><button type="button" role="tab" aria-selected="true" aria-controls="radix-«Rlr5»-content-overview" data-state="active" id="radix-«Rlr5»-trigger-overview" data-profile-primary-tab="overview" class="text-txt-secondary hover:text-txt-primary data-[state=active]:text-txt-primary data-[state=active]:border-lav-400 data-[state=active]:bg-lav-500/18 data-[state=active]:shadow-[0_10px_30px_rgba(211,115,255,0.18)] flex min-h-12 items-center justify-center gap-2 rounded-2xl border border-white/8 bg-white/4 px-3 py-3 text-sm font-semibold whitespace-nowrap transition-all duration-200 [@media(max-width:359px)]:min-h-11 [@media(max-width:359px)]:gap-1.5 [@media(max-width:359px)]:px-2 [@media(max-width:359px)]:text-[11px] sm:min-h-0 sm:rounded-lg sm:border-0 sm:bg-transparent sm:px-3 sm:py-2 sm:data-[state=active]:bg-gradient-to-b sm:data-[state=active]:from-[#DD94FF] sm:data-[state=active]:to-[#D373FF] sm:data-[state=active]:text-black sm:data-[state=active]:shadow-none" tabindex="-1" data-orientation="horizontal" data-radix-collection-item=""><span class="flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star h-4 w-4" aria-hidden="true">
                                <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                            </svg>Tổng quan</span></button><button type="button" role="tab" aria-selected="false" aria-controls="radix-«Rlr5»-content-waifu" data-state="inactive" id="radix-«Rlr5»-trigger-waifu" data-profile-primary-tab="waifu" class="text-txt-secondary hover:text-txt-primary data-[state=active]:text-txt-primary data-[state=active]:border-lav-400 data-[state=active]:bg-lav-500/18 data-[state=active]:shadow-[0_10px_30px_rgba(211,115,255,0.18)] flex min-h-12 items-center justify-center gap-2 rounded-2xl border border-white/8 bg-white/4 px-3 py-3 text-sm font-semibold whitespace-nowrap transition-all duration-200 [@media(max-width:359px)]:min-h-11 [@media(max-width:359px)]:gap-1.5 [@media(max-width:359px)]:px-2 [@media(max-width:359px)]:text-[11px] sm:min-h-0 sm:rounded-lg sm:border-0 sm:bg-transparent sm:px-3 sm:py-2 sm:data-[state=active]:bg-gradient-to-b sm:data-[state=active]:from-[#DD94FF] sm:data-[state=active]:to-[#D373FF] sm:data-[state=active]:text-black sm:data-[state=active]:shadow-none" tabindex="-1" data-orientation="horizontal" data-radix-collection-item=""><span class="flex items-center gap-2"><img src="{{ asset('vendor/theme-vinahentai/images/multi-star.svg') }}" alt="Icon sao" class="h-4 w-4">Waifu</span></button><button type="button" role="tab" aria-selected="false" aria-controls="radix-«Rlr5»-content-stories" data-state="inactive" id="radix-«Rlr5»-trigger-stories" data-profile-primary-tab="stories" class="text-txt-secondary hover:text-txt-primary data-[state=active]:text-txt-primary data-[state=active]:border-lav-400 data-[state=active]:bg-lav-500/18 data-[state=active]:shadow-[0_10px_30px_rgba(211,115,255,0.18)] flex min-h-12 items-center justify-center gap-2 rounded-2xl border border-white/8 bg-white/4 px-3 py-3 text-sm font-semibold whitespace-nowrap transition-all duration-200 [@media(max-width:359px)]:min-h-11 [@media(max-width:359px)]:gap-1.5 [@media(max-width:359px)]:px-2 [@media(max-width:359px)]:text-[11px] sm:min-h-0 sm:rounded-lg sm:border-0 sm:bg-transparent sm:px-3 sm:py-2 sm:data-[state=active]:bg-gradient-to-b sm:data-[state=active]:from-[#DD94FF] sm:data-[state=active]:to-[#D373FF] sm:data-[state=active]:text-black sm:data-[state=active]:shadow-none" tabindex="-1" data-orientation="horizontal" data-radix-collection-item=""><span class="flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open h-4 w-4" aria-hidden="true">
                                <path d="M12 7v14"></path>
                                <path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"></path>
                            </svg>Theo dõi</span></button><button type="button" role="tab" aria-selected="false" aria-controls="radix-«Rlr5»-content-comments" data-state="inactive" id="radix-«Rlr5»-trigger-comments" data-profile-primary-tab="comments" class="text-txt-secondary hover:text-txt-primary data-[state=active]:text-txt-primary data-[state=active]:border-lav-400 data-[state=active]:bg-lav-500/18 data-[state=active]:shadow-[0_10px_30px_rgba(211,115,255,0.18)] flex min-h-12 items-center justify-center gap-2 rounded-2xl border border-white/8 bg-white/4 px-3 py-3 text-sm font-semibold whitespace-nowrap transition-all duration-200 [@media(max-width:359px)]:min-h-11 [@media(max-width:359px)]:gap-1.5 [@media(max-width:359px)]:px-2 [@media(max-width:359px)]:text-[11px] sm:min-h-0 sm:rounded-lg sm:border-0 sm:bg-transparent sm:px-3 sm:py-2 sm:data-[state=active]:bg-gradient-to-b sm:data-[state=active]:from-[#DD94FF] sm:data-[state=active]:to-[#D373FF] sm:data-[state=active]:text-black sm:data-[state=active]:shadow-none" tabindex="-1" data-orientation="horizontal" data-radix-collection-item=""><span class="flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-circle h-4 w-4" aria-hidden="true">
                                <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"></path>
                            </svg>Bình luận</span></button><button type="button" role="tab" aria-selected="false" aria-controls="radix-«Rlr5»-content-titles" data-state="inactive" id="radix-«Rlr5»-trigger-titles" data-profile-primary-tab="titles" class="text-txt-secondary hover:text-txt-primary data-[state=active]:text-txt-primary data-[state=active]:border-lav-400 data-[state=active]:bg-lav-500/18 data-[state=active]:shadow-[0_10px_30px_rgba(211,115,255,0.18)] flex min-h-12 items-center justify-center gap-2 rounded-2xl border border-white/8 bg-white/4 px-3 py-3 text-sm font-semibold whitespace-nowrap transition-all duration-200 [@media(max-width:359px)]:min-h-11 [@media(max-width:359px)]:gap-1.5 [@media(max-width:359px)]:px-2 [@media(max-width:359px)]:text-[11px] sm:min-h-0 sm:rounded-lg sm:border-0 sm:bg-transparent sm:px-3 sm:py-2 sm:data-[state=active]:bg-gradient-to-b sm:data-[state=active]:from-[#DD94FF] sm:data-[state=active]:to-[#D373FF] sm:data-[state=active]:text-black sm:data-[state=active]:shadow-none" tabindex="-1" data-orientation="horizontal" data-radix-collection-item=""><span class="flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-award h-4 w-4" aria-hidden="true">
                                <path d="m15.477 12.89 1.515 8.526a.5.5 0 0 1-.81.47l-3.58-2.687a1 1 0 0 0-1.197 0l-3.586 2.686a.5.5 0 0 1-.81-.469l1.514-8.526"></path>
                                <circle cx="12" cy="8" r="6"></circle>
                            </svg>Danh hiệu</span></button></div>
                <div data-state="active" data-orientation="horizontal" role="tabpanel" aria-labelledby="radix-«Rlr5»-trigger-overview" id="radix-«Rlr5»-content-overview" data-profile-primary-panel="overview" tabindex="0" class="w-full" style="animation-duration:0s">
                    <div class="flex w-full flex-col gap-6">
                        {{-- Khung giống layout gốc: icon multi-star + tiêu đề + lưới 3 cột --}}
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-3">
                                <img src="{{ asset('vendor/theme-vinahentai/images/multi-star.svg') }}" alt="" class="h-8 w-8 shrink-0 sm:h-9 sm:w-9" width="36" height="36">
                                <h2 class="text-xl font-semibold text-white uppercase">Tổng quan tài khoản</h2>
                            </div>
                            <a href="{{ route('user.profile-edit') }}" class="text-success-success text-xs font-semibold hover:underline sm:text-sm" data-discover="true">Chỉnh sửa hồ sơ</a>
                        </div>

                        <div class="grid w-full grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 lg:gap-6">
                            <div class="bg-bgc-layer2 border-bd-default flex flex-col gap-2 rounded-xl border p-4">
                                <span class="text-txt-secondary text-xs font-medium uppercase tracking-wide">Cấp &amp; kinh nghiệm</span>
                                <p class="text-txt-primary text-2xl font-semibold tabular-nums">{{ $user->current_level }}</p>
                                <p class="text-txt-secondary text-sm font-medium">{{ $user->level->name }}@if($user->level_stage_label) · {{ $user->level_stage_label }}@endif</p>
                                <p class="text-txt-secondary text-xs leading-relaxed">{{ $user->experience_points }}/{{ $user->level->required_experience }} EXP</p>
                            </div>
                            <div class="bg-bgc-layer2 border-bd-default flex flex-col gap-2 rounded-xl border p-4">
                                <span class="text-txt-secondary text-xs font-medium uppercase tracking-wide">Dâm Ngọc</span>
                                <p class="text-txt-primary flex items-center gap-2 text-2xl font-semibold tabular-nums">
                                    <img class="h-7 w-8" src="{{ asset('vendor/theme-vinahentai/images/gold-icon.png') }}" alt="">
                                    {{ $user->points }}
                                </p>
                                <p class="text-txt-secondary text-xs leading-relaxed">Đọc chương (0–3), like / bình luận đầu ngày (+1). Xem nút “hướng dẫn mới” bên trên.</p>
                            </div>
                            <div class="bg-bgc-layer2 border-bd-default flex flex-col gap-2 rounded-xl border p-4 sm:col-span-2 lg:col-span-1">
                                <span class="text-txt-secondary text-xs font-medium uppercase tracking-wide">Hoạt động</span>
                                <p class="text-txt-primary text-2xl font-semibold tabular-nums">{{ $user->readingHistories->count() }} <span class="text-txt-secondary text-base font-medium">chương đã đọc</span></p>
                                @isset($waifuGrandTotal, $waifuOwnedTotal, $waifuCollectPercent)
                                    <p class="text-txt-secondary text-sm font-medium">{{ $user->waifus->count() }} waifu · Bộ sưu tập {{ $waifuCollectPercent }}% ({{ $waifuOwnedTotal }}/{{ $waifuGrandTotal }})</p>
                                @else
                                    <p class="text-txt-secondary text-sm font-medium">{{ $user->waifus->count() }} waifu · {{ $user->mangas->count() }} truyện đã đăng</p>
                                @endisset
                            </div>
                        </div>

                        <div class="bg-bgc-layer1 border-bd-default rounded-xl border p-4 sm:p-5">
                            <p class="text-txt-primary text-sm font-medium leading-relaxed">
                                Xin chào <span class="text-lav-300 font-semibold">{{ $user->name }}</span> — chúc bạn đọc truyện vui vẻ. Tab <strong class="text-white">Waifu</strong> là nơi xem bộ sưu tập chi tiết; tab <strong class="text-white">Theo dõi</strong> gom truyện đang theo và lịch sử; <strong class="text-white">Bình luận</strong> liệt kê các bài bạn đã gửi.
                            </p>
                        </div>
                    </div>
                </div>
                <div data-state="inactive" data-orientation="horizontal" role="tabpanel" aria-labelledby="radix-«Rlr5»-trigger-waifu" hidden="" id="radix-«Rlr5»-content-waifu" data-profile-primary-panel="waifu" tabindex="0" class="w-full">
                    @include('theme-vinahentai::user.partials.profile-waifu-tab')
                </div>
                <div data-state="inactive" data-orientation="horizontal" role="tabpanel" aria-labelledby="radix-«Rlr5»-trigger-stories" hidden="" id="radix-«Rlr5»-content-stories" data-profile-primary-panel="stories" tabindex="0" class="w-full">
                    <div class="w-full"
                        data-profile-library
                        data-url-followed="{{ route('api.user.library.followed-mangas') }}"
                        data-url-history="{{ route('api.user.library.reading-history') }}"
                        data-url-translators="{{ route('api.user.library.followed-translators') }}"
                        data-url-authors="{{ route('api.user.library.followed-authors') }}">
                        <div dir="ltr" data-orientation="horizontal" class="w-full">
                            <div role="tablist" aria-orientation="horizontal" class="grid grid-cols-2 gap-2 md:flex md:gap-0 md:border-b md:border-bd-default" tabindex="0" data-orientation="horizontal" style="outline:none" data-profile-subtab-list>
                                <button type="button" role="tab" aria-selected="true" data-state="active" data-profile-subtab="following" class="text-txt-secondary hover:text-txt-primary data-[state=active]:text-txt-primary data-[state=active]:border-lav-400 data-[state=active]:bg-lav-500/18 data-[state=active]:shadow-[0_10px_30px_rgba(211,115,255,0.16)] flex min-h-12 items-center justify-center gap-2 rounded-2xl border border-white/8 bg-white/4 px-3 py-3 text-sm font-medium whitespace-nowrap transition-all duration-200 [@media(max-width:359px)]:min-h-11 [@media(max-width:359px)]:gap-1.5 [@media(max-width:359px)]:px-2 [@media(max-width:359px)]:text-[11px] md:min-h-0 md:flex-1 md:rounded-none md:border-0 md:border-b md:border-transparent md:bg-transparent md:px-3 md:py-3 md:text-base md:data-[state=active]:border-lav-500 md:data-[state=active]:bg-transparent md:data-[state=active]:shadow-none md:data-[state=active]:font-semibold">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2Z"></path><path d="m9 10 2 2 4-4"></path></svg>
                                    Truyện theo dõi
                                </button>
                                <button type="button" role="tab" aria-selected="false" data-state="inactive" data-profile-subtab="recent-read" class="text-txt-secondary hover:text-txt-primary data-[state=active]:text-txt-primary data-[state=active]:border-lav-400 data-[state=active]:bg-lav-500/18 data-[state=active]:shadow-[0_10px_30px_rgba(211,115,255,0.16)] flex min-h-12 items-center justify-center gap-2 rounded-2xl border border-white/8 bg-white/4 px-3 py-3 text-sm font-medium whitespace-nowrap transition-all duration-200 [@media(max-width:359px)]:min-h-11 [@media(max-width:359px)]:gap-1.5 [@media(max-width:359px)]:px-2 [@media(max-width:359px)]:text-[11px] md:min-h-0 md:flex-1 md:rounded-none md:border-0 md:border-b md:border-transparent md:bg-transparent md:px-3 md:py-3 md:text-base md:data-[state=active]:border-lav-500 md:data-[state=active]:bg-transparent md:data-[state=active]:shadow-none md:data-[state=active]:font-semibold">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path><path d="M12 7v5l4 2"></path></svg>
                                    Lịch sử
                                </button>
                                <button type="button" role="tab" aria-selected="false" data-state="inactive" data-profile-subtab="translators" class="text-txt-secondary hover:text-txt-primary data-[state=active]:text-txt-primary data-[state=active]:border-lav-400 data-[state=active]:bg-lav-500/18 data-[state=active]:shadow-[0_10px_30px_rgba(211,115,255,0.16)] flex min-h-12 items-center justify-center gap-2 rounded-2xl border border-white/8 bg-white/4 px-3 py-3 text-sm font-medium whitespace-nowrap transition-all duration-200 [@media(max-width:359px)]:min-h-11 [@media(max-width:359px)]:gap-1.5 [@media(max-width:359px)]:px-2 [@media(max-width:359px)]:text-[11px] md:min-h-0 md:flex-1 md:rounded-none md:border-0 md:border-b md:border-transparent md:bg-transparent md:px-3 md:py-3 md:text-base md:data-[state=active]:border-lav-500 md:data-[state=active]:bg-transparent md:data-[state=active]:shadow-none md:data-[state=active]:font-semibold">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path d="m5 8 6 6"></path><path d="m4 14 6-6 2-3"></path><path d="M2 5h12"></path><path d="M7 2h1"></path><path d="m22 22-5-10-5 10"></path><path d="M14 18h6"></path></svg>
                                    Dịch giả
                                </button>
                                <button type="button" role="tab" aria-selected="false" data-state="inactive" data-profile-subtab="authors" class="text-txt-secondary hover:text-txt-primary data-[state=active]:text-txt-primary data-[state=active]:border-lav-400 data-[state=active]:bg-lav-500/18 data-[state=active]:shadow-[0_10px_30px_rgba(211,115,255,0.16)] flex min-h-12 items-center justify-center gap-2 rounded-2xl border border-white/8 bg-white/4 px-3 py-3 text-sm font-medium whitespace-nowrap transition-all duration-200 [@media(max-width:359px)]:min-h-11 [@media(max-width:359px)]:gap-1.5 [@media(max-width:359px)]:px-2 [@media(max-width:359px)]:text-[11px] md:min-h-0 md:flex-1 md:rounded-none md:border-0 md:border-b md:border-transparent md:bg-transparent md:px-3 md:py-3 md:text-base md:data-[state=active]:border-lav-500 md:data-[state=active]:bg-transparent md:data-[state=active]:shadow-none md:data-[state=active]:font-semibold">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path d="M15.707 21.293a1 1 0 0 1-1.414 0l-1.586-1.586a1 1 0 0 1 0-1.414l5.586-5.586a1 1 0 0 1 1.414 0l1.586 1.586a1 1 0 0 1 0 1.414z"></path><path d="m18 13-1.375-6.874a1 1 0 0 0-.746-.776L3.235 2.028a1 1 0 0 0-1.207 1.207L5.35 15.879a1 1 0 0 0 .776.746L13 18"></path><path d="m2.3 2.3 7.286 7.286"></path><circle cx="11" cy="11" r="2"></circle></svg>
                                    Tác giả
                                </button>
                            </div>
                            <div data-state="active" role="tabpanel" id="saved-stories" tabindex="0" class="mt-4" data-profile-subpanel="following">
                                <div data-profile-library-pane="following" class="flex flex-col items-start gap-4">
                                    <p class="text-txt-secondary w-full py-6 text-center text-sm font-medium">Đang tải…</p>
                                </div>
                            </div>
                            <div data-state="inactive" role="tabpanel" id="reading-history" hidden tabindex="0" class="mt-4" data-profile-subpanel="recent-read">
                                <div data-profile-library-pane="recent-read" class="flex flex-col items-start gap-4">
                                    <p class="text-txt-secondary w-full py-6 text-center text-sm font-medium">Đang tải…</p>
                                </div>
                            </div>
                            <div data-state="inactive" role="tabpanel" id="saved-translators" hidden tabindex="0" class="mt-4" data-profile-subpanel="translators">
                                <div data-profile-library-pane="translators" class="flex flex-col items-start gap-4">
                                    <p class="text-txt-secondary w-full py-6 text-center text-sm font-medium">Đang tải…</p>
                                </div>
                            </div>
                            <div data-state="inactive" role="tabpanel" id="saved-authors" hidden tabindex="0" class="mt-4" data-profile-subpanel="authors">
                                <div data-profile-library-pane="authors" class="flex flex-col items-start gap-4">
                                    <p class="text-txt-secondary w-full py-6 text-center text-sm font-medium">Đang tải…</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div data-state="inactive" data-orientation="horizontal" role="tabpanel" aria-labelledby="radix-«Rlr5»-trigger-comments" hidden="" id="radix-«Rlr5»-content-comments" data-profile-primary-panel="comments" tabindex="0" class="w-full">
                    <div class="w-full" data-profile-comments-root data-url-my-comments="{{ route('api.user.library.my-comments') }}">
                        <div class="inline-flex w-full max-w-[968px] flex-col items-start justify-start gap-4">
                            <div class="inline-flex items-center justify-start gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-circle text-lav-500 h-5 w-5 fill-current" aria-hidden="true">
                                    <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"></path>
                                </svg>
                                <div class="text-txt-primary justify-center font-sans text-xl leading-7 font-semibold uppercase">bình luận đã đăng</div>
                            </div>
                            <div data-profile-comments-pane class="flex flex-col items-start justify-start gap-4 self-stretch">
                                <p class="text-txt-secondary w-full py-6 text-center text-sm font-medium">Đang tải…</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div data-state="inactive" data-orientation="horizontal" role="tabpanel" aria-labelledby="radix-«Rlr5»-trigger-titles" hidden="" id="radix-«Rlr5»-content-titles" data-profile-primary-panel="titles" tabindex="0" class="w-full"></div>
            </div>
        </div>
    </div>
@endsection
