{{-- Modal hướng dẫn Triệu Hồi Waifu — bật bằng [data-waifu-summon-guide-trigger], điều khiển trong main.js --}}
<div data-waifu-summon-guide-modal
    class="fixed inset-0 z-[10050] hidden"
    aria-hidden="true"
    data-state="closed">
    <div data-waifu-summon-guide-overlay
        data-state="closed"
        class="data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 fixed inset-0 z-0 bg-black/50"
        aria-hidden="true"></div>
    <div role="dialog"
        id="waifu-summon-guide-dialog"
        aria-modal="true"
        aria-labelledby="waifu-summon-guide-title"
        aria-describedby="waifu-summon-guide-desc"
        data-state="closed"
        tabindex="-1"
        class="data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[state=closed]:slide-out-to-left-1/2 data-[state=closed]:slide-out-to-top-[48%] data-[state=open]:slide-in-from-left-1/2 data-[state=open]:slide-in-from-top-[48%] fixed top-[50%] left-[50%] z-[1] max-h-[90vh] w-[90vw] max-w-[615px] translate-x-[-50%] translate-y-[-50%] overflow-y-auto duration-200 sm:w-[615px]">
        <div class="bg-bgc-layer1 outline-bd-default inline-flex w-full flex-col items-center justify-center gap-6 rounded-2xl p-4 outline outline-offset-[-1px]">
            <div class="flex w-full flex-col items-start justify-start gap-4">
                <div class="flex w-full flex-col items-center justify-start gap-4">
                    <h2 id="waifu-summon-guide-title" class="text-txt-primary w-full text-center text-xl leading-9 font-semibold sm:text-3xl">Hướng dẫn Triệu Hồi Waifu</h2>
                    <div class="h-0 w-24 outline-1 outline-offset-[-0.50px] outline-white/20 sm:w-96" aria-hidden="true"></div>
                </div>
                <div id="waifu-summon-guide-desc" class="w-full justify-center text-sm leading-normal sm:text-base">
                    <div class="text-txt-secondary font-medium">Chào mừng bạn đến với tính năng<span class="text-txt-primary font-medium"> Triệu Hồi Waifu</span> — nơi bạn có thể <span class="text-txt-primary font-medium">triệu hồi</span> những waifu xinh đẹp, quyến rũ và hiếm có, để tạo nên dàn harem độc nhất vô nhị của riêng bạn!</div>
                    <div class="text-txt-secondary mt-2 font-medium">Hãy bắt đầu hành trình sưu tầm và mở rộng bộ sưu tập waifu mơ ước ngay hôm nay!</div>
                </div>
                <div class="w-full justify-center text-sm leading-normal sm:text-base">
                    <div class="text-txt-primary mb-2 text-xl font-medium">Cách nhận Dâm Ngọc</div>
                    <div class="text-txt-secondary flex flex-col gap-1.5 font-medium">
                        <div>Dâm Ngọc rơi ngẫu nhiên trong quá trình đọc truyện khi dâm khí hội tụ.</div>
                        <div>Lượt like truyện, comment đầu tiên trong ngày sẽ +1 dâm ngọc.</div>
                        <div>Hoặc có thể nhận Dâm Ngọc khi: Là người đầu tiên Báo Lỗi hợp lệ, tham gia event, boost server Discord VinaHentai nhận 50 Dâm Ngọc cho mỗi boost.</div>
                    </div>
                </div>
                <div class="w-full justify-center text-sm leading-normal sm:text-base">
                    <div class="text-txt-primary mb-2 text-xl font-medium">Banner Thường</div>
                    <ul class="text-txt-secondary list-disc space-y-2 pl-5 font-medium">
                        <li><span>1 lượt: </span><span class="text-txt-focus font-medium">tốn 1 Dâm Ngọc</span></li>
                        <li><span>10 lượt: </span><span class="text-txt-focus font-medium">tốn 9 Dâm Ngọc và chắc chắn ra waifu 3 sao</span></li>
                        <li><span class="text-txt-focus font-medium">Ghi chú: </span>Tỉ lệ Triệu Hồi sẽ tăng dần theo Tu Vi, ví dụ tại cảnh giới Lọ Vương, tỉ lệ ra WF5S cao gấp 4 lần so với Nhập Lọ</li>
                        <li><span class="text-txt-focus font-medium">Đặc biệt: </span>các lượt quay <span class="text-txt-focus font-medium">được tích lũy</span> để nhận thưởng theo các mốc dưới đây.</li>
                    </ul>
                </div>
                <div class="text-txt-primary w-full justify-center text-sm leading-normal font-medium sm:text-base">Mốc Quay Tích Lũy</div>
                <div class="w-full flex-col items-start justify-start overflow-hidden">
                    <div class="bg-bgc-layer2 outline-bd-default flex w-full outline-1 outline-offset-[-1px]">
                        <div class="border-bd-default flex flex-[2] items-center justify-center gap-2.5 border-r px-2 py-2 sm:px-3">
                            <div class="text-txt-primary justify-center text-xs font-medium sm:text-base">Mốc quay</div>
                        </div>
                        <div class="flex flex-[3] items-center justify-center gap-2.5 px-2 py-2 sm:px-3">
                            <div class="text-txt-primary justify-center text-xs font-medium sm:text-base">Phần thưởng</div>
                        </div>
                    </div>
                    <div class="border-bd-default outline-bd-default flex w-full border-b outline-1 outline-offset-[-1px]">
                        <div class="border-bd-default flex flex-[2] items-center justify-center gap-2.5 border-r px-2 py-2 sm:px-3">
                            <div class="text-txt-secondary justify-center text-xs font-medium sm:text-base">50 lượt</div>
                        </div>
                        <div class="flex flex-[3] items-center justify-center gap-2.5 px-2 py-2 sm:px-3">
                            <div class="text-txt-secondary justify-center text-xs font-medium sm:text-base">+5 Vàng</div>
                        </div>
                    </div>
                    <div class="border-bd-default outline-bd-default flex w-full border-b outline-1 outline-offset-[-1px]">
                        <div class="border-bd-default flex flex-[2] items-center justify-center gap-2.5 border-r px-2 py-2 sm:px-3">
                            <div class="text-txt-secondary justify-center text-xs font-medium sm:text-base">100 lượt</div>
                        </div>
                        <div class="flex flex-[3] items-center justify-center gap-2.5 px-2 py-2 sm:px-3">
                            <div class="text-txt-secondary justify-center text-xs font-medium sm:text-base">1 Waifu 3★ (khi mở quà)</div>
                        </div>
                    </div>
                    <div class="border-bd-default outline-bd-default flex w-full border-b outline-1 outline-offset-[-1px]">
                        <div class="border-bd-default flex flex-[2] items-center justify-center gap-2.5 border-r px-2 py-2 sm:px-3">
                            <div class="text-txt-secondary justify-center text-xs font-medium sm:text-base">200 lượt</div>
                        </div>
                        <div class="flex flex-[3] items-center justify-center gap-2.5 px-2 py-2 sm:px-3">
                            <div class="text-txt-secondary justify-center text-xs font-medium sm:text-base">1 Waifu 4★ (khi mở quà)</div>
                        </div>
                    </div>
                    <div class="border-bd-default outline-bd-default flex w-full border-b outline-1 outline-offset-[-1px]">
                        <div class="border-bd-default flex flex-[2] items-center justify-center gap-2.5 border-r px-2 py-2 sm:px-3">
                            <div class="text-txt-secondary justify-center text-xs font-medium sm:text-base">500 lượt</div>
                        </div>
                        <div class="flex flex-[3] items-center justify-center gap-2.5 px-2 py-2 sm:px-3">
                            <div class="text-txt-secondary justify-center text-xs font-medium sm:text-base">1 Waifu 5★ (khi mở quà)</div>
                        </div>
                    </div>
                </div>
                <div class="inline-flex w-full items-start justify-start gap-4">
                    <button type="button"
                        data-waifu-summon-guide-close
                        class="outline-lav-500 hover:bg-lav-500/5 flex flex-1 cursor-pointer items-center justify-center gap-2.5 rounded-xl px-4 py-3 shadow-[0px_4px_8.899999618530273px_0px_rgba(146,53,190,0.25)] outline outline-offset-[-1px] transition-colors">
                        <span class="text-txt-focus justify-center text-center text-sm leading-tight font-semibold">Đóng</span>
                    </button>
                    <button type="button"
                        data-waifu-summon-guide-close
                        class="flex flex-1 cursor-pointer items-center justify-center gap-2.5 rounded-xl bg-gradient-to-b from-fuchsia-300 to-fuchsia-400 px-4 py-3 shadow-[0px_4px_8.899999618530273px_0px_rgba(196,69,255,0.25)] transition-colors hover:from-fuchsia-400 hover:to-fuchsia-500">
                        <span class="justify-center text-center text-sm leading-tight font-semibold text-black">Đã hiểu</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
