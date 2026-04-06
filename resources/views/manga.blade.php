@extends('theme-vinahentai::layout.main')

@section('body')
    <div class="container-page mx-auto px-4 pb-24 pt-1 md:pt-6 md:pb-32">
        <div class="mb-4 hidden items-start justify-between gap-4 md:flex">
            <nav aria-label="Breadcrumb" class="text-txt-focus font-sans text-sm font-medium">
                <ol class="flex flex-wrap items-center gap-0.5 sm:gap-1">
                    <li class="flex items-center gap-0.5 sm:gap-1"><a class="transition-colors hover:text-lav-500" href="{{ route('home') }}" data-discover="true">Trang chủ</a><span class="text-txt-secondary/60 px-0.5">/</span></li>
                    <li><span class="text-txt-focus" title="{{ $manga->title }}">{{ $manga->title }}</span></li>
                </ol>
            </nav>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-[minmax(0,1fr)_27vw] lg:grid-cols-[minmax(0,1fr)_22vw]">
            <section>
                <nav aria-label="Breadcrumb" class="text-txt-focus font-sans text-sm font-medium mb-3 md:hidden">
                    <ol class="flex flex-wrap items-center gap-0.5 sm:gap-1">
                        <li class="flex items-center gap-0.5 sm:gap-1"><a class="transition-colors hover:text-lav-500" href="{{ route('home') }}" data-discover="true">Trang chủ</a><span class="text-txt-secondary/60 px-0.5">/</span></li>
                        <li><span class="text-txt-focus" title="{{ $manga->title }}">{{ $manga->title }}</span></li>
                    </ol>
                </nav>

                <div class="w-full">
                    <div class="flex flex-col gap-6 portrait:gap-[0.9rem] sm:portrait:gap-10 md:flex-row md:items-start md:gap-5">
                        <div class="relative mx-auto flex flex-shrink-0 items-center justify-center overflow-hidden rounded-lg h-[403px] w-[269px] portrait:w-[302px] sm:portrait:w-[269px] md:mx-0 md:mt-[5px] md:w-[min(280px,36vw)] md:h-auto md:aspect-[2/3]  md:self-start"><img alt="{{ $manga->title }}" class="h-full w-full object-cover" loading="lazy" decoding="async" src="{{ $manga->cover_image }}"></div>

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
                                    @php
                                        $mangaTotalChapterLikes = (int) ($manga->chapters_sum_like_count ?? 0);
                                    @endphp
                                    <span class="text-txt-secondary inline-flex items-center gap-1.5 text-sm" title="Tổng lượt thích các chương" aria-label="Tổng lượt thích các chương">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-thumbs-up h-4 w-4" aria-hidden="true">
                                            <path d="M7 10v12"></path>
                                            <path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"></path>
                                        </svg>
                                        <span class="text-txt-primary font-medium">
                                            {{ number_format($mangaTotalChapterLikes) }}
                                        </span>
                                    </span>
                                    <span class="text-txt-secondary inline-flex items-center gap-1.5 text-sm" title="Mã truyện"><span class="text-txt-primary font-medium select-all">{{ $manga->id }}</span></span>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-center gap-2 md:justify-start">
                                <button
                                    class="border-lav-500 text-txt-focus hover:bg-lav-500/10 flex items-center justify-center gap-1 rounded-lg border px-3 py-2 transition-colors cursor-pointer"
                                    aria-pressed="false"
                                    aria-label="Theo dõi"
                                    data-follow-btn
                                    data-manga-id="{{ $manga->id }}"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bookmark h-4 w-4" aria-hidden="true"><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"></path></svg>
                                    <span class="text-sm font-semibold">
                                        <span data-follow-label>Theo dõi</span>
                                        (<span data-follow-count>{{ number_format($manga->total_follows) }}</span>)
                                    </span>
                                </button>

                                @auth
                                    @if (count($manga->chapters) > 0)
                                        {{-- Hiện sau khi GET /api/manga/{id}/reading-history/latest có entry (script dưới). --}}
                                        <a
                                            href="#"
                                            class="hidden flex min-w-[7.65rem] justify-center rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] px-[0.85rem] py-3 text-black shadow-[0px_4px_8.9px_0px_rgba(196,69,255,0.25)] transition-transform hover:scale-105"
                                            data-manga-resume-read
                                            aria-label="Đọc tiếp"
                                        >
                                            <span class="text-base font-semibold" data-manga-resume-read-label>Đọc tiếp</span>
                                        </a>
                                    @endif
                                @endauth

                                @if (count($manga->chapters) > 0)
                                <a href="{{ $manga->chapters->last()->getUrl() }}" class="flex min-w-[7.65rem] justify-center rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] px-[0.85rem] py-3 text-black shadow-[0px_4px_8.9px_0px_rgba(196,69,255,0.25)] transition-transform hover:scale-105" aria-label="Đọc từ đầu"><span class="text-base font-semibold">Đọc từ đầu</span></a>
                                <a href="{{ $manga->chapters->first()->getUrl() }}" class="flex min-w-[7.65rem] justify-center rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] px-[0.85rem] py-3 text-black shadow-[0px_4px_8.9px_0px_rgba(196,69,255,0.25)] transition-transform hover:scale-105" aria-label="Đọc mới nhất"><span class="text-base font-semibold">Đọc mới nhất</span></a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col gap-[0.8rem]" id="manga-description-section">
                        <div class="flex items-center gap-3"><h2 class="text-txt-primary text-xl font-semibold uppercase">GIỚI THIỆU</h2></div>
                        <div class="relative">
                            <div class="text-txt-secondary text-base font-medium leading-[1.25] whitespace-pre-line">
                                {!! $manga->description !!}
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col gap-4">
                        <div class="flex items-center gap-3"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-lav-500"><line x1="8" x2="21" y1="6" y2="6"></line><line x1="8" x2="21" y1="12" y2="12"></line><line x1="8" x2="21" y1="18" y2="18"></line><path d="M3 6h.01"></path><path d="M3 12h.01"></path><path d="M3 18h.01"></path></svg><h2 class="text-txt-primary text-xl font-semibold uppercase">DANH SÁCH CHƯƠNG</h2></div>

                        <div class="relative">
                            <div class="flex max-h-[304px] flex-col overflow-y-auto rounded-md border border-bd-default bg-bgc-layer1 divide-y divide-bd-default md:max-h-[400px] lg:max-h-[492px]">
                                @foreach ($manga->chapters as $chapter)
                                <a href="{{ $chapter->getUrl() }}" class="block w-full px-4 py-2 transition-colors hover:bg-white/5" aria-label="Đọc chương {{ $chapter->chapter_number }}">
                                    <div class="grid w-full grid-cols-[minmax(0,1fr)_auto] items-start gap-4">
                                        <span class="text-txt-primary min-w-0 text-base font-medium" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $chapter->title }}</span>
                                        <div class="grid grid-cols-[auto_auto] items-start justify-end gap-x-6 text-right">
                                            <span class="text-txt-secondary flex items-center justify-end gap-1 text-sm whitespace-nowrap">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye h-4 w-4" aria-hidden="true">
                                                    <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                                {{ number_format($chapter->views_count) }}
                                            </span>
                                            <time class="text-txt-secondary text-sm whitespace-nowrap" title="{{ $chapter->published_at->format('H:i · d/m/Y') }}" datetime="{{ $chapter->published_at->toW3CString() }}">{{ $chapter->published_at->diffForHumans() }}</time>
                                        </div>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                @if ($manga->user_id)
                <div class="mt-6 md:hidden">
                    <div class="relative bg-bgc-layer1 border-bd-default rounded-2xl border px-4 py-3">
                        <a href="{{ $manga->user->getUrl() }}" class="absolute right-3 top-2 inline-flex items-center gap-1 text-[11px] text-txt-secondary hover:text-txt-primary transition" aria-label="Xem trang cá nhân">
                            Xem trang cá nhân
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-right h-3.5 w-3.5" aria-hidden="true">
                                <path d="M7 7h10v10"></path>
                                <path d="M7 17 17 7"></path>
                            </svg>
                        </a>
                        <div class="flex items-center gap-3 pr-24">
                            <div class="border-bd-default bg-bgc-layer2 relative flex h-14 w-14 items-center justify-center overflow-hidden rounded-full border md:h-16 md:w-16">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user h-7 w-7 text-txt-primary md:h-8 md:w-8" aria-hidden="true">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                @if ($manga->user->avatar)
                                <img alt="{{ $manga->user->name }}" class="absolute inset-0 h-full w-full object-cover" src="{{ $manga->user->avatar }}">
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1 min-w-0"><a href="{{ $manga->user->getUrl() }}" class="text-txt-primary truncate text-[15px] font-semibold hover:opacity-90" title="{{ $manga->user->name }}"><span class="translator-shine" data-text="{{ $manga->user->name }}">{{ $manga->user->name }}</span></a></div>
                                <div class="text-txt-secondary mt-0.5 text-[12px]">Cấp {{ $manga->user->current_level }} · {{ $manga->user->mangas->count() }} truyện</div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <p class="text-txt-secondary bg-transparent border-0 shadow-none p-0 m-0 text-[13px] leading-snug whitespace-pre-line">{{ $manga->user->bio }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <div>
                    @include('theme-vinahentai::components.comments', ['manga' => $manga])
                </div>
            </section>

            <section class="mt-8 md:mt-0">
                <div class="space-y-10">
                    @if ($manga->user_id)
                    <div class="hidden md:block">
                        <div class="relative bg-bgc-layer1 border-bd-default rounded-2xl border px-4 py-3">
                            <a href="{{ $manga->user->getUrl() }}" class="absolute right-3 top-2 inline-flex items-center gap-1 text-[11px] text-txt-secondary hover:text-txt-primary transition" aria-label="Xem trang cá nhân">
                                Xem trang cá nhân
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-right h-3.5 w-3.5" aria-hidden="true">
                                    <path d="M7 7h10v10"></path>
                                    <path d="M7 17 17 7"></path>
                                </svg>
                            </a>
                            <div class="flex items-center gap-3 pr-24">
                                <div class="border-bd-default bg-bgc-layer2 relative flex h-14 w-14 items-center justify-center overflow-hidden rounded-full border md:h-16 md:w-16">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user h-7 w-7 text-txt-primary md:h-8 md:w-8" aria-hidden="true">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    @if ($manga->user->avatar)
                                    <img alt="{{ $manga->user->name }}" class="absolute inset-0 h-full w-full object-cover" src="{{ $manga->user->avatar }}">
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1 min-w-0"><a href="{{ $manga->user->getUrl() }}" class="text-txt-primary truncate text-[15px] font-semibold hover:opacity-90" title="{{ $manga->user->name }}"><span class="translator-shine" data-text="{{ $manga->user->name }}">{{ $manga->user->name }}</span></a></div>
                                    <div class="text-txt-secondary mt-0.5 text-[12px]">Cấp {{ $manga->user->current_level }} · {{ $manga->user->mangas->count() }} truyện</div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p class="text-txt-secondary bg-transparent border-0 shadow-none p-0 m-0 text-[13px] leading-snug whitespace-pre-line">{{ $manga->user->bio }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if (count($authorMangas) > 0)
                    <section class="mt-8">
                        <div class="flex items-center gap-3"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user h-4 w-4 text-lav-500" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg><h2 class="text-txt-primary text-xl font-semibold uppercase">cùng tác giả</h2></div>

                        <div class="mt-4 bg-bgc-layer1 border-bd-default rounded-2xl border p-4">
                            <div class="space-y-5">
                                @foreach ($authorMangas as $authorManga)
                                @include('theme-vinahentai::components.item-sidebar-manga', ['manga' => $authorManga])
                                @endforeach
                            </div>
                        </div>
                    </section>
                    @endif

                    <section class="mt-8">
                        <div class="flex items-center justify-between"><div class="flex items-center gap-3"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-thumbs-up h-4 w-4 text-lav-500" aria-hidden="true"><path d="M7 10v12"></path><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"></path></svg><h2 class="text-txt-primary text-xl font-semibold uppercase">có thể bạn sẽ thích</h2></div></div>

                        <div class="mt-4 bg-bgc-layer1 border-bd-default rounded-2xl border p-4">
                            <div class="space-y-5">
                                @foreach ($relatedMangas as $relatedManga)
                                @include('theme-vinahentai::components.item-sidebar-manga', ['manga' => $relatedManga])
                                @endforeach
                            </div>
                        </div>
                    </section>

                    <div>
                        @include('theme-vinahentai::components.sidebar-rank')
                    </div>
                </div>
            </section>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const btn = document.querySelector('[data-follow-btn]');
            if (!btn) return;

            const mangaId = Number(btn.getAttribute('data-manga-id') || 0);
            if (!mangaId) return;

            const isLoggedIn = @json(auth()->check());
            const loginUrl = @json(route('login'));

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const labelEl = btn.querySelector('[data-follow-label]');
            const countEl = btn.querySelector('[data-follow-count]');

            const setUi = (followed, totalFollows) => {
                btn.setAttribute('aria-pressed', followed ? 'true' : 'false');
                if (labelEl) labelEl.textContent = followed ? 'Đã theo dõi' : 'Theo dõi';
                if (countEl) countEl.textContent = new Intl.NumberFormat('vi-VN').format(Number(totalFollows || 0));
            };

            const redirectToLogin = () => {
                const redirect = encodeURIComponent(window.location.href);
                window.location.href = `${loginUrl}?redirect=${redirect}`;
            };

            const apiUrl = `{{ url('/api/manga') }}/${mangaId}/follow`;

            const fetchStatus = async () => {
                const res = await fetch(apiUrl, { method: 'GET', headers: { 'Accept': 'application/json' } });
                if (res.status === 401) return;
                if (!res.ok) return;
                const data = await res.json();
                setUi(!!data.followed, data.total_follows);
            };

            const toggle = async () => {
                const res = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({}),
                });

                if (res.status === 401) {
                    redirectToLogin();
                    return;
                }

                if (!res.ok) return;
                const data = await res.json();
                setUi(!!data.followed, data.total_follows);
            };

            btn.addEventListener('click', (e) => {
                e.preventDefault();
                if (!isLoggedIn) {
                    redirectToLogin();
                    return;
                }
                toggle();
            });

            if (isLoggedIn) {
                fetchStatus();
            }

            const resumeRead = document.querySelector('[data-manga-resume-read]');
            if (resumeRead && isLoggedIn) {
                const resumeApi = `{{ url('/api/manga') }}/${mangaId}/reading-history/latest`;
                fetch(resumeApi, {
                    method: 'GET',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                })
                    .then((r) => (r.ok ? r.json() : null))
                    .then((data) => {
                        if (!data || !data.entry || !data.entry.url) {
                            return;
                        }
                        const e = data.entry;
                        resumeRead.href = e.url;
                        const labelEl = resumeRead.querySelector('[data-manga-resume-read-label]');
                        const num = e.chapter_number != null ? String(e.chapter_number) : '';
                        const title = (e.chapter_title || '').trim();
                        const pageHint =
                            typeof e.last_read_page === 'number' && e.last_read_page > 1
                                ? ` · Tr.${e.last_read_page}`
                                : '';
                        if (labelEl) {
                            labelEl.textContent = title
                                ? `Đọc tiếp: ${title}`
                                : `Đọc tiếp${num ? ` · Ch.${num}` : ''}${pageHint}`;
                        }
                        resumeRead.setAttribute(
                            'aria-label',
                            title ? `Đọc tiếp: ${title}` : `Đọc tiếp chương ${num}${pageHint}`
                        );
                        if (title) {
                            resumeRead.setAttribute('title', `Đang dở: ${title}${pageHint}`);
                        }
                        resumeRead.classList.remove('hidden');
                    })
                    .catch(() => {});
            }
        })();
    </script>
@endpush
