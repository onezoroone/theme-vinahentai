@php
    /** @var \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, \App\Models\Genre>> $genreGroups */
    $isEditing = isset($manga) && $manga instanceof \App\Models\Manga;
    if ($isEditing) {
        // Đảm bảo quan hệ có mặt (controller có thể thiếu with); cần cho thể loại + badge tác giả/dịch giả.
        $manga->loadMissing(['genres', 'authors', 'translators', 'tags']);
        $manga->authors->loadCount('mangas');
        $manga->translators->loadCount('mangas');
    }
    $genreGroups = $genreGroups ?? collect();
    $selectedGenreIds = collect(old('genre_ids', $isEditing ? $manga->genres->pluck('id')->all() : []))
        ->map(fn ($v) => (int) $v)
        ->filter(fn (int $id) => $id > 0)
        ->values()
        ->all();
    $genreSlugsForHidden = \App\Models\Genre::query()
        ->whereIn('id', $selectedGenreIds)
        ->pluck('slug')
        ->values()
        ->all();
    $coverPreviewUrl = old('posterUrl', $isEditing && $manga->cover_image ? (string) $manga->cover_image : '');
    $titleValue = old('title', $isEditing ? $manga->title : '');
    $alternateTitleValue = old('alternateTitle', $isEditing ? (string) ($manga->alternative_title ?? '') : '');
    $descriptionValue = old('description', $isEditing ? (string) ($manga->description ?? '') : '');
    $editorNameValue = old('editorName', '');
    $currentStatus = old('status', $isEditing ? $manga->status : 'ongoing');
    if (! in_array($currentStatus, ['ongoing', 'completed'], true)) {
        $currentStatus = 'ongoing';
    }
    $userStatusHidden = $currentStatus === 'completed' ? '1' : '0';
    $keywordsValue = old('keywords', $isEditing ? (string) ($manga->getKeywords() ?? '') : '');
    $hasOneshotGenre = $isEditing && $manga->genres->contains('slug', 'oneshot');
    $oneshotChecked = (string) old('oneshot', $hasOneshotGenre ? '1' : '0') === '1';
    $authorNames = old('authorNames', $isEditing ? $manga->authors->pluck('name')->values()->all() : []);
    $authorSlugs = old('authorSlugs', $isEditing ? $manga->authors->pluck('slug')->values()->all() : []);
    $authorIds = old('authorIds', $isEditing ? $manga->authors->pluck('id')->values()->all() : []);
    $authorMangaCounts = old('authorMangaCounts', $isEditing ? $manga->authors->pluck('mangas_count')->values()->all() : []);
    $doujinshiNames = old('doujinshiNames', []);
    $doujinshiSlugs = old('doujinshiSlugs', []);
    $translatorNames = old('translatorNames', $isEditing ? $manga->translators->pluck('name')->values()->all() : []);
    $translatorSlugs = old('translatorSlugs', $isEditing ? $manga->translators->pluck('slug')->values()->all() : []);
    $translatorIds = old('translatorIds', $isEditing ? $manga->translators->pluck('id')->values()->all() : []);
    $translatorMangaCounts = old('translatorMangaCounts', $isEditing ? $manga->translators->pluck('mangas_count')->values()->all() : []);
    $characterNames = old('characterNames', []);
    $characterSlugs = old('characterSlugs', []);
    $taxonomyJsonKeys = ['authorNames', 'authorSlugs', 'authorIds', 'authorMangaCounts', 'doujinshiNames', 'doujinshiSlugs', 'translatorNames', 'translatorSlugs', 'translatorIds', 'translatorMangaCounts', 'characterNames', 'characterSlugs'];
    foreach ($taxonomyJsonKeys as $arrKey) {
        $v = $$arrKey;
        if (is_array($v)) {
            $$arrKey = array_values($v);

            continue;
        }
        // old() sau lỗi validate có thể trả về chuỗi JSON từ input hidden — không được ép thành [].
        if (is_string($v) && $v !== '') {
            $decoded = json_decode($v, true);
            if (is_array($decoded)) {
                $$arrKey = array_values($decoded);

                continue;
            }
            // JSON lỗi / bị cắt (vd. max_input_vars) — không xóa sạch; khi sửa truyện lấy lại từ DB giống admin.
            if ($isEditing) {
                $$arrKey = match ($arrKey) {
                    'authorNames' => $manga->authors->pluck('name')->values()->all(),
                    'authorSlugs' => $manga->authors->pluck('slug')->values()->all(),
                    'authorIds' => $manga->authors->pluck('id')->values()->all(),
                    'authorMangaCounts' => $manga->authors->pluck('mangas_count')->values()->all(),
                    'translatorNames' => $manga->translators->pluck('name')->values()->all(),
                    'translatorSlugs' => $manga->translators->pluck('slug')->values()->all(),
                    'translatorIds' => $manga->translators->pluck('id')->values()->all(),
                    'translatorMangaCounts' => $manga->translators->pluck('mangas_count')->values()->all(),
                    default => [],
                };

                continue;
            }
        }
        $$arrKey = [];
    }

    /** Dữ liệu gốc từ DB (chỉ khi sửa) — JS dùng khi hidden JSON lỗi/rỗng để vẫn hiện pill tác giả/dịch giả. */
    $taxonomyBootstrap = null;
    if ($isEditing) {
        $taxonomyBootstrap = [
            'authors' => $manga->authors->map(static function ($a): array {
                return [
                    'id' => (int) $a->id,
                    'name' => (string) $a->name,
                    'slug' => (string) ($a->slug ?? ''),
                    'mangasCount' => (int) ($a->mangas_count ?? 0),
                ];
            })->values()->all(),
            'translators' => $manga->translators->map(static function ($t): array {
                return [
                    'id' => (int) $t->id,
                    'name' => (string) $t->name,
                    'slug' => (string) ($t->slug ?? ''),
                    'mangasCount' => (int) ($t->mangas_count ?? 0),
                ];
            })->values()->all(),
        ];
    }
@endphp
@if ($taxonomyBootstrap !== null)
    {{-- Ngoài form: tránh lỗi cấu trúc; không bị HTML-escape làm hỏng JSON như value="" trên hidden. --}}
    <script type="application/json" id="manga-user-taxonomy-bootstrap">
        @json($taxonomyBootstrap)
    </script>
