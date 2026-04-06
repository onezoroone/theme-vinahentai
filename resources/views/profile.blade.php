@extends('theme-vinahentai::layout.main')

@push('header')
<link rel="stylesheet" href="{{ asset('vendor/theme-vinahentai/css/profile.css') }}">
@endpush

@section('body')
    <div class="mx-auto flex w-full max-w-[968px] flex-col items-center gap-6 p-4 lg:py-8">
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
                        <div class="text-txt-primary text-base font-semibold">{{ (int) ($user->mangas_published_count ?? 0) }}</div>
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

        <div class="flex w-full flex-col gap-6">
            <div class="flex items-center justify-between"><div class="flex items-center gap-3"><img src="{{ asset('vendor/theme-vinahentai/images/multi-star.svg') }}" alt="star"><h2 class="text-xl font-semibold text-white uppercase">bộ sưu tập waifu</h2></div></div>

            <div class="grid w-full grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach ($waifus as $userWaifu)
                @if ($userWaifu->waifu)
                <div class="aspect-[2/3] w-full overflow-hidden rounded-lg transition-all duration-300 hover:scale-105 hover:shadow-[0_0_12px_rgba(146,53,190,0.6)] hover:ring-2 hover:ring-lav-500"><img src="{{ asset($userWaifu->waifu->image) }}" alt="Waifu {{ $userWaifu->waifu->name }}" class="h-full w-full object-cover"></div>
                @endif
                @endforeach
            </div>
        </div>

        <div class="flex w-full flex-col gap-4">
            <div class="flex items-center gap-3"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open text-lav-500 h-5 w-5" aria-hidden="true"><path d="M12 7v14"></path><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"></path></svg><h2 class="text-xl font-semibold text-white uppercase">TRUYỆN ĐÃ ĐĂNG</h2></div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-4">
                @foreach ($mangas as $manga)
                @include('theme-vinahentai::components.item', ['manga' => $manga])
                @endforeach
            </div>
        </div>
    </div>
@endsection
