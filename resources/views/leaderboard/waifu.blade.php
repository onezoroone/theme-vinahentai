@extends('theme-vinahentai::layout.main')

@section('body')
    <div class="container-page flex flex-col items-center justify-center gap-11 px-4 py-8 md:px-6 lg:px-0">
        @include('theme-vinahentai::leaderboard.layout', ['active' => 'waifu'])

        <h1 class="w-full text-center text-4xl leading-10 font-semibold">Top những dàn harem mạnh nhất lịch sử</h1>

        <div class="bg-bgc-layer1 border-bd-default mx-auto flex w-full max-w-[750px] flex-col gap-4 space-y-0 overflow-hidden rounded-2xl border px-4 py-4">
            @forelse ($entries as $entry)
                @php
                    $user = $entry['user'];
                    // Luôn dùng mảng (tránh offset trên string nếu cache/driver lỗi).
                    $counts = is_array($entry['rarity_counts'] ?? null) ? $entry['rarity_counts'] : [];
                    $haremLabel = collect([5, 4, 3, 2, 1])
                        ->map(function (int $star) use ($counts) {
                            $n = (int) ($counts[$star] ?? 0);

                            return $n > 0 ? $star . '★×' . number_format($n) : null;
                        })
                        ->filter()
                        ->implode(' · ');
                @endphp
                <a href="{{ $user->getUrl() }}" class="border-bd-default inline-flex flex-col items-start justify-start self-stretch overflow-hidden rounded-xl border">
                    <div class="bg-background-layer-1 flex flex-col items-start justify-center gap-4 self-stretch overflow-hidden p-3 md:flex-row md:justify-between md:gap-3">
                        <div class="inline-flex items-center justify-start gap-3">
                            @if ($loop->iteration === 1)
                            <div class="min-w-8 justify-center text-center leading-normal font-semibold text-[#FFE133] text-2xl">    {{ sprintf('%02d', $loop->iteration) }}</div>
                            @elseif ($loop->iteration === 2)
                            <div class="min-w-8 justify-center text-center leading-normal font-semibold text-[#5BD8FA] text-2xl">{{ sprintf('%02d', $loop->iteration) }}</div>
                            @elseif ($loop->iteration === 3)
                            <div class="min-w-8 justify-center text-center leading-normal font-semibold text-[#FF7158] text-2xl">{{ sprintf('%02d', $loop->iteration) }}</div>
                            @else
                                <div class="min-w-8 justify-center text-center leading-normal font-semibold text-txt-primary text-base">
                                    {{ sprintf('%02d', $loop->iteration) }}
                                </div>
                            @endif

                            <div class="h-14 w-14 overflow-hidden rounded bg-gradient-to-b from-gray-900/0 to-gray-900 flex items-center justify-center">
                                @if ($user->avatar)
                                <img class="h-full w-full object-cover" alt="{{ $user->name }}" src="{{ $user->avatar }}">
                                @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user h-7 w-7 text-txt-primary" aria-hidden="true">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                @endif
                            </div>
                            <div class="inline-flex w-36 flex-col items-start justify-start gap-1">
                                <div class="text-text-text-primary h-6 justify-center self-stretch text-base leading-normal font-semibold">{{ $user->name }}</div><span class="relative inline-flex h-6 overflow-visible"><img class="block h-full w-auto object-contain" alt="Title" src="{{ asset($user->level?->image) }}" style="transform: scale(2); transform-origin: center center;"></span>
                            </div>
                        </div>

                        <div class="flex w-full items-center justify-between gap-2 overflow-x-auto sm:gap-4 md:w-auto md:overflow-x-visible">
                            <div class="inline-flex min-w-20 flex-col items-start justify-start gap-1 sm:min-w-24"><div class="text-text-text-secondary justify-center text-xs leading-none font-medium">Tổng số Waifu</div><div class="text-text-text-primary justify-center text-base leading-normal font-medium">{{ $user->waifus->count() }}</div></div>
                            <div class="inline-flex min-w-20 flex-col items-start justify-start gap-1 sm:min-w-24"><div class="text-text-text-secondary justify-center text-xs leading-none font-medium">Waifu 5 sao</div><div class="text-text-text-primary justify-center text-base leading-normal font-medium">{{ $counts[5] ?? 0 }}</div></div>
                            <div class="inline-flex min-w-20 flex-col items-start justify-start gap-1 sm:min-w-24"><div class="text-text-text-secondary justify-center text-xs leading-none font-medium">Waifu 4 sao</div><div class="text-text-text-primary justify-center text-base leading-normal font-medium">{{ $counts[4] ?? 0 }}</div></div>
                            <div class="inline-flex min-w-20 flex-col items-start justify-start gap-1 sm:min-w-24"><div class="text-text-text-secondary justify-center text-xs leading-none font-medium">Waifu 3 sao</div><div class="text-text-text-primary justify-center text-base leading-normal font-medium">{{ $counts[3] ?? 0 }}</div></div>
                        </div>
                    </div>
                    <div class="border-bd-default flex items-center justify-center gap-4 border-t p-4">
                        @foreach ($entry['top_waifus'] ?? [] as $w)
                            @if (! empty($w['image']))
                                <img alt="{{ $w['name'] }}" class="aspect-2/3 w-[100px] rounded-lg lozad" loading="lazy" data-src="{{ $w['image'] }}">
                            @endif
                        @endforeach
                    </div>
                </a>
            @empty
                <p class="text-txt-secondary px-3 py-6 text-center text-sm">Chưa có dữ liệu harem để xếp hạng.</p>
            @endforelse
        </div>
    </div>
@endsection
