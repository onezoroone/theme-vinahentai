@extends('theme-vinahentai::layout.main')

@php
    $defaultTitle = (string) active_theme_config('seo_home_default_title', config('app.name', 'Laravel'));
    $defaultDescription = (string) active_theme_config('seo_home_description', '');
    $defaultKeywords = (string) active_theme_config('seo_home_keywords', '');
    $defaultImage = (string) active_theme_config('seo_home_image', '');

    SEOMeta::setTitle($defaultTitle);
    if ($defaultDescription !== '') {
        SEOMeta::setDescription($defaultDescription);
    }
    if ($defaultKeywords !== '') {
        SEOMeta::addKeyword(array_filter(array_map('trim', explode(',', $defaultKeywords))));
    }
    if ($defaultImage !== '') {
        OpenGraph::addImage($defaultImage);
    }
@endphp

@push('header')
    <link rel="stylesheet" href="{{ asset('vendor/theme-vinahentai/css/swiper.css') }}" />

    <script src="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js"></script>
@endpush

@section('body')
    <div class="container-page mx-auto px-4 pt-3 pb-6 md:py-6">
        <h1 class="sr-only">{{ $defaultTitle }}</h1>
        <div class="flex items-center gap-2 px-1 pb-3"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" class="lucide lucide-flame h-5 w-5 text-red-400" aria-hidden="true">
                <path
                    d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z">
                </path>
            </svg>
            <h2 class="text-txt-primary text-xl font-semibold uppercase">Truyện HOT</h2>
        </div>
        @include('theme-vinahentai::components.hot', compact('hotMangas'))

        @if (!empty($homeSections))
            <div class="mt-[18px] grid grid-cols-1 gap-4 sm:mt-8 sm:grid-cols-[minmax(0,1fr)_27%]">
                <div class="min-w-0 space-y-8">
                    @foreach ($homeSections as $block)
                        @include('theme-vinahentai::components.home-section', [
                            'label' => $block['label'],
                            'mangas' => $block['mangas'],
                            'show_more_url' => $block['show_more_url'],
                        ])
                    @endforeach
                </div>

                <section class="mt-0 w-full sm:justify-self-end">
                    <div class="space-y-10">
                        <div>
                            @include('theme-vinahentai::components.sidebar-rank')
                        </div>
                        <div>
                            <div
                                class="bg-bgc-layer1 border-bd-default w-full overflow-hidden rounded-2xl border p-0 ml-auto mt-6 md:mt-8">
                                <div class="flex items-center gap-2 px-4 py-3"><svg xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="lucide lucide-message-square h-6 w-6 text-lav-500"
                                        aria-hidden="true">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                    </svg><span class="text-txt-primary text-base font-semibold uppercase">bình luận</span>
                                </div>
                                <ul
                                    class="divide-bd-default border-bd-default flex max-h-[520px] flex-col divide-y overflow-y-auto overflow-x-hidden overscroll-contain">
                                    @foreach ($newComments as $comment)
                                        <li class="flex items-start gap-3 px-3 py-3">
                                            <div
                                                class="relative flex h-8 w-8 flex-shrink-0 items-center justify-center overflow-hidden rounded-full bg-[#121826] ring-1 ring-white/10">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="lucide lucide-user h-4 w-4 text-txt-primary" aria-hidden="true">
                                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                                    <circle cx="12" cy="7" r="4"></circle>
                                                </svg>
                                                @if ($comment->user->avatar)
                                                <img alt="{{ $comment->user->name }}"
                                                class="absolute inset-0 h-full w-full object-cover" loading="lazy"
                                                src="{{ $comment->user->avatar }}">
                                                @endif
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2 text-xs text-white/60"><span
                                                        class="truncate max-w-[40%] font-medium text-white/80">{{ $comment->user->name }}</span><span
                                                        class="text-white/50">→</span><a
                                                        href="{{ $comment->manga->getUrl() }}"
                                                        class="truncate max-w-[40%] text-lav-300 hover:text-lav-200 [touch-action:manipulation]">{{ $comment->manga->title }}</a><span
                                                        class="ml-auto text-[11px] text-white/40">{{ $comment->created_at->diffForHumans() }}</span>
                                                </div>
                                                <div class="mt-1 text-sm leading-5 text-white/90">{!! $comment->content !!}
                                                </div>
                                            </div>
                                            <a href="{{ $comment->manga->getUrl() }}"
                                                class="ml-2 flex flex-shrink-0 overflow-hidden rounded-md [touch-action:manipulation]"
                                                aria-label="{{ $comment->manga->title }}">
                                                <div class="w-[39.2px] sm:w-[44.8px] aspect-[2/3]"><img
                                                        alt="{{ $comment->manga->title }}"
                                                        class="h-full w-full object-cover opacity-80 lozad" loading="lazy"
                                                        data-src="{{ $comment->manga->cover_image }}"></div>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <div class="flex items-center gap-3">
                                <div class="relative h-[15px] w-[15px]"><span role="img" aria-label="Rồng"
                                        class="absolute top-0 left-[1px] text-lg leading-none">🐉</span></div>
                                <h2 class="text-txt-primary text-xl font-semibold uppercase">Thánh Lọ Bảng</h2>
                            </div>

                            <div
                                class="bg-bgc-layer1 border-bd-default space-y-0 overflow-hidden rounded-2xl border p-0 py-4">
                                @foreach ($usersMostExperience as $user)
                                    <a href="{{ $user->getUrl() }}" class="flex items-center gap-3 p-3">
                                        @if ($loop->iteration === 1)
                                            <span class="w-5 text-center text-base font-semibold text-[#FFE133]">
                                                {{ $loop->iteration }}
                                            </span>
                                        @elseif ($loop->iteration === 2)
                                            <span class="w-5 text-center text-base font-semibold text-[#5BD8FA]">
                                                {{ $loop->iteration }}
                                            </span>
                                        @elseif ($loop->iteration === 3)
                                            <span class="w-5 text-center text-base font-semibold text-[#FF7158]">
                                                {{ $loop->iteration }}
                                            </span>
                                        @else
                                            <span class="w-5 text-center text-base font-semibold text-txt-primary">
                                                {{ $loop->iteration }}
                                            </span>
                                        @endif
                                        <div
                                            class="relative h-14 w-14 flex-shrink-0 overflow-hidden rounded-full bg-[#121826] flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-user h-7 w-7 text-txt-primary" aria-hidden="true">
                                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                            @if ($user->avatar)
                                                <img alt="{{ $user->name }}"
                                                    class="absolute inset-0 h-full w-full object-cover" loading="lazy"
                                                    src="{{ $user->avatar }}">
                                            @endif
                                        </div>
                                        <div class="flex-1 space-y-2">
                                            <h3 class="text-txt-primary text-base leading-6 font-semibold">
                                                {{ $user->name }} - {{ $user->level?->name }}{{ $user->level_stage_label !== '' ? ' ' . $user->level_stage_label : '' }}</h3>
                                            <div class="flex items-center gap-4">
                                                <span
                                                    class="relative inline-flex h-6 overflow-visible flex-shrink-0 align-middle">
                                                    @if ($user->level->image)
                                                        <img alt="{{ $user->level->name }}"
                                                            class="block h-full w-auto object-contain" loading="lazy"
                                                            src="{{ asset($user->level->image) }}"
                                                            style="transform: scale(2); transform-origin: center center;">
                                                    @endif
                                                </span>
                                                <div class="flex-1 flex items-center align-middle">
                                                    <div class="bg-bgc-layer2 h-2 w-full overflow-hidden rounded-full">
                                                        <div class="h-full w-full rounded-full bg-gradient-to-r from-[#3D1351] to-[#E8B5FF]"
                                                            style="width: {{ $user->level_progress_percent }}%">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <div class="space-y-6">
                                <div class="flex items-center gap-3"><div class="relative h-[15px] w-[15px]"><span role="img" aria-label="Bút" class="absolute top-0 left-[1px] text-lg leading-none">✍️</span></div><h2 class="text-txt-primary text-xl font-semibold uppercase">BXH Dịch Giả</h2></div>

                                @include('theme-vinahentai::components.user-rank')
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        @endif
    </div>
@endsection
