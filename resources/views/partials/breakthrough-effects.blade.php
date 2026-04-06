{{-- Hiệu ứng full màn hình khi session flash breakthrough_effect (success | fail) --}}
@if (session('breakthrough_effect'))
    @push('header')
        <link rel="stylesheet" href="{{ asset('vendor/theme-vinahentai/css/breakthrough-effects.css') }}">
    @endpush

    <div id="breakthrough-effect-root" data-breakthrough-effect="{{ session('breakthrough_effect') }}" aria-hidden="true"></div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var root = document.getElementById('breakthrough-effect-root');
                if (!root) {
                    return;
                }
                var kind = root.getAttribute('data-breakthrough-effect');
                var colors = ['#E8B5FF', '#C445FF', '#FFD700', '#F5F0FF', '#D373FF', '#FFF8E7', '#FF6FD8'];

                function removeEl(el) {
                    if (el && el.parentNode) {
                        el.parentNode.removeChild(el);
                    }
                }

                if (kind === 'success') {
                    var overlay = document.createElement('div');
                    overlay.className = 'breakthrough-success-fullscreen';
                    overlay.setAttribute('aria-hidden', 'true');
                    overlay.innerHTML =
                        '<div class="bts-disc"></div>' +
                        '<div class="bts-ray"></div>' +
                        '<div class="bts-ring"></div>' +
                        '<div class="bts-flash-plane"></div>';
                    document.body.appendChild(overlay);
                    window.setTimeout(function () {
                        removeEl(overlay);
                    }, 2400);

                    var i;
                    for (i = 0; i < 110; i++) {
                        var p = document.createElement('span');
                        p.className = 'breakthrough-particle';
                        var size = 4 + Math.random() * 12;
                        p.style.width = size + 'px';
                        p.style.height = size + 'px';
                        p.style.left = Math.random() * 100 + '%';
                        p.style.animationDelay = Math.random() * 0.5 + 's';
                        p.style.animationDuration = 1.1 + Math.random() * 0.55 + 's';
                        p.style.setProperty('--drift', (Math.random() * 80 - 40) + 'px');
                        p.style.background = colors[i % colors.length];
                        p.style.color = colors[i % colors.length];
                        root.appendChild(p);
                    }
                    for (i = 0; i < 45; i++) {
                        var c = document.createElement('span');
                        c.className = 'breakthrough-particle breakthrough-particle--confetti';
                        c.style.width = 6 + Math.random() * 8 + 'px';
                        c.style.height = 10 + Math.random() * 10 + 'px';
                        c.style.left = Math.random() * 100 + '%';
                        c.style.top = -5 - Math.random() * 15 + '%';
                        c.style.animationDelay = Math.random() * 0.4 + 's';
                        c.style.setProperty('--drift', (Math.random() * 120 - 60) + 'px');
                        c.style.background = colors[(i + 3) % colors.length];
                        root.appendChild(c);
                    }
                    window.setTimeout(function () {
                        removeEl(root);
                    }, 2200);
                }

                if (kind === 'fail') {
                    var failWrap = document.createElement('div');
                    failWrap.className = 'breakthrough-fail-fullscreen';
                    failWrap.setAttribute('aria-hidden', 'true');
                    failWrap.innerHTML =
                        '<div class="btf-blood"></div>' +
                        '<div class="btf-scan"></div>' +
                        '<div class="btf-strike"></div>' +
                        '<div class="btf-border-flash"></div>';
                    document.body.appendChild(failWrap);
                    window.setTimeout(function () {
                        removeEl(failWrap);
                    }, 1300);

                    var card = document.getElementById('breakthrough-page-card');
                    if (card) {
                        card.classList.add('breakthrough-fail-shake');
                        window.setTimeout(function () {
                            card.classList.remove('breakthrough-fail-shake');
                        }, 700);
                    }
                }
            });
        </script>
    @endpush
@endif
