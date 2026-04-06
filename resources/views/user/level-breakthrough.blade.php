@extends('theme-vinahentai::layout.main')

@section('body')
    @include('theme-vinahentai::partials.breakthrough-effects')

    <div class="mx-auto flex w-full max-w-[520px] flex-col gap-6 p-4 lg:py-8">
        <div id="breakthrough-page-card" class="bg-bgc-layer1 border-bd-default flex flex-col gap-5 rounded-xl border p-5 lg:p-6">
            <div>
                <h1 class="text-txt-primary font-sans text-xl font-semibold lg:text-2xl">Đột phá cấp độ</h1>
                <p class="text-txt-secondary mt-2 text-sm leading-relaxed">
                    Kinh nghiệm đã đủ để xung kích cảnh giới tiếp theo. Xác nhận để thử đột phá (có thể thất bại theo tỉ lệ).
                </p>
            </div>

            @if (session('status'))
                <div class="rounded-lg border border-green-500/40 bg-green-500/10 px-3 py-2 text-sm font-medium text-green-300"
                    role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-lg border border-red-500/40 bg-red-500/10 px-3 py-2 text-sm font-medium text-red-300"
                    role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border border-red-500/40 bg-red-500/10 px-3 py-2 text-sm font-medium text-red-300"
                    role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (!empty($preview))
                <div class="bg-bgc-layer2 flex flex-col gap-3 rounded-lg border border-bd-default/60 p-4">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-txt-secondary text-sm">Cấp hiện tại</span>
                    <span class="text-txt-focus text-sm font-semibold">Cấp {{ $preview['current_level'] }} — {{ $preview['current_level_name'] }}</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-txt-secondary text-sm">Mục tiêu</span>
                    <span class="text-txt-primary text-sm font-semibold">Cấp {{ $preview['next_level'] }} — {{ $preview['next_level_name'] }}</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-txt-secondary text-sm">Kinh nghiệm hiện có</span>
                    <span class="text-txt-primary text-sm font-semibold tabular-nums">{{ $preview['experience_points'] }} / {{ $preview['required_experience'] }}</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-txt-secondary text-sm">Tỉ lệ thành công</span>
                    <span class="text-txt-focus text-sm font-semibold tabular-nums">{{ rtrim(rtrim(number_format($preview['success_rate'], 2, '.', ''), '0'), '.') }}%</span>
                </div>
                @if (($preview['points_cost'] ?? 0) > 0)
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-txt-secondary text-sm">Phí Dâm Ngọc</span>
                        <span class="text-txt-primary text-sm font-semibold tabular-nums">{{ $preview['points_cost'] }}</span>
                    </div>
                @endif
                @if (($preview['gold_cost'] ?? 0) > 0)
                    <p class="text-txt-secondary text-xs">Phí vàng: {{ $preview['gold_cost'] }} (chưa mở khóa trong phiên bản này).</p>
                @endif
                </div>
            @else
                <div class="bg-bgc-layer2 rounded-lg border border-bd-default/60 p-4">
                    <p class="text-txt-secondary text-sm">Hiện bạn chưa đủ điều kiện đột phá tiếp. Hãy tích lũy thêm kinh nghiệm.</p>
                </div>
            @endif

            @php($prot = $preview['exp_protection'] ?? null)

            @if (!empty($preview))
                <form method="post" action="{{ route('user.level-breakthrough.attempt') }}" class="flex flex-col gap-4">
                    @csrf
                    @if (!empty($prot['available']))
                        <div class="bg-bgc-layer2 flex flex-col gap-2 rounded-lg border border-bd-default/60 p-4">
                            <label class="flex cursor-pointer items-start gap-3">
                                <input type="checkbox" name="use_protection" value="1"
                                    class="border-bd-default bg-bgc-layer1 text-txt-focus mt-0.5 h-4 w-4 shrink-0 rounded"
                                    @checked(old('use_protection')) />
                                <span class="text-txt-secondary text-sm leading-relaxed">
                                    <span class="text-txt-primary font-medium">Dùng bảo hộ tu vi</span>
                                    — {{ $prot['name'] ?? '' }} (còn {{ (int) ($prot['quantity'] ?? 0) }}).
                                    Nếu đột phá <strong class="text-txt-primary">thất bại</strong>, bí kíp giữ lại
                                    <strong class="text-txt-focus tabular-nums">{{ (int) ($prot['protection_percent'] ?? 0) }}%</strong>
                                    tu vi Bá Lọ rồi tự tiêu hao (mỗi lần thử tối đa 1 quyển).
                                </span>
                            </label>
                            @error('use_protection')
                                <p class="text-sm font-medium text-red-300">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ route('profile', $user->id) }}"
                            class="text-txt-secondary hover:text-txt-primary text-center text-sm font-medium transition sm:text-left">
                            ← Quay về hồ sơ
                        </a>
                        <button type="submit"
                            class="from-[#DD94FF] to-[#D373FF] text-txt-inverse inline-flex min-h-[44px] min-w-[200px] items-center justify-center self-center rounded-xl bg-gradient-to-b px-6 py-3 font-sans text-sm font-semibold shadow-[0px_4px_9px_rgba(196,69,255,0.25)] transition-opacity hover:opacity-90 sm:self-end">
                            Xác nhận đột phá
                        </button>
                    </div>
                </form>
            @else
                <div class="flex justify-start">
                    <a href="{{ route('profile', $user->id) }}"
                        class="text-txt-secondary hover:text-txt-primary text-sm font-medium transition">
                        ← Quay về hồ sơ
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
