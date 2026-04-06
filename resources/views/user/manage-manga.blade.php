@extends('theme-vinahentai::layout.main')

@section('body')
    <div class="mx-auto w-full max-w-[968px] p-4 lg:py-8">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-semibold text-white uppercase">Quản lý truyện</h1>
            <button type="button"
                data-translator-guide-open
                class="inline-flex items-center gap-2 text-xs font-semibold text-purple-300 transition hover:text-purple-200"
                aria-haspopup="dialog"
                aria-expanded="false"
                aria-controls="translator-guide-dialog">
                <span class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-purple-300/70 text-[10px] leading-none" aria-hidden="true">i</span>
                Hướng dẫn 10 phút trở thành dịch giả
            </button>
        </div>

        <div class="flex flex-col items-start gap-4">
            <div class="flex w-full items-center justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-txt-secondary text-sm font-medium">Tổng số view kiếm được:
                        {{ (int) ($user->mangas_sum_total_views ?? 0) }}
                    </span>
                    <a href="{{ route('profile', $user->id) }}#titles" class="inline-flex items-center gap-1.5 rounded-lg border border-lav-500/30 bg-lav-500/10 px-3 py-1.5 text-xs font-semibold text-lav-400 transition hover:bg-lav-500/20">Danh hiệu của tôi</a>
                </div>

                <a href="{{ route('user.create-manga') }}" class="flex items-center gap-1.5 rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] px-4 py-3 text-sm font-semibold text-black shadow-[0px_4px_8.9px_0px_rgba(196,69,255,0.25)] transition-all hover:opacity-90"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus h-5 w-5" aria-hidden="true"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>Tạo truyện mới</a>
            </div>

            @if ($mangas->isEmpty())
            <div class="py-8 text-center"><p class="text-txt-secondary text-sm font-medium">Bạn chưa đăng truyện nào</p></div>
            @else
            <div class="bg-bgc-layer1 border-bd-default w-full overflow-hidden rounded-xl border">
                <div class="border-bd-default grid w-full grid-cols-1 gap-3 border-b px-3 py-2 text-xs font-semibold text-txt-secondary md:grid-cols-[72px_minmax(320px,1fr)_minmax(200px,240px)_minmax(160px,200px)] md:gap-0"><div class="flex items-center justify-center md:pr-4">STT</div><div class="md:border-l md:border-bd-default md:px-4">Truyện</div><div class="md:border-l md:border-bd-default md:px-4">Trạng thái</div><div class="md:border-l md:border-bd-default md:px-4 md:text-right">Hành động</div></div>

                @foreach ($mangas as $manga)
                @php
                    // Đã xuất bản (published_at có giá trị) = đã duyệt hiển thị công khai; null = chờ duyệt
                    $daDuyet = $manga->published_at !== null;
                    $trangThaiTiepTuc = match ($manga->status) {
                        'completed' => 'bg-emerald-500/10 text-emerald-400',
                        'hiatus' => 'bg-orange-500/10 text-orange-400',
                        'cancelled' => 'bg-red-500/10 text-red-400',
                        default => 'bg-sky-500/10 text-sky-400',
                    };
                @endphp
                <div class="border-bd-default border-b last:border-b-0" data-manage-manga-row data-manga-id="{{ $manga->id }}">
                    <div class="grid w-full grid-cols-1 items-start gap-4 px-3 py-3 md:grid-cols-[72px_minmax(320px,1fr)_minmax(200px,240px)_minmax(160px,200px)] md:gap-0 md:items-stretch">
                        <div class="flex flex-col items-center justify-center md:pr-4"><span class="text-lg font-bold text-white">{{ $mangas->firstItem() + $loop->index }}</span></div>
                        <div class="flex items-start gap-3 md:border-l md:border-bd-default md:px-4"><a href="{{ $manga->getUrl() }}" class="flex items-start gap-3">
                                <div class="relative"><img alt="{{ $manga->title }}" class="w-20 min-w-[80px] rounded object-cover aspect-[2/3]" loading="lazy" src="{{ $manga->cover_image }}"></div>
                                <div class="flex flex-col gap-1">
                                    <h3 class="text-txt-primary line-clamp-1 text-sm leading-tight font-medium" title="{{ $manga->title }}">{{ $manga->title }}</h3>
                                    <div class="flex flex-wrap items-center gap-3"><span class="text-txt-focus text-xs font-medium">Số lượng chap: {{ (int) $manga->chapters_count }}</span>
                                        <div class="flex items-center gap-1.5 rounded-[32px] backdrop-blur-[3.40px]"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye text-txt-secondary h-3 w-3" aria-hidden="true">
                                                <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg><span class="text-txt-secondary text-xs font-medium">{{ number_format((int) $manga->total_views) }}</span></div>
                                    </div>
                                </div>
                            </a></div>
                        <div class="flex flex-col gap-2 md:border-l md:border-bd-default md:px-4">
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($daDuyet)
                                <span class="rounded-[32px] px-2 py-1 text-xs font-medium backdrop-blur-[3.4px] bg-[#25EBAC]/15 text-[#25EBAC]">Đã duyệt</span>
                                @else
                                <span class="rounded-[32px] px-2 py-1 text-xs font-medium backdrop-blur-[3.4px] bg-[#FFE133]/10 text-[#FFE133]">Chờ duyệt</span>
                                @endif
                                <span class="rounded-[32px] px-2 py-1 text-xs font-medium backdrop-blur-[3.4px] {{ $trangThaiTiepTuc }}">{{ $manga->getStatus() }}</span>
                            </div>
                        </div>
                        <div class="flex flex-col items-start gap-2 md:items-end md:border-l md:border-bd-default md:px-4 md:justify-self-stretch"><a href="{{ route('mangas.preview', $manga->slug) }}" class="flex items-center gap-1.5 rounded-lg border border-[#25EBAC] px-2.5 py-1.5 text-sm font-medium text-[#25EBAC] transition-colors hover:bg-[#25EBAC]/10"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-pen h-4 w-4" aria-hidden="true">
                                    <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"></path>
                                </svg><span>Quản lý</span></a>
                                <button type="button"
                                    class="inline-flex rounded p-0.5 text-txt-secondary transition-colors hover:text-error-error focus:outline-none focus-visible:ring-2 focus-visible:ring-error-error/50"
                                    data-manage-manga-delete
                                    data-manga-id="{{ $manga->id }}"
                                    data-manga-title="{{ e($manga->title) }}"
                                    aria-label="Xóa truyện">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2 lucide-trash-2 h-5 w-5 pointer-events-none" aria-hidden="true">
                                        <path d="M3 6h18"></path>
                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                        <line x1="10" x2="10" y1="11" y2="17"></line>
                                        <line x1="14" x2="14" y1="11" y2="17"></line>
                                    </svg>
                                </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @if ($mangas->hasPages())
            <div class="mt-4 w-full text-sm text-txt-secondary">{{ $mangas->links('theme-vinahentai::components.pagination') }}</div>
            @endif
            @endif
        </div>
    </div>

    @include('theme-vinahentai::user.partials.translator-guide-modal')
