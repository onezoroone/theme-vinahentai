@extends('theme-vinahentai::layout.main')

@section('body')
    <div class="container-page flex flex-col items-center justify-center gap-11 px-4 py-8 md:px-6 lg:px-0">
        @include('theme-vinahentai::leaderboard.layout', ['active' => 'translator'])
        <h1 class="w-full text-center text-4xl leading-10 font-semibold">BXH Dịch Giả</h1>

        <div class="w-full max-w-[750px] rounded-xl bg-slate-950 outline-1 outline-offset-[-1px] outline-slate-700">
            @include('theme-vinahentai::components.user-rank-full')
        </div>

    </div>
@endsection
