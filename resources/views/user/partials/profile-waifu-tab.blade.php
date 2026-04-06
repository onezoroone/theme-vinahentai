{{-- Tab Waifu profile: đồng hành, điểm danh (placeholder), bộ sưu tập theo sao --}}
@php
    /** @var array<int, array{total: int, owned_in_tier: int, waifus: \Illuminate\Support\Collection<int, \App\Models\Waifu>}> $waifuTiers */
    /** @var list<int> $ownedWaifuIds */
    /** @var int $waifuGrandTotal */
    /** @var int $waifuOwnedTotal */
    /** @var int $waifuCollectPercent */
    /** @var \App\Models\Waifu|null $companionWaifu */
@endphp
<div class="w-full flex flex-col gap-8" data-profile-waifu-root data-url-companion="{{ route('api.user.companion-waifu') }}">
    <div class="flex flex-col gap-4">
        <div class="flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart h-5 w-5 text-pink-400" aria-hidden="true">
                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
            </svg>
            <h2 class="text-xl font-semibold text-white uppercase">Nhận thưởng cùng waifu</h2>
        </div>
        <div class="flex flex-col gap-6 w-full">
            <div class="h-auto w-full max-w-[968px] rounded-xl">
                <div class="flex flex-col gap-4 lg:grid lg:grid-cols-2">
                    <div class="border-bd-default flex w-full flex-col gap-6 rounded-lg border bg-[radial-gradient(ellipse_100%_96.27%_at_50%_0%,_rgba(12,11,56,0.45)_0%,_black_77%)] p-4">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart text-txt-focus h-5 w-5" aria-hidden="true">
                                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                            </svg>
                            <h3 class="text-txt-primary font-sans text-xl font-semibold uppercase">WAIFU ĐỒNG HÀNH</h3>
                        </div>
                        <div class="flex items-center gap-4 self-stretch">
                            <div data-profile-companion-slot class="border-bd-default bg-bgc-layer1 relative flex h-[180px] w-[100px] flex-shrink-0 items-center justify-center overflow-hidden rounded-lg border sm:h-[200px] sm:w-[110px] lg:h-[221px] lg:w-[122px]">
                                @if ($companionWaifu && $companionWaifu->image)
                                    <img class="h-full w-full object-cover" src="{{ asset($companionWaifu->image) }}" alt="{{ $companionWaifu->name }}" loading="lazy">
                                @else
                                    <span class="text-txt-tertiary text-lg font-semibold uppercase">Trống</span>
                                @endif
                            </div>
                            <div class="flex-1 text-center sm:text-left">
                                <p class="text-sm leading-tight font-medium">
                                    <span class="text-txt-secondary">Nhấn vào Waifu bạn yêu thích trong bộ sưu tập bên dưới để đồng hành và mở khóa tính năng </span>
                                    <span class="text-txt-primary">Điểm danh hằng ngày.</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="border-bd-default flex flex-1 flex-col gap-6 rounded-lg border bg-[radial-gradient(ellipse_100%_96.27%_at_50%_0%,_rgba(12,11,56,0.45)_0%,_black_77%)] p-4">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-gift text-txt-focus h-5 w-5" aria-hidden="true">
                                <rect x="3" y="8" width="18" height="4" rx="1"></rect>
                                <path d="M12 8v13"></path>
                                <path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"></path>
                                <path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"></path>
                            </svg>
                            <h3 class="text-txt-primary font-sans text-xl font-semibold uppercase">ĐIỂM DANH HẰNG NGÀY</h3>
                        </div>
                        <div class="bg-bgc-layer2/75 rounded-xl p-3">
                            <div data-profile-waifu-daily-blur class="flex flex-1 flex-col gap-4 {{ $companionWaifu ? '' : 'blur-md' }}">
                                <div class="flex items-center justify-between gap-2 sm:gap-4">
                                    <button type="button" disabled class="min-w-28 cursor-not-allowed rounded-xl bg-gray-600/80 px-3 py-1.5 text-sm font-semibold text-gray-400 transition-all">
                                        <span class="inline-flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-gift h-5 w-5 animate-pulse text-yellow-300" aria-hidden="true">
                                                <rect x="3" y="8" width="18" height="4" rx="1"></rect>
                                                <path d="M12 8v13"></path>
                                                <path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"></path>
                                                <path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"></path>
                                            </svg>
                                            Nhận quà
                                        </span>
                                    </button>
                                </div>
                                <div class="mt-4">
                                    <p data-profile-waifu-daily-hint class="text-sm text-txt-secondary">
                                        @if ($companionWaifu)
                                            Tính năng điểm danh sẽ bật trong bản cập nhật tới.
                                        @else
                                            Vui lòng chọn Waifu để nhận thưởng hàng ngày.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex w-full flex-col gap-6">
        <div class="flex flex-col gap-2">
            <div class="flex items-center gap-3">
                <img src="{{ asset('vendor/theme-vinahentai/images/multi-star.svg') }}" alt="star">
                <h2 class="text-xl font-semibold text-white uppercase">Bộ sưu tập waifu</h2>
            </div>
            <p class="text-sm text-txt-secondary">
                Đã sở hữu:
                <span class="font-bold text-lav-400" data-profile-waifu-owned-label>{{ $waifuOwnedTotal }}/{{ $waifuGrandTotal }}</span>
                <span class="text-lav-300">(<span data-profile-waifu-pct-label>{{ $waifuCollectPercent }}</span>%)</span>
            </p>
            <div class="bg-bgc-layer2 h-2 w-full overflow-hidden rounded">
                <div data-profile-waifu-progress-bar class="via-lav-500 h-2 rounded bg-gradient-to-r from-[#3D1351] to-[#E8B5FF]" style="width: {{ $waifuCollectPercent }}%;"></div>
            </div>
        </div>

        @foreach ([5 => 'r5', 4 => 'r4', 3 => 'r3'] as $stars => $tierKey)
            @php
                $tier = $waifuTiers[$stars] ?? ['total' => 0, 'owned_in_tier' => 0, 'waifus' => collect()];
            @endphp
            <div class="flex flex-col gap-3">
                <button type="button" class="flex w-full items-center gap-2 text-left" data-profile-waifu-tier-toggle="{{ $tierKey }}" aria-expanded="true">
                    <div class="flex items-center gap-1" aria-hidden="true">
                        @for ($i = 0; $i < $stars; $i++)
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star h-4 w-4 fill-[#FFD700] text-[#FFD700]" aria-hidden="true">
                                <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                            </svg>
                        @endfor
                    </div>
                    <span class="text-base font-semibold text-white">Waifu {{ $stars }} sao</span>
                    <span class="text-sm text-txt-secondary">{{ $tier['owned_in_tier'] }}/{{ $tier['total'] }}</span>
                    <span data-profile-waifu-tier-chevron class="ml-auto text-xs text-txt-tertiary transition-transform rotate-180" aria-hidden="true">▼</span>
                </button>
                <div data-profile-waifu-tier-body="{{ $tierKey }}" class="grid grid-cols-3 gap-3 sm:grid-cols-4 lg:grid-cols-5">
                    @foreach ($tier['waifus'] as $w)
                        @php
                            $owned = in_array((int) $w->id, $ownedWaifuIds, true);
                        @endphp
                        <div class="relative aspect-2/3 w-full overflow-hidden rounded-lg">
                            @if ($w->image)
                                <img
                                    class="h-full w-full object-cover {{ $owned ? '' : 'brightness-[0.3] grayscale-[50%]' }}"
                                    src="{{ asset($w->image) }}"
                                    alt="{{ $w->name }}"
                                    loading="lazy"
                                >
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-bgc-layer2 brightness-[0.3] grayscale-[50%]"></div>
                            @endif
                            @unless ($owned)
                                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-lock h-8 w-8 text-white/60" aria-hidden="true">
                                        <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                    </svg>
                                </div>
                            @endunless
                            <div class="pointer-events-none absolute right-0 bottom-0 left-0 bg-gradient-to-t from-black/80 to-transparent px-1.5 pt-4 pb-1.5">
                                <p class="truncate text-center text-xs font-medium {{ $owned ? 'text-white' : 'text-white/50' }}">{{ $w->name }}</p>
                            </div>
                            @if ($owned)
                                <button
                                    type="button"
                                    class="absolute inset-0 z-10 cursor-pointer rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-lav-500"
                                    data-profile-waifu-pick="{{ $w->id }}"
                                    data-waifu-name="{{ e($w->name) }}"
                                    data-waifu-image="{{ $w->image ? e(asset($w->image)) : '' }}"
                                    aria-label="Chọn {{ $w->name }} làm waifu đồng hành"
                                ></button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
