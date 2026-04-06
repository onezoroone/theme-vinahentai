{{-- Hộp thoại hướng dẫn dịch giả — điều khiển bằng JS (data-translator-guide-open / đóng) --}}
<div id="translator-guide-modal-root" class="hidden" data-translator-guide-root>
    <div class="fixed inset-0 z-[60] bg-black/60 backdrop-blur-sm" data-translator-guide-backdrop aria-hidden="true"></div>
    <div role="dialog"
        id="translator-guide-dialog"
        aria-modal="true"
        aria-labelledby="translator-guide-title"
        aria-describedby="translator-guide-desc"
        data-translator-guide-panel
        class="fixed left-1/2 top-1/2 z-[61] flex w-[calc(100%-2rem)] max-w-2xl -translate-x-1/2 -translate-y-1/2 flex-col gap-4 rounded-2xl border border-white/10 bg-bgc-layer1 p-6 text-white shadow-lg outline-none"
        style="z-index: 61"
        tabindex="-1">
        <div class="flex items-start justify-between gap-4">
            <h2 id="translator-guide-title" class="text-lg font-semibold pr-2">✍️ HƯỚNG DẪN 10 PHÚT TRỞ THÀNH DỊCH GIẢ TRUYỆN TRANH NGHIỆP DƯ</h2>
            <button type="button" data-translator-guide-close class="shrink-0 rounded-md border border-white/10 px-2 py-1 text-xs text-white/80 transition hover:bg-white/10">Đóng</button>
        </div>
        <div id="translator-guide-desc" class="max-h-[70vh] overflow-y-auto pr-2 text-sm leading-relaxed text-white/90 [scrollbar-color:rgba(255,255,255,0.3)_transparent] [scrollbar-width:thin]">
            <div class="mb-4">
                <div class="mb-2 text-base font-semibold text-white">🧾 TÓM TẮT NHANH (ĐỌC 1 PHÚT)</div>
                <ul class="list-disc space-y-1 pl-5">
                    <li>Dịch giả truyện tranh nghiệp dư thường kiêm luôn 2 công đoạn: Dịch (dịch thoại gốc sang tiếng Việt) và Edit (xóa thoại gốc trên ảnh, chèn bản dịch tiếng Việt vào).</li>
                    <li>Không bắt buộc phải tải Photoshop để edit. Dùng công cụ online là đủ. Photopea là web cho phép edit trực tiếp trên trình duyệt.</li>
                    <li>Bắt buộc cần biết dùng font chữ Việt hóa.</li>
                    <li>Phải xóa sạch chữ gốc trước khi chèn bản dịch. Có nhiều website hỗ trợ xóa text online.</li>
                    <li>Thoại cần rõ ràng, gọn trong bong bóng thoại.</li>
                    <li>Xuất ảnh định dạng WEBP (95–99%) để đăng web.</li>
                    <li>Hoàn thành một chương tử tế là có thể đăng truyện.</li>
                </ul>
            </div>
            <div class="mb-4 rounded-xl border border-purple-300/20 bg-purple-300/10 p-4 text-purple-200">
                <div class="mb-2 text-base font-semibold text-purple-300">🚀 Muốn trở thành Dịch Giả chính thức?</div>
                <div>Chỉ cần đăng đủ 3 truyện có chất lượng dịch ổn định, chúng tôi rất vinh dự được trao cho bạn role Dịch Giả.</div>
                <div class="mt-2">Khi đạt role, bạn sẽ nhận được quyền:</div>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    <li>⚡ Truyện được duyệt ngay lập tức – không cần chờ admin</li>
                    <li>💎 Nhận ngay 100 Dâm ngọc, và nhận Dn hàng tuần theo số views</li>
                    <li>🎖 Được công nhận là Dịch Giả chính thức trong hệ thống</li>
                    <li>🔥Bạn rất ngầu, là rồng phượng trong loài người</li>
                </ul>
                <div class="mt-2">3 truyện đạt chuẩn là bạn đã bước lên một cấp độ mới.</div>
                <div class="mt-1">Bắt đầu từ chương đầu tiên hôm nay. 💪</div>
            </div>
            <div class="mb-2 text-base font-semibold text-white">📖 HƯỚNG DẪN CHI TIẾT</div>
            <div class="space-y-3">
                <div>
                    <div class="font-semibold text-white">1️⃣ Dịch giả truyện tranh nghiệp dư là gì?</div>
                    <div>Là người tự làm toàn bộ quy trình:</div>
                    <ul class="list-disc space-y-1 pl-5">
                        <li>Dịch nội dung</li>
                        <li>Xóa thoại gốc</li>
                        <li>Chèn bản dịch tiếng Việt</li>
                        <li>Xuất ảnh để đăng web</li>
                    </ul>
                    <div>Không cần kỹ thuật cao. Chỉ cần: Gọn – Dễ đọc – Đúng ngữ cảnh.</div>
                </div>
                <div>
                    <div class="font-semibold text-white">2️⃣ Công cụ chỉnh ảnh (Không cần Photoshop)</div>
                    <div>Bạn không bắt buộc phải dùng Photoshop.</div>
                    <div>👉 Khuyên dùng: Photopea</div>
                    <div><a href="https://www.photopea.com/" target="_blank" rel="noopener noreferrer" class="text-purple-300 underline hover:text-purple-200">https://www.photopea.com/</a></div>
                    <ul class="list-disc space-y-1 pl-5">
                        <li>Chạy trực tiếp trên trình duyệt</li>
                        <li>Chỉnh ảnh</li>
                        <li>Chèn thoại tiếng Việt</li>
                        <li>Load font tùy chỉnh</li>
                    </ul>
                    <div>Với dịch giả nghiệp dư, Photopea là quá đủ.</div>
                </div>
                <div>
                    <div class="font-semibold text-white">3️⃣ Font là gì và vì sao quan trọng?</div>
                    <div>Font = kiểu chữ.</div>
                    <div>Trong truyện tranh, font ảnh hưởng trực tiếp đến cảm giác đọc.</div>
                    <ul class="list-disc space-y-1 pl-5">
                        <li>Font phù hợp → nhìn gọn gàng, chuyên nghiệp</li>
                        <li>Font sai → lỗi cơ bản</li>
                    </ul>
                    <div>👉 Đừng xem nhẹ font.</div>
                </div>
                <div>
                    <div class="font-semibold text-white">4️⃣ Bộ font cơ bản nên dùng</div>
                    <div>Bạn có thể dùng bộ font truyện tranh TeddyBear (77 font đã Việt hóa):</div>
                    <div>👉 <a href="https://drive.google.com/drive/folders/10i9ODtnokxR5yE8jOL96K0EiK0YeIpyt?usp=drive_link" target="_blank" rel="noopener noreferrer" class="break-all text-purple-300 underline hover:text-purple-200">https://drive.google.com/drive/folders/10i9ODtnokxR5yE8jOL96K0EiK0YeIpyt?usp=drive_link</a></div>
                    <div>Cách cài:</div>
                    <ul class="list-disc space-y-1 pl-5">
                        <li>Tải về → Giải nén</li>
                        <li>Chuột phải file font → Install</li>
                        <li>Trong Photopea → bật Load Fonts</li>
                    </ul>
                </div>
                <div>
                    <div class="font-semibold text-white">5️⃣ Xóa chữ gốc trong bong bóng thoại</div>
                    <div>Trước khi chèn bản dịch, phải xóa sạch chữ gốc.</div>
                    <div>Cách 1:</div>
                    <div>Xóa thủ công trong Photopea.</div>
                    <div>Cách 2:</div>
                    <div>Dùng web xóa chữ online, ví dụ:</div>
                    <div><a href="https://www.pixelcut.ai/cleanup-pictures/remove-text-from-images" target="_blank" rel="noopener noreferrer" class="break-all text-purple-300 underline hover:text-purple-200">https://www.pixelcut.ai/cleanup-pictures/remove-text-from-images</a></div>
                    <div>(Dành cho trường hợp trung bình – khó. Bôi đen chữ cần xóa, AI sẽ redraw, tức là vẽ lại các chi tiết bị mất trong quá trình xóa thoại gốc.)</div>
                    <div><a href="https://imagetranslate.ai/text-remover" target="_blank" rel="noopener noreferrer" class="break-all text-purple-300 underline hover:text-purple-200">https://imagetranslate.ai/text-remover</a></div>
                    <div>(Xóa text hàng loạt trong các bong bóng thoại đơn giản.)</div>
                    <div>Có rất nhiều web tương tự. Thử vài cái, thấy cái nào hợp tay thì dùng.</div>
                </div>
                <div>
                    <div class="font-semibold text-white">6️⃣ Gõ / chèn bản dịch tiếng Việt</div>
                    <div>Khi chèn bản dịch:</div>
                    <ul class="list-disc space-y-1 pl-5">
                        <li>Canh đều trong bong bóng thoại</li>
                        <li>Chọn size to nhất có thể để dễ đọc</li>
                        <li>Không che chi tiết quan trọng</li>
                        <li>Ưu tiên dễ đọc hơn “kỹ thuật đẹp”</li>
                    </ul>
                </div>
                <div>
                    <div class="font-semibold text-white">7️⃣ Dịch thế nào là ổn?</div>
                    <div>Quan trọng nhất: font chuẩn, edit sạch (nghiệp dư bị lem nhẹ một chút cũng không sao).</div>
                    <div>Về bản dịch, chỉ cần:</div>
                    <ul class="list-disc space-y-1 pl-5">
                        <li>Đúng ý gốc</li>
                        <li>Đúng ngữ cảnh</li>
                        <li>Tự nhiên như người Việt nói chuyện</li>
                    </ul>
                    <div>Miễn người đọc không thấy “cấn” là đạt.</div>
                </div>
                <div>
                    <div class="font-semibold text-white">8️⃣ Xuất ảnh sau khi dịch (RẤT QUAN TRỌNG)</div>
                    <div>✅ Định dạng khuyên dùng: WEBP (95–99%)</div>
                    <div>WEBP là định dạng ảnh tối ưu cho website.</div>
                    <div>Ở mức chất lượng 95–99%:</div>
                    <ul class="list-disc space-y-1 pl-5">
                        <li>Chất lượng ảnh gần như giữ nguyên so với ảnh gốc (mắt thường khó phân biệt), nhưng dung lượng có thể giảm khoảng 30–50%.</li>
                        <li>Ảnh nhẹ hơn → load nhanh hơn → đọc mượt hơn (nhất là trên điện thoại).</li>
                    </ul>
                    <div>👉 WEBP 95–99% là lựa chọn rất tốt cho web truyện.</div>
                    <div>❌ Về PNG</div>
                    <div>PNG là định dạng lưu thông tin (các bước dịch, layers,...) vì thế dung lượng cực nặng.</div>
                    <div>Không hiển thị đẹp hơn khi đăng web.</div>
                    <div>📌 PNG chỉ nên dùng để lưu file gốc. Không dùng để đăng truyện.</div>
                </div>
                <div>
                    <div class="font-semibold text-white">🧠 CÂU CHỐT</div>
                    <div>Xóa chữ gọn. Font phù hợp. Dịch tự nhiên. Xuất WEBP 95–99%.</div>
                    <div>Làm được một chương tử tế là bạn đã trở thành dịch giả truyện tranh nghiệp dư rồi.</div>
                </div>
            </div>
            <div class="mt-6 space-y-4">
                <div class="text-base font-semibold text-white">💬 KINH NGHIỆM CHIA SẺ TỪ DỊCH GIẢ</div>
                <div class="space-y-4 rounded-xl border border-sky-400/20 bg-sky-400/5 p-4">
                    <div class="text-sm font-semibold text-sky-300">✍️ Dịch Giả Kirin</div>
                    <div>
                        <div class="mb-1 font-semibold text-white">📦 Gợi ý các bộ font <span class="font-normal text-white/60">(Cre: Yuki Team)</span></div>
                        <ul class="space-y-1 pl-1">
                            <li class="flex flex-wrap items-center gap-2"><span class="w-12 shrink-0 font-semibold text-white/80">Teddy</span><a href="https://drive.google.com/drive/folders/0B82qhrQFHEqUNlZ2Z3hwMk1lbnc?resourcekey=0-Lm6B35SiuZom-fnO4m1WLg" target="_blank" rel="noopener noreferrer" class="min-w-0 truncate text-purple-300 underline hover:text-purple-200">Google Drive — Teddy</a></li>
                            <li class="flex flex-wrap items-center gap-2"><span class="w-12 shrink-0 font-semibold text-white/80">MTO</span><a href="https://drive.google.com/drive/folders/1Z9d0F31su97K9_uY3FEdJJBjgwzXhZf8" target="_blank" rel="noopener noreferrer" class="min-w-0 truncate text-purple-300 underline hover:text-purple-200">Google Drive — MTO</a></li>
                            <li class="flex flex-wrap items-center gap-2"><span class="w-12 shrink-0 font-semibold text-white/80">UEE</span><a href="https://drive.google.com/drive/folders/14sPPvxRYDrob49jl_PBSQLIzN_WuJrKj" target="_blank" rel="noopener noreferrer" class="min-w-0 truncate text-purple-300 underline hover:text-purple-200">Google Drive — UEE</a></li>
                            <li class="flex flex-wrap items-center gap-2"><span class="w-12 shrink-0 font-semibold text-white/80">SVN</span><a href="https://drive.google.com/drive/folders/14i9WlvW4iWEG94OkZFkF1v9P5-DsrKHH" target="_blank" rel="noopener noreferrer" class="min-w-0 truncate text-purple-300 underline hover:text-purple-200">Google Drive — SVN</a></li>
                        </ul>
                    </div>
                    <div>
                        <div class="mb-1 font-semibold text-white">🖊️ Gợi ý dùng font theo loại thoại</div>
                        <ul class="space-y-1.5 pl-1">
                            <li><span class="text-white/80">Thoại bình thường:</span> <span class="text-white">Astro City</span> hoặc <span class="text-white">AnimeAce3</span> <span class="text-white/50">(Teddy)</span></li>
                            <li><span class="text-white/80">La lớn / quát:</span> <span class="text-white">ObelixPro TB</span> <span class="text-white/50">(Teddy)</span></li>
                            <li><span class="text-white/80">Lời kể chuyện:</span> <span class="text-white">DigitalStrip 2</span> <span class="text-white/50">(Teddy)</span></li>
                            <li><span class="text-white/80">Suy nghĩ nhân vật:</span> <span class="text-white">Wildwords2 TB Bold Italic</span> <span class="text-white/50">(Teddy)</span></li>
                            <li><span class="text-white/80">Rên la 18+ (Ah, Oh, Ưm…):</span> <a href="https://drive.google.com/drive/folders/1gn1BFp9814kJXEn8byp23QLLI76iN_td" target="_blank" rel="noopener noreferrer" class="text-purple-300 underline hover:text-purple-200">SJ Brightest Melody</a> hoặc <a href="https://drive.google.com/drive/folders/1fNpkDs_TiyFRTjr-bNwZwf6DRQJiUJ8Y" target="_blank" rel="noopener noreferrer" class="text-purple-300 underline hover:text-purple-200">SJ CDX Amraam</a></li>
                        </ul>
                    </div>
                    <div>
                        <div class="mb-1 font-semibold text-white">⚡ Bổ sung nâng cao</div>
                        <ul class="space-y-1.5 pl-1">
                            <li><span class="text-white/80">Run rẩy / lo sợ:</span> <a href="https://drive.google.com/drive/folders/1Z_6vx89qjT1X7NqCaNWMbrHgPNcjFfJy" target="_blank" rel="noopener noreferrer" class="text-purple-300 underline hover:text-purple-200">SJ Fluxus LT</a></li>
                            <li><span class="text-white/80">Ma quỷ / kinh dị:</span> <a href="https://drive.google.com/drive/folders/1JONnAbUzqlWBszH5yIldfpJyPdCUI9ot" target="_blank" rel="noopener noreferrer" class="text-purple-300 underline hover:text-purple-200">SJ CCCarryOnScreaming</a> hoặc <a href="https://www.svnfont.com/viet-hoa-svn-another-danger/" target="_blank" rel="noopener noreferrer" class="text-purple-300 underline hover:text-purple-200">SVN-Another Danger</a></li>
                        </ul>
                    </div>
                    <div>
                        <div class="mb-1 font-semibold text-white">🛠️ Kinh nghiệm thực chiến</div>
                        <ul class="list-disc space-y-2 pl-5">
                            <li><span class="text-white">Thoại đè lên background bắt buộc phải có Stroke</span> để chữ nổi bật. <span class="text-white/60">(Photoshop: Tab Layer → Double click vào layer Text → Layer Style → Stroke → Position: Outside. Tùy chỉnh Size và Color theo ý muốn.)</span></li>
                            <li><span class="text-white">Thoại trong bong bóng thoại phải căn giữa</span> — trừ một số trường hợp đặc biệt như giới thiệu nhân vật thì căn trái như bình thường.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
