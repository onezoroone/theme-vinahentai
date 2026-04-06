<a href="{{ $manga->getUrl() }}" class="flex items-start gap-4 rounded-lg transition-colors hover:bg-white/5 p-2 -m-2">
    <div class="aspect-[2/3] shrink-0 overflow-hidden rounded-lg bg-black/20" style="width:94.08px"><img data-src="{{ $manga->cover_image }}" alt="{{ $manga->title }}" class="h-full w-full object-cover lozad" loading="lazy"></div>
    <div class="min-w-0 flex-1">
        <div class="text-txt-primary text-base font-semibold"><span class="hidden lg:inline">{{ $manga->title }}</span><span class="lg:hidden truncate inline-block max-w-full align-bottom">{{ $manga->title }}</span></div>

        @if ($manga->description)
        <div class="text-txt-secondary mt-1 line-clamp-2 text-sm">{!! $manga->description !!}</div>
        @endif
    </div>
</a>