@endsection

@push('scripts')
    <script>
        (function () {
            const root = document.querySelector("[data-translator-guide-root]");
            const openBtn = document.querySelector("[data-translator-guide-open]");
            const backdrop = document.querySelector("[data-translator-guide-backdrop]");
            const panel = document.querySelector("[data-translator-guide-panel]");
            const closeBtns = root ? root.querySelectorAll("[data-translator-guide-close]") : [];

            if (!root || !openBtn || !panel) {
                return;
            }

            let lastFocus = null;

            function openModal() {
                lastFocus = document.activeElement;
                root.classList.remove("hidden");
                openBtn.setAttribute("aria-expanded", "true");
                document.body.classList.add("overflow-hidden");
                window.setTimeout(function () {
                    try {
                        panel.focus({ preventScroll: true });
                    } catch (e) {}
                }, 0);
            }

            function closeModal() {
                root.classList.add("hidden");
                openBtn.setAttribute("aria-expanded", "false");
                document.body.classList.remove("overflow-hidden");
                if (lastFocus && typeof lastFocus.focus === "function") {
                    try {
                        lastFocus.focus({ preventScroll: true });
                    } catch (e) {}
                }
            }

            openBtn.addEventListener("click", function () {
                openModal();
            });

            backdrop?.addEventListener("click", function () {
                closeModal();
            });

            closeBtns.forEach(function (btn) {
                btn.addEventListener("click", function () {
                    closeModal();
                });
            });

            document.addEventListener("keydown", function (e) {
                if (e.key !== "Escape") {
                    return;
                }
                if (!root.classList.contains("hidden")) {
                    closeModal();
                }
            });
        })();

        (function () {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
            const apiBase = @json(url('/api/manga'));

            document.querySelectorAll("[data-manage-manga-delete]").forEach(function (btn) {
                btn.addEventListener("click", function () {
                    const id = btn.getAttribute("data-manga-id");
                    const title = btn.getAttribute("data-manga-title") || "";
                    if (!id || !csrf) {
                        return;
                    }
                    const msg = title
                        ? "Xóa truyện \"" + title + "\"? Hành động không thể hoàn tác."
                        : "Xóa truyện này? Hành động không thể hoàn tác.";
                    if (!window.confirm(msg)) {
                        return;
                    }

                    btn.disabled = true;
                    fetch(apiBase + "/" + id, {
                        method: "DELETE",
                        headers: {
                            Accept: "application/json",
                            "X-CSRF-TOKEN": csrf,
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        credentials: "same-origin",
                    })
                        .then(function (res) {
                            if (res.status === 403 || res.status === 404) {
                                window.alert("Không thể xóa truyện này.");
                                throw new Error("skip");
                            }
                            if (!res.ok) {
                                window.alert("Có lỗi xảy ra, thử lại sau.");
                                throw new Error("skip");
                            }
                            return res.json().catch(function () {
                                return {};
                            });
                        })
                        .then(function () {
                            const row = btn.closest("[data-manage-manga-row]");
                            if (row && row.parentElement) {
                                row.remove();
                            }
                            if (!document.querySelector("[data-manage-manga-row]")) {
                                window.location.reload();
                            }
                        })
                        .catch(function (err) {
                            if (err && err.message === "skip") {
                                return;
                            }
                            window.alert("Có lỗi mạng, thử lại sau.");
                        })
                        .finally(function () {
                            btn.disabled = false;
                        });
                });
            });
        })();
    </script>
@endpush
