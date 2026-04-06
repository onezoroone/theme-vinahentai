@extends('theme-vinahentai::layout.main')

@php
    /** @var array<string|int, mixed> $rawImageServers */
    $rawImageServers = is_array($chapter->image_servers) ? $chapter->image_servers : [];

    $chapterId = (int) ($chapter->id ?? 0);

    $normalizedServers = collect($rawImageServers)
        ->map(function ($images, $key) use ($chapterId): ?array {
            $urls = collect(is_array($images) ? $images : [])
                ->flatten()
                ->filter(fn($url) => is_string($url) && trim($url) !== '')
                ->map(fn(string $url) => trim($url))
                ->values()
                ->all();

            if ($urls === []) {
                return null;
            }

            $label = is_string($key) && trim($key) !== '' ? trim($key) : 'Server ' . ((int) $key + 1);

            return [
                'id' => md5((string) $key . '|' . $label),
                'label' => $label,
                'images' => $urls,
            ];
        })
        ->filter()
        ->values();

    $defaultServer = $normalizedServers->first();
    $defaultImages = is_array($defaultServer) ? $defaultServer['images'] ?? [] : [];

    $likeCount = (int) ($chapter->like_count ?? 0);
    $dislikeCount = (int) ($chapter->dislike_count ?? 0);

    $me = auth()->user();
    $likedIds = is_array($me?->liked_chapter_ids) ? $me?->liked_chapter_ids : [];
    $dislikedIds = is_array($me?->disliked_chapter_ids) ? $me?->disliked_chapter_ids : [];
    $meLiked = in_array($chapterId, $likedIds, true);
    $meDisliked = in_array($chapterId, $dislikedIds, true);
@endphp

@push('header')
    <link rel="stylesheet" href="{{ asset('vendor/theme-vinahentai/css/swiper.css') }}" />

    <script src="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js"></script>
@endpush

