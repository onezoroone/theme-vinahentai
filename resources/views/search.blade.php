@extends('theme-vinahentai::layout.main')

@section('body')
    <div class="container-page mx-auto px-4 py-6 pb-24">
        <h1 class="text-txt-primary mb-2 text-3xl font-semibold">Tìm kiếm</h1>
        <div class="mb-6 h-1.5 w-20 bg-fuchsia-400"></div>

        <form class="bg-bgc-layer2 mb-6 flex items-center gap-2 rounded-xl px-4 py-3" data-discover="true" action="{{ route('search') }}" method="get"><input type="text" placeholder="Nhập từ khóa và nhấn Enter" class="text-txt-primary placeholder:text-txt-secondary w-full bg-transparent text-base outline-none" autofocus="" name="q" value="{{ $q }}"></form>

        <div class="mb-6"><a href="{{ route('search.advanced', ['q' => $q]) }}" class="inline-flex items-center gap-2 rounded-full border border-[#C084FC] bg-bgc-layer2/80 px-4 py-2 text-sm font-semibold text-[#E0B2FF] transition hover:bg-bgc-layer2">Tới tìm kiếm nâng cao</a></div>

        @if (empty($q))
        <p class="text-txt-secondary text-sm">Nhập từ khóa để bắt đầu tìm kiếm truyện.</p>
        @else
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2"><p class="text-txt-secondary text-sm">Từ khóa: <span class="text-txt-primary font-semibold">{{ $q }}</span></p></div>
        @endif

        <div class="space-y-2">
            @foreach ($mangas as $manga)
            <a class="border-bd-default hover:bg-bgc-layer2 flex items-center gap-3 rounded-xl border p-3 transition-colors bg-bgc-layer2/70" href="{{ $manga->getUrl() }}" data-discover="true">
                <img src="{{ $manga->cover_image }}" alt="{{ $manga->title }}" class="h-[7.8rem] w-[5.2rem] flex-shrink-0 rounded-lg object-cover">
                <div class="min-w-0 flex-1 space-y-1">
                    <h2 class="text-txt-primary line-clamp-1 text-base font-semibold">{{ $manga->title }}</h2>
                    <p class="text-txt-secondary line-clamp-1 text-xs italic">{{ $manga->alternative_title }}</p>
                    <div class="flex flex-wrap gap-2 pt-1">
                        @foreach ($manga->genres as $genre)
                        <span class="inline-flex items-center rounded-md border border-white/20 bg-black/20 px-3 py-1.5 text-sm leading-tight text-txt-primary hover:border-[#D373FF]/35 hover:bg-black/26 transition-colors w-fit"><span class="text-xs font-medium capitalize">{{ $genre->name }}</span></span>
                        @endforeach

                    </div>
                    <p class="text-txt-secondary text-[11px] font-semibold">{{ $manga->chapters->count() }}<!-- --> chương</p>
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $mangas->withQueryString()->links('theme-vinahentai::components.pagination') }}
        </div>
    </div>
@endsection