@endif
        <form method="post" action="{{ $mangaFormAction ?? '#' }}" class="flex flex-col gap-6" id="manga-user-form">
            @csrf
            @if ($isEditing)
                @method('put')
            @endif
            {{--
                Đặt hidden tác giả / dịch giả (và meta) TRƯỚC hàng loạt genre_ids[].
                PHP max_input_vars (mặc định 1000) có thể cắt bỏ các field ở cuối body → mất authorNames khi gửi form.
            --}}
            <input type="hidden" name="keywords" value="{{ $keywordsValue }}">
            <input type="hidden" name="userStatus" value="{{ $userStatusHidden }}">
            <input type="hidden" name="genres" value="{{ e(json_encode($genreSlugsForHidden, JSON_UNESCAPED_UNICODE)) }}">
            <input type="hidden" name="posterUrl" value="{{ old('posterUrl', $coverPreviewUrl) }}">
            <input type="hidden" name="posterVariantsJson" value="{{ old('posterVariantsJson', '') }}">
            <input type="hidden" name="author" value="{{ old('author', '') }}">
            <input type="hidden" name="authorNames" value="{{ e(json_encode($authorNames, JSON_UNESCAPED_UNICODE)) }}">
            <input type="hidden" name="authorSlugs" value="{{ e(json_encode($authorSlugs, JSON_UNESCAPED_UNICODE)) }}">
            <input type="hidden" name="authorIds" value="{{ e(json_encode($authorIds, JSON_UNESCAPED_UNICODE)) }}">
            <input type="hidden" name="authorMangaCounts" value="{{ e(json_encode($authorMangaCounts, JSON_UNESCAPED_UNICODE)) }}">
            <input type="hidden" name="doujinshiNames" value="{{ e(json_encode($doujinshiNames, JSON_UNESCAPED_UNICODE)) }}">
            <input type="hidden" name="doujinshiSlugs" value="{{ e(json_encode($doujinshiSlugs, JSON_UNESCAPED_UNICODE)) }}">
            <input type="hidden" name="translatorNames" value="{{ e(json_encode($translatorNames, JSON_UNESCAPED_UNICODE)) }}">
            <input type="hidden" name="translatorSlugs" value="{{ e(json_encode($translatorSlugs, JSON_UNESCAPED_UNICODE)) }}">
            <input type="hidden" name="translatorIds" value="{{ e(json_encode($translatorIds, JSON_UNESCAPED_UNICODE)) }}">
            <input type="hidden" name="translatorMangaCounts" value="{{ e(json_encode($translatorMangaCounts, JSON_UNESCAPED_UNICODE)) }}">
            <input type="hidden" name="characterNames" value="{{ e(json_encode($characterNames, JSON_UNESCAPED_UNICODE)) }}">
            <input type="hidden" name="characterSlugs" value="{{ e(json_encode($characterSlugs, JSON_UNESCAPED_UNICODE)) }}">
            @if ($isEditing)
                <input type="hidden" name="manga_id" value="{{ $manga->id }}">
            @endif
            <input type="hidden" name="isCosplay" value="{{ old('isCosplay', '0') }}">
            <div class="bg-bgc-layer1 border-bd-default flex flex-col gap-6 rounded-xl border p-4 shadow-lg sm:p-6">
                <div class="flex flex-col gap-6 md:flex-row">
                    <div class="md:w-2/5 flex flex-col gap-6">
                        <div class="flex flex-col gap-4">
                            <div class="flex min-w-fit items-center gap-1.5"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-image text-txt-secondary mt-0.5 h-4 w-4" aria-hidden="true">
                                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                                    <circle cx="9" cy="9" r="2"></circle>
                                    <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
                                </svg><label class="text-txt-primary font-sans text-base font-semibold">Ảnh bìa</label></div>
                            <div class="flex w-full flex-row flex-wrap items-start gap-4">
                                <div class="flex min-h-64 min-w-44 flex-col gap-3">
                                    <div class="rounded-xl transition" data-manga-cover-dropzone aria-label="Kéo ảnh vào vùng này hoặc bấm Tải ảnh lên" title="Kéo ảnh vào đây hoặc bấm Tải ảnh lên">
                                        <div class="border-bd-default bg-bgc-layer2 relative flex w-full min-h-[200px] items-center justify-center overflow-hidden rounded-xl border aspect-[3/4]" data-manga-cover-box>
                                            @if ($coverPreviewUrl !== '')
                                                <img alt="Preview ảnh bìa" class="h-full w-full rounded-xl object-cover" src="{{ $coverPreviewUrl }}" data-manga-cover-preview>
                                            @else
                                                <span class="text-txt-secondary px-3 text-center text-xs font-medium" data-manga-cover-placeholder>Chưa có ảnh bìa</span>
                                            @endif
                                            <button type="button" class="absolute -top-2 -right-2 z-10 flex h-6 w-6 cursor-pointer items-center justify-center rounded-full border border-gray-600 bg-gray-800 transition-colors hover:bg-gray-700" data-manga-cover-clear aria-label="Xóa ảnh bìa preview">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x text-txt-primary h-4 w-4" aria-hidden="true">
                                                    <path d="M18 6 6 18"></path>
                                                    <path d="m6 6 12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <input type="file" id="image-upload" class="sr-only" accept="image/jpeg,image/png,image/webp,image/gif" tabindex="-1">
                                    <label for="image-upload" class="flex w-36 cursor-pointer items-center justify-center gap-1.5 rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] py-3 shadow-lg transition-opacity hover:opacity-90">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-upload ml-1 h-5 w-5 text-black" aria-hidden="true">
                                            <path d="M12 3v12"></path>
                                            <path d="m17 8-5-5-5 5"></path>
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        </svg>
                                        <span class="mr-1 line-clamp-1 font-sans text-sm font-semibold text-black">Tải ảnh lên</span>
                                    </label>
                                </div>

                            </div>
                        </div>
                        <div class="flex flex-col gap-6">
                            <div class="flex flex-col gap-4">
                                <div class="flex min-w-fit items-center gap-1.5"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user text-txt-secondary h-4 w-4" aria-hidden="true">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg><label class="text-txt-primary font-sans text-base font-semibold">Tác giả</label></div>
                                <div>
                                    <div
                                        class="w-full"
                                        data-manga-person-field="author"
                                        data-search-url="{{ route('user.authors.search') }}"
                                        data-entity-label="tác giả"
                                    >
                                        <div class="mb-2 flex flex-wrap gap-2" data-person-badges aria-live="polite">
                                            @if ($isEditing)
                                                {{-- Cùng nguồn với hidden JSON + JS (như dịch giả), tránh lệch khi old() lỗi --}}
                                                @foreach ($authorNames as $idx => $name)
                                                    @php
                                                        $nameTrim = trim((string) $name);
                                                    @endphp
                                                    @if ($nameTrim === '')
                                                        @continue
                                                    @endif
                                                    @php
                                                        $authorBadgeCount = (int) ($authorMangaCounts[$idx] ?? 0);
                                                    @endphp
                                                    <button
                                                        type="button"
                                                        draggable="true"
                                                        class="group border-bd-default bg-bgc-layer2 text-txt-primary hover:bg-bgc-layer1 flex cursor-move items-center gap-2 rounded-full border px-3 py-1 text-sm"
                                                        title="Kéo để đổi thứ tự"
                                                        aria-label="Tác giả {{ $nameTrim }}"
                                                    >
                                                        <span class="bg-bgc-layer1 text-txt-secondary flex h-5 min-w-5 shrink-0 items-center justify-center rounded-full px-0.5 text-[10px] tabular-nums">{{ $authorBadgeCount }}</span>
                                                        <span>{{ $nameTrim }}</span>
                                                        <span class="bg-bgc-layer1 text-txt-secondary hover:bg-error-error ml-1 inline-flex h-5 w-5 cursor-pointer items-center justify-center rounded-full transition-colors hover:text-white" title="Bỏ gắn" aria-hidden="true">×</span>
                                                    </button>
                                                @endforeach
                                            @endif
                                        </div>
                                        <div class="relative">
                                            <input
                                                type="text"
                                                placeholder="Thêm tác giả…"
                                                class="border-bd-default bg-bgc-layer2 text-txt-secondary focus:border-lav-500 focus:text-txt-primary w-full rounded-xl border px-3 py-2.5 font-sans text-base focus:outline-none"
                                                value=""
                                                data-person-input
                                                autocomplete="off"
                                            >
                                        </div>
                                        <div
                                            class="border-bd-default bg-bgc-layer1 mt-1 hidden w-full overflow-hidden rounded-xl border shadow-lg"
                                            data-person-dropdown
                                        >
                                            <ul class="max-h-64 overflow-auto" data-person-suggestions></ul>
                                        </div>
                                        <div class="mt-2 text-xs text-gray-500">
                                            Sẽ lưu: <span class="font-medium" data-person-save-preview>—</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="flex flex-col gap-4">
                                <div class="flex min-w-fit items-center gap-1.5"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-tag text-txt-secondary h-4 w-4" aria-hidden="true">
                                        <path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"></path>
                                        <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
                                    </svg><label class="text-txt-primary font-sans text-base font-semibold">Doujinshi</label></div>
                                <div>
                                    <div class="w-full">
                                        <div class="mb-2 flex flex-wrap gap-2"></div>
                                        <div class="relative"><input placeholder="Thêm doujinshi…" class="border-bd-default bg-bgc-layer2 text-txt-secondary focus:border-lav-500 focus:text-txt-primary w-full rounded-xl border px-3 py-2.5 font-sans text-base focus:outline-none" value=""></div>
                                    </div>
                                    <div class="mt-2 text-xs text-gray-500">Tùy chọn</div>
                                </div>
                            </div> --}}
                            <div class="flex flex-col gap-4">
                                <div class="flex min-w-fit items-center gap-1.5"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user text-txt-secondary h-4 w-4" aria-hidden="true">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg><label class="text-txt-primary font-sans text-base font-semibold">Dịch giả</label></div>
                                <div>
                                    <div
                                        class="w-full"
                                        data-manga-person-field="translator"
                                        data-search-url="{{ route('user.translators.search') }}"
                                        data-entity-label="dịch giả"
                                    >
                                        <div class="mb-2 flex flex-wrap gap-2" data-person-badges aria-live="polite">
                                            @if ($isEditing)
                                                @foreach ($translatorNames as $idx => $name)
                                                    @php
                                                        $tNameTrim = trim((string) $name);
                                                    @endphp
                                                    @if ($tNameTrim === '')
                                                        @continue
                                                    @endif
                                                    @php
                                                        $translatorBadgeCount = (int) ($translatorMangaCounts[$idx] ?? 0);
                                                    @endphp
                                                    <button
                                                        type="button"
                                                        draggable="true"
                                                        class="group border-bd-default bg-bgc-layer2 text-txt-primary hover:bg-bgc-layer1 flex cursor-move items-center gap-2 rounded-full border px-3 py-1 text-sm"
                                                        title="Kéo để đổi thứ tự"
                                                        aria-label="Dịch giả {{ $tNameTrim }}"
                                                    >
                                                        <span class="bg-bgc-layer1 text-txt-secondary flex h-5 min-w-5 shrink-0 items-center justify-center rounded-full px-0.5 text-[10px] tabular-nums">{{ $translatorBadgeCount }}</span>
                                                        <span>{{ $tNameTrim }}</span>
                                                        <span class="bg-bgc-layer1 text-txt-secondary hover:bg-error-error ml-1 inline-flex h-5 w-5 cursor-pointer items-center justify-center rounded-full transition-colors hover:text-white" title="Bỏ gắn" aria-hidden="true">×</span>
                                                    </button>
                                                @endforeach
                                            @endif
                                        </div>
                                        <div class="relative">
                                            <input
                                                type="text"
                                                placeholder="Thêm dịch giả…"
                                                class="border-bd-default bg-bgc-layer2 text-txt-secondary focus:border-lav-500 focus:text-txt-primary w-full rounded-xl border px-3 py-2.5 font-sans text-base focus:outline-none"
                                                value=""
                                                data-person-input
                                                autocomplete="off"
                                            >
                                        </div>
                                        <div
                                            class="border-bd-default bg-bgc-layer1 mt-1 hidden w-full overflow-hidden rounded-xl border shadow-lg"
                                            data-person-dropdown
                                        >
                                            <ul class="max-h-64 overflow-auto" data-person-suggestions></ul>
                                        </div>
                                        <div class="mt-2 text-xs text-gray-500">
                                            Sẽ lưu: <span class="font-medium" data-person-save-preview>—</span>
                                            <span class="text-txt-secondary"> · Tùy chọn</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="flex flex-col gap-4">
                                <div class="flex min-w-fit items-center gap-1.5"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user text-txt-secondary h-4 w-4" aria-hidden="true">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg><label class="text-txt-primary font-sans text-base font-semibold">Nhân vật</label></div>
                                <div>
                                    <div class="w-full">
                                        <div class="mb-2 flex flex-wrap gap-2"></div>
                                        <div class="relative"><input placeholder="Thêm nhân vật…" class="border-bd-default bg-bgc-layer2 text-txt-secondary focus:border-lav-500 focus:text-txt-primary w-full rounded-xl border px-3 py-2.5 font-sans text-base focus:outline-none" value=""></div>
                                    </div>
                                    <div class="mt-2 text-xs text-gray-500">Tùy chọn</div>
                                </div>
                            </div> --}}
                            {{-- <div class="flex flex-col gap-4">
                                <div class="flex min-w-fit items-center gap-1.5"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pen-line text-txt-secondary h-4 w-4" aria-hidden="true">
                                        <path d="M12 20h9"></path>
                                        <path d="M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z"></path>
                                    </svg><label class="text-txt-primary font-sans text-base font-semibold">Người Edit</label></div>
                                <div><input placeholder="Tên người edit (nếu có)" class="bg-bgc-layer2 border-bd-default text-txt-secondary focus:border-lav-500 focus:text-txt-primary w-full rounded-xl border px-3 py-2.5 font-sans text-base font-medium focus:outline-none" type="text" value="{{ $editorNameValue }}" name="editorName">
                                    <div class="mt-2 text-xs text-gray-500">Tùy chọn</div>
                                </div>
                            </div> --}}
                        </div>
                        <div class="flex flex-col gap-4">
                            <div class="flex min-w-fit items-center gap-1.5"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings text-txt-secondary h-4 w-4" aria-hidden="true">
                                    <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg><label class="text-txt-primary font-sans text-base font-semibold">Trạng thái / One-shot</label></div>
                            <div class="flex flex-col gap-2">
                                <label class="flex cursor-pointer items-center gap-2"><input class="h-4 w-4 cursor-pointer" type="radio" name="status" value="ongoing" @checked($currentStatus === 'ongoing')><span class="text-txt-primary text-sm font-medium">Đang tiến hành</span></label>
                                <label class="flex cursor-pointer items-center gap-2"><input class="h-4 w-4 cursor-pointer" type="radio" name="status" value="completed" @checked($currentStatus === 'completed')><span class="text-txt-primary text-sm font-medium">Đã hoàn thành</span></label>
                                {{-- hidden 0: gửi khi bỏ tick checkbox --}}
                                <label class="flex cursor-pointer items-center gap-2"><input type="hidden" name="oneshot" value="0"><input class="h-4 w-4 cursor-pointer" type="checkbox" name="oneshot" value="1" @checked($oneshotChecked)><span class="text-txt-primary text-sm font-medium">One-shot (thể loại)</span></label>
                            </div>
                        </div>
                    </div>
                    <div class="md:w-3/5 flex flex-col gap-6">
                        <div class="flex flex-col gap-4">
                            <div class="flex min-w-fit items-center gap-1.5"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book text-txt-secondary h-4 w-4" aria-hidden="true">
                                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"></path>
                                </svg><label class="text-txt-primary font-sans text-base font-semibold">Tên truyện</label></div>
                            <div><input placeholder="Viết hoa ký tự đầu tiên mỗi từ" class="bg-bgc-layer2 border-bd-default text-txt-secondary focus:border-lav-500 focus:text-txt-primary w-full rounded-xl border px-3 py-2.5 font-sans text-base font-medium focus:outline-none" required type="text" value="{{ $titleValue }}" name="title"></div>
                        </div>
                        <div class="flex flex-col gap-2"><label class="text-txt-secondary text-sm font-medium">Tên khác (không bắt buộc)</label><input placeholder="Tên khác (ví dụ: bản ENG, tên rút gọn...)" class="bg-bgc-layer2 border-bd-default text-txt-secondary w-full rounded-lg border px-3 py-2 text-sm font-sans font-medium focus:outline-none" type="text" value="{{ $alternateTitleValue }}" name="alternateTitle"></div>
                        <div class="flex flex-col gap-4">
                            <div class="flex min-w-fit items-center gap-1.5"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-tag text-txt-secondary mt-0.5 h-4 w-4" aria-hidden="true">
                                    <path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"></path>
                                    <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
                                </svg><label class="text-txt-primary font-sans text-base font-semibold">Thể loại</label></div>
                            <div class="flex w-full flex-col gap-3">
                                <div class="text-txt-secondary text-xs">Gõ <b>a,b,c…</b> hoặc cách/khoảng trắng để lọc; mỗi từ khóa phải xuất hiện trong tên/slug thể loại (AND). Esc để xóa ô lọc.</div>
                                <label class="relative w-full max-w-[360px]">
                                    <input
                                        type="search"
                                        data-manga-genre-filter
                                        autocomplete="off"
                                        placeholder="Vd: manhwa, ecchi, oneshot …"
                                        class="w-full rounded-md border border-bd-default bg-bgc-layer2 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary/40"
                                        value=""
                                    >
                                </label>
                                <div class="max-h-[360px] overflow-y-auto rounded-lg border border-white/10 p-3 pr-1 [scrollbar-color:rgba(255,255,255,0.3)_transparent] [scrollbar-width:thin]">
                                    <div class="space-y-4" id="manga-genre-checkboxes">
                                        @if ($genreGroups->isEmpty())
                                            <p class="text-txt-secondary text-sm font-medium">Chưa có thể loại trong hệ thống.</p>
                                        @endif
                                        @foreach ($genreGroups as $letter => $genresInLetter)
                                            <div data-manga-genre-letter-group>
                                                <div class="mb-2 flex items-center gap-2">
                                                    <div class="text-sm font-bold text-txt-focus">{{ $letter }}</div>
                                                    <div class="h-px flex-1 bg-bd-default/60"></div>
                                                </div>
                                                <div class="grid grid-cols-3 gap-x-4 gap-y-2">
                                                    @foreach ($genresInLetter as $genre)
                                                        @php
                                                            $genreChecked = in_array((int) $genre->id, $selectedGenreIds, true);
                                                            $gFirst = mb_strtoupper(mb_substr($genre->name, 0, 1, 'UTF-8'));
                                                            $gRest = mb_substr($genre->name, 1, null, 'UTF-8');
                                                            $genreSearchHaystack = mb_strtolower(
                                                                trim((string) $genre->name).' '.trim((string) $genre->slug),
                                                                'UTF-8'
                                                            );
                                                        @endphp
                                                        <label
                                                            class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 select-none hover:bg-white/5"
                                                            data-manga-genre-search-text="{{ e($genreSearchHaystack) }}"
                                                        >
                                                            <div class="relative">
                                                                <input
                                                                    type="checkbox"
                                                                    name="genre_ids[]"
                                                                    value="{{ $genre->id }}"
                                                                    class="peer bg-bgc-layer2 border-bd-default checked:bg-lav-500 checked:border-lav-500 h-4 w-4 cursor-pointer appearance-none rounded border manga-genre-cb"
                                                                    data-genre-slug="{{ $genre->slug }}"
                                                                    @checked($genreChecked)>
                                                                <div class="pointer-events-none absolute inset-0 hidden items-center justify-center peer-checked:flex" aria-hidden="true">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check text-txt-primary h-3 w-3">
                                                                        <path d="M20 6 9 17l-5-5"></path>
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                            <span class="text-txt-primary line-clamp-1 font-sans text-xs font-medium"><span class="font-bold">{{ $gFirst }}</span>{{ $gRest }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col gap-4">
                            <div class="flex min-w-fit items-center gap-1.5"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text text-txt-secondary mt-0.5 h-4 w-4" aria-hidden="true">
                                    <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                                    <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                                    <path d="M10 9H8"></path>
                                    <path d="M16 13H8"></path>
                                    <path d="M16 17H8"></path>
                                </svg><label class="text-txt-primary font-sans text-base font-semibold">Giới thiệu</label></div>
                            <div><textarea name="description" placeholder="Nhập nội dung giới thiệu (có cũng được, không có cũng được)" rows="8" class="bg-bgc-layer2 border-bd-default text-txt-secondary focus:border-lav-500 focus:text-txt-primary w-full resize-none rounded-xl border px-3 py-2.5 font-sans text-base font-medium focus:outline-none">{{ $descriptionValue }}</textarea></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end"><button type="submit" class="w-full rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] px-4 py-3 font-sans text-sm font-semibold text-black shadow-lg transition-opacity hover:opacity-90 disabled:opacity-50 sm:w-52">{{ $isEditing ? 'Cập nhật truyện' : 'Lưu lại' }}</button></div>
        </form>

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById("manga-user-form");
            if (!form) {
                return;
            }

            /** Dữ liệu tác giả/dịch giả từ server (chỉ trang sửa). */
            let taxonomyBootstrap = null;
            (function docTaxonomyBootstrap() {
                const el = document.getElementById(["manga-user", "taxonomy-bootstrap"].join("-"));
                if (!el || !el.textContent) {
                    return;
                }
                try {
                    taxonomyBootstrap = JSON.parse(el.textContent.trim());
                } catch {
                    taxonomyBootstrap = null;
                }
            })();

            const userStatusHidden = form.querySelector('input[name="userStatus"]');
            const statusRadios = form.querySelectorAll('input[name="status"]');
            function dongBoTrangThaiAn() {
                if (!userStatusHidden) {
                    return;
                }
                const hoanThanh = form.querySelector('input[name="status"][value="completed"]');
                userStatusHidden.value = hoanThanh && hoanThanh.checked ? "1" : "0";
            }
            statusRadios.forEach(function (r) {
                r.addEventListener("change", dongBoTrangThaiAn);
            });
            dongBoTrangThaiAn();

            // Lọc thể loại theo ô tìm (AND từ khóa; tách bằng khoảng trắng hoặc dấu phẩy)
            (function khoiTaoLocTheLoai() {
                const filterInput = form.querySelector("[data-manga-genre-filter]");
                const container = form.querySelector("#manga-genre-checkboxes");
                if (!filterInput || !container) {
                    return;
                }

                function layToken(q) {
                    return String(q || "")
                        .toLowerCase()
                        .split(/[\s,，]+/u)
                        .map(function (s) {
                            return s.trim();
                        })
                        .filter(Boolean);
                }

                function loc() {
                    const tokens = layToken(filterInput.value);
                    container.querySelectorAll("[data-manga-genre-letter-group]").forEach(function (grp) {
                        let coHien = false;
                        grp.querySelectorAll("[data-manga-genre-search-text]").forEach(function (el) {
                            const haystack = (el.getAttribute("data-manga-genre-search-text") || "").toLowerCase();
                            const ok =
                                tokens.length === 0 ||
                                tokens.every(function (t) {
                                    return haystack.indexOf(t) !== -1;
                                });
                            el.classList.toggle("hidden", !ok);
                            if (ok) {
                                coHien = true;
                            }
                        });
                        grp.classList.toggle("hidden", !coHien);
                    });
                }

                filterInput.addEventListener("input", loc);
                filterInput.addEventListener("keydown", function (e) {
                    if (e.key === "Escape") {
                        filterInput.value = "";
                        loc();
                    }
                });
            })();

            // Upload ảnh bìa (API) + kéo thả
            const uploadUrl = @json(route('user.manga-cover.upload'));
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
            const fileInput = document.getElementById("image-upload");
            const posterHidden = form.querySelector('input[name="posterUrl"]');
            const dropzone = form.querySelector("[data-manga-cover-dropzone]");
            const coverBox = form.querySelector("[data-manga-cover-box]");
            const clearBtn = form.querySelector("[data-manga-cover-clear]");

            function datAnhBiaPreview(url) {
                if (!posterHidden || !coverBox) {
                    return;
                }
                posterHidden.value = url;
                const ph = coverBox.querySelector("[data-manga-cover-placeholder]");
                if (ph) {
                    ph.remove();
                }
                let img = coverBox.querySelector("[data-manga-cover-preview]");
                if (!img) {
                    img = document.createElement("img");
                    img.setAttribute("alt", "Preview ảnh bìa");
                    img.className = "h-full w-full rounded-xl object-cover";
                    img.setAttribute("data-manga-cover-preview", "");
                    coverBox.insertBefore(img, coverBox.firstChild);
                }
                img.src = url;
            }

            function xoaAnhBiaPreview() {
                if (posterHidden) {
                    posterHidden.value = "";
                }
                if (fileInput) {
                    fileInput.value = "";
                }
                if (!coverBox) {
                    return;
                }
                const img = coverBox.querySelector("[data-manga-cover-preview]");
                if (img) {
                    img.remove();
                }
                if (!coverBox.querySelector("[data-manga-cover-placeholder]")) {
                    const span = document.createElement("span");
                    span.className = "text-txt-secondary px-3 text-center text-xs font-medium";
                    span.setAttribute("data-manga-cover-placeholder", "");
                    span.textContent = "Chưa có ảnh bìa";
                    coverBox.insertBefore(span, coverBox.firstChild);
                }
            }

            function taiLen(file) {
                if (!file || !csrf || !posterHidden) {
                    return;
                }
                if (file.size > 1024 * 1024) {
                    window.alert("Ảnh vượt quá 1MB.");
                    return;
                }
                const fd = new FormData();
                fd.append("cover", file);
                fd.append("_token", csrf);
                fetch(uploadUrl, {
                    method: "POST",
                    body: fd,
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    credentials: "same-origin",
                })
                    .then(function (res) {
                        return res.json().catch(function () {
                            return {};
                        }).then(function (body) {
                            if (!res.ok) {
                                let msg = "Upload thất bại.";
                                if (body.errors) {
                                    msg = Object.values(body.errors)
                                        .flat()
                                        .join(" ");
                                } else if (body.message) {
                                    msg = body.message;
                                }
                                throw new Error(msg);
                            }
                            return body;
                        });
                    })
                    .then(function (data) {
                        if (data && data.url) {
                            datAnhBiaPreview(data.url);
                        }
                    })
                    .catch(function (err) {
                        window.alert(err.message || "Không tải được ảnh, thử lại.");
                    });
            }

            if (fileInput) {
                fileInput.addEventListener("change", function () {
                    const f = fileInput.files && fileInput.files[0];
                    if (f) {
                        taiLen(f);
                    }
                    fileInput.value = "";
                });
            }

            if (clearBtn) {
                clearBtn.addEventListener("click", function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    xoaAnhBiaPreview();
                });
            }

            if (dropzone && coverBox) {
                ["dragenter", "dragover"].forEach(function (ev) {
                    dropzone.addEventListener(ev, function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        coverBox.classList.add("ring-2", "ring-lav-500/60");
                    });
                });
                ["dragleave", "drop"].forEach(function (ev) {
                    dropzone.addEventListener(ev, function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        coverBox.classList.remove("ring-2", "ring-lav-500/60");
                    });
                });
                dropzone.addEventListener("drop", function (e) {
                    const f = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
                    if (f && f.type && f.type.indexOf("image/") === 0) {
                        taiLen(f);
                    }
                });
            }

            /**
             * Tác giả / dịch giả: gõ ≥1 ký tự → API trả tối đa 10; mục đã chọn hiển thị dạng pill giống badge.
             */
            (function khoiTaoBangNhapCaNhan() {
                /** Đẩy lại hidden ngay trước submit (capture: trước Turbo / gửi form). */
                const dongBoCallbacksPerson = [];

                /** @param {HTMLInputElement|null} el — trả về { ok, arr, emptyAttr } */
                function docJsonHiddenKetQua(el) {
                    if (!el) {
                        return { ok: true, arr: [], emptyAttr: true };
                    }
                    const raw = el.value;
                    if (raw === undefined || String(raw).trim() === "") {
                        return { ok: true, arr: [], emptyAttr: true };
                    }
                    try {
                        const v = JSON.parse(String(raw));
                        return {
                            ok: true,
                            arr: Array.isArray(v) ? v : [],
                            emptyAttr: false,
                        };
                    } catch {
                        return { ok: false, arr: [], emptyAttr: false };
                    }
                }

                const roots = form.querySelectorAll("[data-manga-person-field]");
                roots.forEach(function (root) {
                    const kind = root.getAttribute("data-manga-person-field") || "author";
                    const searchUrl = root.getAttribute("data-search-url") || "";
                    const entityLabel = root.getAttribute("data-entity-label") || "mục";
                    const badges = root.querySelector("[data-person-badges]");
                    const input = root.querySelector("[data-person-input]");
                    const dropdown = root.querySelector("[data-person-dropdown]");
                    const ul = root.querySelector("[data-person-suggestions]");
                    const previewEl = root.querySelector("[data-person-save-preview]");

                    const namesInput = form.querySelector(
                        'input[name="' + (kind === "translator" ? "translatorNames" : "authorNames") + '"]'
                    );
                    const slugsInput = form.querySelector(
                        'input[name="' + (kind === "translator" ? "translatorSlugs" : "authorSlugs") + '"]'
                    );
                    const idsInput = form.querySelector(
                        'input[name="' + (kind === "translator" ? "translatorIds" : "authorIds") + '"]'
                    );
                    const countsInput = form.querySelector(
                        'input[name="' +
                            (kind === "translator" ? "translatorMangaCounts" : "authorMangaCounts") +
                            '"]'
                    );
                    if (
                        !badges ||
                        !input ||
                        !dropdown ||
                        !ul ||
                        !namesInput ||
                        !slugsInput ||
                        !idsInput ||
                        !countsInput
                    ) {
                        return;
                    }

                    /** Danh sách tác giả/dịch giả đã chọn (id, name, slug, mangasCount). */
                    let selected = [];
                    let dragIndex = null;
                    let debounceTimer = null;

                    /** Huy hiệu số truyện (không phải thứ tự index). */
                    function taoNutSoTruyen(so) {
                        const sp = document.createElement("span");
                        sp.className =
                            "bg-bgc-layer1 text-txt-secondary flex h-5 min-w-5 shrink-0 items-center justify-center rounded-full px-0.5 text-[10px] tabular-nums";
                        const n = Number(so);
                        sp.textContent = String(Number.isFinite(n) && n >= 0 ? Math.floor(n) : 0);
                        return sp;
                    }

                    function normalizeSlug(s) {
                        return (s || "").trim();
                    }

                    function trung(a, b) {
                        if (a.id != null && b.id != null && Number(a.id) === Number(b.id)) {
                            return true;
                        }
                        const sa = normalizeSlug(a.slug);
                        const sb = normalizeSlug(b.slug);
                        if (sa !== "" && sb !== "" && sa === sb) {
                            return true;
                        }
                        return a.name.trim().toLowerCase() === b.name.trim().toLowerCase();
                    }

                    function daCo(entry) {
                        return selected.some(function (x) {
                            return trung(x, entry);
                        });
                    }

                    function capNhatPreview() {
                        if (!previewEl) {
                            return;
                        }
                        const t = selected
                            .map(function (s) {
                                return s.name;
                            })
                            .join(", ");
                        previewEl.textContent = t === "" ? "—" : t;
                    }

                    function dongBoAn() {
                        namesInput.value = JSON.stringify(
                            selected.map(function (s) {
                                return s.name;
                            })
                        );
                        slugsInput.value = JSON.stringify(
                            selected.map(function (s) {
                                return normalizeSlug(s.slug);
                            })
                        );
                        idsInput.value = JSON.stringify(
                            selected.map(function (s) {
                                return s.id != null && Number(s.id) > 0 ? Number(s.id) : 0;
                            })
                        );
                        countsInput.value = JSON.stringify(
                            selected.map(function (s) {
                                const n = Number(s.mangasCount);
                                return Number.isFinite(n) && n >= 0 ? Math.floor(n) : 0;
                            })
                        );
                        capNhatPreview();
                        veBadge();
                    }

                    function veBadge() {
                        badges.innerHTML = "";
                        selected.forEach(function (item, index) {
                            const btn = document.createElement("button");
                            btn.type = "button";
                            btn.draggable = true;
                            btn.setAttribute("title", "Kéo để đổi thứ tự");
                            btn.className =
                                "group border-bd-default bg-bgc-layer2 text-txt-primary hover:bg-bgc-layer1 flex cursor-move items-center gap-2 rounded-full border px-3 py-1 text-sm";
                            btn.setAttribute("data-person-badge-idx", String(index));

                            btn.appendChild(taoNutSoTruyen(item.mangasCount));

                            const nameSpan = document.createElement("span");
                            nameSpan.textContent = item.name;

                            const xSpan = document.createElement("span");
                            xSpan.className =
                                "bg-bgc-layer1 text-txt-secondary hover:bg-error-error ml-1 inline-flex h-5 w-5 cursor-pointer items-center justify-center rounded-full transition-colors hover:text-white";
                            xSpan.setAttribute("title", "Bỏ gắn");
                            xSpan.textContent = "×";
                            xSpan.addEventListener("click", function (e) {
                                e.preventDefault();
                                e.stopPropagation();
                                selected.splice(index, 1);
                                dongBoAn();
                            });

                            btn.appendChild(nameSpan);
                            btn.appendChild(xSpan);

                            btn.addEventListener("dragstart", function (e) {
                                dragIndex = index;
                                if (e.dataTransfer) {
                                    e.dataTransfer.effectAllowed = "move";
                                }
                            });
                            btn.addEventListener("dragover", function (e) {
                                e.preventDefault();
                                if (e.dataTransfer) {
                                    e.dataTransfer.dropEffect = "move";
                                }
                            });
                            btn.addEventListener("drop", function (e) {
                                e.preventDefault();
                                const to = parseInt(btn.getAttribute("data-person-badge-idx") || "-1", 10);
                                if (dragIndex === null || dragIndex === to || to < 0) {
                                    return;
                                }
                                const moved = selected.splice(dragIndex, 1)[0];
                                selected.splice(to, 0, moved);
                                dragIndex = null;
                                dongBoAn();
                            });
                            btn.addEventListener("dragend", function () {
                                dragIndex = null;
                            });

                            badges.appendChild(btn);
                        });
                    }

                    function napTuHidden() {
                        const namesP = docJsonHiddenKetQua(namesInput);
                        const slugsP = docJsonHiddenKetQua(slugsInput);
                        const idsP = docJsonHiddenKetQua(idsInput);
                        const countsP = docJsonHiddenKetQua(countsInput);
                        const names = namesP.arr;
                        const slugs = slugsP.arr;
                        const ids = idsP.arr;
                        const counts = countsP.arr;
                        selected = [];
                        const n = Math.max(names.length, slugs.length, ids.length, counts.length);
                        for (let i = 0; i < n; i++) {
                            const name = names[i] != null ? String(names[i]) : "";
                            if (name.trim() === "") {
                                continue;
                            }
                            const slug = slugs[i] != null ? String(slugs[i]) : "";
                            const rawId = ids[i];
                            let id = null;
                            if (rawId != null && Number(rawId) > 0) {
                                id = Number(rawId);
                            }
                            const rawMc = counts[i];
                            let mangasCount = 0;
                            if (rawMc != null && Number(rawMc) >= 0) {
                                mangasCount = Math.floor(Number(rawMc));
                            }
                            selected.push({
                                id: id,
                                name: name.trim(),
                                slug: slug.trim(),
                                mangasCount: mangasCount,
                            });
                        }

                        // Hidden bị rỗng / JSON hỏng (entity, cắt body) — lấy lại từ bootstrap DB; không áp khi user cố ý gửi [].
                        const tenBiLoi = !namesP.ok || namesP.emptyAttr;
                        if (
                            selected.length === 0 &&
                            taxonomyBootstrap &&
                            tenBiLoi
                        ) {
                            const rows =
                                kind === "translator"
                                    ? taxonomyBootstrap.translators
                                    : taxonomyBootstrap.authors;
                            if (Array.isArray(rows) && rows.length > 0) {
                                rows.forEach(function (r) {
                                    const nm = r && r.name != null ? String(r.name).trim() : "";
                                    if (nm === "") {
                                        return;
                                    }
                                    const sid = r.id != null && Number(r.id) > 0 ? Number(r.id) : null;
                                    let mc = 0;
                                    if (r.mangasCount != null && Number(r.mangasCount) >= 0) {
                                        mc = Math.floor(Number(r.mangasCount));
                                    }
                                    selected.push({
                                        id: sid,
                                        name: nm,
                                        slug: r.slug != null ? String(r.slug).trim() : "",
                                        mangasCount: mc,
                                    });
                                });
                                dongBoAn();

                                return;
                            }
                        }

                        capNhatPreview();
                        veBadge();
                    }

                    function anDropdown() {
                        dropdown.classList.add("hidden");
                        ul.innerHTML = "";
                    }

                    function hienDropdown() {
                        dropdown.classList.remove("hidden");
                    }

                    function veGoiY(rows, rawQuery) {
                        ul.innerHTML = "";
                        const q = rawQuery.trim();
                        if (q.length < 1) {
                            anDropdown();
                            return;
                        }

                        // Luôn có dòng tạo mới (kể cả khi đã có tên trùng trong DB).
                        const liCreate = document.createElement("li");
                        liCreate.className =
                            "cursor-pointer px-3 py-2 text-sm bg-bgc-layer1 hover:bg-bgc-layer2 text-txt-primary";
                        liCreate.setAttribute("title", "Tạo " + entityLabel + " \"" + q + "\"");
                        const spanCreate = document.createElement("span");
                        spanCreate.appendChild(document.createTextNode("➕ Tạo " + entityLabel + " "));
                        const strong = document.createElement("strong");
                        strong.textContent = q;
                        spanCreate.appendChild(strong);
                        liCreate.appendChild(spanCreate);
                        liCreate.addEventListener("mousedown", function (e) {
                            e.preventDefault();
                        });
                        liCreate.addEventListener("click", function () {
                            const entry = {
                                id: null,
                                name: q,
                                slug: "",
                                mangasCount: 0,
                            };
                            if (!daCo(entry)) {
                                selected.push(entry);
                                dongBoAn();
                            }
                            input.value = "";
                            anDropdown();
                        });
                        ul.appendChild(liCreate);

                        rows.forEach(function (r) {
                            const cnt =
                                r.mangas_count != null && !Number.isNaN(Number(r.mangas_count))
                                    ? Math.floor(Number(r.mangas_count))
                                    : 0;
                            const entry = {
                                id: r.id != null ? Number(r.id) : null,
                                name: String(r.name || ""),
                                slug: String(r.slug || ""),
                                mangasCount: cnt,
                            };
                            const chonRoi = daCo(entry);

                            const li = document.createElement("li");
                            li.className =
                                "cursor-pointer px-3 py-2 text-sm flex items-center gap-2 text-txt-primary";
                            if (chonRoi) {
                                li.className +=
                                    " border-bd-default bg-bgc-layer2 rounded-full mx-2 my-1.5 border px-3 py-1";
                            } else {
                                li.className += " bg-bgc-layer1 hover:bg-bgc-layer2";
                            }
                            li.setAttribute("title", chonRoi ? entry.name + " (đã chọn)" : entry.name);

                            li.appendChild(taoNutSoTruyen(cnt));
                            const nameEl = document.createElement("span");
                            nameEl.className = chonRoi ? "font-medium" : "";
                            nameEl.textContent = entry.name;
                            li.appendChild(nameEl);

                            li.addEventListener("mousedown", function (e) {
                                e.preventDefault();
                            });
                            li.addEventListener("click", function () {
                                if (!daCo(entry)) {
                                    selected.push(entry);
                                    dongBoAn();
                                }
                                input.value = "";
                                anDropdown();
                            });

                            ul.appendChild(li);
                        });

                        if (ul.children.length === 0) {
                            anDropdown();
                            return;
                        }
                        hienDropdown();
                    }

                    function timKiem(q) {
                        if (!searchUrl || q.length < 1) {
                            ul.innerHTML = "";
                            anDropdown();
                            return;
                        }
                        const url =
                            searchUrl + (searchUrl.indexOf("?") >= 0 ? "&" : "?") + "q=" + encodeURIComponent(q);
                        fetch(url, {
                            method: "GET",
                            headers: {
                                Accept: "application/json",
                                "X-Requested-With": "XMLHttpRequest",
                            },
                            credentials: "same-origin",
                        })
                            .then(function (res) {
                                return res.json().then(function (body) {
                                    return { ok: res.ok, body: body };
                                });
                            })
                            .then(function (r) {
                                if (!r.ok || !r.body || !Array.isArray(r.body.data)) {
                                    return;
                                }
                                veGoiY(r.body.data, q);
                            })
                            .catch(function () {
                                ul.innerHTML = "";
                                anDropdown();
                            });
                    }

                    input.addEventListener("input", function () {
                        const q = input.value;
                        if (debounceTimer) {
                            clearTimeout(debounceTimer);
                        }
                        if (q.trim().length < 1) {
                            anDropdown();
                            return;
                        }
                        debounceTimer = window.setTimeout(function () {
                            timKiem(q.trim());
                        }, 200);
                    });

                    input.addEventListener("focus", function () {
                        const q = input.value.trim();
                        if (q.length >= 1) {
                            timKiem(q);
                        }
                    });

                    input.addEventListener("blur", function () {
                        window.setTimeout(function () {
                            anDropdown();
                        }, 200);
                    });

                    napTuHidden();
                    dongBoCallbacksPerson.push(dongBoAn);
                });

                form.addEventListener(
                    "submit",
                    function () {
                        dongBoCallbacksPerson.forEach(function (fn) {
                            fn();
                        });
                    },
                    true
                );
            })();
        })();
    </script>
@endpush
