@extends('theme-vinahentai::layout.main')

@php
    $laChuTruyen = auth()->check() && (int) auth()->id() === (int) ($manga->user_id ?? 0);
@endphp

@section('body')
    <div class="container-page mx-auto px-4 py-6">
        <div data-rht-toaster="" style="position:fixed;z-index:9999;top:16px;left:16px;right:16px;bottom:16px;pointer-events:none"></div>
        <div class="w-full">
            <div class="mb-6 flex flex-wrap items-center gap-4"></div>
            <div class="mb-6 flex flex-col gap-4 rounded-xl border border-bd-default bg-bgc-layer1 p-4 shadow md:flex-row md:items-center md:justify-between">
                <div class="flex flex-col gap-2">
                    <p class="text-sm font-semibold text-txt-primary">Tình trạng truyện</p>
                    <div class="flex flex-wrap items-center gap-3"><select class="bg-bgc-layer2 border-bd-default text-txt-primary focus:border-lav-500 focus:ring-2 focus:ring-primary/40 rounded-lg border px-3 py-2 text-sm font-semibold outline-none">
                            <option value="0" selected="">Đang tiến hành</option>
                            <option value="1">Đã hoàn thành</option>
                        </select></div>
                </div>
            </div>

            <div class="mb-6"><a href="{{ route('mangas.edit', $manga->slug) }}" data-discover="true"><button type="button" class="border-lav-500 text-txt-focus hover:bg-lav-500/10 flex min-w-32 cursor-pointer items-center justify-center gap-2 rounded-xl border px-4 py-3 shadow-[0px_4px_8.9px_0px_rgba(146,53,190,0.25)] transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-pen h-5 w-5" aria-hidden="true"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"></path></svg><span class="text-sm font-semibold">Sửa thông tin chung</span></button></a></div>

            <div class="w-full">
                <div class="flex flex-col gap-6 portrait:gap-[0.9rem] sm:portrait:gap-10 md:flex-row md:items-start md:gap-5">
                    <div class="relative mx-auto flex flex-shrink-0 items-center justify-center overflow-hidden rounded-lg h-[403px] w-[269px] portrait:w-[302px] sm:portrait:w-[269px] md:mx-0 md:mt-[5px] md:w-[min(280px,36vw)] md:h-auto md:aspect-[2/3] group cursor-copy md:self-start"><img src="{{ $manga->cover_image }}" alt="Bìa truyện Quá khứ của mẹ kế" width="204" height="272" class="h-full w-full object-cover" loading="lazy" decoding="async"><input type="file" accept="image/*" class="hidden">
                        <div class="pointer-events-none absolute inset-0 rounded-lg border-2 border-dashed transition border-transparent bg-transparent group-hover:border-white/20"></div>
                        <div class="pointer-events-none absolute inset-0 z-10 rounded-lg bg-black/60 text-center text-sm text-white transition opacity-0 group-hover:opacity-100">
                            <div class="flex h-full flex-col items-center justify-center gap-2 px-4"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-cloud-upload h-5 w-5 text-[#D373FF]" aria-hidden="true">
                                    <path d="M12 13v8"></path>
                                    <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"></path>
                                    <path d="m8 17 4-4 4 4"></path>
                                </svg>
                                <p class="text-sm font-semibold">Thả ảnh để cập nhật</p>
                                <p class="text-xs text-white/70">Ảnh mới được lưu ngay khi thả</p>
                            </div>
                        </div><button type="button" class="absolute bottom-3 left-1/2 z-20 -translate-x-1/2 rounded-full border border-white/30 bg-black/70 px-4 py-1 text-xs font-semibold text-white shadow-lg transition hover:border-[#D373FF] hover:text-[#D373FF] disabled:cursor-not-allowed disabled:opacity-60">Đổi ảnh</button>
                    </div>

                    <div class="flex w-full min-w-0 flex-col gap-4 md:flex-1">
                        <div class="flex flex-col gap-1 md:gap-1.5">
                            <h1 class="text-txt-primary text-2xl leading-snug font-semibold">{{ $manga->title }}</h1>
                            <div class="text-txt-secondary text-sm mt-0.5 md:mt-1">{{ $manga->alternative_title }}</div>
                        </div>
                        <div class="border-bd-default h-0 border-t mt-[-0.3rem] md:mt-0"></div>

                        <div class="grid grid-cols-[auto_1fr] gap-y-3 gap-x-[0.6rem] md:gap-x-[0.45rem]">
                            <div class="text-txt-secondary w-28 text-base font-medium">Tình trạng:</div>
                            <div class="text-txt-primary text-base font-medium">{{ $manga->getStatus() }}</div>
                            <div class="text-txt-secondary w-28 text-base font-medium">Tác giả:</div>
                            <div class="flex flex-wrap gap-2 justify-self-start">
                                @foreach ($manga->authors as $author)
                                <a class="group inline-flex h-9 max-w-[260px] items-stretch overflow-hidden rounded-md border border-[rgba(211,115,255,.22)] text-[#EBD7FF]" title="{{ $author->name }}" aria-label="Xem tác giả {{ $author->name }}" href="{{ $author->getUrl() }}" data-discover="true"><span class="flex min-w-0 items-center bg-[rgba(211,115,255,.08)] px-4 group-hover:bg-[rgba(211,115,255,.12)]"><span class="truncate capitalize text-base">{{ $author->name }}</span></span><span class="flex items-center bg-[rgba(211,115,255,.14)] px-2.5 text-sm font-semibold tabular-nums text-[#EBD7FF]/95 group-hover:bg-[rgba(211,115,255,.18)]">{{ $author->mangas->count() }}</span></a>
                                @endforeach
                            </div>
                            @if ($manga->translators->isNotEmpty())
                            <div class="text-txt-secondary w-28 text-base font-medium">Dịch giả:</div>
                            <div class="flex flex-wrap gap-2 justify-self-start">
                                @foreach ($manga->translators as $translator)
                                <a class="group inline-flex h-9 max-w-[260px] items-stretch overflow-hidden rounded-md border border-[rgba(211,115,255,.22)] text-[#EBD7FF]" title="{{ $translator->name }}" aria-label="Xem tác giả {{ $translator->name }}" href="{{ $translator->getUrl() }}" data-discover="true"><span class="flex min-w-0 items-center bg-[rgba(211,115,255,.08)] px-4 group-hover:bg-[rgba(211,115,255,.12)]"><span class="truncate capitalize text-base">{{ $translator->name }}</span></span><span class="flex items-center bg-[rgba(211,115,255,.14)] px-2.5 text-sm font-semibold tabular-nums text-[#EBD7FF]/95 group-hover:bg-[rgba(211,115,255,.18)]">{{ $translator->mangas->count() }}</span></a>
                                @endforeach
                            </div>
                            @endif
                            <div class="text-txt-secondary w-28 text-base font-medium">Cập nhật:</div>
                            <div class="flex items-center gap-2"><time class="text-txt-primary text-base font-medium" title="{{ $manga->updated_at->format('d/m/Y H:i:s') }}" datetime="{{ $manga->updated_at->toW3CString() }}">{{ $manga->updated_at->diffForHumans() }}</time></div>
                            <div class="text-txt-secondary w-28 text-base font-medium">Thể loại:</div>
                            <div class="relative justify-self-start">
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($manga->genres as $genre)
                                    <a class="inline-flex items-center rounded-md border border-white/20 bg-black/20 px-3 py-1.5 text-sm leading-tight text-[#EBD7FF]/95 hover:border-[#D373FF]/35 hover:bg-black/26 transition-colors w-fit" title="{{ $genre->name }}" aria-label="Xem thể loại {{ $genre->name }}" href="{{ $genre->getUrl() }}" data-discover="true"><span class="capitalize">{{ $genre->name }}</span></a>
                                    @endforeach
                                </div>
                            </div>
                            <div class="text-txt-secondary w-28 text-base font-medium">Tổng quan:</div>
                            <div class="flex flex-wrap items-center gap-4">
                                <span class="text-txt-secondary inline-flex items-center gap-1.5 text-sm" title="Lượt xem">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye h-4 w-4" aria-hidden="true">
                                        <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <span class="text-txt-primary font-medium">
                                        {{ number_format($manga->total_views) }}
                                    </span>
                                </span>
                                <span class="text-txt-secondary inline-flex items-center gap-1.5 text-sm" title="Tổng lượt thích" aria-label="Lượt thích">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-thumbs-up h-4 w-4" aria-hidden="true">
                                        <path d="M7 10v12"></path>
                                        <path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"></path>
                                    </svg>
                                    <span class="text-txt-primary font-medium">
                                        {{ number_format($manga->total_follows) }}
                                    </span>
                                </span>
                                <span class="text-txt-secondary inline-flex items-center gap-1.5 text-sm" title="Mã truyện"><span class="text-txt-primary font-medium select-all">{{ $manga->id }}</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex flex-col gap-[0.8rem]" id="manga-description-section"><div class="flex items-center gap-3"><h2 class="text-txt-primary text-xl font-semibold uppercase">GIỚI THIỆU</h2></div><div class="relative"><div class="text-txt-secondary text-base font-medium leading-[1.25] whitespace-pre-line">{!! $manga->description !!}</div></div></div>
            </div>
        </div>

        <div class="mt-10 flex flex-col gap-4" id="manga-preview-chapters" data-manga-preview-root data-manga-slug="{{ e($manga->slug) }}">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-txt-primary text-lg font-semibold">Chương đã đăng</h2>
                @if ($laChuTruyen)
                    <a href="{{ route('user.create-chapter', $manga->slug) }}" data-discover="true"><button type="button" class="to-btn-primary flex min-w-40 cursor-pointer items-center justify-center gap-1 rounded-xl bg-gradient-to-b from-[#DD94FF] px-4 py-3 text-sm font-semibold text-black"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus h-5 w-5" aria-hidden="true">
                                <path d="M5 12h14"></path>
                                <path d="M12 5v14"></path>
                            </svg>Thêm chương</button></a>
                @endif
                <div class="flex items-center gap-2"></div>
            </div>

            <div class="flex max-h-[304px] flex-col overflow-y-auto rounded-lg border border-white/10 md:max-h-[400px] lg:max-h-[492px]">
                <div class="bg-bgc-layer1/60 sticky top-0 z-[1] grid {{ $laChuTruyen ? 'grid-cols-[64px_1fr_96px]' : 'grid-cols-[64px_1fr]' }} items-center gap-3 border-b border-white/10 px-4 py-2 text-xs font-semibold text-txt-secondary">
                    <div>STT</div>
                    <div>Tên chương</div>
                    @if ($laChuTruyen)
                        <div class="text-right pr-2">Hành động</div>
                    @endif
                </div>
                <div class="flex flex-col divide-y divide-white/10" data-manga-preview-chapter-list>

                @if($manga->chapters->isEmpty())
                    <div class="text-txt-secondary px-4 py-6 text-sm" data-manga-preview-chapters-empty>Chưa có chương nào.</div>
                @else
                @foreach ($manga->chapters as $chapter)
                    <div class="grid {{ $laChuTruyen ? 'grid-cols-[64px_1fr_96px]' : 'grid-cols-[64px_1fr]' }} items-center gap-3 bg-bgc-layer2 px-4 py-2" data-manga-preview-chapter-row data-chapter-id="{{ $chapter->id }}">
                        <div class="text-txt-secondary text-sm">{{ $loop->iteration }}</div>
                        <div class="text-txt-primary min-w-0">
                            <a href="{{ $chapter->getUrl() }}" class="group block w-full text-left hover:text-txt-focus" data-discover="true" title="Đọc chương">
                                <span class="text-sm font-medium">{{ $chapter->title }}</span>
                            </a>
                        </div>
                        @if ($laChuTruyen)
                            <div class="flex items-center justify-end gap-2 shrink-0">
                                <a class="text-txt-secondary hover:text-txt-focus" title="Chỉnh sửa (cùng form thêm chương)" aria-label="Chỉnh sửa chương" href="{{ route('user.edit-chapter', [$manga->slug, $chapter->id]) }}" data-discover="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-pen h-5 w-5" aria-hidden="true">
                                        <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"></path>
                                    </svg>
                                </a>
                                <button
                                    type="button"
                                    class="text-rose-400 hover:text-rose-300"
                                    title="Xóa chương"
                                    aria-label="Xóa chương"
                                    data-manga-preview-delete-chapter
                                    data-delete-url="{{ route('user.destroy-chapter', ['mangaSlug' => $manga->slug, 'chapter' => $chapter->id]) }}"
                                    data-chapter-title="{{ e($chapter->title) }}"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2 lucide-trash-2 h-5 w-5" aria-hidden="true">
                                        <path d="M3 6h18"></path>
                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                        <line x1="10" x2="10" y1="11" y2="17"></line>
                                        <line x1="14" x2="14" y1="11" y2="17"></line>
                                    </svg>
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
                @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@if ($laChuTruyen)
    @push('scripts')
        <script>
            (function () {
                const root = document.querySelector("[data-manga-preview-root]");
                if (!root) {
                    return;
                }
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfMeta ? csrfMeta.getAttribute("content") || "" : "";

                function toastOk(msg) {
                    if (typeof FuiToast !== "undefined" && FuiToast.success) {
                        FuiToast.success(msg);
                    } else {
                        window.alert(msg);
                    }
                }
                function toastLoi(msg) {
                    if (typeof FuiToast !== "undefined" && FuiToast.error) {
                        FuiToast.error(msg);
                    } else {
                        window.alert(msg);
                    }
                }

                root.addEventListener("click", function (e) {
                    const btn = e.target && e.target.closest && e.target.closest("[data-manga-preview-delete-chapter]");
                    if (!btn || !root.contains(btn)) {
                        return;
                    }
                    const url = btn.getAttribute("data-delete-url");
                    const tieuDe = btn.getAttribute("data-chapter-title") || "chương này";
                    if (!url || !csrfToken) {
                        toastLoi("Không xóa được (thiếu cấu hình).");
                        return;
                    }
                    if (!window.confirm('Xóa "' + tieuDe + '"? Hành động không hoàn tác.')) {
                        return;
                    }
                    btn.disabled = true;
                    fetch(url, {
                        method: "DELETE",
                        headers: {
                            Accept: "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                            "X-CSRF-TOKEN": csrfToken,
                        },
                        credentials: "same-origin",
                    })
                        .then(function (res) {
                            return res.json().then(function (data) {
                                return { res: res, data: data };
                            });
                        })
                        .then(function (ketQua) {
                            if (ketQua.res.status === 403) {
                                toastLoi("Bạn không có quyền xóa chương này.");
                                return;
                            }
                            if (!ketQua.res.ok || !ketQua.data || !ketQua.data.ok) {
                                toastLoi((ketQua.data && ketQua.data.message) || "Xóa thất bại.");
                                return;
                            }
                            toastOk(ketQua.data.message || "Đã xóa chương.");
                            const hang = btn.closest("[data-manga-preview-chapter-row]");
                            if (hang) {
                                hang.remove();
                            }
                            const list = root.querySelector("[data-manga-preview-chapter-list]");
                            if (list && !list.querySelector("[data-manga-preview-chapter-row]")) {
                                let trong = list.querySelector("[data-manga-preview-chapters-empty]");
                                if (!trong) {
                                    trong = document.createElement("div");
                                    trong.className = "text-txt-secondary px-4 py-6 text-sm";
                                    trong.setAttribute("data-manga-preview-chapters-empty", "");
                                    trong.textContent = "Chưa có chương nào.";
                                    list.appendChild(trong);
                                }
                            }
                        })
                        .catch(function () {
                            toastLoi("Lỗi mạng, thử lại.");
                        })
                        .finally(function () {
                            btn.disabled = false;
                        });
                });
            })();
        </script>
    @endpush
@endif
