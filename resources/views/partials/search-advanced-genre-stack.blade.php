{{-- Danh sách thể loại theo chữ cái; checkbox gắn slug để build includeGenres / excludeGenres --}}
@php
    /** @var \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, \App\Models\Genre>> $genresByLetter */
    /** @var list<string> $selectedSlugs */
    /** @var string $mode include|exclude */
@endphp
<div class="flex w-full flex-col gap-3">
    <div class="text-txt-secondary text-xs">
        @if ($mode === 'include')
            Gõ <b>a,b,c…</b> hoặc tên thể loại để lọc nhanh trong danh sách.
        @else
            Truyện có <b>bất kỳ</b> thể loại trong danh sách này sẽ bị loại khỏi kết quả.
        @endif
    </div>
    <div class="flex items-center gap-2">
        <label class="relative w-full max-w-[360px]">
            <input
                type="search"
                placeholder="Lọc thể loại theo từ khóa"
                class="w-full rounded-md border border-bd-default bg-bgc-layer2 px-3 py-2 text-base outline-none focus:ring-2 focus:ring-primary/40 md:text-sm"
                autocomplete="off"
                @if ($mode === 'include')
                    data-adv-filter-include
                @else
                    data-adv-filter-exclude
                @endif
            >
        </label>
    </div>
    <div class="max-h-[360px] overflow-y-auto rounded-lg p-2 pr-1 [scrollbar-color:rgba(255,255,255,0.3)_transparent] [scrollbar-width:thin]">
        <div class="space-y-4" @if ($mode === 'include') data-adv-include-grid @else data-adv-exclude-grid @endif>
            @foreach ($genresByLetter as $letter => $genres)
                @if ($genres->isEmpty())
                    @continue
                @endif
                <div {{ $mode === 'include' ? 'data-adv-letter-group' : 'data-adv-letter-group-ex' }}>
                    <div class="mb-2 flex items-center gap-2">
                        <div class="text-sm font-bold text-txt-focus">{{ $letter }}</div>
                        <div class="h-px flex-1 bg-bd-default/60"></div>
                    </div>
                    <div class="grid grid-cols-3 gap-x-4 gap-y-2 sm:grid-cols-4 lg:grid-cols-5">
                        @foreach ($genres as $genre)
                            @php
                                $name = (string) $genre->name;
                                $first = mb_substr($name, 0, 1, 'UTF-8');
                                $rest = mb_substr($name, 1, null, 'UTF-8');
                            @endphp
                            <label class="adv-genre-label flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 select-none hover:bg-white/5">
                                <div class="relative">
                                    <input
                                        type="checkbox"
                                        class="bg-bgc-layer2 border-bd-default checked:bg-lav-500 checked:border-lav-500 h-4 w-4 cursor-pointer appearance-none rounded border"
                                        value="{{ $genre->slug }}"
                                        @if ($mode === 'include')
                                            data-adv-include
                                        @else
                                            data-adv-exclude
                                        @endif
                                        @checked(in_array($genre->slug, $selectedSlugs, true))
                                    >
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
