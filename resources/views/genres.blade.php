@extends('theme-vinahentai::layout.main')

@section('body')
    <div class="container-page mx-auto px-4 py-10">
        <div class="max-w-3xl space-y-4">
            <h1 class="text-txt-primary text-4xl font-semibold leading-tight">Tất cả thể loại truyện Hentai</h1>
            <div class="h-1 w-28 rounded-full bg-gradient-to-r from-lav-600 to-lav-500"></div>
            <p class="rounded-2xl border border-white/5 bg-bgc-layer2/60 p-4 text-sm leading-relaxed text-txt-secondary"><strong class="text-txt-primary">Cảnh báo 18+:</strong> Đây là trang web 18+, nghiêm cấm người dưới 18 tuổi truy cập.</p>
            <p class="text-base leading-relaxed text-txt-secondary">Trang trụ cột tập hợp các thể loại hentai/18+ phổ biến: NTR, MILF, 3D, Doujinshi, ảnh cosplay, học đường, giả tưởng, hardcore nhẹ đến nặng và nhiều nhánh phụ khác. Mỗi thể loại có kho truyện riêng được kiểm duyệt, cập nhật thường xuyên, giúp bạn định hướng nhanh và chuyển trang đúng gu mà không phải dò tìm từng danh sách lẻ.</p>
            <div class="flex flex-wrap items-center gap-3 text-sm font-medium"><a class="rounded-full border border-white/10 bg-bgc-layer2/80 px-4 py-2 text-txt-primary transition hover:-translate-y-0.5 hover:border-lav-500 hover:text-white" href="{{ route('danh-sach') }}" data-discover="true">Danh sách truyện</a><a class="rounded-full border border-white/10 bg-bgc-layer2/80 px-4 py-2 text-txt-primary transition hover:-translate-y-0.5 hover:border-lav-500 hover:text-white" href="{{ route('leaderboard.manga') }}" data-discover="true">Bảng xếp hạng manga</a></div>
        </div>

        <div class="mt-8">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($genres as $genre)
                <a class="group rounded-2xl border border-white/5 bg-bgc-layer2/80 p-4 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-lav-500 hover:shadow-lg" href="{{ route('genres.show', $genre->slug) }}" data-discover="true"><div class="flex items-start justify-between gap-2"><div class="text-txt-primary text-lg font-semibold group-hover:text-white">{{ $genre->name }}</div><span class="text-xs uppercase tracking-wide text-lav-500 group-hover:text-white">Xem</span></div><p class="mt-2 text-sm leading-relaxed text-txt-secondary">{{ $genre->description }}</p></a>
                @endforeach
            </div>
        </div>
    </div>
@endsection
