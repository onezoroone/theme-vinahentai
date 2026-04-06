@php
    $moreHref = null;
    if ($show_more_url !== '') {
        $moreHref = \Illuminate\Support\Str::startsWith($show_more_url, ['http://', 'https://'])
            ? $show_more_url
            : url($show_more_url);
    }
@endphp
<section class="mt-0">
    <div class="scroll-mt-16"></div>
    <div class="flex items-center justify-between"><div class="flex items-center gap-3"><div class="relative h-[15px] w-[15px]"><img src="{{ asset('vendor/theme-vinahentai/images/multi-star.svg') }}" alt="" class="absolute top-0 left-[4.62px] h-4"></div><h2 class="text-txt-primary text-xl font-semibold uppercase">{{ $label }}</h2></div></div>

    <div class="mt-6 -mx-2 grid grid-cols-2 gap-y-4 gap-x-2 sm:mx-0 sm:grid-cols-3 sm:gap-4 xl:grid-cols-4">
        @foreach ($mangas as $manga)
            @include('theme-vinahentai::components.item', ['manga' => $manga])
        @endforeach
    </div>

    <div class="mt-8 flex flex-col items-center gap-2"><a href="{{ $moreHref }}" class="rounded-xl bg-btn-primary px-4 py-2 text-sm font-semibold text-bgc-layer1 hover:opacity-90 active:opacity-80" aria-label="Xem thêm">Xem thêm</a><a href="{{ $moreHref }}" class="text-txt-secondary text-xs underline-offset-2 hover:underline" aria-label="Mở trang 1 danh sách truyện">Trang 1 danh sách</a></div>
</section>
