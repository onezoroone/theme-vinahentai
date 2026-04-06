@extends('theme-vinahentai::layout.main')

@section('body')
    @php
        $goldIcon = asset('vendor/theme-vinahentai/images/gold-icon.png');
    @endphp
    <div class="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-6 sm:gap-8 lg:px-6 lg:py-10">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#FFCF8B] sm:text-sm">Cửa hàng</p>
        </div>

        @if (session('shop_success'))
            <div class="rounded-xl border border-emerald-500/35 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-200"
                role="status">
                {{ session('shop_success') }}
            </div>
        @endif
        @if (session('shop_error'))
            <div class="rounded-xl border border-red-500/35 bg-red-500/10 px-4 py-3 text-sm font-medium text-red-200"
                role="alert">
                {{ session('shop_error') }}
            </div>
        @endif
        @if ($errors->has('shop_item_id'))
            <div class="rounded-xl border border-red-500/35 bg-red-500/10 px-4 py-3 text-sm font-medium text-red-200"
                role="alert">
                {{ $errors->first('shop_item_id') }}
            </div>
        @endif

        <div
            class="rounded-2xl border border-white/10 bg-[radial-gradient(circle_at_top,_rgba(255,209,143,0.14),_transparent_42%),linear-gradient(180deg,rgba(17,14,22,0.98),rgba(8,7,12,0.96))] p-4 shadow-[0_30px_80px_rgba(0,0,0,0.38)] sm:rounded-[30px] sm:p-5 lg:p-8">
            <div class="rounded-xl border border-[#FFCF8B]/18 bg-[#150f12]/75 p-4 sm:rounded-2xl">
                <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-[#FFCF8B] sm:text-xs">Dâm Ngọc hiện có
                </p>
                <div class="mt-2">
                    <span class="inline-flex items-center gap-2 font-black text-white text-2xl">
                        <img src="{{ $goldIcon }}" alt="Dâm Ngọc" class="h-7 w-7">
                        <span>{{ $userPoints }}</span>
                    </span>
                </div>
            </div>

            <div class="mt-5 flex flex-col gap-4 sm:mt-6">
                @forelse ($shopItems as $item)
                    @php
                        $qty = (int) ($quantitiesByItemId[$item->id] ?? 0);
                        $price = (int) $item->price_points;
                        $duDn = $userPoints >= $price;
                        $img = $item->imageUrl();
                    @endphp
                    <article
                        class="overflow-hidden rounded-2xl border border-white/10 bg-[linear-gradient(180deg,rgba(34,27,36,0.88),rgba(18,14,22,0.94))] shadow-[0_22px_60px_rgba(0,0,0,0.24)]">
                        <div
                            class="flex flex-col gap-4 p-4 sm:p-5 xl:grid xl:grid-cols-[112px_minmax(0,0.95fr)_minmax(0,1.3fr)_minmax(240px,0.82fr)] xl:items-center xl:gap-5 xl:p-6">
                            <div class="flex gap-4 sm:items-start xl:contents">
                                <div
                                    class="h-28 w-24 flex-shrink-0 overflow-hidden rounded-xl border border-white/10 bg-[#0d0a11] sm:h-32 sm:w-28 xl:h-[120px] xl:w-[112px] xl:rounded-2xl">
                                    @if ($img !== '')
                                        <img src="{{ asset($img) }}" alt="{{ $item->name }}"
                                            class="h-full w-full object-contain object-center">
                                    @else
                                        <div
                                            class="flex h-full w-full items-center justify-center text-[10px] text-white/40">
                                            No img</div>
                                    @endif
                                </div>
                                <div class="flex min-w-0 flex-1 flex-col gap-1.5 xl:gap-2">
                                    <p class="text-base font-black text-white sm:text-xl">{{ $item->name }}</p>
                                    <p class="text-xs leading-5 text-white/55 sm:text-sm sm:leading-6">
                                        {{ $item->summary ?? '' }}</p>
                                </div>
                            </div>
                            <div class="min-w-0 xl:self-stretch xl:py-1">
                                <p class="text-sm leading-6 text-white/70 sm:text-[15px] sm:leading-7">
                                    {{ $item->description ?? '' }}</p>
                            </div>
                            <div
                                class="flex flex-col gap-3 rounded-2xl border border-white/10 bg-[#150f12]/78 p-4 xl:self-stretch xl:justify-center">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="rounded-xl border border-white/8 bg-white/[0.03] p-3">
                                        <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-white/40">Đang
                                            sở hữu</p>
                                        <p class="mt-2 text-xl font-black text-[#FFCF8B]">×{{ $qty }}</p>
                                    </div>
                                    <div class="rounded-xl border border-white/8 bg-white/[0.03] p-3">
                                        <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-white/40">Giá
                                        </p>
                                        <div class="mt-2">
                                            <span class="inline-flex items-center gap-2 font-black text-white text-lg">
                                                <img src="{{ $goldIcon }}" alt="Dâm Ngọc" class="h-5 w-5">
                                                <span>{{ $price }}</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <form class="w-full" method="post" action="{{ route('shop.purchase') }}">
                                    @csrf
                                    <input type="hidden" name="shop_item_id" value="{{ $item->id }}">
                                    <button type="submit" @disabled(!$duDn)
                                        class="w-full rounded-xl bg-[linear-gradient(135deg,#f0b86d,#ff7b57)] px-5 py-3 text-sm font-black text-[#1a0d08] transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-50">{{ $duDn ? 'Mua ngay' : 'Không đủ' }}</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="text-center text-sm text-white/60">Chưa có vật phẩm. Chạy seeder: <code
                            class="rounded bg-white/10 px-1">php artisan db:seed --class=ShopItemSeeder</code></p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
