@extends('theme-vinahentai::layout.main')

@section('body')
    <div class="container-page flex flex-col items-center justify-center gap-11 px-4 py-8 md:px-6 lg:px-0">
        @include('theme-vinahentai::leaderboard.layout', ['active' => 'member'])
        <h1 class="w-full text-center text-4xl leading-10 font-semibold">Thánh Lọ Bảng</h1>

        <div class="bg-bgc-layer1 border-bd-default w-full max-w-[750px] space-y-0 overflow-hidden rounded-2xl border p-0 py-4">
            @foreach ($users as $user)
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
                    <h3 class="text-txt-primary text-base leading-6 font-semibold">{{ $user->name }} - {{ $user->level?->name }}{{ $user->level_stage_label !== '' ? ' ' . $user->level_stage_label : '' }}</h3>
                    <div class="flex items-center gap-4"><span class="relative inline-flex h-6 overflow-visible flex-shrink-0 align-middle">
                            @if ($user->level->image)
                                <img alt="{{ $user->level->name }}"
                                    class="block h-full w-auto object-contain" loading="lazy"
                                    src="{{ asset($user->level->image) }}"
                                    style="transform: scale(2); transform-origin: center center;">
                            @endif
                        </span>
                        <div class="flex-1 flex items-center align-middle">
                            <div class="bg-bgc-layer2 h-2 w-full overflow-hidden rounded-full">
                                <div class="h-full w-full rounded-full bg-gradient-to-r from-[#3D1351] to-[#E8B5FF]" style="width: {{ $user->level_progress_percent }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
@endsection
