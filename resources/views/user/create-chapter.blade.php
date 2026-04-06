@extends('theme-vinahentai::layout.main')

@section('body')
    @php
        $mangaPreviewUrl = route('mangas.preview', $manga->slug);
        $chapterCheckUrl = route('user.chapter-check', ['mangaSlug' => $manga->slug]);
        $tzHenGio = 'Asia/Ho_Chi_Minh';
        $ngayHenGioLuaChon = collect(range(0, 6))->map(function (int $i) use ($tzHenGio): array {
            $d = \Carbon\Carbon::now($tzHenGio)->addDays($i)->startOfDay();

            return [
                'value' => $d->format('Y-m-d'),
                'label' => $d->format('d/m/Y'),
            ];
        });
        $laChuongSua = isset($chapter) && $chapter instanceof \App\Models\Chapter;
        $chapterFormPayload = null;
        $chapterUpdateUrl = '';
        if ($laChuongSua) {
            $chapterUpdateUrl = route('user.update-chapter', ['mangaSlug' => $manga->slug, 'chapter' => $chapter->id]);
            $urls = [];
            $servers = $chapter->image_servers;
            if (is_array($servers)) {
                foreach ($servers as $arr) {
                    if (is_array($arr)) {
                        foreach (\Illuminate\Support\Arr::flatten($arr) as $u) {
                            if (is_string($u) && trim($u) !== '') {
                                $urls[] = $u;
                            }
                        }
                    }
                }
            }
            $pub = $chapter->published_at;
            if ($pub !== null && $pub->isFuture()) {
                $plPublish = 'scheduled';
                $local = $pub->clone()->timezone($tzHenGio);
                $plDate = $local->format('Y-m-d');
                $plHour = (int) $local->format('G');
                $plMinute = (int) $local->format('i');
            } else {
                $plPublish = 'immediate';
                $plDate = '';
                $plHour = 0;
                $plMinute = 0;
            }
            $chapterFormPayload = [
                'title' => (string) ($chapter->title ?? ''),
                'publishMode' => $plPublish,
                'scheduleDate' => $plDate,
                'scheduleHour' => $plHour,
                'scheduleMinute' => $plMinute,
                'pageUrls' => $urls,
            ];
        }
    @endphp
    <div
        id="chapter-create-root"
        class="flex flex-col"
        data-chapter-check-url="{{ $chapterCheckUrl }}"
        data-chapter-store-url="{{ route('user.store-chapter', ['mangaSlug' => $manga->slug]) }}"
        @if ($laChuongSua)
            data-chapter-update-url="{{ $chapterUpdateUrl }}"
            data-chapter-initial='@json($chapterFormPayload)'
        @endif
    >
        {{-- Chế độ chỉnh sửa: tải ảnh / thư mục / kéo thả + lưới thumbnail --}}
        <div
            id="chapter-editor-panel"
            class="mx-auto flex w-full max-w-[951px] flex-col gap-6 px-4 py-6 sm:px-6 lg:px-0"
            data-chapter-editor-panel
        >
            <div class="flex flex-col gap-6">
                <h1 class="text-txt-primary text-left font-sans text-2xl leading-9 font-semibold [text-shadow:_0px_0px_4px_rgb(182_25_255_/_0.59)] sm:text-3xl">{{ $laChuongSua ? 'Sửa chương' : 'Đăng chương mới' }}</h1>
                <form id="chapter-create-form" class="bg-bgc-layer1 border-bd-default flex flex-col gap-6 rounded-xl border p-4 shadow-[0px_4px_4px_0px_rgba(0,0,0,0.25)] sm:p-6" action="#" method="post" onsubmit="return false;">
                    @csrf
                    <input type="hidden" name="manga_slug" value="{{ $manga->slug }}">
                    <div class="flex flex-col gap-2">
                        <label for="chapter-create-title" class="flex items-center gap-1.5 text-base font-semibold text-txt-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open text-txt-secondary h-4 w-4" aria-hidden="true">
                                <path d="M12 7v14"></path>
                                <path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"></path>
                            </svg>
                            <span>Tiêu đề</span>
                        </label>
                        <div class="flex flex-col gap-2">
                            <input
                                id="chapter-create-title"
                                placeholder="Nhập tên chương (bắt buộc)"
                                class="bg-bgc-layer2 border-bd-default text-txt-secondary placeholder:text-txt-secondary focus:border-lav-500 w-full rounded-xl border px-3 py-2.5 text-base font-medium focus:outline-none"
                                maxlength="100"
                                type="text"
                                value="{{ $laChuongSua ? e($chapter->title) : '' }}"
                                name="title"
                                required
                                minlength="1"
                                data-chapter-title-input
                            >
                            <div class="text-right text-sm font-medium text-txt-secondary"><span data-chapter-title-count>0</span>/100</div>
                            <p class="text-xs text-txt-secondary">Tên chương bắt buộc. Đăng / lưu cần <strong class="text-txt-primary">ít nhất 2 ảnh</strong> (hoặc giữ nguyên ảnh đã lưu khi chỉ sửa tiêu đề hoặc lịch).</p>
                            @if ($laChuongSua)
                                <p class="text-xs text-txt-secondary">Đổi toàn bộ ảnh: xóa hết ảnh trong lưới rồi tải bộ mới — không trộn ảnh cũ (URL) với file mới.</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text text-txt-secondary h-4 w-4" aria-hidden="true">
                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                                <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                                <path d="M10 9H8"></path>
                                <path d="M16 13H8"></path>
                                <path d="M16 17H8"></path>
                            </svg>
                            <span class="text-base font-semibold text-txt-primary">Tải truyện lên</span>
                        </div>
                        <div class="relative flex w-full flex-col items-center justify-center gap-2">
                            <div
                                class="bg-bgc-layer2 border-bd-default relative flex h-full min-h-[240px] w-full flex-1 cursor-pointer items-center justify-center rounded-xl border border-dashed px-3 py-2.5"
                                aria-label="Khu vực kéo-thả ảnh"
                                data-chapter-dropzone
                            >
                                <input id="chapter-pages-input" class="sr-only" type="file" name="pages[]" multiple accept="image/*" data-chapter-files-input>
                                <input id="chapter-folder-input" class="sr-only" type="file" name="pages_folder[]" multiple accept="image/*" webkitdirectory data-chapter-folder-input>
                                <div class="flex flex-col items-center gap-3 lg:hidden">
                                    <button type="button" class="flex cursor-pointer items-center gap-1.5 rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] px-4 py-3 shadow-[0px_4px_8.899999618530273px_0px_rgba(196,69,255,0.25)] transition-colors hover:from-[#D373FF] hover:to-[#C962F9] disabled:cursor-not-allowed disabled:opacity-50" data-chapter-trigger-files>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-upload h-5 w-5 text-black" aria-hidden="true">
                                            <path d="M12 3v12"></path>
                                            <path d="m17 8-5-5-5 5"></path>
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        </svg>
                                        <span class="text-center text-sm font-semibold text-black">Tải ảnh lên</span>
                                    </button>
                                    <button type="button" class="flex cursor-pointer items-center gap-1.5 rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] px-4 py-3 shadow-[0px_4px_8.899999618530273px_0px_rgba(196,69,255,0.25)] transition-colors hover:from-[#D373FF] hover:to-[#C962F9] disabled:cursor-not-allowed disabled:opacity-50" data-chapter-trigger-folder>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-upload h-5 w-5 text-black" aria-hidden="true">
                                            <path d="M12 3v12"></path>
                                            <path d="m17 8-5-5-5 5"></path>
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        </svg>
                                        <span class="text-center text-sm font-semibold text-black">Chọn thư mục</span>
                                    </button>
                                    <span class="text-center text-sm font-medium text-txt-primary">Kéo-thả ảnh hoặc cả thư mục (Chrome / Edge / Safari)</span>
                                </div>
                                <div class="absolute inset-0 hidden flex-col items-center justify-center gap-3 lg:flex" data-chapter-dropzone-inner-lg>
                                    <button type="button" class="flex cursor-pointer items-center gap-1.5 rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] px-4 py-3 shadow-[0px_4px_8.899999618530273px_0px_rgba(196,69,255,0.25)] transition-colors hover:from-[#D373FF] hover:to-[#C962F9] disabled:cursor-not-allowed disabled:opacity-50" data-chapter-trigger-files-lg>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-upload h-5 w-5 text-black" aria-hidden="true">
                                            <path d="M12 3v12"></path>
                                            <path d="m17 8-5-5-5 5"></path>
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        </svg>
                                        <span class="text-center text-sm font-semibold text-black">Tải ảnh lên</span>
                                    </button>
                                    <button type="button" class="mt-2 flex cursor-pointer items-center gap-1.5 rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] px-4 py-3 shadow-[0px_4px_8.899999618530273px_0px_rgba(196,69,255,0.25)] transition-colors hover:from-[#D373FF] hover:to-[#C962F9] disabled:cursor-not-allowed disabled:opacity-50" data-chapter-trigger-folder-lg>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-upload h-5 w-5 text-black" aria-hidden="true">
                                            <path d="M12 3v12"></path>
                                            <path d="m17 8-5-5-5 5"></path>
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        </svg>
                                        <span class="text-center text-sm font-semibold text-black">Chọn thư mục</span>
                                    </button>
                                    <span class="text-center text-sm font-medium text-txt-primary">Hoặc kéo-thả ảnh / thư mục vào đây</span>
                                </div>
                            </div>
                            <p class="text-center text-sm font-medium leading-tight text-txt-secondary">Mỗi ảnh không nên vượt quá 5 MB, và tốt nhất hãy giữ dưới 3 MB.</p>
                            <p class="text-center text-sm font-medium leading-tight text-txt-secondary">Sau khi chỉnh sửa bằng Photoshop, nên export ảnh dưới định dạng WebP với Quality 95%. Ở đa số trường hợp, mức này vẫn giữ chất lượng hiển thị gần như không khác biệt đáng kể, nhưng dung lượng sẽ nhẹ hơn rõ rệt.</p>
                            <p class="text-center text-sm font-medium leading-tight text-txt-secondary">Phần lớn người dùng truy cập bằng điện thoại, trong khi không phải thiết bị nào cũng đủ mạnh và kết nối mạng nào cũng ổn định. Nếu ảnh quá nặng, web cũng khó bù lại được trải nghiệm sử dụng.</p>
                            <p class="text-center text-sm font-medium leading-tight text-txt-secondary">Ảnh dung lượng lớn khiến trang tải chậm, tốn data, dễ giật lag và ảnh hưởng trực tiếp đến trải nghiệm trên mobile. Nói ngắn gọn, ảnh nặng không cải thiện độ nét được bao nhiêu, nhưng lại làm người dùng chờ lâu hơn rất nhiều.</p>
                            <div class="bg-bgc-layer2 border-bd-default mt-6 w-full rounded-xl border p-6">
                                <div class="text-lav-500 mb-5 flex items-center gap-2 text-lg font-semibold">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings2 lucide-settings-2 h-5 w-5" aria-hidden="true">
                                        <path d="M20 7h-9"></path>
                                        <path d="M14 17H5"></path>
                                        <circle cx="17" cy="17" r="3"></circle>
                                        <circle cx="7" cy="7" r="3"></circle>
                                    </svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock3 lucide-clock-3 h-5 w-5" aria-hidden="true">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16.5 12"></polyline>
                                    </svg>
                                    <span>Cài đặt hiển thị &amp; đăng tải</span>
                                </div>
                                <div class="flex flex-col gap-6 md:flex-row md:items-start md:gap-8">
                                    <div class="md:w-1/2">
                                        <div class="text-lav-500 mb-4 text-base font-semibold">Chọn kiểu watermark</div>
                                        <div class="flex flex-col gap-4">
                                            <label class="flex cursor-pointer items-start gap-3">
                                                <input class="mt-1 h-4 w-4" type="radio" value="glow" name="watermarkStyle">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-semibold text-txt-primary">Thanh gradient tối → cam</span>
                                                    <span class="mt-1 text-xs text-txt-secondary opacity-70">Nền gradient ngang (đen mờ sang cam), chữ trắng căn giữa thanh — giống banner quảng bá nổi bật.</span>
                                                </div>
                                            </label>
                                            <label class="flex cursor-pointer items-start gap-3">
                                                <input class="mt-1 h-4 w-4" type="radio" value="stroke" name="watermarkStyle" checked>
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-semibold text-txt-primary">Thanh tối nửa trong suốt (mặc định)</span>
                                                    <span class="mt-1 text-xs text-txt-secondary opacity-70">Thanh đen mờ full chiều ngang, viền trên loang nhẹ vào ảnh; chữ trắng viền đen dễ đọc.</span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="hidden self-stretch border-l border-white/10 md:block"></div>
                                    <div class="md:w-1/2">
                                        <div class="text-lav-500 mb-4 text-base font-semibold">Thời điểm đăng</div>
                                        <div class="flex flex-col gap-3">
                                            <label class="flex cursor-pointer items-center gap-2 text-sm text-txt-primary">
                                                <input class="h-4 w-4" type="radio" name="publishMode" value="immediate" checked data-chapter-publish-mode>Đăng ngay
                                            </label>
                                            <label class="flex cursor-pointer items-center gap-2 text-sm text-txt-primary">
                                                <input class="h-4 w-4" type="radio" name="publishMode" value="scheduled" data-chapter-publish-mode>Hẹn giờ đăng
                                            </label>
                                        </div>

                                        <div class="mt-4 hidden pl-2" data-chapter-schedule-fields>
                                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                <div class="flex flex-col gap-1">
                                                    <label class="text-xs text-txt-secondary" for="chapter-schedule-date">Ngày</label>
                                                    <select
                                                        id="chapter-schedule-date"
                                                        class="bg-bgc-layer1 border-bd-default rounded-lg border px-3 py-2 text-sm text-txt-primary"
                                                        data-chapter-schedule-date
                                                    >
                                                        <option value="">Chọn ngày</option>
                                                        @foreach ($ngayHenGioLuaChon as $opt)
                                                            <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-xs text-txt-secondary">Giờ đăng ({{ $tzHenGio }})</span>
                                                    <div class="flex items-center gap-2">
                                                        <input
                                                            id="chapter-schedule-hour"
                                                            name="schedule_hour"
                                                            type="text"
                                                            maxlength="2"
                                                            inputmode="numeric"
                                                            placeholder="00"
                                                            value="00"
                                                            class="bg-bgc-layer1 border-bd-default w-full rounded-lg border px-3 py-2 text-center text-sm text-txt-primary"
                                                            data-chapter-schedule-hour
                                                            aria-label="Giờ"
                                                        >
                                                        <span class="text-sm text-txt-secondary">:</span>
                                                        <input
                                                            id="chapter-schedule-minute"
                                                            name="schedule_minute"
                                                            type="text"
                                                            maxlength="2"
                                                            inputmode="numeric"
                                                            placeholder="00"
                                                            value="00"
                                                            class="bg-bgc-layer1 border-bd-default w-full rounded-lg border px-3 py-2 text-center text-sm text-txt-primary"
                                                            data-chapter-schedule-minute
                                                            aria-label="Phút"
                                                        >
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-2 hidden text-xs text-red-400" data-chapter-schedule-error>Vui lòng chọn ngày đăng</div>
                                            <div class="mt-1 text-xs text-txt-secondary opacity-80">Lịch hẹn theo giờ Việt Nam; chọn trong 7 ngày tới (kể cả hôm nay).</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 w-full">
                                <p class="mb-3 text-center text-sm text-txt-secondary empty:hidden" data-chapter-pages-empty>Kéo thả để sắp xếp thứ tự trang trong lưới bên dưới.</p>
                                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4" data-chapter-pages-grid></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between sm:gap-0">
                <a class="hover:bg-lav-500/5 inline-flex cursor-pointer items-center gap-1.5 self-start rounded-xl px-3 py-2 shadow-[0px_4px_8.899999618530273px_0px_rgba(146,53,190,0.25)] transition-colors" href="{{ $mangaPreviewUrl }}" data-discover="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left text-txt-focus h-5 w-5" aria-hidden="true">
                        <path d="m12 19-7-7 7-7"></path>
                        <path d="M19 12H5"></path>
                    </svg>
                    <span class="text-center text-sm font-semibold text-txt-focus">Trở về</span>
                </a>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <button type="button" class="border-lav-500 hover:bg-lav-500/5 flex w-full cursor-pointer items-center justify-center gap-2.5 rounded-xl border px-4 py-3 shadow-[0px_4px_8.899999618530273px_0px_rgba(146,53,190,0.25)] transition-colors sm:w-52" data-chapter-btn-preview>
                        <span class="text-center text-sm font-semibold text-txt-focus">Xem trước</span>
                    </button>
                    <button type="button" disabled class="flex w-full cursor-pointer items-center justify-center gap-2.5 rounded-xl px-4 py-3 transition-colors disabled:cursor-not-allowed disabled:opacity-50 sm:w-44 border-bd-default border text-txt-primary hover:bg-white/5" data-chapter-btn-schedule>
                        <span class="whitespace-nowrap text-center text-sm font-semibold">Hẹn giờ đăng</span>
                    </button>
                    <button type="button" disabled class="flex w-full cursor-pointer items-center justify-center gap-2.5 rounded-xl px-4 py-3 transition-colors disabled:cursor-not-allowed disabled:opacity-50 sm:w-44 bg-gradient-to-b from-[#DD94FF] to-[#D373FF] text-black" data-chapter-btn-publish>
                        <span class="whitespace-nowrap text-center text-sm font-semibold">Đăng ngay</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Chế độ xem trước toàn trang (blob URL), ẩn cho đến khi bấm "Xem trước" --}}
        <div
            id="chapter-preview-panel"
            class="mx-auto hidden w-full max-w-[1080px] flex-col gap-6 px-4 py-6 sm:px-6 lg:px-0"
            data-chapter-preview-panel
            hidden
        >
            <div data-rht-toaster="" class="pointer-events-none fixed left-4 right-4 top-4 z-[9999] max-h-[40vh]" aria-live="polite"></div>
            <div class="bg-bgc-layer1 border-bd-default flex flex-col gap-4 rounded-xl border p-4 shadow-[0px_4px_4px_0px_rgba(0,0,0,0.25)] sm:p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex flex-col gap-1">
                        <p class="text-sm font-medium text-txt-secondary">Xem trước cục bộ</p>
                        <h2 class="text-2xl font-semibold text-txt-primary sm:text-3xl" data-chapter-preview-heading>Chương mới</h2>
                        <p class="text-sm leading-6 text-txt-secondary">Ảnh đang được đọc trực tiếp từ máy của bạn bằng URL tạm thời, chưa cần upload lên hệ thống, chưa cần qua bước watermark hoặc R2.</p>
                    </div>
                    <div class="bg-bgc-layer2 border-bd-default flex min-w-[180px] flex-col gap-1 rounded-xl border px-4 py-3">
                        <span class="text-xs font-medium uppercase tracking-wide text-txt-secondary">Tổng trang</span>
                        <span class="text-2xl font-semibold text-txt-primary" data-chapter-preview-total>0</span>
                    </div>
                </div>
                <div class="bg-bgc-layer2 border-bd-default rounded-xl border px-4 py-3 text-sm leading-6 text-txt-secondary">Preview này chỉ phục vụ kiểm tra thứ tự trang và nội dung chap trước khi đăng. Nút preview của chap đã đăng vẫn dùng luồng bình thường từ hệ thống lưu trữ.</div>
            </div>
            <div class="flex flex-col gap-3" data-chapter-preview-pages></div>
            <div class="flex justify-center">
                <button type="button" class="hover:bg-lav-500/5 border-lav-500 flex cursor-pointer items-center justify-center gap-1.5 rounded-xl border px-6 py-3 shadow-[0px_4px_8.899999618530273px_0px_rgba(146,53,190,0.25)] transition-colors" data-chapter-btn-back-editor>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left text-txt-focus h-5 w-5" aria-hidden="true">
                        <path d="m12 19-7-7 7-7"></path>
                        <path d="M19 12H5"></path>
                    </svg>
                    <span class="text-center text-sm font-semibold text-txt-focus">Trở về chỉnh sửa</span>
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const root = document.getElementById("chapter-create-root");
            if (!root) {
                return;
            }

            const editorPanel = document.getElementById("chapter-editor-panel");
            const previewPanel = document.getElementById("chapter-preview-panel");
            const dropzone = root.querySelector("[data-chapter-dropzone]");
            const filesInput = root.querySelector("[data-chapter-files-input]");
            const folderInput = root.querySelector("[data-chapter-folder-input]");
            const grid = root.querySelector("[data-chapter-pages-grid]");
            const emptyHint = root.querySelector("[data-chapter-pages-empty]");
            const titleInput = root.querySelector("[data-chapter-title-input]");
            const titleCount = root.querySelector("[data-chapter-title-count]");
            const btnPreview = root.querySelector("[data-chapter-btn-preview]");
            const btnBackEditor = root.querySelector("[data-chapter-btn-back-editor]");
            const btnSchedule = root.querySelector("[data-chapter-btn-schedule]");
            const btnPublish = root.querySelector("[data-chapter-btn-publish]");
            const previewHeading = root.querySelector("[data-chapter-preview-heading]");
            const previewTotal = root.querySelector("[data-chapter-preview-total]");
            const previewPages = root.querySelector("[data-chapter-preview-pages]");

            if (!editorPanel || !previewPanel || !dropzone || !filesInput || !folderInput || !grid) {
                return;
            }

            // Danh sách trang: mỗi mục có .file (Blob) và .url (blob URL)
            let trang = [];
            let dragChiSo = null;

            const scheduleFields = root.querySelector("[data-chapter-schedule-fields]");
            const scheduleErrorEl = root.querySelector("[data-chapter-schedule-error]");
            const scheduleDateSel = root.querySelector("[data-chapter-schedule-date]");
            const scheduleHourInp = root.querySelector("[data-chapter-schedule-hour]");
            const scheduleMinuteInp = root.querySelector("[data-chapter-schedule-minute]");
            const publishModeRadios = root.querySelectorAll("[data-chapter-publish-mode]");
            const chapterStoreUrl = root.getAttribute("data-chapter-store-url") || "";
            const chapterUpdateUrl = root.getAttribute("data-chapter-update-url") || "";
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute("content") || "" : "";

            function toastSuccess(noiju) {
                if (typeof FuiToast !== "undefined" && typeof FuiToast.success === "function") {
                    FuiToast.success(noiju);
                } else {
                    window.alert(noiju);
                }
            }

            function toastError(noiju) {
                if (typeof FuiToast !== "undefined" && typeof FuiToast.error === "function") {
                    FuiToast.error(noiju);
                } else {
                    window.alert(noiju);
                }
            }

            const CLS_NUT_VIEN =
                "flex w-full cursor-pointer items-center justify-center gap-2.5 rounded-xl px-4 py-3 transition-colors disabled:cursor-not-allowed disabled:opacity-50 sm:w-44 border-bd-default border text-txt-primary hover:bg-white/5";
            const CLS_NUT_GRADIENT =
                "flex w-full cursor-pointer items-center justify-center gap-2.5 rounded-xl px-4 py-3 transition-colors disabled:cursor-not-allowed disabled:opacity-50 sm:w-44 bg-gradient-to-b from-[#DD94FF] to-[#D373FF] text-black";

            function capNhatTrangThaiNutDang() {
                if (!btnPublish || !btnSchedule) {
                    return;
                }
                const immediate = root.querySelector('input[name="publishMode"][value="immediate"]:checked');
                const scheduled = root.querySelector('input[name="publishMode"][value="scheduled"]:checked');
                const tenDayDu = titleInput && titleInput.value.trim() !== "";
                const duItNhatHaiAnh = trang.length > 1;
                const coTheDang = tenDayDu && duItNhatHaiAnh;
                const lichOk = scheduleDateSel && scheduleDateSel.value !== "";

                if (immediate) {
                    btnPublish.className = CLS_NUT_GRADIENT;
                    btnPublish.disabled = !coTheDang;
                    btnSchedule.className = CLS_NUT_VIEN;
                    btnSchedule.disabled = true;
                } else if (scheduled) {
                    btnPublish.className = CLS_NUT_VIEN;
                    btnPublish.disabled = true;
                    btnSchedule.className = CLS_NUT_GRADIENT;
                    btnSchedule.disabled = !coTheDang || !lichOk;
                }
            }

            function capNhatHienThiHenGio() {
                const hen = root.querySelector('input[name="publishMode"][value="scheduled"]:checked');
                if (scheduleFields) {
                    scheduleFields.classList.toggle("hidden", !hen);
                }
                if (!hen && scheduleErrorEl) {
                    scheduleErrorEl.classList.add("hidden");
                }
                capNhatTrangThaiNutDang();
            }

            publishModeRadios.forEach(function (r) {
                r.addEventListener("change", capNhatHienThiHenGio);
            });
            capNhatHienThiHenGio();

            if (scheduleDateSel && scheduleErrorEl) {
                scheduleDateSel.addEventListener("change", function () {
                    if (scheduleDateSel.value !== "") {
                        scheduleErrorEl.classList.add("hidden");
                    }
                    capNhatTrangThaiNutDang();
                });
            }
            if (scheduleHourInp) {
                scheduleHourInp.addEventListener("input", capNhatTrangThaiNutDang);
            }
            if (scheduleMinuteInp) {
                scheduleMinuteInp.addEventListener("input", capNhatTrangThaiNutDang);
            }

            napBanDauTuPayload();

            function docGiaTriGioPhutHopLe() {
                let h = parseInt(String(scheduleHourInp && scheduleHourInp.value), 10);
                let m = parseInt(String(scheduleMinuteInp && scheduleMinuteInp.value), 10);
                if (!Number.isFinite(h) || h < 0) {
                    h = 0;
                }
                if (h > 23) {
                    h = 23;
                }
                if (!Number.isFinite(m) || m < 0) {
                    m = 0;
                }
                if (m > 59) {
                    m = 59;
                }

                return { h: h, m: m };
            }

            let dangGuiDangChuong = false;

            function thongBaoLoi422(payload) {
                if (!payload || typeof payload !== "object") {
                    toastError("Không đăng được chương.");

                    return;
                }
                if (payload.message && typeof payload.message === "string") {
                    toastError(payload.message);

                    return;
                }
                const errs = payload.errors;
                if (errs && typeof errs === "object") {
                    const parts = [];
                    Object.keys(errs).forEach(function (k) {
                        const arr = errs[k];
                        if (Array.isArray(arr)) {
                            arr.forEach(function (x) {
                                if (typeof x === "string") {
                                    parts.push(x);
                                }
                            });
                        }
                    });
                    if (parts.length) {
                        toastError(parts.join(" "));

                        return;
                    }
                }
                toastError("Dữ liệu không hợp lệ.");
            }

            async function guiDangChuong(loai) {
                if (dangGuiDangChuong) {
                    return;
                }
                if (!titleInput || titleInput.value.trim() === "") {
                    toastError("Vui lòng nhập đầy đủ tên chương.");

                    return;
                }
                if (trang.length <= 1) {
                    toastError("Cần ít nhất 2 ảnh mới được đăng chương.");

                    return;
                }
                const hen = root.querySelector('input[name="publishMode"][value="scheduled"]:checked');
                if (hen) {
                    if (!scheduleDateSel || scheduleDateSel.value === "") {
                        if (scheduleErrorEl) {
                            scheduleErrorEl.classList.remove("hidden");
                        }
                        toastError("Vui lòng chọn ngày đăng.");

                        return;
                    }
                    if (scheduleErrorEl) {
                        scheduleErrorEl.classList.add("hidden");
                    }
                    const gp = docGiaTriGioPhutHopLe();
                    if (scheduleHourInp) {
                        scheduleHourInp.value = String(gp.h).padStart(2, "0");
                    }
                    if (scheduleMinuteInp) {
                        scheduleMinuteInp.value = String(gp.m).padStart(2, "0");
                    }
                }
                const endpoint = chapterUpdateUrl || chapterStoreUrl;
                if (!endpoint) {
                    toastError("Thiếu URL lưu chương.");

                    return;
                }
                if (!csrfToken) {
                    toastError("Thiếu CSRF, tải lại trang.");

                    return;
                }

                const dangSua = Boolean(chapterUpdateUrl);
                let coAnhCu = false;
                let coFileMoi = false;
                trang.forEach(function (item) {
                    if (item.file) {
                        coFileMoi = true;
                    } else if (item.anhCu) {
                        coAnhCu = true;
                    }
                });
                if (dangSua) {
                    if (coFileMoi && coAnhCu) {
                        toastError(
                            "Không trộn ảnh đã lưu với file mới: hoặc chỉ sửa tiêu đề/lịch (giữ ảnh), hoặc xóa hết ảnh trong lưới rồi tải bộ mới (≥2)."
                        );

                        return;
                    }
                    if (coFileMoi && !trang.every(function (item) { return item.file; })) {
                        toastError("Khi thay ảnh, mọi trang phải là file tải mới từ máy.");

                        return;
                    }
                }

                const title = titleInput.value.trim();
                const publishMode = loai === "scheduled" ? "scheduled" : "immediate";
                const fd = new FormData();
                fd.append("_token", csrfToken);
                fd.append("title", title);
                fd.append("publish_mode", publishMode);
                if (publishMode === "scheduled" && scheduleDateSel) {
                    fd.append("schedule_date", scheduleDateSel.value);
                    const gp2 = docGiaTriGioPhutHopLe();
                    fd.append("schedule_hour", String(gp2.h));
                    fd.append("schedule_minute", String(gp2.m));
                }
                const guiCaTrangFile = !dangSua || coFileMoi;
                if (guiCaTrangFile) {
                    trang.forEach(function (item) {
                        fd.append("pages[]", item.file);
                    });
                    const wmChon = root.querySelector('input[name="watermarkStyle"]:checked');
                    fd.append("watermark_style", wmChon ? wmChon.value : "stroke");
                }

                dangGuiDangChuong = true;
                if (btnPublish) {
                    btnPublish.disabled = true;
                }
                if (btnSchedule) {
                    btnSchedule.disabled = true;
                }

                try {
                    const res = await fetch(endpoint, {
                        method: "POST",
                        headers: {
                            Accept: "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                            "X-CSRF-TOKEN": csrfToken,
                        },
                        body: fd,
                        credentials: "same-origin",
                    });
                    const data = await res.json().catch(function () {
                        return null;
                    });
                    if (res.status === 403) {
                        toastError("Bạn không có quyền đăng chương cho truyện này.");

                        return;
                    }
                    if (res.status === 422) {
                        thongBaoLoi422(data);

                        return;
                    }
                    if (!res.ok || !data || !data.ok) {
                        toastError((data && data.message) || "Không đăng được chương.");

                        return;
                    }
                    if (data.message) {
                        toastSuccess(data.message);
                    }
                    if (data.redirect) {
                        window.location.assign(data.redirect);

                        return;
                    }
                    toastError("Phản hồi thiếu đường dẫn chuyển trang.");
                } catch (err) {
                    toastError("Lỗi mạng, thử lại sau.");
                } finally {
                    dangGuiDangChuong = false;
                    capNhatTrangThaiNutDang();
                }
            }

            if (btnPublish) {
                btnPublish.addEventListener("click", function () {
                    guiDangChuong("immediate");
                });
            }
            if (btnSchedule) {
                btnSchedule.addEventListener("click", function () {
                    guiDangChuong("scheduled");
                });
            }

            function laAnhFile(f) {
                return f && f.type && f.type.indexOf("image/") === 0;
            }

            function sapXepTheoTen(files) {
                return Array.prototype.slice.call(files, 0).sort(function (a, b) {
                    return a.name.localeCompare(b.name, undefined, { numeric: true, sensitivity: "base" });
                });
            }

            function dinhDangDungLuong(bytes) {
                if (bytes >= 1048576) {
                    return (bytes / 1048576).toFixed(1) + "MB";
                }
                if (bytes >= 1024) {
                    return (bytes / 1024).toFixed(1) + "KB";
                }
                return bytes + "B";
            }

            function nhanDangDinhDang(file) {
                const t = (file.type || "").split("/")[1];
                if (t) {
                    return t.toUpperCase().slice(0, 8);
                }
                const p = file.name.split(".");
                return p.length > 1 ? p.pop().toUpperCase().slice(0, 8) : "IMG";
            }

            function capNhatNutVaLuoi() {
                const coTrang = trang.length > 0;
                if (emptyHint) {
                    emptyHint.classList.toggle("hidden", coTrang);
                }
                grid.innerHTML = "";
                trang.forEach(function (item, idx) {
                    const wrap = document.createElement("div");
                    wrap.className = "group relative select-none";
                    wrap.setAttribute("draggable", "true");
                    wrap.setAttribute("data-chapter-thumb-idx", String(idx));

                    const inner = document.createElement("div");
                    inner.className = "bg-bgc-layer1 border-bd-default aspect-[2/3] w-full overflow-hidden rounded-lg border";
                    const img = document.createElement("img");
                    img.alt = item.file ? item.file.name : item.tenHienThi || "Ảnh đã lưu";
                    img.className = "pointer-events-none h-full w-full object-cover object-top";
                    img.src = item.url;
                    inner.appendChild(img);
                    wrap.appendChild(inner);

                    const btnXoa = document.createElement("button");
                    btnXoa.type = "button";
                    btnXoa.className =
                        "absolute -top-2 -right-2 flex h-6 w-6 cursor-pointer items-center justify-center rounded-full border border-gray-600 bg-gray-800 transition-colors hover:bg-gray-700";
                    btnXoa.setAttribute("aria-label", "Xoá ảnh này");
                    btnXoa.title = "Xoá ảnh này";
                    btnXoa.innerHTML =
                        '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-txt-primary h-4 w-4"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>';
                    btnXoa.addEventListener("click", function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (item.url && String(item.url).indexOf("blob:") === 0) {
                            URL.revokeObjectURL(item.url);
                        }
                        trang.splice(idx, 1);
                        capNhatNutVaLuoi();
                    });
                    wrap.appendChild(btnXoa);

                    const badgeWrap = document.createElement("div");
                    badgeWrap.className = "absolute top-1 left-1 flex flex-col items-start gap-1";
                    const b1 = document.createElement("div");
                    b1.className = "bg-lav-500 rounded px-1 py-0.5 text-xs text-white";
                    b1.textContent = item.file ? nhanDangDinhDang(item.file) : "Lưu";
                    const b2 = document.createElement("div");
                    b2.className = "rounded bg-black/70 px-1 py-0.5 text-[11px] leading-none text-white";
                    b2.textContent = item.file ? dinhDangDungLuong(item.file.size) : "—";
                    badgeWrap.appendChild(b1);
                    badgeWrap.appendChild(b2);
                    wrap.appendChild(badgeWrap);

                    const cap = document.createElement("div");
                    cap.className = "mt-1 w-full";
                    const capSo = document.createElement("div");
                    capSo.className = "text-txt-primary text-xs font-semibold";
                    capSo.textContent = "#" + (idx + 1);
                    const capTen = document.createElement("div");
                    capTen.className = "text-txt-secondary text-[11px] leading-tight truncate";
                    const tenHien = item.file ? item.file.name : item.tenHienThi || "Ảnh đã lưu";
                    capTen.title = tenHien;
                    capTen.textContent = tenHien;
                    cap.appendChild(capSo);
                    cap.appendChild(capTen);
                    wrap.appendChild(cap);

                    wrap.addEventListener("dragstart", function (e) {
                        dragChiSo = idx;
                        if (e.dataTransfer) {
                            e.dataTransfer.effectAllowed = "move";
                        }
                    });
                    wrap.addEventListener("dragover", function (e) {
                        e.preventDefault();
                        if (e.dataTransfer) {
                            e.dataTransfer.dropEffect = "move";
                        }
                    });
                    wrap.addEventListener("drop", function (e) {
                        e.preventDefault();
                        const to = parseInt(wrap.getAttribute("data-chapter-thumb-idx") || "-1", 10);
                        if (dragChiSo === null || dragChiSo === to || to < 0) {
                            return;
                        }
                        const from = dragChiSo;
                        const moved = trang.splice(from, 1)[0];
                        let insert = to;
                        if (from < to) {
                            insert = to - 1;
                        }
                        trang.splice(insert, 0, moved);
                        dragChiSo = null;
                        capNhatNutVaLuoi();
                    });
                    wrap.addEventListener("dragend", function () {
                        dragChiSo = null;
                    });

                    grid.appendChild(wrap);
                });
                capNhatTrangThaiNutDang();
            }

            function napBanDauTuPayload() {
                const raw = root.getAttribute("data-chapter-initial");
                if (!raw) {
                    capNhatNutVaLuoi();

                    return;
                }
                let pl = null;
                try {
                    pl = JSON.parse(raw);
                } catch (err) {
                    capNhatNutVaLuoi();

                    return;
                }
                if (titleInput && pl.title != null) {
                    titleInput.value = String(pl.title);
                }
                if (pl.publishMode === "scheduled") {
                    const rHen = root.querySelector('input[name="publishMode"][value="scheduled"]');
                    if (rHen) {
                        rHen.checked = true;
                    }
                } else {
                    const rNgay = root.querySelector('input[name="publishMode"][value="immediate"]');
                    if (rNgay) {
                        rNgay.checked = true;
                    }
                }
                if (scheduleDateSel && pl.scheduleDate) {
                    scheduleDateSel.value = pl.scheduleDate;
                }
                if (scheduleHourInp && pl.scheduleHour != null) {
                    scheduleHourInp.value = String(pl.scheduleHour).padStart(2, "0");
                }
                if (scheduleMinuteInp && pl.scheduleMinute != null) {
                    scheduleMinuteInp.value = String(pl.scheduleMinute).padStart(2, "0");
                }
                capNhatHienThiHenGio();
                if (Array.isArray(pl.pageUrls)) {
                    pl.pageUrls.forEach(function (u, i) {
                        if (typeof u === "string" && u !== "") {
                            trang.push({
                                file: null,
                                url: u,
                                anhCu: true,
                                tenHienThi: "Trang " + (i + 1) + " (đã lưu)",
                            });
                        }
                    });
                }
                capNhatNutVaLuoi();
                if (titleInput && titleCount) {
                    titleCount.textContent = String(titleInput.value.length);
                }
            }

            function themTep(files) {
                const list = sapXepTheoTen(files).filter(laAnhFile);
                const maxBytes = 5 * 1024 * 1024;
                list.forEach(function (file) {
                    if (file.size > maxBytes) {
                        toastError('Ảnh "' + file.name + '" vượt quá 5 MB, đã bỏ qua.');
                        return;
                    }
                    trang.push({ file: file, url: URL.createObjectURL(file) });
                });
                capNhatNutVaLuoi();
            }

            function ganSuKienNut(triggerSel, input) {
                root.querySelectorAll(triggerSel).forEach(function (btn) {
                    btn.addEventListener("click", function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        input.click();
                    });
                });
            }
            ganSuKienNut("[data-chapter-trigger-files], [data-chapter-trigger-files-lg]", filesInput);
            ganSuKienNut("[data-chapter-trigger-folder], [data-chapter-trigger-folder-lg]", folderInput);

            filesInput.addEventListener("change", function () {
                if (filesInput.files && filesInput.files.length) {
                    themTep(filesInput.files);
                }
                filesInput.value = "";
            });
            folderInput.addEventListener("change", function () {
                if (folderInput.files && folderInput.files.length) {
                    themTep(folderInput.files);
                }
                folderInput.value = "";
            });

            /**
             * Duyệt FileSystemEntry (file hoặc thư mục) — bắt buộc để kéo thư mục từ máy vào trình duyệt.
             * dataTransfer.files thường rỗng khi thả folder; chỉ có Chrome / Edge / Safari hỗ trợ đầy đủ.
             */
            function duyetEntryHeThongTep(entry, out, xong) {
                if (entry.isFile) {
                    entry.file(
                        function (file) {
                            out.push(file);
                            xong();
                        },
                        function () {
                            xong();
                        }
                    );

                    return;
                }
                if (entry.isDirectory) {
                    const reader = entry.createReader();
                    function docThemLo() {
                        reader.readEntries(function (entries) {
                            if (entries.length === 0) {
                                xong();

                                return;
                            }
                            let conLai = entries.length;
                            entries.forEach(function (con) {
                                duyetEntryHeThongTep(con, out, function () {
                                    conLai--;
                                    if (conLai === 0) {
                                        docThemLo();
                                    }
                                });
                            });
                        });
                    }
                    docThemLo();

                    return;
                }
                xong();
            }

            function layFileTuDataTransfer(dt, callback) {
                if (!dt) {
                    callback([]);

                    return;
                }
                const items = dt.items;
                const flat = [];
                if (items && items.length > 0) {
                    let pending = 0;
                    function ketThuc() {
                        if (pending === 0) {
                            if (flat.length) {
                                callback(flat);
                            } else if (dt.files && dt.files.length) {
                                callback(Array.prototype.slice.call(dt.files));
                            } else {
                                callback([]);
                            }
                        }
                    }
                    for (let i = 0; i < items.length; i++) {
                        const item = items[i];
                        const entry =
                            (typeof item.webkitGetAsEntry === "function" && item.webkitGetAsEntry()) ||
                            (typeof item.getAsEntry === "function" && item.getAsEntry());
                        if (entry) {
                            pending++;
                            duyetEntryHeThongTep(entry, flat, function () {
                                pending--;
                                ketThuc();
                            });
                        } else if (item.kind === "file") {
                            const f = item.getAsFile();
                            if (f) {
                                flat.push(f);
                            }
                        }
                    }
                    ketThuc();

                    return;
                }
                if (dt.files && dt.files.length) {
                    callback(Array.prototype.slice.call(dt.files));

                    return;
                }
                callback([]);
            }

            ["dragenter", "dragover"].forEach(function (ev) {
                dropzone.addEventListener(ev, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (e.dataTransfer) {
                        e.dataTransfer.dropEffect = "copy";
                    }
                    dropzone.classList.add("ring-2", "ring-lav-500/50");
                });
            });
            dropzone.addEventListener("dragleave", function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove("ring-2", "ring-lav-500/50");
            });
            dropzone.addEventListener("drop", function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove("ring-2", "ring-lav-500/50");
                layFileTuDataTransfer(e.dataTransfer, function (files) {
                    if (files.length) {
                        themTep(files);
                    }
                });
            });
            dropzone.addEventListener("click", function (e) {
                if (e.target.closest("button")) {
                    return;
                }
                filesInput.click();
            });

            if (titleInput) {
                function khiNhapTieuDe() {
                    if (titleCount) {
                        titleCount.textContent = String(titleInput.value.length);
                    }
                    capNhatTrangThaiNutDang();
                }
                titleInput.addEventListener("input", khiNhapTieuDe);
                khiNhapTieuDe();
            }

            function hienEditor() {
                editorPanel.classList.remove("hidden");
                editorPanel.removeAttribute("hidden");
                previewPanel.classList.add("hidden");
                previewPanel.setAttribute("hidden", "");
                window.scrollTo({ top: 0, behavior: "smooth" });
            }

            function hienPreview() {
                if (trang.length === 0) {
                    toastError("Hãy thêm ít nhất một ảnh trước khi xem trước.");
                    return;
                }
                const tieuDe = titleInput && titleInput.value.trim() !== "" ? titleInput.value.trim() : "Chương mới";
                if (previewHeading) {
                    previewHeading.textContent = tieuDe;
                }
                if (previewTotal) {
                    previewTotal.textContent = String(trang.length);
                }
                if (previewPages) {
                    previewPages.innerHTML = "";
                    trang.forEach(function (item, i) {
                        const sec = document.createElement("section");
                        sec.className =
                            "bg-bgc-layer1 border-bd-default overflow-hidden rounded-xl border shadow-[0px_4px_4px_0px_rgba(0,0,0,0.25)]";
                        const head = document.createElement("div");
                        head.className =
                            "border-bd-default bg-bgc-layer2 flex items-center justify-between border-b px-4 py-3";
                        const sp1 = document.createElement("span");
                        sp1.className = "text-txt-primary text-sm font-semibold";
                        sp1.textContent = "Trang " + (i + 1);
                        const sp2 = document.createElement("span");
                        sp2.className = "text-txt-secondary text-xs font-medium";
                        sp2.textContent = item.file ? item.file.name : item.tenHienThi || "Ảnh đã lưu";
                        head.appendChild(sp1);
                        head.appendChild(sp2);
                        const imgBox = document.createElement("div");
                        imgBox.className = "flex justify-center bg-black/20 p-2 sm:p-4";
                        const im = document.createElement("img");
                        im.alt = "Preview trang " + (i + 1);
                        im.className = "h-auto w-full max-w-full object-contain";
                        im.loading = i < 2 ? "eager" : "lazy";
                        im.decoding = "async";
                        im.src = item.url;
                        imgBox.appendChild(im);
                        sec.appendChild(head);
                        sec.appendChild(imgBox);
                        previewPages.appendChild(sec);
                    });
                }
                editorPanel.classList.add("hidden");
                editorPanel.setAttribute("hidden", "");
                previewPanel.classList.remove("hidden");
                previewPanel.removeAttribute("hidden");
                window.scrollTo({ top: 0, behavior: "smooth" });
            }

            if (btnPreview) {
                btnPreview.addEventListener("click", hienPreview);
            }
            if (btnBackEditor) {
                btnBackEditor.addEventListener("click", hienEditor);
            }
        })();
    </script>
@endpush
