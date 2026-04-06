@extends('theme-vinahentai::layout.main')

@section('body')
    @php
        /** @var \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, \App\Models\Genre>> $genresByLetter */
        /** @var \Illuminate\Support\Collection<int, \App\Models\Genre> $hiddenGenres */
        /** @var list<int> $hiddenIds */
    @endphp
    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 px-4 py-6" data-blacklist-tags-page>
        <h1 class="text-txt-primary font-sans text-2xl leading-9 font-semibold">Lọc thể loại không thích</h1>

        @if (session('status'))
            <div class="border-bd-default bg-bgc-layer2 text-txt-primary rounded-xl border px-4 py-3 text-sm font-medium" role="status">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-bgc-layer1 border-bd-default rounded-xl border p-4 shadow-lg">
            <div class="mb-3 text-sm text-txt-secondary">Các thể loại hiện đang bị ẩn:</div>
            <div class="flex flex-wrap gap-2" data-blacklist-chips>
                @forelse ($hiddenGenres as $genre)
                    <span class="inline-flex items-center gap-1 rounded-full bg-white/5 px-2 py-1 text-xs text-txt-primary">
                        <span>{{ $genre->name }}</span>
                        <button type="button"
                            class="rounded-full bg-white/10 p-0.5 hover:bg-white/20"
                            aria-label="Bỏ chọn {{ $genre->name }}"
                            data-blacklist-chip-remove="{{ $genre->id }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x h-3 w-3" aria-hidden="true">
                                <path d="M18 6 6 18"></path>
                                <path d="m6 6 12 12"></path>
                            </svg>
                        </button>
                    </span>
                @empty
                    <span class="text-txt-secondary text-sm" data-blacklist-chips-empty>Chưa có thể loại nào bị ẩn.</span>
                @endforelse
            </div>
        </div>

        <form method="post" action="{{ route('user.blacklist-tags.update') }}" class="contents">
            @csrf
            <div class="bg-bgc-layer1 border-bd-default rounded-xl border p-4 shadow-lg">
                <div class="mb-3 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-tag text-txt-secondary h-4 w-4" aria-hidden="true">
                        <path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"></path>
                        <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
                    </svg>
                    <div class="text-txt-primary font-sans text-base font-semibold">Chọn thể loại để ẩn</div>
                </div>
                <div class="mb-3 text-xs text-txt-secondary">Gõ a,b,c để lọc theo chữ đầu; hoặc nhập nhiều từ khóa để tìm nhanh (vd: manhwa color series).</div>
                <label class="relative mb-4 block w-full max-w-[360px]">
                    <input type="search"
                        data-blacklist-filter
                        placeholder="Có thể nhập nhiều từ khóa cùng lúc để tìm (vd: manhwa color series)"
                        class="w-full rounded-md border border-bd-default bg-bgc-layer2 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary/40"
                        value=""
                        autocomplete="off"
                        aria-label="Lọc danh sách thể loại">
                </label>
                <div class="max-h-[420px] overflow-y-auto rounded-lg border border-white/10 p-3 pr-1 [scrollbar-color:rgba(255,255,255,0.3)_transparent] [scrollbar-width:thin]">
                    <div class="space-y-4" data-blacklist-letter-root>
                        @foreach ($genresByLetter as $letter => $genres)
                            <div data-blacklist-letter-group>
                                <div class="mb-2 flex items-center gap-2">
                                    <div class="text-sm font-bold text-txt-focus">{{ $letter }}</div>
                                    <div class="h-px flex-1 bg-bd-default/60"></div>
                                </div>
                                <div class="grid grid-cols-3 gap-x-4 gap-y-2">
                                    @foreach ($genres as $genre)
                                        @php
                                            $name = $genre->name;
                                            $first = mb_substr($name, 0, 1, 'UTF-8');
                                            $rest = mb_substr($name, 1, null, 'UTF-8');
                                            $searchText = mb_strtolower($name, 'UTF-8');
                                        @endphp
                                        <label class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 select-none hover:bg-white/5"
                                            data-blacklist-tag-row
                                            data-blacklist-search-text="{{ $searchText }}">
                                            <div class="relative flex h-4 w-4 shrink-0 items-center justify-center">
                                                <input type="checkbox"
                                                    name="genre_ids[]"
                                                    value="{{ $genre->id }}"
                                                    class="bg-bgc-layer2 border-bd-default checked:bg-lav-500 checked:border-lav-500 h-4 w-4 cursor-pointer appearance-none rounded border"
                                                    @checked(in_array((int) $genre->id, $hiddenIds, true))>
                                                <span class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check text-txt-primary h-3 w-3" aria-hidden="true">
                                                        <path d="M20 6 9 17l-5-5"></path>
                                                    </svg>
                                                </span>
                                            </div>
                                            <span class="text-txt-primary line-clamp-1 font-sans text-xs font-medium">
                                                <span class="font-bold">{{ $first }}</span>{{ $rest }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('home') }}" class="rounded-xl border border-bd-default px-4 py-2 text-sm font-medium text-txt-primary">Hủy</a>
                <button type="submit" class="rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] px-4 py-2.5 text-sm font-semibold text-black shadow-lg disabled:opacity-50">
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const root = document.querySelector("[data-blacklist-tags-page]");
            if (!root) return;

            const input = root.querySelector("[data-blacklist-filter]");
            const syncCheckIcon = () => {
                root.querySelectorAll('input[name="genre_ids[]"]').forEach((cb) => {
                    const wrap = cb.parentElement && cb.parentElement.querySelector("span.pointer-events-none");
                    if (!wrap) return;
                    wrap.classList.toggle("hidden", !cb.checked);
                });
            };
            syncCheckIcon();
            root.addEventListener("change", (e) => {
                if (e.target && e.target.matches && e.target.matches('input[name="genre_ids[]"]')) {
                    syncCheckIcon();
                }
            });

            if (input) {
                input.addEventListener("input", () => {
                    const q = input.value.trim().toLowerCase();
                    const tokens = q.split(/\s+/).filter(Boolean);
                    root.querySelectorAll("[data-blacklist-letter-group]").forEach((group) => {
                        let visibleInGroup = false;
                        group.querySelectorAll("[data-blacklist-search-text]").forEach((row) => {
                            const text = row.getAttribute("data-blacklist-search-text") || "";
                            const match = tokens.length === 0 || tokens.every((t) => text.includes(t));
                            const label = row.closest("[data-blacklist-tag-row]");
                            if (label) {
                                label.classList.toggle("hidden", !match);
                            }
                            if (match) visibleInGroup = true;
                        });
                        group.classList.toggle("hidden", !visibleInGroup);
                    });
                });
            }

            root.querySelectorAll("[data-blacklist-chip-remove]").forEach((btn) => {
                btn.addEventListener("click", () => {
                    const id = btn.getAttribute("data-blacklist-chip-remove");
                    const cb = root.querySelector('input[name="genre_ids[]"][value="' + id + '"]');
                    if (cb) {
                        cb.checked = false;
                        syncCheckIcon();
                    }
                    const chip = btn.closest("span.inline-flex");
                    if (chip) chip.remove();
                    const chips = root.querySelector("[data-blacklist-chips]");
                    if (chips && !chips.querySelector("[data-blacklist-chip-remove]")) {
                        if (!chips.querySelector("[data-blacklist-chips-empty]")) {
                            const empty = document.createElement("span");
                            empty.className = "text-txt-secondary text-sm";
                            empty.setAttribute("data-blacklist-chips-empty", "");
                            empty.textContent = "Chưa có thể loại nào bị ẩn.";
                            chips.appendChild(empty);
                        }
                    }
                });
            });
        })();
    </script>
@endpush
