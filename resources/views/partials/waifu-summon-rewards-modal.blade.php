{{-- Modal Nhận thưởng: thanh mốc + lịch sử triệu hồi + nhận quà mốc (POST). --}}
@php
    use App\WaifuSummon\WaifuSummonConfig;

    $cap = max(1, (int) ($waifuRewardPityCap ?? WaifuSummonConfig::PITY_CAP));
    $summonsTotal = max(0, (int) ($rewardSummonsTotal ?? 0));
    $pct = min(100.0, ($summonsTotal / $cap) * 100.0);
    $markerLeftPct = min(99.0, $pct);
    $remainingToCap = max(0, $cap - $summonsTotal);
    $claimedSet = $waifuSummonClaimedMilestones ?? [];
    $milestoneKeys = WaifuSummonConfig::milestoneKeys();
    $milestoneRewards = WaifuSummonConfig::MILESTONE_REWARDS;
    $milestones = array_map(static function (int $n) use ($cap, $milestoneRewards) {
        return [
            'n' => $n,
            'left' => min(99.0, round(($n / $cap) * 100, 2)),
            'points' => (int) ($milestoneRewards[$n]['points'] ?? 0),
        ];
    }, $milestoneKeys);
@endphp
<div data-waifu-summon-rewards-modal
    class="fixed inset-0 z-[10050] hidden"
    aria-hidden="true"
    data-state="closed">
    <div data-waifu-summon-rewards-overlay
        data-state="closed"
        class="fixed inset-0 z-0 bg-black/50"
        aria-hidden="true"></div>
    <div role="dialog"
        id="waifu-summon-rewards-dialog"
        aria-modal="true"
        aria-labelledby="waifu-summon-rewards-title"
        aria-describedby="waifu-summon-rewards-summary"
        data-state="closed"
        tabindex="-1"
        class="fixed top-1/2 left-1/2 z-50 max-h-[90vh] w-full max-w-[615px] -translate-x-1/2 -translate-y-1/2 transform overflow-auto">
        <div class="bg-bgc-layer1 border-bd-default flex flex-col gap-5 rounded-2xl border p-4 sm:p-6">
            <div class="flex flex-col items-center gap-4">
                <h1 id="waifu-summon-rewards-title"
                    class="text-txt-primary text-center font-sans text-xl leading-9 font-semibold sm:text-3xl">Nhận Thưởng</h1>

                <div class="relative mt-2 h-[120px] w-full max-w-[560px]">
                    <div class="absolute top-1/2 right-0 left-0 h-2 -translate-y-1/2 rounded-full bg-bgc-layer2"></div>
                    <div class="absolute top-1/2 left-0 h-2 max-w-full -translate-y-1/2 rounded-full bg-gradient-to-r from-[#DD94FF] to-[#D373FF] shadow-[0_4px_10px_rgba(221,82,255,0.30)] transition-all"
                        style="width: {{ $pct }}%;"></div>
                    @foreach ($milestones as $m)
                        @php
                            $reached = $summonsTotal >= $m['n'];
                            $claimedThis = in_array($m['n'], $claimedSet, true);
                            $canClaim = $reached && ! $claimedThis && auth()->check();
                        @endphp
                        <div class="absolute flex flex-col items-center"
                            style="left: {{ $m['left'] }}%; transform: translateX(-50%); top: calc(50% - 60px);">
                            @if ($canClaim)
                                <button type="button"
                                    data-waifu-summon-milestone-claim
                                    data-milestone="{{ $m['n'] }}"
                                    title="Nhận {{ $m['points'] }} Dâm Ngọc (mốc {{ $m['n'] }} lượt)"
                                    class="relative flex h-10 w-10 cursor-pointer items-center justify-center rounded-lg border-2 border-btn-primary bg-btn-primary/15 shadow-[0_4px_12px_rgba(221,82,255,0.25)] ring-2 ring-btn-primary/40 transition-transform hover:scale-105">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-txt-primary" aria-hidden="true">
                                        <rect x="3" y="8" width="18" height="4" rx="1"></rect>
                                        <path d="M12 8v13"></path>
                                        <path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"></path>
                                        <path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"></path>
                                    </svg>
                                    <span class="absolute -bottom-5 whitespace-nowrap text-[10px] font-semibold text-txt-focus sm:text-xs"
                                        data-waifu-summon-milestone-label>{{ $m['n'] }}</span>
                                </button>
                            @else
                                <button type="button"
                                    disabled
                                    title="{{ $claimedThis ? 'Đã nhận' : 'Chưa đủ lượt' }} — mốc {{ $m['n'] }}"
                                    class="relative flex h-10 w-10 cursor-not-allowed items-center justify-center rounded-lg border border-transparent transition-all {{ $reached ? 'bg-gradient-to-b from-[#DD94FF] to-[#D373FF]' : 'bg-bgc-layer2 opacity-70' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-txt-primary" aria-hidden="true">
                                        <rect x="3" y="8" width="18" height="4" rx="1"></rect>
                                        <path d="M12 8v13"></path>
                                        <path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"></path>
                                        <path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"></path>
                                    </svg>
                                    <div class="absolute -bottom-3 text-xs font-semibold text-txt-primary">{{ $m['n'] }}</div>
                                </button>
                            @endif
                        </div>
                    @endforeach
                    <div class="absolute flex flex-col items-center"
                        style="left: {{ $markerLeftPct }}%; transform: translateX(-50%); top: calc(50% - 22px);">
                        <div class="h-8 w-1 rounded-sm bg-btn-primary shadow-[0_4px_10px_rgba(221,82,255,0.12)]"></div>
                        <div class="mt-1 text-center text-sm font-semibold text-txt-focus">{{ $summonsTotal }}</div>
                    </div>
                </div>

                <div id="waifu-summon-rewards-summary" class="bg-bgc-layer2 w-full max-w-[560px] rounded-lg px-3 py-2">
                    <div class="flex items-start gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-4 w-4 flex-shrink-0 text-txt-secondary" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 16v-4"></path>
                            <path d="M12 8h.01"></path>
                        </svg>
                        <div class="flex-1 text-center sm:text-left">
                            @if ($remainingToCap > 0)
                                <span class="text-txt-primary font-sans text-sm leading-6 font-medium sm:text-base">Còn
                                    <strong class="text-txt-focus">{{ $remainingToCap }}</strong> lượt triệu hồi nữa để đạt mốc pity
                                    <strong>{{ $cap }}</strong> (gợi ý: Waifu 5★).</span>
                            @else
                                <span class="text-txt-primary font-sans text-sm leading-6 font-medium sm:text-base">Bạn đã đạt
                                    <strong class="text-txt-focus">{{ $cap }}</strong> lượt trên thanh tiến độ — nhận quà mốc nếu còn.</span>
                            @endif
                            <p class="mt-1 text-xs leading-normal text-txt-secondary">Mỗi lượt triệu hồi (mỗi thẻ trong x10 tính 1 lượt) được ghi trong bảng lịch sử bên dưới.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-txt-primary mb-2 font-sans text-base font-semibold sm:text-lg">Lịch sử nhận quà</h2>
                <div class="border-bd-default overflow-hidden rounded-lg border">
                    <div class="bg-bgc-layer2 border-bd-default grid grid-cols-3 border-b">
                        <div class="border-bd-default border-r px-3 py-2">
                            <div class="text-txt-primary font-sans text-base leading-6 font-medium">Thời gian</div>
                        </div>
                        <div class="border-bd-default border-r px-3 py-2">
                            <div class="text-txt-primary font-sans text-base leading-6 font-medium">Kết quả</div>
                        </div>
                        <div class="px-3 py-2">
                            <div class="text-txt-primary font-sans text-base leading-6 font-medium">Độ hiếm</div>
                        </div>
                    </div>
                    {{-- Lịch sử: tải qua API khi mở modal (không đổi query URL trang). --}}
                    <div data-waifu-summon-rewards-rows>
                        @guest
                            <div class="px-3 py-8 text-center font-sans text-sm font-medium text-txt-secondary">Đăng nhập để xem lịch sử triệu hồi.</div>
                        @endguest
                    </div>
                </div>
            </div>

            <div data-waifu-summon-rewards-pagination class="flex flex-col items-center justify-center self-stretch"></div>

            <div class="flex flex-col gap-3 sm:flex-row sm:gap-4">
                <button type="button"
                    data-waifu-summon-rewards-close
                    class="border-btn-primary text-txt-focus hover:bg-bgc-layer2 flex-1 rounded-xl border px-4 py-3 font-sans text-sm leading-5 font-semibold shadow-[0px_4px_8.9px_rgba(146,53,190,0.25)] transition-colors">
                    Đóng
                </button>
                <button type="button"
                    data-waifu-summon-rewards-close
                    class="text-txt-inverse flex-1 rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] px-4 py-3 font-sans text-sm leading-5 font-semibold shadow-[0px_4px_8.9px_rgba(195,68,255,0.25)] transition-opacity hover:opacity-90">
                    Đã hiểu
                </button>
            </div>
        </div>
    </div>
</div>
