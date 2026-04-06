@extends('theme-vinahentai::layout.main')

@section('body')
    <div class="bg-bgc-layer1 text-txt-primary">
        <div class="container-page mx-auto px-4 py-10 lg:py-14">
            <div class="mb-10 flex flex-col gap-6 lg:mb-12 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-4 lg:w-3/5">
                    <p class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-fuchsia-500/10 to-rose-500/10 px-3 py-1 text-sm font-semibold uppercase tracking-wide text-fuchsia-300">
                        {{ env('APP_NAME') }}
                    </p>
                    <div class="rounded-2xl border border-white/5 bg-bgc-layer2/60 p-4 text-sm leading-relaxed text-txt-secondary"><strong class="text-txt-primary">Cảnh báo 18+:</strong> Đây là trang web 18+, nghiêm cấm người dưới 18 tuổi truy cập.</div>
                    <h1 class="text-3xl font-black leading-tight sm:text-4xl lg:text-5xl">Giới thiệu {{ env('APP_NAME') }} – Website đọc truyện hentai 18+ chất lượng</h1>
                    <p class="text-lg text-txt-secondary sm:text-xl">Nơi bạn khám phá truyện hentai 18+ với trải nghiệm mượt, không quảng cáo làm phiền, cập nhật liên tục và phân loại rõ ràng để tìm nhanh thể loại yêu thích.</p>
                    <div class="flex flex-wrap gap-3"><a class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-fuchsia-500 to-rose-500 px-4 py-2 text-sm font-semibold text-white shadow-lg transition-transform hover:translate-y-[-2px]" href="{{ route('home') }}" data-discover="true">Bắt đầu đọc ngay<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right h-4 w-4" aria-hidden="true">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg></a><a class="inline-flex items-center gap-2 rounded-full border border-white/10 px-4 py-2 text-sm font-semibold text-txt-primary transition-colors hover:border-fuchsia-400 hover:text-white" href="{{ route('search.advanced') }}" data-discover="true">Tìm truyện nhanh</a></div>
                </div>
                <div class="grid w-full grid-cols-2 gap-4 rounded-2xl bg-bgc-layer2 p-5 shadow-lg lg:w-2/5">
                    <div class="rounded-xl border border-white/5 bg-bgc-layer1 p-4 shadow-sm">
                        <div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/5 text-fuchsia-200"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sparkles h-5 w-5" aria-hidden="true">
                                    <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"></path>
                                    <path d="M20 3v4"></path>
                                    <path d="M22 5h-4"></path>
                                    <path d="M4 17v2"></path>
                                    <path d="M5 18H3"></path>
                                </svg></span>
                            <div>
                                <p class="text-base font-semibold">Nội dung chọn lọc</p>
                                <p class="text-sm text-txt-secondary">Doujinshi, 3D, anime hentai… được duyệt kỹ để giữ chất lượng.</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-white/5 bg-bgc-layer1 p-4 shadow-sm">
                        <div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/5 text-fuchsia-200"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield h-5 w-5" aria-hidden="true">
                                    <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path>
                                </svg></span>
                            <div>
                                <p class="text-base font-semibold">Không quảng cáo</p>
                                <p class="text-sm text-txt-secondary">Tập trung đọc, không bị làm phiền bởi pop-up khó chịu.</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-white/5 bg-bgc-layer1 p-4 shadow-sm">
                        <div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/5 text-fuchsia-200"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open h-5 w-5" aria-hidden="true">
                                    <path d="M12 7v14"></path>
                                    <path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"></path>
                                </svg></span>
                            <div>
                                <p class="text-base font-semibold">Trải nghiệm mượt</p>
                                <p class="text-sm text-txt-secondary">Tải nhanh, tối ưu cho mobile và desktop.</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-white/5 bg-bgc-layer1 p-4 shadow-sm">
                        <div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/5 text-fuchsia-200"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trophy h-5 w-5" aria-hidden="true">
                                    <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path>
                                    <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path>
                                    <path d="M4 22h16"></path>
                                    <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path>
                                    <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path>
                                    <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path>
                                </svg></span>
                            <div>
                                <p class="text-base font-semibold">Cộng đồng nhiệt</p>
                                <p class="text-sm text-txt-secondary">Bình luận, theo dõi, leaderboard cập nhật theo tuần.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-[1.6fr_1fr]">
                <section class="space-y-6 rounded-2xl bg-bgc-layer2 p-6 shadow-lg lg:p-8">
                    <header class="space-y-2">
                        <h2 class="text-2xl font-bold sm:text-3xl">Sứ mệnh và trải nghiệm</h2>
                        <p class="text-txt-secondary">{{ env('APP_NAME') }} hướng tới việc trở thành điểm đến an toàn, nhanh và thân thiện cho người yêu thích hentai 18+ tại Việt Nam.</p>
                    </header>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="rounded-xl border border-white/5 bg-bgc-layer1 p-4 shadow-sm">
                            <h4 class="text-lg font-semibold">Kho truyện đa dạng</h4>
                            <p class="mt-2 text-sm text-txt-secondary">NTR, MILF, 3D, doujinshi… được phân loại rõ ràng và cập nhật liên tục.</p>
                            <p class="mt-3 inline-flex items-center gap-2 rounded-full bg-white/5 px-3 py-1 text-xs font-semibold text-fuchsia-200"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sparkles h-4 w-4" aria-hidden="true">
                                    <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"></path>
                                    <path d="M20 3v4"></path>
                                    <path d="M22 5h-4"></path>
                                    <path d="M4 17v2"></path>
                                    <path d="M5 18H3"></path>
                                </svg>Ưu tiên truyện hot, tránh trùng lặp</p>
                        </div>
                        <div class="rounded-xl border border-white/5 bg-bgc-layer1 p-4 shadow-sm">
                            <h4 class="text-lg font-semibold">Tối ưu tốc độ</h4>
                            <p class="mt-2 text-sm text-txt-secondary">Ảnh nén hợp lý, CDN, lazy-load để đọc mượt trên mạng di động.</p>
                            <p class="mt-3 inline-flex items-center gap-2 rounded-full bg-white/5 px-3 py-1 text-xs font-semibold text-fuchsia-200"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sparkles h-4 w-4" aria-hidden="true">
                                    <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"></path>
                                    <path d="M20 3v4"></path>
                                    <path d="M22 5h-4"></path>
                                    <path d="M4 17v2"></path>
                                    <path d="M5 18H3"></path>
                                </svg>Giảm giật lag trên mobile</p>
                        </div>
                        <div class="rounded-xl border border-white/5 bg-bgc-layer1 p-4 shadow-sm">
                            <h4 class="text-lg font-semibold">Theo dõi &amp; gợi ý</h4>
                            <p class="mt-2 text-sm text-txt-secondary">Theo dõi truyện, lưu lịch sử, gợi ý dựa trên thể loại bạn thích.</p>
                            <p class="mt-3 inline-flex items-center gap-2 rounded-full bg-white/5 px-3 py-1 text-xs font-semibold text-fuchsia-200"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sparkles h-4 w-4" aria-hidden="true">
                                    <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"></path>
                                    <path d="M20 3v4"></path>
                                    <path d="M22 5h-4"></path>
                                    <path d="M4 17v2"></path>
                                    <path d="M5 18H3"></path>
                                </svg>Leaderboard, bình luận realtime</p>
                        </div>
                        <div class="rounded-xl border border-white/5 bg-bgc-layer1 p-4 shadow-sm">
                            <h4 class="text-lg font-semibold">Tôn trọng người dùng</h4>
                            <p class="mt-2 text-sm text-txt-secondary">Không chèn quảng cáo gây khó chịu; ưu tiên nội dung sạch, nguồn rõ ràng.</p>
                            <p class="mt-3 inline-flex items-center gap-2 rounded-full bg-white/5 px-3 py-1 text-xs font-semibold text-fuchsia-200"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sparkles h-4 w-4" aria-hidden="true">
                                    <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"></path>
                                    <path d="M20 3v4"></path>
                                    <path d="M22 5h-4"></path>
                                    <path d="M4 17v2"></path>
                                    <path d="M5 18H3"></path>
                                </svg>Có nút báo cáo nội dung vi phạm</p>
                        </div>
                    </div>
                    <div class="rounded-xl border border-white/5 bg-bgc-layer1 p-5">
                        <h3 class="text-xl font-semibold">Liên kết nhanh các thể loại được tìm nhiều</h3>
                        <p class="mt-2 text-txt-secondary">Truy cập trực tiếp để xem danh sách truyện mới nhất theo thể loại:</p>
                        <div class="mt-4 flex flex-wrap gap-3">
                            @php
                                $top4Genres = \App\Models\Genre::withCount('mangas')->orderByDesc('mangas_count')->take(4)->get();
                            @endphp
                            @foreach ($top4Genres as $genre)
                                <a class="inline-flex items-center gap-2 rounded-full border border-white/10 px-3 py-1.5 text-sm font-semibold text-txt-primary transition-colors hover:border-fuchsia-400 hover:text-white" href="{{ route('genres.show', $genre->slug) }}" data-discover="true"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right h-3.5 w-3.5" aria-hidden="true">
                                    <path d="M5 12h14"></path>
                                    <path d="m12 5 7 7-7 7"></path>
                                </svg>{{ $genre->name }}</a>

                            @endforeach
                        </div>
                    </div>
                </section>
                <aside class="space-y-6">
                    <div class="rounded-2xl bg-bgc-layer2 p-6 shadow-lg">
                        <h3 class="text-xl font-bold">Cam kết nội dung</h3>
                        <ul class="mt-3 space-y-3 text-txt-secondary">
                            <li>- Chỉ hiển thị truyện dành cho 18+, có cảnh báo rõ ràng.</li>
                            <li>- Ưu tiên nguồn gốc minh bạch, hạn chế trùng lặp.</li>
                            <li>- Xóa bỏ nội dung vi phạm khi có báo cáo.</li>
                        </ul>
                    </div>
                    <div class="rounded-2xl bg-gradient-to-br from-fuchsia-600 to-rose-600 p-6 text-white shadow-lg">
                        <h3 class="text-xl font-bold">Trải nghiệm ngay</h3>
                        <p class="mt-2 text-white/90">Khám phá truyện mới, theo dõi tác giả yêu thích và tham gia bình luận.</p>
                        <div class="mt-4 flex flex-wrap gap-3"><a class="inline-flex items-center gap-2 rounded-lg bg-white/10 px-4 py-2 text-sm font-semibold backdrop-blur transition hover:bg-white/20" href="{{ route('search.advanced') }}" data-discover="true">Tìm nâng cao</a><a class="inline-flex items-center gap-2 rounded-lg bg-black/20 px-4 py-2 text-sm font-semibold backdrop-blur transition hover:bg-black/30" href="{{ route('leaderboard.manga') }}" data-discover="true">Xem bảng xếp hạng</a></div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
@endsection