@section('body')
    <div class="container-page mx-auto overflow-x-hidden px-4 py-6 sm:px-6">
        <div data-rht-toaster="" style="position: fixed; z-index: 9999; inset: 16px; pointer-events: none;"></div>
        <div
            class="bg-bgc-layer1 border-bd-default mx-auto flex w-full max-w-[1080px] flex-col gap-4 rounded-xl border p-4 sm:p-6 isolate">
            <div class="flex flex-col gap-2">
                <nav aria-label="Breadcrumb" class="text-txt-focus font-sans text-sm font-medium">
                    <ol class="flex flex-wrap items-center gap-0.5 sm:gap-1">
                        <li class="flex items-center gap-0.5 sm:gap-1"><a class="transition-colors hover:text-lav-500"
                                title="Trang chủ" href="{{ route('home') }}" data-discover="true">Trang chủ</a><span
                                class="text-txt-secondary/60 px-0.5">/</span></li>
                        <li class="flex items-center gap-0.5 sm:gap-1"><a class="transition-colors hover:text-lav-500"
                                title="Reader's Paradise" href="{{ $manga->getUrl() }}"
                                data-discover="true">{{ $manga->title }}</a><span
                                class="text-txt-secondary/60 px-0.5">/</span></li>
                        <li class="flex items-center gap-0.5 sm:gap-1"><span class="text-txt-focus"
                                title="{{ $chapter->title }}">{{ $chapter->title }}</span></li>
                    </ol>
                </nav>
                <div class="text-txt-primary font-sans text-2xl font-semibold leading-loose">{{ $chapter->title }}</div>

                <div class="flex flex-col items-center justify-center gap-3 text-center">
                    <div class="text-txt-primary font-sans text-base font-medium">Đổi <a href="#"
                            rel="nofollow noopener noreferrer"
                            class="text-[17px] font-semibold text-green-400 drop-shadow-[0_0_8px_rgba(34,197,94,0.85)] hover:text-green-300">Server
                            ảnh</a> khi <span
                            class="font-semibold text-red-400 drop-shadow-[0_0_8px_rgba(248,113,113,0.9)]">bị
                            lỗi</span><span>&nbsp;&nbsp;&nbsp;</span></div>
                    <div class="flex flex-wrap items-center justify-center gap-2"
                        style="transform:scale(0.8);transform-origin:center center" data-server-tabs>
                        @forelse ($normalizedServers as $index => $server)
                            <button type="button" data-server-tab="{{ $server['id'] }}"
                                class="rounded-xl border px-4 py-2 text-sm font-semibold transition-colors {{ $index === 0 ? 'border-lav-500 bg-lav-500/15 text-lav-300' : 'border-bd-default text-txt-secondary hover:text-txt-primary' }}">
                                {{ $server['label'] }}
                            </button>
                        @empty
                            <span class="text-sm text-txt-secondary">Chưa có dữ liệu server ảnh.</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="relative z-0 -mx-4 my-6 flex flex-col items-center justify-center overflow-x-hidden sm:mx-0 sm:my-8"
            data-chapter-reader
            data-servers='@json($normalizedServers)'
            data-chapter-view-url="{{ route('chapters.view.record', $chapter) }}"
            data-chapter-view-token="{{ $chapterViewToken }}"
            @auth data-chapter-reading-history-url="{{ route('chapters.reading-history.store', $chapter) }}" @endauth>
            <div class="mb-3 flex w-full max-w-[1080px] items-center justify-center px-2 transition-opacity duration-200 sm:px-0">
                <div class="relative" data-reader-settings-root>
                    <button type="button" data-reader-settings-open class="flex items-center gap-2 px-1 py-1 text-sm font-medium text-white/90" aria-label="Cài đặt chế độ đọc"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings h-4 w-4" aria-hidden="true">
                            <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg><span>Cài đặt chế độ đọc</span>
                    </button>

                    <div class="absolute hidden right-0 top-[calc(100%+8px)] z-50 w-[280px] rounded-2xl border border-white/10 bg-[#0B0F1A] p-4 shadow-2xl" data-reader-settings-panel>
                        <div class="mb-3 flex items-center justify-between">
                            <div class="text-sm font-semibold text-white">Cài đặt chế độ đọc</div><button type="button" data-reader-settings-close class="rounded-lg border border-white/10 p-1.5 text-white/80" aria-label="Đóng cài đặt"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x h-4 w-4" aria-hidden="true">
                                    <path d="M18 6 6 18"></path>
                                    <path d="m6 6 12 12"></path>
                                </svg></button>
                        </div>
                        <div class="space-y-3 text-sm text-white/90">
                            <div>
                                <div class="mb-1 text-white/70">Chế độ đọc</div>
                                <div class="space-y-1">
                                    <button type="button" data-reader-mode="vertical" data-reader-mode-btn class="w-full rounded-lg border px-3 py-2 text-left border-lav-500 bg-white/10 text-lav-500">↕ Dọc</button>
                                    <button type="button" data-reader-mode="horizontal" data-reader-mode-btn class="w-full rounded-lg border px-3 py-2 text-left border-white/10 text-white/90">↔ Ngang</button>
                                </div>
                            </div>
                            <div class="hidden" data-reader-horizontal-options>
                                <div class="mb-1 text-white/70">Hướng đọc</div>
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" data-reader-direction="ltr" data-reader-direction-btn class="rounded-lg border px-3 py-2 border-lav-500 bg-white/10 text-lav-500">→ Trái sang phải</button>
                                    <button type="button" data-reader-direction="rtl" data-reader-direction-btn class="rounded-lg border px-3 py-2 border-white/10 text-white/90">← Phải sang trái</button>
                                </div>
                            </div>
                            <div class="hidden" data-reader-horizontal-options>
                                <div class="mb-1 text-white/70">Hiển thị trang</div>
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" data-reader-page-mode="single" data-reader-page-mode-btn class="rounded-lg border px-3 py-2 border-lav-500 bg-white/10 text-lav-500"><span class="inline-flex items-center gap-2"><span aria-hidden="true" class="inline-flex h-4 w-3 rounded-[2px] border border-current"></span><span>1 trang</span></span></button>
                                    <button type="button" data-reader-page-mode="double" data-reader-page-mode-btn class="rounded-lg border px-3 py-2 border-white/10 text-white/90"><span class="inline-flex items-center gap-2"><span aria-hidden="true" class="inline-flex items-center gap-0.5"><span class="inline-flex h-4 w-3 rounded-[2px] border border-current"></span><span class="inline-flex h-4 w-3 rounded-[2px] border border-current"></span></span><span>2 trang</span></span></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full max-w-[1080px] flex flex-col gap-0" data-reader-images>
                @forelse ($defaultImages as $idx => $url)
                    <div class="relative flex w-full items-center justify-center">
                        <div class="w-full">
                            <div class="relative">
                                <img alt="Trang {{ $idx + 1 }}" class="block w-full opacity-100 lozad"
                                    data-src="{{ $url }}"
                                    src="data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==">
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-sm text-txt-secondary py-8">
                        Chương này chưa có ảnh để hiển thị.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mx-auto mb-6 flex w-full max-w-[1080px] flex-col gap-2 px-0">
            <div class="rounded-2xl border border-white/10 bg-[#05070F] px-0 py-0 shadow-none w-full"
                data-chapter-reaction-root
                data-chapter-reaction-status-url="{{ route('chapters.reaction.status', $chapter) }}"
                data-chapter-reaction-react-url="{{ route('chapters.reaction.react', $chapter) }}"
                data-chapter-login-url="{{ route('login') }}"
                data-chapter-is-logged-in="{{ auth()->check() ? '1' : '0' }}"
                data-chapter-report-url="{{ route('chapters.report', $chapter) }}">
                <div class="flex w-full items-center justify-center gap-[2.5%] px-2 pt-2 sm:hidden">
                    @if ($prevChapter)
                        <a href="{{ $prevChapter->getUrl() }}"
                            type="button" style="width:25%"
                            class="flex h-10 shrink-0 items-center justify-center rounded-xl border px-0 py-0 text-sm font-semibold transition-all border-transparent bg-gradient-to-b from-[#DD94FF] to-[#D373FF] text-bgc-layer1 shadow-[0px_4px_8.9px_0px_rgba(196,69,255,0.25)] hover:brightness-105"
                            aria-label="Chương trước">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-chevron-left h-4 w-4" aria-hidden="true">
                                <path d="m15 18-6-6 6-6"></path>
                            </svg>
                        </a>
                    @else
                        <button type="button" disabled style="width:25%"
                            class="flex h-10 shrink-0 items-center justify-center rounded-xl border px-0 py-0 text-sm font-semibold transition-all border-white/10 bg-[#141727] text-white/40 cursor-not-allowed opacity-70"
                            aria-label="Chương trước">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-chevron-left h-4 w-4" aria-hidden="true">
                                <path d="m15 18-6-6 6-6"></path>
                            </svg>
                        </button>
                    @endif

                    <button type="button" style="width:20%" data-chapter-react="like" data-chapter-reaction-btn
                        class="{{ $meLiked ? 'relative flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl border px-2 text-sm font-semibold transition-all border-transparent bg-gradient-to-r from-pink-500 to-yellow-300 text-bgc-layer1 shadow-lg' : 'relative flex h-10 shrink-0 items-center justify-center gap-1 rounded-xl border px-2 text-sm font-semibold transition-all border-white/10 bg-[#141727] text-white/80 hover:bg-[#1b1f33]' }}"
                        aria-pressed="{{ $meLiked ? 'true' : 'false' }}" aria-label="Like chương">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round"
                            class="lucide lucide-thumbs-up h-4 w-4 {{ $meLiked ? 'text-bgc-layer1' : 'text-green-500' }}"
                            aria-hidden="true">
                            <path d="M7 10v12"></path>
                            <path
                                d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z">
                            </path>
                        </svg>
                        <span class="tabular-nums" data-chapter-like-count>{{ $likeCount }}</span>
                    </button>

                    <button type="button" style="width:20%" data-chapter-react="dislike" data-chapter-reaction-btn
                        class="{{ $meDisliked ? 'flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl border px-2 text-sm font-semibold transition-all border-transparent bg-gradient-to-b from-[#DD94FF] to-[#D373FF] text-bgc-layer1' : 'flex h-10 shrink-0 items-center justify-center gap-1 rounded-xl border px-2 text-sm font-semibold transition-all border-white/10 bg-[#141727] text-white/80 hover:bg-[#1b1f33]' }}"
                        aria-pressed="{{ $meDisliked ? 'true' : 'false' }}" aria-label="Dislike chương">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-thumbs-down h-4 w-4 text-red-500"
                            aria-hidden="true">
                            <path d="M17 14V2"></path>
                            <path
                                d="M9 18.12 10 14H4.17a2 2 0 0 1-1.92-2.56l2.33-8A2 2 0 0 1 6.5 2H20a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2.76a2 2 0 0 0-1.79 1.11L12 22a3.13 3.13 0 0 1-3-3.88Z">
                            </path>
                        </svg>
                        <span class="tabular-nums" data-chapter-dislike-count>{{ $dislikeCount }}</span>
                    </button>

                    @if ($nextChapter)
                        <a href="{{ $nextChapter->getUrl() }}" type="button" style="width:25%"
                            class="flex h-10 shrink-0 items-center justify-center rounded-xl border px-0 py-0 text-sm font-semibold transition-all border-transparent bg-gradient-to-b from-[#DD94FF] to-[#D373FF] text-bgc-layer1 shadow-[0px_4px_8.9px_0px_rgba(196,69,255,0.25)] hover:brightness-105"
                            aria-label="Chương sau">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-chevron-right h-4 w-4" aria-hidden="true">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg>
                        </a>
                    @else
                        <button type="button" disabled="" style="width:25%"
                            class="flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl border px-0 py-0 text-sm font-semibold transition-all border-white/10 bg-[#141727] text-white/40 cursor-not-allowed opacity-70"
                            aria-label="Chương sau">
                            <span class="hidden sm:inline">Chương sau</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-chevron-right h-4 w-4" aria-hidden="true">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg>
                        </button>
                    @endif
                </div>
                <div class="px-2 pb-2 pt-1 sm:hidden">
                    <div class="text-center text-[10px] font-medium text-white/65" data-chapter-liked-label>
                        {{ $likeCount }} người đã thích chương này
                    </div>
                </div>
                <div class="hidden sm:flex w-full items-center justify-center gap-[2.5%] px-2 pt-2">
                    @if ($prevChapter)
                        <a href="{{ $prevChapter->getUrl() }}" style="width:25%"
                            class="flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl border px-0 py-0 text-sm font-semibold transition-all border-transparent bg-gradient-to-b from-[#DD94FF] to-[#D373FF] text-bgc-layer1 shadow-[0px_4px_8.9px_0px_rgba(196,69,255,0.25)] hover:brightness-105"><svg
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-chevron-left h-4 w-4" aria-hidden="true">
                                <path d="m15 18-6-6 6-6"></path>
                            </svg><span class="hidden sm:inline">Chương trước</span>
                        </a>
                    @else
                        <button disabled type="button" style="width:25%"
                            class="flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl border px-0 py-0 text-sm font-semibold transition-all border-transparent bg-gradient-to-b from-[#DD94FF] to-[#D373FF] text-bgc-layer1 shadow-[0px_4px_8.9px_0px_rgba(196,69,255,0.25)] hover:brightness-105"><svg
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-chevron-left h-4 w-4" aria-hidden="true">
                                <path d="m15 18-6-6 6-6"></path>
                            </svg><span class="hidden sm:inline">Chương trước</span>
                        </button>
                    @endif
                    <button type="button" style="width:20%" data-chapter-react="like" data-chapter-reaction-btn
                        class="relative flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl border px-2 text-sm font-semibold transition-all border-white/10 bg-[#141727] text-white/80 hover:bg-[#1b1f33]  "
                        aria-pressed="{{ $meLiked ? 'true' : 'false' }}" aria-label="Like chương"><svg xmlns="http://www.w3.org/2000/svg"
                            width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-thumbs-up h-4 w-4 text-green-500" aria-hidden="true">
                            <path d="M7 10v12"></path>
                            <path
                                d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z">
                            </path>
                    </svg><span class="tabular-nums">{{ $likeCount }}</span></button><button type="button"
                        style="width:20%" data-chapter-react="dislike" data-chapter-reaction-btn
                        class="flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl border px-2 text-sm font-semibold transition-all border-white/10 bg-[#141727] text-white/80 hover:bg-[#1b1f33] "
                        aria-pressed="{{ $meDisliked ? 'true' : 'false' }}" aria-label="Dislike chương"><svg xmlns="http://www.w3.org/2000/svg"
                            width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-thumbs-down h-4 w-4 text-red-500" aria-hidden="true">
                            <path d="M17 14V2"></path>
                            <path
                                d="M9 18.12 10 14H4.17a2 2 0 0 1-1.92-2.56l2.33-8A2 2 0 0 1 6.5 2H20a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2.76a2 2 0 0 0-1.79 1.11L12 22a3.13 3.13 0 0 1-3-3.88Z">
                            </path>
                        </svg><span class="tabular-nums">{{ $dislikeCount }}</span></button>
                    @if ($nextChapter)
                        <a href="{{ $nextChapter->getUrl() }}" style="width:25%"
                            class="flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl border px-0 py-0 text-sm font-semibold transition-all border-transparent bg-gradient-to-b from-[#DD94FF] to-[#D373FF] text-bgc-layer1 shadow-[0px_4px_8.9px_0px_rgba(196,69,255,0.25)] hover:brightness-105"
                            aria-label="Chương sau">
                            <span class="hidden sm:inline">Chương sau</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-chevron-right h-4 w-4" aria-hidden="true">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg>
                        </a>
                    @else
                        <button type="button" disabled="" style="width:25%"
                            class="flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl border px-0 py-0 text-sm font-semibold transition-all border-white/10 bg-[#141727] text-white/40 cursor-not-allowed opacity-70"
                            aria-label="Chương sau">
                            <span class="hidden sm:inline">Chương sau</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-chevron-right h-4 w-4" aria-hidden="true">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg>
                        </button>
                    @endif
                </div>
                <div class="hidden sm:block px-2 pb-2 pt-1">
                    <div class="text-center text-[11px] text-white/65" data-chapter-liked-label>
                        {{ $likeCount }} người đã thích chương này
                    </div>
                </div>
                <button type="button" data-chapter-report-open="{{ $chapter->id }}"
                    class="mt-2 flex items-center justify-center gap-2 rounded-xl border border-red-500 px-0 py-0 h-10 w-full text-sm font-semibold text-red-400 bg-transparent transition-all shadow-[0px_4px_8.9px_0px_rgba(239,68,68,0.25)] hover:bg-red-500/10"><svg
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-triangle-alert h-4 w-4" aria-hidden="true">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"></path>
                        <path d="M12 9v4"></path>
                        <path d="M12 17h.01"></path>
                    </svg>Báo mọi lỗi<span class="ml-2 text-xs font-normal text-red-300 whitespace-nowrap">(mất ảnh, lỗi
                        ảnh...)</span>
                </button>
            </div>
        </div>

        <div class="fixed bottom-0 left-0 right-0 z-40 w-full will-change-transform transition-transform duration-200 ease-out translate-y-0"
            data-chapter-bottom-bar
            style="padding-bottom:env(safe-area-inset-bottom);--ctrl-size:clamp(44px, 8vw, 52px);margin-bottom:0">
            <div
                class="mx-auto flex max-w-[1080px] items-center justify-between border-t border-white/10 px-4 py-2 bg-[#0B0F1A]/80 backdrop-blur rounded-t-xl shadow-[0_-6px_16px_-8px_rgba(0,0,0,0.35)]">
                <a href="{{ $manga->getUrl() }}" aria-label="Về trang truyện"
                    class="flex items-center justify-center rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] shadow transition-all hover:shadow-lg"
                    style="width:var(--ctrl-size);height:var(--ctrl-size)"><span aria-hidden="true"
                        class="text-bgc-layer1 text-2xl leading-none">🎴</span>
                </a>

                <div class="flex items-center justify-center gap-3">
                    @if($prevChapter)
                    <a href="{{ $prevChapter->getUrl() }}" class="flex items-center justify-center rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] p-3 shadow transition-all cursor-pointer hover:shadow-lg"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left text-bgc-layer1 h-5 w-5" aria-hidden="true"><path d="m15 18-6-6 6-6"></path></svg></a>
                    @else
                    <button disabled="" class="flex items-center justify-center rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] p-3 shadow transition-all cursor-not-allowed opacity-50"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right text-bgc-layer1 h-5 w-5" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg></button>
                    @endif

                    <div class="relative min-w-[140px]" data-chapter-dropdown-root>
                        <button type="button" aria-haspopup="listbox" aria-expanded="false"
                            data-chapter-dropdown-trigger
                            class="bg-bgc-layer2 border-bd-default flex w-full items-center justify-between rounded-xl border px-3 py-2.5 font-sans text-base font-medium transition-colors focus:outline-none text-txt-primary focus:border-lav-500 hover:border-txt-secondary ">
                            <span class="text-txt-primary truncate" data-chapter-dropdown-label>
                                {{ $chapter->title ?? 'Chương '.$chapter->chapter_number }}
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="lucide lucide-chevron-down text-txt-secondary h-6 w-6 transition-transform"
                                aria-hidden="true">
                                <path d="m6 9 6 6 6-6"></path>
                            </svg>
                        </button>

                        <div data-dropdown-portal="true" class="hidden" data-chapter-dropdown-portal>
                            <div role="listbox" data-chapter-dropdown-list
                                class="bg-bgc-layer2 border-bd-default fixed z-[999] overflow-y-auto rounded-xl border shadow-lg origin-bottom"
                                >
                                @foreach ($manga->chapters as $item)
                                    @php
                                        $isCurrent = (int) $item->id === (int) $chapter->id;
                                    @endphp
                                    <button role="option"
                                        aria-selected="{{ $isCurrent ? 'true' : 'false' }}"
                                        type="button"
                                        data-chapter-dropdown-option
                                        data-chapter-target-url="{{ $item->getUrl() }}"
                                        class="w-full px-3 py-2.5 text-left font-sans text-base font-medium transition-colors first:rounded-t-xl last:rounded-b-xl {{ $isCurrent ? 'bg-bgc-layer-semi-purple text-txt-focus' : 'text-txt-primary hover:bg-bgc-layer-semi-neutral' }}"
                                        title="{{ $item->title }}">
                                        {{ $item->title }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @if($nextChapter)
                    <a href="{{ $nextChapter->getUrl() }}" class="flex items-center justify-center rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] p-3 shadow transition-all cursor-pointer hover:shadow-lg"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right text-bgc-layer1 h-5 w-5" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg></a>
                    @else
                    <button disabled="" class="flex items-center justify-center rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] p-3 shadow transition-all cursor-not-allowed opacity-50"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right text-bgc-layer1 h-5 w-5" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg></button>
                    @endif
                </div>

                <div class="flex flex-col items-center justify-between rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] shadow overflow-hidden" style="width:var(--ctrl-size);height:var(--ctrl-size)">
                    <button type="button" aria-label="Cuộn lên đầu" data-chapter-scroll-top
                        class="flex h-full w-full items-center justify-center hover:opacity-80">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-arrow-up-to-line text-bgc-layer1 h-5 w-5"
                            aria-hidden="true">
                            <path d="M5 3h14"></path>
                            <path d="m18 13-6-6-6 6"></path>
                            <path d="M12 7v14"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div>
            <section class="mt-8">
                <div class="flex items-center justify-between"><div class="flex items-center gap-3"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-thumbs-up h-4 w-4 text-lav-500" aria-hidden="true"><path d="M7 10v12"></path><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"></path></svg><h2 class="text-txt-primary text-xl font-semibold uppercase">có thể bạn sẽ thích</h2></div></div>

                <div class="mt-4">
                    <div class="relative swiper swiper-related">
                        <div class="swiper-wrapper">
                        @foreach ($relatedMangas as $mangaItem)
                        <div class="swiper-slide">
                            @include('theme-vinahentai::components.item', ['manga' => $mangaItem])
                        </div>
                        @endforeach
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-center gap-3">
                        <button type="button" class="h-8 w-8 rounded-full border border-bd-default bg-bgc-layer1 text-txt-primary transition hover:bg-bgc-layer2 btn-prev" aria-label="Xem truyện trước"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left mx-auto h-4 w-4" aria-hidden="true"><path d="m15 18-6-6 6-6"></path></svg></button>
                        <button type="button" class="h-8 w-8 rounded-full border border-bd-default bg-bgc-layer1 text-txt-primary transition hover:bg-bgc-layer2 btn-next" aria-label="Xem truyện tiếp theo"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right mx-auto h-4 w-4" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg></button>
                    </div>
                </div>
            </section>
        </div>

        @include('theme-vinahentai::components.comments', ['manga' => $manga, 'chapter' => $chapter])
    </div>

    <div class="hidden" data-chapter-report-root aria-hidden="true">
        <div data-chapter-report-overlay data-state="closed"
            class="data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 fixed inset-0 z-[210] bg-black/50">
        </div>
        <div role="dialog" aria-modal="true" aria-labelledby="chapter-report-title" data-chapter-report-dialog
            data-state="closed"
            class="data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[state=closed]:slide-out-to-left-1/2 data-[state=closed]:slide-out-to-top-[48%] data-[state=open]:slide-in-from-left-1/2 data-[state=open]:slide-in-from-top-[48%] fixed top-[50%] left-[50%] z-[211] translate-x-[-50%] translate-y-[-50%] w-[320px] max-w-[95vw]"
            tabindex="-1">
            <form
                class="bg-bgc-layer1 border-bd-default flex max-h-[90vh] w-[320px] flex-col gap-6 overflow-hidden rounded-2xl border p-4 sm:w-[400px] sm:gap-10 sm:p-6 md:w-[500px]"
                data-chapter-report-form>
                <div class="flex flex-col gap-4 sm:gap-6">
                    <h2 id="chapter-report-title"
                        class="text-txt-primary text-center font-sans text-xl leading-loose font-semibold sm:text-2xl">
                        Nội dung báo cáo
                    </h2>
                    <div class="flex h-32 flex-col gap-1.5 sm:h-44">
                        <label for="chapter-report-textarea"
                            class="text-txt-primary font-sans text-sm leading-normal font-semibold sm:text-base">Nội
                            dung</label>
                        <div class="flex flex-1 flex-col">
                            <textarea id="chapter-report-textarea" name="content" maxlength="2000"
                                placeholder="Nhập nội dung báo cáo tại đây..." data-chapter-report-text
                                class="bg-bgc-layer2 border-bd-default text-txt-primary placeholder:text-txt-secondary focus:border-lav-500 focus:ring-lav-500 flex-1 resize-none rounded-xl border px-3 py-2.5 font-sans text-sm leading-normal font-medium transition-colors outline-none focus:ring-1 sm:text-base"
                                rows="4"></textarea>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <button type="button" data-chapter-report-close
                        class="border-lav-500 hover:bg-lav-500/10 flex flex-1 items-center justify-center gap-2.5 rounded-xl border px-4 py-3 shadow-[0px_4px_8.9px_0px_rgba(146,53,190,0.25)] transition-colors">
                        <span class="text-txt-focus text-center font-sans text-sm leading-tight font-semibold">Đóng</span>
                    </button>
                    <button type="submit" disabled data-chapter-report-submit
                        class="flex flex-1 items-center justify-center gap-2.5 rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] px-4 py-3 shadow-[0px_4px_8.9px_0px_rgba(196,69,255,0.25)] transition-all hover:from-[#E1A3FF] hover:to-[#DC85FF] disabled:cursor-not-allowed disabled:opacity-50">
                        <span class="text-center font-sans text-sm leading-tight font-semibold text-black">Gửi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="{{ asset('vendor/theme-vinahentai/js/chapter.js') }}"></script>
    <script>
        const swiper = new Swiper('.swiper-related', {
            slidesPerView: 5,
            spaceBetween: 3,
            breakpoints: {
                1024: {
                    slidesPerView: 5,
                },
                0: {
                    slidesPerView: 2,
                },
            },
            navigation: {
                nextEl: '.btn-next',
                prevEl: '.btn-prev',
            },
        });
    </script>
@endpush
