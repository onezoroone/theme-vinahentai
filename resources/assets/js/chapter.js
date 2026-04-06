(function() {
    const readerRoot = document.querySelector('[data-chapter-reader]');

    /** Thông báo FuiToast khi nhận Dâm Ngọc (layout đã load fuiToast.min.js). */
    const showDamNgocToast = (message) => {
        if (!message) {
            return;
        }
        if (typeof FuiToast !== 'undefined' && typeof FuiToast.success === 'function') {
            FuiToast.success(message);
        }
    };

    if (!readerRoot) return;
    const READER_PREF_KEY = 'theme-vinahentai.reader-settings.v1';

    let servers = [];
    try {
        servers = JSON.parse(readerRoot.getAttribute('data-servers') || '[]');
    } catch (_) {
        servers = [];
    }
    if (!Array.isArray(servers) || servers.length === 0) return;

    const tabsHost = document.querySelector('[data-server-tabs]');
    const imagesHost = readerRoot.querySelector('[data-reader-images]');
    if (!tabsHost || !imagesHost) return;

    /** Cùng URL với POST lưu lịch sử; GET để lấy trang đang đọc khi vào chương. */
    const historySyncUrl = (readerRoot.getAttribute('data-chapter-reading-history-url') || '').trim();
    let resumePageLoaded = !historySyncUrl;
    let resumeLogicalPage = null;

    let currentServerId = servers[0]?.id || null;
    let horizontalSwiper = null;
    /** Gỡ listener chế độ dọc trước khi render lại (đổi server / đổi mode). */
    let detachVerticalPageTracking = null;

    const destroyHorizontalSwiper = () => {
        if (horizontalSwiper && typeof horizontalSwiper.destroy === 'function') {
            horizontalSwiper.destroy(true, true);
        }
        horizontalSwiper = null;
    };

    const initHorizontalSwiper = () => {
        destroyHorizontalSwiper();
        const swiperEl = imagesHost.querySelector('[data-reader-swiper]');
        if (!swiperEl || typeof window.Swiper !== 'function') {
            return;
        }

        horizontalSwiper = new window.Swiper(swiperEl, {
            slidesPerView: 1,
            spaceBetween: 12,
            speed: 320,
            resistanceRatio: 0.85,
            allowTouchMove: true,
            autoHeight: true,
            observer: true,
            observeParents: true,
        });

        // Cập nhật lại chiều cao theo ảnh thực tế sau khi load.
        const refreshHeight = () => {
            if (!horizontalSwiper) {
                return;
            }
            horizontalSwiper.updateAutoHeight(120);
        };

        swiperEl.querySelectorAll('img').forEach((img) => {
            if (img.complete) {
                return;
            }
            img.addEventListener('load', refreshHeight, { once: true });
            img.addEventListener('error', refreshHeight, { once: true });
        });

        horizontalSwiper.on('slideChangeTransitionEnd', refreshHeight);

        // Đồng bộ trang đang đọc cho API lịch sử (swiper ngang / 2 trang, có RTL).
        const syncPageFromHorizontalSwiper = () => {
            if (!horizontalSwiper) {
                return;
            }
            const rs = getReaderSettings();
            const totalPages = parseInt(readerRoot.getAttribute('data-reader-total-pages'), 10) || 1;
            const idx = horizontalSwiper.activeIndex;
            let page;
            if (rs.direction === 'rtl') {
                // orderedPages đảo — trang logic = total - chỉ số visual (xem logicalPageToVisualIndex).
                if (rs.pageMode === 'double') {
                    page = Math.max(1, totalPages - idx * 2);
                } else {
                    page = Math.max(1, totalPages - idx);
                }
            } else if (rs.pageMode === 'double') {
                page = Math.min((idx + 1) * 2, totalPages);
            } else {
                page = Math.min(idx + 1, totalPages);
            }
            readerRoot.setAttribute('data-reader-current-page', String(Math.max(1, page)));
        };
        horizontalSwiper.on('slideChange', syncPageFromHorizontalSwiper);
        horizontalSwiper.on('slideChangeTransitionEnd', syncPageFromHorizontalSwiper);
        requestAnimationFrame(syncPageFromHorizontalSwiper);

        requestAnimationFrame(refreshHeight);
    };

    const getReaderSettings = () => {
        let stored = {};
        try {
            stored = JSON.parse(localStorage.getItem(READER_PREF_KEY) || '{}') || {};
        } catch (_) {
            stored = {};
        }

        const mode = readerRoot.getAttribute('data-reader-mode') || stored.mode || 'vertical';
        const direction = readerRoot.getAttribute('data-reader-direction') || stored.direction || 'ltr';
        const pageMode = readerRoot.getAttribute('data-reader-page-mode') || stored.pageMode || 'single';

        return {
            mode: mode === 'horizontal' ? 'horizontal' : 'vertical',
            direction: direction === 'rtl' ? 'rtl' : 'ltr',
            pageMode: pageMode === 'double' ? 'double' : 'single',
        };
    };

    /** Chỉ số 0-based trong mảng ảnh đang render (đã áp dụng đảo thứ tự RTL). */
    const logicalPageToVisualIndex = (logical, total, direction) => {
        const p = Math.min(Math.max(1, logical), total);
        if (direction === 'rtl') {
            return total - p;
        }
        return p - 1;
    };

    /** Cuộn dọc hoặc slide ngang tới trang đã lưu (sau GET lịch sử). */
    const tryApplyResumePosition = () => {
        if (!resumePageLoaded || resumeLogicalPage === null) {
            return;
        }
        const total = parseInt(readerRoot.getAttribute('data-reader-total-pages'), 10) || 0;
        if (total < 1) {
            return;
        }
        const logical = Math.min(Math.max(1, resumeLogicalPage), total);
        const settings = getReaderSettings();

        if (settings.mode === 'vertical') {
            requestAnimationFrame(() => {
                const v = logicalPageToVisualIndex(logical, total, settings.direction);
                const row = imagesHost.children[v];
                if (row) {
                    row.scrollIntoView({ behavior: 'auto', block: 'start' });
                }
                readerRoot.setAttribute('data-reader-current-page', String(logical));
            });
            return;
        }

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                if (!horizontalSwiper) {
                    return;
                }
                const v = logicalPageToVisualIndex(logical, total, settings.direction);
                if (settings.pageMode === 'double') {
                    const slideIdx = Math.floor(v / 2);
                    horizontalSwiper.slideTo(slideIdx, 0);
                } else {
                    horizontalSwiper.slideTo(v, 0);
                }
                horizontalSwiper.update();
                readerRoot.setAttribute('data-reader-current-page', String(logical));
            });
        });
    };

    if (historySyncUrl) {
        fetch(historySyncUrl, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        })
            .then((r) => (r.ok ? r.json() : null))
            .then((data) => {
                if (data && typeof data.last_read_page === 'number' && data.last_read_page >= 1) {
                    resumeLogicalPage = data.last_read_page;
                }
            })
            .catch(() => {})
            .finally(() => {
                resumePageLoaded = true;
                tryApplyResumePosition();
            });
    }

    const renderVertical = (pages) => {
        destroyHorizontalSwiper();
        imagesHost.className = 'w-full max-w-[1080px] flex flex-col gap-0';
        imagesHost.innerHTML = pages.map((url, idx) => {
            const loading = idx === 0 ? 'eager' : 'lazy';
            const priority = idx === 0 ? 'high' : 'low';
            const page = idx + 1;

            return (
                '<div class="relative flex w-full items-center justify-center">' +
                '<div class="w-full">' +
                '<div class="relative">' +
                '<img alt="Trang ' + page + '" class="block w-full opacity-100" loading="' +
                loading + '" fetchpriority="' + priority + '" src="' + String(url) + '">' +
                '</div>' +
                '</div>' +
                '</div>'
            );
        }).join('');

        bindVerticalPageProgress();
        tryApplyResumePosition();
    };

    /**
     * Chế độ dọc: trang "đang đọc" = ảnh có tâm gần giữa viewport nhất (có thể cuộn lên/xuống, không dùng max cố định).
     * Trước đây dùng Math.max → đã từng thấy trang 12 thì mãi gửi 12 dù đã cuộn lên trang 3.
     */
    const bindVerticalPageProgress = () => {
        if (typeof detachVerticalPageTracking === 'function') {
            detachVerticalPageTracking();
            detachVerticalPageTracking = null;
        }

        const imgs = imagesHost.querySelectorAll('img');
        if (!imgs.length) {
            return;
        }

        const updateVerticalCurrentPage = () => {
            const liveImgs = imagesHost.querySelectorAll('img');
            if (!liveImgs.length) {
                return;
            }
            const vh = window.innerHeight || 0;
            if (vh <= 0) {
                return;
            }
            const centerY = vh / 2;
            let bestIdx = 0;
            let bestDist = Infinity;
            for (let i = 0; i < liveImgs.length; i += 1) {
                const r = liveImgs[i].getBoundingClientRect();
                if (r.bottom <= 0 || r.top >= vh) {
                    continue;
                }
                const imgCenter = r.top + r.height / 2;
                const dist = Math.abs(imgCenter - centerY);
                if (dist < bestDist) {
                    bestDist = dist;
                    bestIdx = i;
                }
            }
            const total = parseInt(readerRoot.getAttribute('data-reader-total-pages'), 10) || liveImgs.length;
            const page = Math.min(Math.max(1, bestIdx + 1), Math.max(1, total));
            readerRoot.setAttribute('data-reader-current-page', String(page));
        };

        let scrollRaf = 0;
        const onScrollVertical = () => {
            if (scrollRaf) {
                return;
            }
            scrollRaf = window.requestAnimationFrame(() => {
                scrollRaf = 0;
                updateVerticalCurrentPage();
            });
        };

        window.addEventListener('scroll', onScrollVertical, { passive: true });
        window.addEventListener('resize', onScrollVertical, { passive: true });

        let verticalIo = null;
        if (typeof IntersectionObserver !== 'undefined') {
            verticalIo = new IntersectionObserver(
                () => {
                    updateVerticalCurrentPage();
                },
                { threshold: [0, 0.1, 0.25, 0.5, 0.75, 1] }
            );
            imgs.forEach((img) => verticalIo.observe(img));
        }

        detachVerticalPageTracking = () => {
            window.removeEventListener('scroll', onScrollVertical);
            window.removeEventListener('resize', onScrollVertical);
            if (verticalIo) {
                verticalIo.disconnect();
                verticalIo = null;
            }
        };

        updateVerticalCurrentPage();
    };

    const renderHorizontalSingle = (pages) => {
        if (typeof detachVerticalPageTracking === 'function') {
            detachVerticalPageTracking();
            detachVerticalPageTracking = null;
        }
        imagesHost.className = 'w-full max-w-[1080px] px-2 sm:px-0';

        const slidesHtml = pages.map((url, idx) => {
            const loading = idx === 0 ? 'eager' : 'lazy';
            const priority = idx === 0 ? 'high' : 'low';
            const page = idx + 1;

            return (
                '<div class="swiper-slide relative flex items-center justify-center">' +
                '<div class="w-full relative">' +
                '<img alt="Trang ' + page + '" class="block w-full opacity-100 rounded-md" loading="' +
                loading + '" fetchpriority="' + priority + '" src="' + String(url) + '">' +
                '</div>' +
                '</div>'
            );
        }).join('');

        imagesHost.innerHTML =
            '<div class="swiper w-full" data-reader-swiper>' +
            '<div class="swiper-wrapper">' + slidesHtml + '</div>' +
            '</div>';

        initHorizontalSwiper();
        tryApplyResumePosition();
    };

    const renderHorizontalDouble = (pages) => {
        if (typeof detachVerticalPageTracking === 'function') {
            detachVerticalPageTracking();
            detachVerticalPageTracking = null;
        }
        const spreads = [];
        for (let i = 0; i < pages.length; i += 2) {
            spreads.push([pages[i], pages[i + 1] || null]);
        }

        imagesHost.className = 'w-full max-w-[1080px] px-2 sm:px-0';

        const slidesHtml = spreads.map((spread, idx) => {
            const left = spread[0];
            const right = spread[1];
            const loading = idx === 0 ? 'eager' : 'lazy';
            const priority = idx === 0 ? 'high' : 'low';

            const leftHtml = left
                ? '<img alt="Trang ' + (idx * 2 + 1) + '" class="block w-full opacity-100 rounded-md" loading="' + loading + '" fetchpriority="' + priority + '" src="' + String(left) + '">'
                : '<div class="w-full h-full rounded-md border border-white/10 bg-[#0B0F1A]"></div>';
            const rightHtml = right
                ? '<img alt="Trang ' + (idx * 2 + 2) + '" class="block w-full opacity-100 rounded-md" loading="lazy" fetchpriority="low" src="' + String(right) + '">'
                : '<div class="w-full h-full rounded-md border border-white/10 bg-[#0B0F1A]"></div>';

            return (
                '<div class="swiper-slide relative">' +
                '<div class="grid grid-cols-2 gap-2 items-start">' +
                '<div class="relative">' + leftHtml + '</div>' +
                '<div class="relative">' + rightHtml + '</div>' +
                '</div>' +
                '</div>'
            );
        }).join('');

        imagesHost.innerHTML =
            '<div class="swiper w-full" data-reader-swiper>' +
            '<div class="swiper-wrapper">' + slidesHtml + '</div>' +
            '</div>';

        initHorizontalSwiper();
        tryApplyResumePosition();
    };

    const renderImages = (serverId) => {
        const server = servers.find((item) => item && item.id === serverId) || servers[0];
        const pages = Array.isArray(server?.images) ? server.images : [];
        currentServerId = server?.id || currentServerId;

        if (pages.length === 0) {
            readerRoot.setAttribute('data-reader-total-pages', '0');
            readerRoot.setAttribute('data-reader-current-page', '1');
            imagesHost.className = 'w-full max-w-[1080px] flex flex-col gap-0';
            imagesHost.innerHTML =
                '<div class="text-center text-sm text-txt-secondary py-8">Server này chưa có ảnh.</div>';
            return;
        }

        readerRoot.setAttribute('data-reader-total-pages', String(pages.length));
        readerRoot.setAttribute('data-reader-current-page', '1');

        const settings = getReaderSettings();
        const orderedPages = settings.direction === 'rtl' ? [...pages].reverse() : [...pages];

        if (settings.mode === 'vertical') {
            renderVertical(orderedPages);
            return;
        }

        if (settings.pageMode === 'double') {
            renderHorizontalDouble(orderedPages);
            return;
        }

        renderHorizontalSingle(orderedPages);
    };

    tabsHost.addEventListener('click', (event) => {
        const target = event.target;
        const btn = target?.closest?.('[data-server-tab]');
        if (!btn || !tabsHost.contains(btn)) return;

        event.preventDefault();
        const selectedId = btn.getAttribute('data-server-tab');
        if (!selectedId) return;

        tabsHost.querySelectorAll('[data-server-tab]').forEach((tab) => {
            tab.classList.remove('border-lav-500', 'bg-lav-500/15', 'text-lav-300');
            tab.classList.add('border-bd-default', 'text-txt-secondary');
        });

        btn.classList.remove('border-bd-default', 'text-txt-secondary');
        btn.classList.add('border-lav-500', 'bg-lav-500/15', 'text-lav-300');

        renderImages(selectedId);
    });

    readerRoot.addEventListener('reader:settings-change', () => {
        renderImages(currentServerId || servers[0]?.id || null);
    });

    imagesHost.addEventListener('click', (event) => {
        const settings = getReaderSettings();
        if (settings.mode !== 'horizontal' || !horizontalSwiper) {
            return;
        }

        const rect = imagesHost.getBoundingClientRect();
        const clickX = event.clientX - rect.left;
        const isLeftHalf = clickX < rect.width / 2;
        if (settings.direction === 'rtl') {
            // RTL: bên trái đi tới, bên phải lùi lại.
            if (isLeftHalf) {
                horizontalSwiper.slideNext();
            } else {
                horizontalSwiper.slidePrev();
            }
            return;
        }

        // LTR: bên trái lùi lại, bên phải đi tới.
        if (isLeftHalf) {
            horizontalSwiper.slidePrev();
        } else {
            horizontalSwiper.slideNext();
        }
    });

    renderImages(currentServerId);
})();

    // Cài đặt chế độ đọc (mở/đóng dialog + trạng thái dọc/ngang)
    (function () {
        const settingsRoot = document.querySelector('[data-reader-settings-root]');
        const readerRoot = document.querySelector('[data-chapter-reader]');
        if (!settingsRoot || !readerRoot) return;
        const READER_PREF_KEY = 'theme-vinahentai.reader-settings.v1';

        const openBtn = settingsRoot.querySelector('[data-reader-settings-open]');
        const closeBtn = settingsRoot.querySelector('[data-reader-settings-close]');
        const panel = settingsRoot.querySelector('[data-reader-settings-panel]');
        const modeBtns = settingsRoot.querySelectorAll('[data-reader-mode-btn]');
        const horizontalOptionBlocks = settingsRoot.querySelectorAll('[data-reader-horizontal-options]');
        const directionBtns = settingsRoot.querySelectorAll('[data-reader-direction-btn]');
        const pageModeBtns = settingsRoot.querySelectorAll('[data-reader-page-mode-btn]');

        if (!openBtn || !panel) return;

        const activeClass = 'border-lav-500 bg-white/10 text-lav-500';
        const neutralClass = 'border-white/10 text-white/90';

        let mode = 'vertical';
        let direction = 'ltr';
        let pageMode = 'single';
        try {
            const stored = JSON.parse(localStorage.getItem(READER_PREF_KEY) || '{}') || {};
            mode = stored.mode === 'horizontal' ? 'horizontal' : 'vertical';
            direction = stored.direction === 'rtl' ? 'rtl' : 'ltr';
            pageMode = stored.pageMode === 'double' ? 'double' : 'single';
        } catch (_) {
            // fallback mặc định nếu localStorage lỗi
        }

        const applyButtonState = (buttons, current, attrName) => {
            buttons.forEach((btn) => {
                const isActive = btn.getAttribute(attrName) === current;
                btn.classList.remove('border-lav-500', 'bg-white/10', 'text-lav-500', 'border-white/10', 'text-white/90');
                btn.classList.add(...(isActive ? activeClass : neutralClass).split(' '));
            });
        };

        const applyReaderState = () => {
            const isHorizontal = mode === 'horizontal';
            horizontalOptionBlocks.forEach((block) => {
                block.classList.toggle('hidden', !isHorizontal);
            });

            // Gắn data state để sau này dễ mở rộng CSS/logic reader ngang.
            readerRoot.setAttribute('data-reader-mode', mode);
            readerRoot.setAttribute('data-reader-direction', direction);
            readerRoot.setAttribute('data-reader-page-mode', pageMode);
            try {
                localStorage.setItem(
                    READER_PREF_KEY,
                    JSON.stringify({
                        mode,
                        direction,
                        pageMode,
                    })
                );
            } catch (_) {
                // bỏ qua nếu localStorage bị chặn/quá quota
            }
            readerRoot.dispatchEvent(
                new CustomEvent('reader:settings-change', {
                    detail: { mode, direction, pageMode },
                })
            );

            applyButtonState(modeBtns, mode, 'data-reader-mode');
            applyButtonState(directionBtns, direction, 'data-reader-direction');
            applyButtonState(pageModeBtns, pageMode, 'data-reader-page-mode');
        };

        const openPanel = () => {
            panel.classList.remove('hidden');
        };

        const closePanel = () => {
            panel.classList.add('hidden');
        };

        openBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            panel.classList.toggle('hidden');
        });

        closeBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            closePanel();
        });

        panel.addEventListener('click', (e) => {
            e.stopPropagation();
            const modeBtn = e.target?.closest?.('[data-reader-mode-btn]');
            if (modeBtn) {
                mode = modeBtn.getAttribute('data-reader-mode') === 'horizontal' ? 'horizontal' : 'vertical';
                applyReaderState();
                return;
            }

            const directionBtn = e.target?.closest?.('[data-reader-direction-btn]');
            if (directionBtn) {
                direction = directionBtn.getAttribute('data-reader-direction') === 'rtl' ? 'rtl' : 'ltr';
                applyReaderState();
                return;
            }

            const pageModeBtn = e.target?.closest?.('[data-reader-page-mode-btn]');
            if (pageModeBtn) {
                pageMode = pageModeBtn.getAttribute('data-reader-page-mode') === 'double' ? 'double' : 'single';
                applyReaderState();
            }
        });

        document.addEventListener('click', (e) => {
            if (settingsRoot.contains(e.target)) return;
            closePanel();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closePanel();
            }
        });

        applyReaderState();
    })();

    // Like/Dislike chương (JSON + toggle class)
    (function () {
        const reactionRoot = document.querySelector('[data-chapter-reaction-root]');
        if (!reactionRoot) return;

        const isLoggedIn = reactionRoot.getAttribute('data-chapter-is-logged-in') === '1';
        const loginUrl = reactionRoot.getAttribute('data-chapter-login-url') || '';
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const reactionStatusUrl = reactionRoot.getAttribute('data-chapter-reaction-status-url') || '';
        const reactionReactUrl = reactionRoot.getAttribute('data-chapter-reaction-react-url') || '';

        if (!reactionStatusUrl || !reactionReactUrl || !loginUrl) {
            return;
        }

        const likeActiveClass =
            'relative flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl border px-2 text-sm font-semibold transition-all border-transparent bg-gradient-to-r from-pink-500 to-yellow-300 text-bgc-layer1 shadow-lg';
        const likeNeutralClass =
            'relative flex h-10 shrink-0 items-center justify-center gap-1 rounded-xl border px-2 text-sm font-semibold transition-all border-white/10 bg-[#141727] text-white/80 hover:bg-[#1b1f33]';
        const dislikeActiveClass =
            'flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl border px-2 text-sm font-semibold transition-all border-transparent bg-gradient-to-b from-[#DD94FF] to-[#D373FF] text-bgc-layer1';
        const dislikeNeutralClass =
            'flex h-10 shrink-0 items-center justify-center gap-1 rounded-xl border px-2 text-sm font-semibold transition-all border-white/10 bg-[#141727] text-white/80 hover:bg-[#1b1f33]';

        const likeBtns = () => reactionRoot.querySelectorAll('button[aria-label="Like chương"]');
        const dislikeBtns = () => reactionRoot.querySelectorAll('button[aria-label="Dislike chương"]');

        const applyState = (state) => {
            const liked = !!state?.liked;
            const disliked = !!state?.disliked;
            const likeCount = Number(state?.like_count || 0);
            const dislikeCount = Number(state?.dislike_count || 0);

            likeBtns().forEach((btn) => {
                btn.className = liked ? likeActiveClass : likeNeutralClass;
                btn.setAttribute('aria-pressed', liked ? 'true' : 'false');

                const svg = btn.querySelector('svg');
                if (svg) {
                    svg.className =
                        'lucide lucide-thumbs-up h-4 w-4 ' + (liked ? 'text-bgc-layer1' : 'text-green-500');
                }

                const countEl = btn.querySelector('span.tabular-nums');
                if (countEl) countEl.textContent = String(likeCount);
            });

            dislikeBtns().forEach((btn) => {
                btn.className = disliked ? dislikeActiveClass : dislikeNeutralClass;
                btn.setAttribute('aria-pressed', disliked ? 'true' : 'false');

                const svg = btn.querySelector('svg');
                if (svg) {
                    svg.className = 'lucide lucide-thumbs-down h-4 w-4 text-red-500';
                }

                const countEl = btn.querySelector('span.tabular-nums');
                if (countEl) countEl.textContent = String(dislikeCount);
            });

            reactionRoot.querySelectorAll('[data-chapter-liked-label]').forEach((el) => {
                el.textContent = `${likeCount} người đã thích chương này`;
            });
        };

        const loadStatus = async () => {
            if (!isLoggedIn) return;
            const res = await fetch(reactionStatusUrl, {
                headers: {
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
            });
            if (!res.ok) return;
            const data = await res.json();
            applyState(data);
        };

        const setButtonsDisabled = (disabled) => {
            likeBtns().forEach((b) => (b.disabled = disabled));
            dislikeBtns().forEach((b) => (b.disabled = disabled));
        };

        reactionRoot.addEventListener('click', async (e) => {
            const btn = e.target?.closest?.('button[data-chapter-reaction-btn][data-chapter-react]');
            if (!btn || !reactionRoot.contains(btn)) return;
            e.preventDefault();

            if (!isLoggedIn) {
                window.location.href =
                    `${loginUrl}?redirect=${encodeURIComponent(window.location.href)}`;
                return;
            }

            const reactionRaw = btn.getAttribute('data-chapter-react');
            const reaction = reactionRaw === 'like' ? 'like' : 'dislike';

            setButtonsDisabled(true);
            try {
                const res = await fetch(reactionReactUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ reaction }),
                });

                if (res.status === 401) {
                    window.location.href =
                        `${loginUrl}?redirect=${encodeURIComponent(window.location.href)}`;
                    return;
                }
                if (!res.ok) return;
                const data = await res.json();
                applyState(data);
                if (data && data.dn_bonus && data.dn_bonus.awarded && data.dn_bonus.message) {
                    showDamNgocToast(data.dn_bonus.message);
                }
            } finally {
                setButtonsDisabled(false);
            }
        });

        loadStatus();
    })();

    // Dialog báo lỗi chương + gửi API
    (function () {
        const btn = document.querySelector('[data-chapter-report-open]');
        const root = document.querySelector('[data-chapter-report-root]');
        if (!btn || !root) return;

        const overlay = root.querySelector('[data-chapter-report-overlay]');
        const dialog = root.querySelector('[data-chapter-report-dialog]');
        const closeBtn = root.querySelector('[data-chapter-report-close]');
        const form = root.querySelector('[data-chapter-report-form]');
        const ta = root.querySelector('[data-chapter-report-text]');
        const submitBtn = root.querySelector('[data-chapter-report-submit]');

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const reactionRoot = document.querySelector('[data-chapter-reaction-root]');
        const loginUrl = reactionRoot?.getAttribute('data-chapter-login-url') || '';
        const isLoggedIn = reactionRoot?.getAttribute('data-chapter-is-logged-in') === '1';
        const reportUrl = reactionRoot?.getAttribute('data-chapter-report-url') || '';

        if (!reportUrl || !loginUrl) {
            return;
        }

        const openModal = () => {
            root.classList.remove('hidden');
            root.setAttribute('aria-hidden', 'false');
            if (overlay) overlay.setAttribute('data-state', 'open');
            if (dialog) dialog.setAttribute('data-state', 'open');
            if (ta) ta.value = '';
            if (submitBtn) submitBtn.disabled = true;
        };

        const closeModal = () => {
            root.classList.add('hidden');
            root.setAttribute('aria-hidden', 'true');
            if (overlay) overlay.setAttribute('data-state', 'closed');
            if (dialog) dialog.setAttribute('data-state', 'closed');
        };

        const updateSubmitState = () => {
            if (!ta || !submitBtn) return;
            const body = String(ta.value || '').trim();
            submitBtn.disabled = !(body.length >= 5 && body.length <= 2000);
        };

        btn.addEventListener(
            'click',
            (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                if (!isLoggedIn) {
                    window.location.href =
                        `${loginUrl}?redirect=${encodeURIComponent(window.location.href)}`;
                    return;
                }
                openModal();
            },
            true
        );

        overlay?.addEventListener('click', closeModal);
        closeBtn?.addEventListener('click', closeModal);

        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            if (root.classList.contains('hidden')) return;
            closeModal();
        });

        ta?.addEventListener('input', updateSubmitState);

        form?.addEventListener('submit', async (e) => {
            e.preventDefault();
            updateSubmitState();
            if (submitBtn?.disabled) return;

            const body = String(ta?.value || '').trim();
            submitBtn.disabled = true;
            try {
                const res = await fetch(reportUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ content: body }),
                });

                if (res.status === 401) {
                    window.location.href =
                        `${loginUrl}?redirect=${encodeURIComponent(window.location.href)}`;
                    return;
                }

                if (!res.ok) {
                    let msg = 'Gửi báo cáo thất bại.';
                    try {
                        const err = await res.json();
                        if (err.message) msg = err.message;
                        else if (err.errors?.content?.[0]) msg = err.errors.content[0];
                    } catch (_) {}
                    FuiToast.error(msg);
                    return;
                }

                FuiToast.success('Báo cáo đã được gửi thành công.');

                closeModal();
            } finally {
                submitBtn.disabled = false;
            }
        });
    })();

    // Thanh header + thanh điều khiển dưới cùng: ẩn khi cuộn xuống, hiện khi cuộn lên + nút cuộn lên đầu
    (function () {
        const header = document.querySelector('[data-site-header]');
        const bottomBar = document.querySelector('[data-chapter-bottom-bar]');
        const scrollTopBtn = document.querySelector('[data-chapter-scroll-top]');

        if (!header && !bottomBar && !scrollTopBtn) {
            return;
        }

        let lastY = window.scrollY || window.pageYOffset || 0;
        let ticking = false;

        const handleScroll = () => {
            const currentY = window.scrollY || window.pageYOffset || 0;
            const delta = currentY - lastY;
            lastY = currentY;

            const goingDown = delta > 4;
            const goingUp = delta < -4;

            if (goingDown && currentY > 60) {
                header?.classList.add('-translate-y-full');
                header?.classList.remove('translate-y-0');
                bottomBar?.classList.add('translate-y-full');
            } else if (goingUp) {
                header?.classList.remove('-translate-y-full');
                header?.classList.add('translate-y-0');
                bottomBar?.classList.remove('translate-y-full');
            }

            ticking = false;
        };

        window.addEventListener(
            'scroll',
            () => {
                if (!ticking) {
                    window.requestAnimationFrame(handleScroll);
                    ticking = true;
                }
            },
            { passive: true }
        );

        scrollTopBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth',
            });
        });
    })();

    // Dropdown chọn chapter ở thanh điều khiển dưới cùng
    (function () {
        const root = document.querySelector('[data-chapter-dropdown-root]');
        if (!root) return;

        const trigger = root.querySelector('[data-chapter-dropdown-trigger]');
        const portalWrapper = root.querySelector('[data-chapter-dropdown-portal]');
        const listbox = portalWrapper?.querySelector('[data-chapter-dropdown-list]');

        if (!trigger || !portalWrapper || !listbox) return;

        let isOpen = false;
        let restoreParent = null;
        let restoreNextSibling = null;

        const portalToBody = () => {
            if (portalWrapper.parentNode === document.body) return;
            restoreParent = portalWrapper.parentNode;
            restoreNextSibling = portalWrapper.nextSibling;
            document.body.appendChild(portalWrapper);
        };

        const restorePortal = () => {
            if (!restoreParent || portalWrapper.parentNode !== document.body) return;
            if (restoreNextSibling && restoreNextSibling.parentNode === restoreParent) {
                restoreParent.insertBefore(portalWrapper, restoreNextSibling);
            } else {
                restoreParent.appendChild(portalWrapper);
            }
            restoreParent = null;
            restoreNextSibling = null;
        };

        const close = () => {
            if (!isOpen) return;
            isOpen = false;
            portalWrapper.classList.add('hidden');
            trigger.setAttribute('aria-expanded', 'false');
            restorePortal();
        };

        const open = () => {
            if (isOpen) return;
            isOpen = true;

            const rect = trigger.getBoundingClientRect();
            const viewportWidth = window.innerWidth || document.documentElement.clientWidth || 0;
            const viewportHeight = window.innerHeight || document.documentElement.clientHeight || 0;
            const gap = 8;

            // Rộng tối thiểu 220, tối đa 360, ưu tiên bằng với trigger.
            const desiredWidth = rect.width || 280;
            const width = Math.max(220, Math.min(360, desiredWidth));

            // Nếu đủ chỗ bên phải thì canh trái theo trigger, nếu không thì canh phải.
            let left = rect.left;
            if (left + width > viewportWidth - 8) {
                left = Math.max(8, viewportWidth - width - 8);
            } else {
                left = Math.max(8, left);
            }

            portalToBody();
            portalWrapper.classList.remove('hidden');
            listbox.style.width = `${width}px`;
            listbox.style.left = `${left}px`;

            const spaceAbove = rect.top - gap;
            const spaceBelow = viewportHeight - rect.bottom - gap;
            const openUp = spaceAbove > spaceBelow;

            if (openUp) {
                listbox.style.top = `${Math.max(gap, rect.top - gap)}px`;
                listbox.style.transform = 'translateY(-100%)';
                listbox.style.maxHeight = `${Math.max(140, Math.min(320, spaceAbove - gap))}px`;
            } else {
                listbox.style.top = `${rect.bottom + gap}px`;
                listbox.style.transform = 'none';
                listbox.style.maxHeight = `${Math.max(140, Math.min(320, spaceBelow - gap))}px`;
            }

            trigger.setAttribute('aria-expanded', 'true');
        };

        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            if (isOpen) {
                close();
            } else {
                open();
            }
        });

        listbox.addEventListener('click', (e) => {
            const btn = e.target?.closest?.('[data-chapter-dropdown-option]');
            if (!btn) return;
            const url = btn.getAttribute('data-chapter-target-url');
            if (url) {
                window.location.href = url;
            }
        });

        document.addEventListener('click', (e) => {
            if (!isOpen) return;
            if (root.contains(e.target) || portalWrapper.contains(e.target)) return;
            close();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                close();
            }
        });
    })();

    // Ghi nhận lượt đọc: chỉ POST sau khi có tương tác (cuộn / ảnh vào viewport) + đủ thời gian tối thiểu (server cũng kiểm tra).
    (function () {
        const readerRoot = document.querySelector('[data-chapter-reader]');
        if (!readerRoot) return;

        const url = readerRoot.getAttribute('data-chapter-view-url') || '';
        const token = (readerRoot.getAttribute('data-chapter-view-token') || '').trim();
        // Token stateless (Crypt) — độ dài không cố định, không dùng size:64.
        if (!url || !token) return;

        let engaged = false;
        let minDelayDone = false;
        let submitted = false;

        const submit = () => {
            if (submitted) return;
            submitted = true;

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const headers = {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            };

            fetch(url, {
                method: 'POST',
                headers,
                credentials: 'same-origin',
                body: JSON.stringify({ token }),
            })
                .then((r) => r.json().catch(() => ({})))
                .then((data) => {
                    if (data && data.dn_toast_message) {
                        showDamNgocToast(data.dn_toast_message);
                    }
                })
                .catch(() => {});
        };

        const trySubmit = () => {
            if (!engaged || !minDelayDone || submitted) return;
            submit();
        };

        const markEngaged = () => {
            if (engaged) return;
            engaged = true;
            trySubmit();
        };

        const imagesHost = readerRoot.querySelector('[data-reader-images]');
        if (imagesHost && typeof IntersectionObserver !== 'undefined') {
            const io = new IntersectionObserver(
                (entries) => {
                    for (let i = 0; i < entries.length; i += 1) {
                        const e = entries[i];
                        if (e.isIntersecting && e.intersectionRatio >= 0.12) {
                            markEngaged();
                            io.disconnect();
                            break;
                        }
                    }
                },
                { threshold: [0.12, 0.25] }
            );
            imagesHost.querySelectorAll('img').forEach((img) => io.observe(img));
        }

        let maxScroll = 0;
        const onScroll = () => {
            maxScroll = Math.max(maxScroll, window.scrollY || window.pageYOffset || 0);
            if (maxScroll > 100) {
                markEngaged();
            }
        };
        window.addEventListener('scroll', onScroll, { passive: true });

        window.setTimeout(() => {
            minDelayDone = true;
            if (!engaged) {
                engaged = true;
            }
            trySubmit();
        }, 30000);
    })();

    // Đã đăng nhập: đồng bộ trang đang đọc lên server mỗi 20s (không phụ thuộc ghi view).
    (function () {
        const readerRoot = document.querySelector('[data-chapter-reader]');
        if (!readerRoot) return;

        const historyUrl = (readerRoot.getAttribute('data-chapter-reading-history-url') || '').trim();
        if (!historyUrl) return;

        const HISTORY_SYNC_MS = 20000;

        const postCurrentPage = () => {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const total = parseInt(readerRoot.getAttribute('data-reader-total-pages'), 10) || 0;
            let page = parseInt(readerRoot.getAttribute('data-reader-current-page'), 10) || 1;
            if (total > 0) {
                page = Math.min(Math.max(1, page), total);
            } else {
                page = Math.max(1, page);
            }

            fetch(historyUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ last_read_page: page }),
            }).catch(() => {});
        };

        const intervalId = window.setInterval(postCurrentPage, HISTORY_SYNC_MS);

        const stop = () => {
            window.clearInterval(intervalId);
        };
        window.addEventListener('pagehide', stop, { once: true });
        window.addEventListener('beforeunload', stop, { once: true });
    })();
