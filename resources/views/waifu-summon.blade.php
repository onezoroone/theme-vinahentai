@extends('theme-vinahentai::layout.main')

@push('header')
    <link rel="stylesheet" href="{{ asset('vendor/theme-vinahentai/css/waifu-summon.css') }}">
@endpush

@section('body')
    @php
        $waifuSummonCardMobile = asset('vendor/theme-vinahentai/images/waifu/card.mobile.webp');
        $waifuSummonCardWebp = asset('vendor/theme-vinahentai/images/waifu/card.webp');
        $waifuSummonCardPng = asset('vendor/theme-vinahentai/images/waifu/card.png');
    @endphp
    {{-- Autoplay có tiếng bị trình duyệt chặn — phát sau lần chạm/click đầu trên trang (xem main.entry.js). --}}
    <audio data-waifu-summon-bgm data-volume="0.45" class="pointer-events-none fixed h-px w-px overflow-hidden opacity-0"
        loop preload="auto" playsinline>
        <source src="{{ asset('vendor/theme-vinahentai/audio/music-nen.mp3') }}" type="audio/mpeg">
    </audio>
    <div class="relative w-full" data-waifu-summon-page
        @auth
        data-waifu-summon-perform-url="{{ route('waifu.summon.perform') }}"
        data-waifu-summon-milestone-claim-url="{{ route('waifu.summon.milestone-claim') }}"
        data-waifu-summon-rewards-history-url="{{ route('waifu.summon.rewards-history') }}" @endauth
        data-waifu-summon-login-url="{{ route('login') }}">
        <div data-rht-toaster="" style="position: fixed; z-index: 9999; inset: 16px; pointer-events: none;"></div>
        <div class="flex w-full items-center justify-between gap-2 px-3 py-2 sm:gap-4">
            <div class="flex items-center gap-2 sm:gap-4"><a href="{{ route('waifu.summon') }}" aria-current="page"
                    class="bg-btn-primary text-txt-inverse rounded-[32px] px-3 py-1.5 text-center text-xs leading-normal font-medium backdrop-blur-[3.4px] sm:text-base touch-manipulation [touch-action:manipulation]">Banner
                    thường</a><a href="{{ route('leaderboard.waifu') }}"
                    class="bg-bgc-layer-semi-neutral text-txt-primary rounded-[32px] px-3 py-1.5 text-center text-xs leading-normal font-medium backdrop-blur-[3.4px] sm:text-base touch-manipulation [touch-action:manipulation]">Bảng
                    xếp hạng</a></div>
            <div class="flex items-center gap-2 sm:gap-3">
                <div
                    class="bg-bgc-layer1 flex items-center justify-center rounded-[32px] border border-white/70 px-3 py-1.5">
                    <div class="flex items-center gap-2"><img class="h-4 w-4 sm:h-5 sm:w-6" alt="Gem"
                            src="{{ asset('vendor/theme-vinahentai/images/gold-icon.png') }}">
                        <div class="text-txt-primary font-sans text-xs leading-normal font-semibold sm:text-base"
                            data-waifu-summon-points>{{ $userWaifuPoints }}</div><button type="button"
                            aria-label="Thông tin về Dâm Ngọc (sắp có)"
                            class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-white/35 bg-white/5 text-[10px] font-bold leading-none text-white/80 transition-colors hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C084FC] sm:h-5 sm:w-5 sm:text-xs">i</button>
                    </div>
                </div><button type="button" data-waifu-summon-rewards-trigger
                    class="rounded-[32px] border border-white/70 bg-black/55 px-3 py-1.5 text-xs text-white shadow-[0_6px_24px_rgba(0,0,0,.35)] backdrop-blur-sm transition-colors hover:bg-black/70 sm:px-5 sm:py-3 sm:text-base"
                    aria-haspopup="dialog" aria-expanded="false" aria-controls="waifu-summon-rewards-dialog"
                    data-state="closed">
                    <span class="font-semibold">Nhận Thưởng</span>
                </button><button type="button" data-waifu-summon-guide-trigger
                    class="rounded-[32px] border border-white/70 bg-black/55 px-3 py-1.5 text-xs text-white shadow-[0_6px_24px_rgba(0,0,0,.35)] backdrop-blur-sm transition-colors hover:bg-black/70 sm:px-5 sm:py-3 sm:text-base"
                    aria-haspopup="dialog" aria-expanded="false" aria-controls="waifu-summon-guide-dialog"
                    data-state="closed">
                    <span class="font-semibold">Hướng dẫn</span>
                </button>
            </div>
        </div>
        <div>
            <div class="relative w-full"><img alt="01/10/2025 - 01102026" class="w-full object-cover"
                    src="{{ asset('vendor/theme-vinahentai/images/iomaya_1_4-1757344513567-dd6cb1db-1760885967764-f41c721f.webp') }}">
            </div>
            <div class="absolute right-8 bottom-8 flex w-auto min-w-[500px] flex-col items-start gap-3">
                <div class="flex w-full items-center justify-center gap-4"></div>
                <div class="flex w-full items-center justify-start gap-4"><button type="button" data-waifu-summon-start
                        data-waifu-summon-type="single"
                        class="bg-bgc-layer1 hover:bg-bgc-layer2 flex flex-1 cursor-pointer items-center justify-center gap-2.5 rounded-[32px] border border-white px-5 py-3 transition-colors">
                        <div class="text-txt-primary justify-center font-sans text-base leading-normal font-bold">TRIỆU HỒI
                        </div>
                        <div class="flex items-center justify-start gap-1.5"><img class="h-5 w-6" alt="Gem"
                                src="{{ asset('vendor/theme-vinahentai/images/gold-icon.png') }}">
                            <div
                                class="text-txt-primary justify-center text-center font-sans text-base leading-normal font-semibold">
                                1</div>
                        </div>
                    </button><button type="button" data-waifu-summon-start data-waifu-summon-type="ten"
                        class="flex flex-1 cursor-pointer items-center justify-center gap-2.5 rounded-[32px] bg-white px-5 py-3 transition-colors hover:bg-gray-100">
                        <div class="text-bgc-layer1 justify-center font-sans text-base leading-normal font-bold">TRIỆU HỒI
                            X10</div>
                        <div class="flex items-center justify-start gap-1.5"><img class="h-5 w-6" alt="Gem"
                                src="{{ asset('vendor/theme-vinahentai/images/gold-icon.png') }}">
                            <div
                                class="text-bgc-layer1 justify-center text-center font-sans text-base leading-normal font-semibold">
                                9</div>
                        </div>
                    </button></div>
                <div class="mt-1 flex items-center gap-2 text-xs text-white"><button type="button"
                        data-waifu-summon-skip-video-toggle
                        class="flex cursor-pointer items-center gap-2 touch-manipulation [touch-action:manipulation]"
                        aria-pressed="false"
                        aria-label="Bật để sau triệu hồi bỏ qua video, vào thẻ ngay"><span>Bỏ qua video</span><span
                            data-waifu-summon-skip-video-track class="waifu-summon-skip-track" aria-hidden="true"><span
                                data-waifu-summon-skip-video-knob class="waifu-summon-skip-knob"></span></span></button><span
                        class="text-white/70">• Trải nghiệm thị giác tốt nhất khi xoay ngang màn hình</span></div>
            </div>
        </div>

        {{-- Bước 1: fullscreen video — ẩn mặc định, bật khi bấm Triệu hồi --}}
        <div data-waifu-summon-video-layer
            class="fixed inset-0 z-[10050] hidden touch-none isolation-isolate flex items-center justify-center bg-black"
            aria-hidden="true">
            <picture data-waifu-summon-video-bg class="pointer-events-none absolute inset-0 z-0 block h-full w-full">
                <img alt="Nền triệu hồi" class="h-full w-full object-cover object-center" loading="lazy" decoding="async"
                    src="{{ $summonBackgroundWebp }}">
            </picture>
            <div class="absolute top-2 right-2 z-20 lg:top-4 lg:right-4">
                <button type="button" data-waifu-summon-video-skip
                    class="to-lav-500 flex cursor-pointer items-center justify-center gap-1 rounded-xl bg-gradient-to-b from-[#DD94FF] px-3 py-3 shadow-[0px_4px_8.899999618530273px_0px_rgba(196,69,255,0.25)] transition-colors hover:from-[#e3a8ff]"
                    aria-label="Bỏ qua video triệu hồi">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-skip-forward h-5 w-5" aria-hidden="true">
                        <polygon points="5 4 15 12 5 20 5 4"></polygon>
                        <line x1="19" x2="19" y1="5" y2="19"></line>
                    </svg>
                </button>
            </div>
            <video data-waifu-summon-video class="relative z-10 h-full w-full object-cover" src="{{ $summonVideoUrl }}"
                playsinline preload="auto" controlslist="nodownload" disablepictureinpicture></video>
        </div>

        {{-- Bước 2: nền cố định dưới; stage absolute full + căn giữa màn hình; mobile cuộn ngang thẻ --}}
        {{-- Không dùng isolation-isolate / overflow-hidden trên layer: flatten 3D → lật xong vẫn thấy mặt lá bài đè mặt thưởng. --}}
        <div data-waifu-summon-card-layer class="fixed inset-0 z-[10000] hidden"
            aria-hidden="true">
            <picture data-waifu-summon-card-bg class="pointer-events-none absolute inset-0 z-0 block h-full w-full">
                <img alt="Nền triệu hồi" class="h-full w-full object-cover object-center" loading="lazy"
                    decoding="async" src="{{ $summonBackgroundWebp }}">
            </picture>
            <div data-waifu-summon-card-backdrop
                class="absolute inset-0 z-[1] cursor-pointer bg-gradient-to-b from-transparent via-black/50 to-black"
                aria-hidden="true"></div>
            <div class="absolute top-2 right-2 z-30 flex flex-col items-end gap-2 lg:top-4 lg:right-4 lg:flex-row">
                <button type="button" data-waifu-summon-flip-all
                    class="to-lav-500 flex cursor-pointer items-center justify-center rounded-xl bg-gradient-to-b from-[#DD94FF] px-4 py-2 text-sm font-semibold text-black shadow-[0px_4px_8.899999618530273px_0px_rgba(196,69,255,0.25)] transition-colors hover:from-[#e3a8ff]">
                    Lật hết
                </button>
            </div>
            {{-- Căn giữa viewport: flex + justify-center items-center (không dùng min-h-full — dễ bị đẩy lên trên) --}}
            <div data-waifu-summon-card-stage
                class="absolute inset-0 z-10 box-border flex min-w-0 flex-col items-center justify-center overflow-x-hidden overflow-y-auto overscroll-y-contain px-3 pb-[max(1.25rem,env(safe-area-inset-bottom,0px))] pt-[max(4.25rem,env(safe-area-inset-top,0px)+2.75rem)] sm:px-4 sm:pb-8 sm:pt-[5.5rem]">
                <div class="flex w-full min-w-0 max-w-full flex-col items-center justify-center gap-3 sm:gap-4">
                    {{-- Wrapper căn giữa theo chiều ngang; cuộn ngang khi nhiều thẻ (lớp trong co theo nội dung). --}}
                    <div data-waifu-summon-cards-scroll
                        class="flex w-full min-w-0 max-w-full justify-center overflow-x-auto overflow-y-visible overscroll-x-contain py-2 pl-2 pr-4 sm:max-w-7xl sm:px-2 sm:pr-5">
                        <div data-waifu-summon-cards-grid class="flex min-h-0 min-w-0 flex-row flex-nowrap items-center gap-x-2 sm:gap-x-3 md:gap-x-4">
                        </div>
                    </div>
                    <div data-waifu-summon-card-footer
                        class="hidden w-full max-w-md shrink-0 flex flex-col items-center gap-3 px-1 sm:px-2">

                        <button type="button" data-waifu-summon-done-cards
                            class="rounded-xl border border-white/40 bg-white/10 px-5 py-2 text-sm font-semibold text-white transition-colors hover:bg-white/15 sm:px-6">Hoàn
                            tất</button>
                    </div>
                </div>
            </div>

            {{-- Chúc mừng Waifu 5★ — flash + tia + confetti (main.entry.js + waifu-summon.css). --}}
            <div data-waifu-summon-5star-celebration
                class="pointer-events-none absolute inset-0 z-[10060] hidden cursor-pointer flex flex-col items-center justify-center px-4"
                aria-hidden="true">
                <div class="waifu-5star-celebration__veil absolute inset-0 z-0 bg-gradient-to-b from-amber-500/45 via-fuchsia-950/90 to-black backdrop-blur-md"
                    aria-hidden="true"></div>
                <div class="waifu-5star-celebration__rays pointer-events-none absolute inset-0 z-[1] overflow-hidden"
                    aria-hidden="true"></div>
                <div data-waifu-summon-5star-burst
                    class="pointer-events-none absolute inset-0 z-[2] overflow-hidden"
                    aria-hidden="true"></div>
                <div class="waifu-5star-celebration__flash pointer-events-none absolute inset-0 z-[4]"
                    aria-hidden="true"></div>
                <div class="waifu-5star-celebration__panel-wrap relative z-10 flex flex-col items-center justify-center">
                    <div class="waifu-5star-celebration__orbit waifu-5star-celebration__orbit--a" aria-hidden="true"></div>
                    <div class="waifu-5star-celebration__orbit waifu-5star-celebration__orbit--b" aria-hidden="true"></div>
                    <div
                        class="waifu-5star-celebration__panel relative flex max-w-md flex-col items-center rounded-3xl border border-amber-400/40 bg-black/35 px-6 py-8 text-center shadow-[0_0_60px_rgba(255,215,0,0.35),0_0_120px_rgba(221,147,255,0.22)] backdrop-blur-sm sm:px-10 sm:py-10">
                        <div class="waifu-5star-celebration__stars mb-3 text-5xl leading-none text-amber-200 sm:text-7xl"
                            aria-hidden="true">
                            @foreach (range(1, 5) as $_)
                                <span class="waifu-5star-celebration__star">★</span>
                            @endforeach
                        </div>
                        <h2
                            class="waifu-5star-celebration__title font-sans text-4xl font-black tracking-tight text-transparent sm:text-6xl">
                            5 SAO!</h2>
                        <p
                            class="waifu-5star-celebration__subtitle mt-4 font-sans text-lg font-bold text-white sm:text-xl">
                            Siêu hiếm! Chúc mừng bạn triệu hồi Waifu 5★!</p>
                        <p class="mt-3 text-sm font-medium text-amber-100/90">Chạm bất kỳ đây để đóng</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Một thẻ lật — kích thước giống mẫu: mobile nhỏ hơn, sm+ w-48, lg 300px --}}
        <template id="waifu-summon-card-tmpl">
            {{-- Kích thước / perspective: waifu-summon.css ([data-waifu-summon-card-root]) vì arbitrary Tailwind không có trong app.css đã build. --}}
            <div data-waifu-summon-card-root
                class="shrink-0 cursor-pointer animate-card-drop animate-card-idle touch-manipulation">
                <div data-waifu-summon-card-inner
                    class="relative h-full w-full [transform-style:preserve-3d] transition-transform duration-700 [transition-timing-function:cubic-bezier(0.4,0,0.2,1)]"
                    style="transform: rotateY(0deg);">
                    {{-- Mặt A (úp): lá bài. translateZ + webkit backface tránh Safari vẽ nhầm lớp. --}}
                    <div class="absolute inset-0 overflow-hidden rounded-lg"
                        style="-webkit-backface-visibility: hidden; backface-visibility: hidden; transform: translateZ(1px);">
                        <img alt="Mặt sau thẻ" class="h-full w-full rounded-lg object-cover shadow-xl" draggable="false"
                            loading="lazy" decoding="async" src="{{ $waifuSummonCardPng }}">
                    </div>
                    {{-- Mặt B (thưởng): placeholder = lá bài; bấm lật → JS gán data-reward-url vào src = ảnh thưởng thật (API image_url) rồi xoay 3D. --}}
                    <div class="absolute inset-0 overflow-hidden rounded-lg"
                        style="-webkit-backface-visibility: hidden; backface-visibility: hidden; transform: rotateY(180deg) translateZ(1px);">
                        <img data-waifu-summon-result-img alt="" data-reward-url=""
                            class="h-full w-full rounded-lg object-cover shadow-xl" draggable="false" decoding="async"
                            loading="eager"
                            src="{{ $waifuSummonCardPng }}">
                    </div>
                </div>
            </div>
        </template>
        @include('theme-vinahentai::partials.waifu-summon-rewards-modal')
        @include('theme-vinahentai::partials.waifu-summon-guide-modal')
    </div>
@endsection
