<section class="min-w-0">
    <div class="relative md:hidden">
        <style>
            [data-mobile-hot-grid] {
              --hot-grid-gap-x: 3px;
              --hot-grid-gap-y: 12px;
              --hot-grid-col-width: calc((100% - var(--hot-grid-gap-x)) / 2);
              --hot-grid-card-height: calc(var(--hot-grid-col-width) * 1.5); /* poster 2:3 */
              --hot-grid-slide-height: calc(var(--hot-grid-card-height) * 2 + var(--hot-grid-gap-y));
            }
            @media (min-width: 640px) {
              [data-mobile-hot-grid] { --hot-grid-col-width: calc((100% - var(--hot-grid-gap-x)) / 3); }
            }
            @media (min-width: 768px) {
              [data-mobile-hot-grid] { --hot-grid-col-width: calc((100% - var(--hot-grid-gap-x)) / 4); }
            }
            [data-mobile-hot-grid] .hot-grid-slide { min-height: var(--hot-grid-slide-height); }
          </style>
        <div class="mx-auto w-full">
            <div class="relative overflow-hidden -mx-2 sm:mx-0" data-mobile-hot-grid="true">
                <div class="swiper swiper_columns swiper-hot">
                    <div class="swiper-wrapper">
                        @foreach (collect($hotMangas)->chunk(2) as $group)
                            <div class="swiper-slide">
                                <div class="hot-grid-slide flex flex-col gap-3">
                                    @foreach ($group as $manga)
                                    <div class="relative">
                                        <div class="ios-composite" style="transform: translateZ(0px); backface-visibility: hidden; -webkit-font-smoothing: antialiased; will-change: transform; contain: paint; isolation: isolate;">
                                            @include('theme-vinahentai::components.item', ['manga' => $manga])
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="swiper-pagination"></div>

                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="relative hidden md:block">
        <div class="overflow-x-hidden">
            <div class="hot-rail">
                <div class="relative w-full" data-hot-banner="1" data-count="{{ count($hotMangas) }}">
                    <style>
                        [data-hot-banner="1"] { --tb-gap: 12px; --tb-cols: 3; }
                        @media (min-width: 1024px) {
                          [data-hot-banner="1"] { --tb-cols: 5; }
                        }
                        /* Phân trang Swiper desktop: bullet tròn, căn giữa (thay span tĩnh) */
                        .swiper-hot-desktop .hot-desktop-pagination.swiper-pagination {
                          position: relative;
                          inset: auto;
                          width: 100%;
                          margin-top: 0.75rem;
                          display: none;
                          gap: 0.5rem;
                          justify-content: center;
                          align-items: center;
                        }
                        @media (min-width: 1024px) {
                          .swiper-hot-desktop .hot-desktop-pagination.swiper-pagination {
                            display: flex;
                          }
                        }
                        .swiper-hot-desktop .hot-desktop-pagination .swiper-pagination-bullet {
                          width: 0.5rem;
                          height: 0.5rem;
                          margin: 0 !important;
                          border-radius: 9999px;
                          background: rgb(255 255 255 / 0.25);
                          opacity: 1;
                        }
                        .swiper-hot-desktop .hot-desktop-pagination .swiper-pagination-bullet-active {
                          background: #c084fc;
                        }
                    </style>
                    <div class="overflow-hidden w-full pb-2">
                        <div class="swiper swiper-hot-desktop">
                            <div class="swiper-wrapper">
                                @foreach ($hotMangas as $manga)
                                    <div class="swiper-slide relative w-full">

                                        @include('theme-vinahentai::components.item', ['manga' => $manga, 'hot' => true])
                                    </div>
                                @endforeach
                            </div>

                            <div class="hot-desktop-pagination swiper-pagination" role="navigation" aria-label="Phân trang truyện hot"></div>

                            <button aria-label="Trước" class="absolute swiper-desktop-button-prev left-3 top-1/2 -translate-y-1/2 rounded-full bg-black/45 px-3 py-6 text-white hover:bg-black/65 z-10">‹</button>
                            <button aria-label="Sau" class="absolute swiper-desktop-button-next right-3 top-1/2 -translate-y-1/2 rounded-full bg-black/45 px-3 py-6 text-white hover:bg-black/65 z-10">›</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<div class="hidden" aria-hidden="true"></div>

@push('scripts')
<script>
    const swiperHot = new Swiper('.swiper-hot', {
        slidesPerView: 2,
        spaceBetween: 3,
        loop: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
            pauseOnMouseEnter: true,
        },
        pagination: {
            el: '.swiper-hot .swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-hot .swiper-button-next',
            prevEl: '.swiper-hot .swiper-button-prev',
        },
    });

    const swiperHotDesktop = new Swiper('.swiper-hot-desktop', {
        slidesPerView: 5,
        spaceBetween: 8,
        loop: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
        breakpoints: {
            640: {
                slidesPerView: 3,
            },
            1024: {
                slidesPerView: 5,
            },
        },
        pagination: {
            el: '.swiper-hot-desktop .hot-desktop-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-hot-desktop .swiper-desktop-button-next',
            prevEl: '.swiper-hot-desktop .swiper-desktop-button-prev',
        },
    });
</script>
@endpush


