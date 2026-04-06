@extends('theme-vinahentai::layout.main')

@section('body')
    <div class="mx-auto flex w-full max-w-[1212px] flex-col gap-6 px-4 py-6 sm:px-6 lg:px-0">
        {{-- Chỉ vùng trên cùng + pointer-events-none để không chặn click nút submit --}}
        <div data-rht-toaster="" class="pointer-events-none fixed left-4 right-4 top-4 z-[9999] max-h-[40vh]" aria-live="polite"></div>

        <div class="flex flex-col gap-2">
            <h1 class="text-txt-primary font-sans text-2xl leading-9 font-semibold sm:text-3xl">{{ $pageTitle ?? 'Đăng truyện' }}</h1>
            @if (session('status'))
                <p class="rounded-lg border border-[#25EBAC]/40 bg-[#25EBAC]/10 px-3 py-2 text-sm font-medium text-[#25EBAC]" role="status">{{ session('status') }}</p>
            @endif
            @if ($errors->any())
                <div class="rounded-lg border border-red-500/40 bg-red-500/10 px-3 py-2 text-sm text-red-300" role="alert">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        @include('theme-vinahentai::user.partials.manga-form-fields', [
            'manga' => $manga ?? null,
            'genreGroups' => $genreGroups ?? collect(),
            'mangaFormAction' => $mangaFormAction ?? '#',
        ])
    </div>
@endsection
