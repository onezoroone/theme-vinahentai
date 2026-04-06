@php
    $maskHiddenGenres = false;
    if (auth()->check()) {
        $hiddenGenreIds = array_map('intval', auth()->user()->hidden_genre_ids ?? []);
        $maskHiddenGenres = $hiddenGenreIds !== [] && $manga->intersectsHiddenGenres($hiddenGenreIds);
    }
@endphp
<div class="group bg-bgc-layer1 relative block overflow-hidden rounded-xl border border-bd-default transform transition-transform duration-200 ease-out will-change-transform vh-hover-scale-105" data-variant="default">
    {{-- Link chính tới truyện (tránh lồng <a> trong <a> khi có link Cài đặt) --}}
    <a href="{{ $manga->getUrl() }}" class="absolute inset-0 z-[1]" aria-label="Xem truyện {{ $manga->title }}"></a>

    @if ($maskHiddenGenres)
        <div class="pointer-events-none absolute inset-0 z-[50] flex flex-col items-center justify-center gap-2 px-4 text-center" style="background-color: rgba(0, 0, 0, 0.92);">
            <div class="text-white/85 text-sm font-semibold leading-snug">
                <div>Ảnh bị che do chứa thể loại bị ẩn</div>
                <div>Cài đặt thêm/bớt tại</div>
                <div class="mt-0.5 inline-flex items-center justify-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield-ban h-4 w-4 text-white/80" aria-hidden="true">
                        <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path>
                        <path d="m4.243 5.21 14.39 12.472"></path>
                    </svg>
                    <a href="{{ route('user.blacklist-tags') }}" class="pointer-events-auto text-white/90 underline-offset-2 hover:underline">Lọc thể loại</a>
                </div>
            </div>
        </div>
    @endif

    <div class="relative z-[2] pointer-events-none">
        <div class="relative w-full overflow-hidden bg-black" style="aspect-ratio: 2 / 3;">
            @isset($hot)
            <span class="pointer-events-none absolute top-0 left-0 z-[4] select-none rounded-none rounded-tl-xl rounded-br-lg px-2 py-0.5 text-sm font-semibold text-white shadow-[0_1px_6px_rgba(0,0,0,0.55)]" style="background-image:linear-gradient(100deg, rgba(244,63,94,0.35) 0%, rgba(244,63,94,0.95) 20%, rgba(244,63,94,0.35) 40%, rgba(244,63,94,0.95) 60%, rgba(244,63,94,0.35) 80%);background-size:200% 100%;animation:shimmer-red 4s linear infinite;border:1px solid rgba(248,113,113,0.45)">Hot</span>
            @endisset
            <div class="absolute left-0 top-0 w-full overflow-hidden" style="aspect-ratio: 3 / 4;">
                <img alt="{{ $manga->title }}" loading="lazy" class="block lozad h-full w-full object-cover object-top select-none pointer-events-none" data-src="{{ $manga->cover_image }}">
                <div class="pointer-events-none absolute inset-x-0 bottom-0 h-[9%] z-[1] bg-gradient-to-t from-black/40 via-black/30 to-transparent/0"></div>
                <div class="pointer-events-none absolute inset-x-0 bottom-0 h-px bg-lav-500/30 z-[3]"></div>
            </div>
        </div>
        <div class="
            pointer-events-none absolute inset-x-[-10px] bottom-[-10px]
            h-[25%] min-h-[58px]
            bg-gradient-to-t from-[#0B0C1D]/95 via-[#0B0C1D]/90 to-transparent/0
            rounded-b-[5px]
            z-[1]
            "></div>
        <div class="absolute inset-x-0 bottom-0 z-[2] min-w-0">
            <div class="flex items-center gap-2 px-2 pt-2 pb-1 text-xs text-white/90 min-w-0 max-[415px]:text-[11px] max-[415px]:leading-[15px] max-[376px]:text-[10px] max-[376px]:leading-[14px] false">
            @if (count($manga->chapters) > 0)
                @if (strtolower($manga->chapters->first()->title) == 'oneshot')
                <span class="inline-flex h-6 items-center rounded-full border px-2 font-semibold max-[415px]:h-[22px] max-[415px]:px-[7px] max-[376px]:h-5 max-[376px]:px-[6px] bg-[#261343] text-[#DFA8FF] border-lav-500/45">Oneshot</span>
                @else
                <span class="text-white/90 font-semibold truncate flex-1 min-w-0 basis-auto text-outline text-[16px] leading-[20px] sm:text-[15px] sm:leading-[19px] max-[415px]:text-[14px] max-[415px]:leading-[18px] max-[376px]:text-[13px] max-[376px]:leading-[17px]" title="{{ $manga->chapters->first()->title }}">{{ $manga->chapters->first()->title }}</span>
                @endif
            @endif

            @if ($manga->status == 'completed')
            <span class="ml-auto inline-flex h-6 items-center rounded-full border px-2 font-semibold max-[415px]:h-[22px] max-[415px]:px-[7px] max-[376px]:h-5 max-[376px]:px-[6px] bg-[#2A1216] text-[#D94545] border-red-500/45" title="Đã END">END!</span>
            @endif
            </div>
            <div class="px-2 pb-2">
                <h3 class="mt-1.5 truncate font-semibold text-white text-[17px] leading-[21px] sm:text-base sm:leading-5 max-[415px]:text-[17px] max-[415px]:leading-[19px] max-[376px]:text-[15px] max-[376px]:leading-[19px]" title="{{ $manga->title }}">{{ $manga->title }}</h3>
            </div>
        </div>
    </div>
</div>
