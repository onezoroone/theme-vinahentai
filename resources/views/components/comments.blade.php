<div class="mt-8 flex flex-col items-start justify-start gap-6 self-stretch pb-24 sm:pb-0" data-comments
    data-manga-id="{{ $manga->id }}"
    @if (isset($chapter) && $chapter)
        data-chapter-id="{{ $chapter->id }}"
    @endif>
    <div class="border-bd-default flex items-center justify-between self-stretch border-b pb-3">
        <div class="flex items-center justify-start gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-message-square h-6 w-6 text-lav-500" aria-hidden="true">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <div class="text-txt-primary font-sans text-xl leading-7 font-semibold uppercase">bình luận</div>
        </div>
        <div class="text-txt-secondary text-sm" data-comments-total></div>
    </div>

    <div class="flex flex-col items-center justify-center gap-10 self-stretch">
        @if (Auth::check())
            <form class="w-full" data-comment-form>
                <div
                    class="bg-bgc-layer2 border-bd-default flex flex-col items-start justify-start gap-3 self-stretch overflow-hidden rounded-xl border-b p-3">
                    <textarea placeholder="Mời đồng dâm vào chém gió về truyện..."
                        class="text-txt-primary placeholder:text-txt-secondary min-h-[60px] w-full resize-none bg-transparent font-sans text-base leading-snug font-medium outline-none"
                        maxlength="1000" data-comment-text></textarea>
                    <div class="-mt-1 hidden w-full flex-wrap gap-2 empty:hidden" data-comment-gif-preview></div>
                    <div class="flex w-full items-center justify-between">
                        <div class="text-txt-secondary font-sans text-xs" data-comment-count>0/1000 ký tự</div>
                        <div class="flex items-center gap-1">
                            <button type="button" data-comment-gif-open
                                class="text-txt-secondary hover:text-txt-primary transition-colors px-1 py-1 rounded-md border border-transparent hover:border-bd-default flex items-center gap-[1px]"
                                title="Chèn GIF">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-image h-5 w-5"
                                    aria-hidden="true">
                                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2">
                                    </rect>
                                    <circle cx="9" cy="9" r="2"></circle>
                                    <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
                                </svg>
                                <span class="text-xs leading-none font-medium">gif-meme</span>
                            </button>
                            <button type="submit" disabled=""
                                class="bg-btn-primary text-txt-primary hover:bg-btn-primary/80 flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                                data-comment-submit>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send h-4 w-4"
                                    aria-hidden="true">
                                    <path
                                        d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z">
                                    </path>
                                    <path d="m21.854 2.147-10.94 10.939"></path>
                                </svg>
                                Gửi
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @else
            <div
                class="bg-bgc-layer2 border-bd-default flex h-28 flex-col items-center justify-center gap-1.5 self-stretch overflow-hidden rounded-xl border-b p-3">
                <div class="text-txt-secondary font-sans text-sm leading-tight font-medium">Vui lòng đăng nhập để bình
                    luận</div>
            </div>
        @endif

        <div class="flex w-full flex-col items-start justify-start gap-6 self-stretch">
            <div class="w-full" data-comments-items>
                <div class="p-3 text-txt-secondary text-sm">Đang tải bình luận...</div>
            </div>

            <div class="flex w-full justify-center" data-comments-pager-host>
                @include('theme-vinahentai::components.pagination', [
                    'current' => 1,
                    'last' => 1,
                    'commentsMode' => true,
                ])
            </div>
        </div>
    </div>

    {{-- Hộp thoại báo cáo bình luận (mở bằng JS) --}}
    <div class="hidden" data-comment-report-root aria-hidden="true">
        <div data-comment-report-overlay data-state="closed"
            class="data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 fixed inset-0 z-[200] bg-black/50">
        </div>
        <div role="dialog" aria-modal="true" aria-labelledby="comment-report-title"
            aria-describedby="comment-report-desc" data-comment-report-dialog data-state="closed" tabindex="-1"
            class="data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[state=closed]:slide-out-to-left-1/2 data-[state=closed]:slide-out-to-top-[48%] data-[state=open]:slide-in-from-left-1/2 data-[state=open]:slide-in-from-top-[48%] fixed top-[50%] left-[50%] z-[201] translate-x-[-50%] translate-y-[-50%]">
            <form
                class="bg-bgc-layer1 border-bd-default flex max-h-[90vh] w-[320px] flex-col gap-6 overflow-hidden rounded-2xl border p-4 sm:w-[400px] sm:gap-10 sm:p-6 md:w-[500px]"
                data-comment-report-form>
                <div class="flex flex-col gap-4 sm:gap-6">
                    <div class="flex flex-col gap-2 sm:gap-3">
                        <h2 id="comment-report-title"
                            class="text-txt-primary text-center font-sans text-xl leading-loose font-semibold sm:text-2xl">
                            Nội dung báo cáo</h2>
                        <p id="comment-report-desc" class="sr-only">Mô tả lý do báo cáo bình luận vi phạm.</p>
                        <div class="flex h-32 flex-col gap-1.5 sm:h-44">
                            <label for="comment-report-textarea"
                                class="text-txt-primary font-sans text-sm leading-normal font-semibold sm:text-base">Nội
                                dung</label>
                            <div class="flex flex-1 flex-col">
                                <textarea id="comment-report-textarea" name="content" maxlength="2000" placeholder="Nhập nội dung báo cáo tại đây..."
                                    data-comment-report-text
                                    class="bg-bgc-layer2 border-bd-default text-txt-primary placeholder:text-txt-secondary focus:border-lav-500 focus:ring-lav-500 flex-1 resize-none rounded-xl border px-3 py-2.5 font-sans text-sm leading-normal font-medium transition-colors outline-none focus:ring-1 sm:text-base"
                                    rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <button type="button" data-comment-report-close
                        class="border-lav-500 hover:bg-lav-500/10 flex flex-1 items-center justify-center gap-2.5 rounded-xl border px-4 py-3 shadow-[0px_4px_8.9px_0px_rgba(146,53,190,0.25)] transition-colors">
                        <span
                            class="text-txt-focus text-center font-sans text-sm leading-tight font-semibold">Đóng</span>
                    </button>
                    <button type="submit" disabled data-comment-report-submit
                        class="flex flex-1 items-center justify-center gap-2.5 rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] px-4 py-3 shadow-[0px_4px_8.9px_0px_rgba(196,69,255,0.25)] transition-all hover:from-[#E1A3FF] hover:to-[#DC85FF] disabled:cursor-not-allowed disabled:opacity-50">
                        <span class="text-center font-sans text-sm leading-tight font-semibold text-black">Gửi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Dialog chọn GIF meme (manifest từ /api/gif-meme/manifest) --}}
    <div class="hidden" data-gif-meme-root aria-hidden="true">
        <div data-gif-meme-overlay data-state="closed"
            class="data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 fixed inset-0 z-[190] bg-black/50">
        </div>
        <div role="dialog" aria-modal="true" aria-labelledby="gif-meme-dialog-title" data-gif-meme-dialog
            data-state="closed" tabindex="-1"
            class="data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[state=closed]:slide-out-to-left-1/2 data-[state=closed]:slide-out-to-top-[48%] data-[state=open]:slide-in-from-left-1/2 data-[state=open]:slide-in-from-top-[48%] fixed top-[50%] left-[50%] z-[191] translate-x-[-50%] translate-y-[-50%]">
            <div
                class="bg-bgc-layer1 border-bd-default flex max-h-[90vh] w-[320px] flex-col gap-4 overflow-hidden rounded-2xl border p-4 sm:w-[520px] sm:gap-4 sm:p-5 md:w-[720px]">
                <div class="flex items-center justify-between">
                    <h2 id="gif-meme-dialog-title" class="text-txt-primary font-sans text-lg font-semibold sm:text-xl">Chọn GIF meme</h2>
                    <button type="button" data-gif-meme-close
                        class="text-txt-secondary hover:text-txt-primary rounded-md px-2 py-1">Đóng</button>
                </div>
                <div data-gif-meme-categories
                    class="flex gap-2 overflow-x-auto rounded-lg border border-bd-default p-2"></div>
                <div class="min-h-[180px] flex-1 overflow-auto rounded-lg border border-bd-default p-2">
                    <div data-gif-meme-grid class="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function() {
            const root = document.querySelector('[data-comments]');
            if (!root) return;

            const mangaId = Number(root.getAttribute('data-manga-id') || 0);
            if (!mangaId) return;

            /** FuiToast: thưởng Dâm Ngọc khi bình luận đầu ngày (layout có #fui-toast + fuiToast.min.js). */
            const showDamNgocToast = (message) => {
                if (!message) return;
                if (typeof FuiToast !== 'undefined' && typeof FuiToast.success === 'function') {
                    FuiToast.success(message);
                }
            };

            /** Chương hiện tại (trang đọc) — gửi kèm POST để badge hiển thị đúng */
            const chapterIdAttr = root.getAttribute('data-chapter-id');
            const chapterIdForApi =
                chapterIdAttr && String(chapterIdAttr).trim() !== '' && Number.isFinite(Number(chapterIdAttr))
                    ? Number(chapterIdAttr)
                    : null;

            const apiBase = @json(url('/api/manga'));
            const itemsEl = root.querySelector('[data-comments-items]');
            const pagerHost = root.querySelector('[data-comments-pager-host]');
            const totalEl = root.querySelector('[data-comments-total]');
            const formEl = root.querySelector('[data-comment-form]');
            const textareaEl = root.querySelector('[data-comment-text]');
            const countEl = root.querySelector('[data-comment-count]');
            const submitEl = root.querySelector('[data-comment-submit]');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const loginUrl = @json(route('login'));
            const isLoggedIn = @json(Auth::check());
            /** User đăng nhập — dùng để ẩn menu báo cáo trên comment của chính mình */
            const authUserId = @json(Auth::id());
            const commentsStoreUrl = `${apiBase}/${mangaId}/comments`;
            const commentReportUrl = (commentId) => `{{ url('/api/comments') }}/${commentId}/report`;
            const commentRepliesUrl = (commentId) => `{{ url('/api/comments') }}/${commentId}/replies`;
            const gifMemeManifestUrl = @json(url('/api/gif-meme/manifest'));
            const gifPreviewEl = root.querySelector('[data-comment-gif-preview]');

            let currentPage = 1;
            let lastPage = 1;
            /** URL ảnh GIF chèn kèm bình luận (asset vendor/theme-vinahentai/...) */
            let commentGifUrl = null;
            /** GIF đang chọn cho từng form trả lời (parent comment id → url) */
            const replyGifUrlByParentId = new Map();
            /** Modal GIF: chèn vào form gốc hay form trả lời nào */
            let gifMemeModalTarget = {
                kind: 'main'
            };
            let gifMemeCategories = [];
            let gifMemeSelectedCategoryId = null;

            const escapeHtml = (str) => {
                return String(str || '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            };

            /** Escape cho giá trị thuộc tính HTML (src, ...) */
            const escapeAttr = (str) => {
                return String(str || '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;');
            };

            /** Thứ tự hiển thị cảm xúc (khớp menu chọn) */
            const REACTION_ORDER = ['like', 'love', 'care', 'haha', 'wow', 'sad', 'angry'];
            /** Emoji + nhãn tooltip cho từng loại */
            const REACTION_META = {
                like: {
                    emoji: '👍',
                    label: 'Like'
                },
                love: {
                    emoji: '❤️',
                    label: 'Yêu thích'
                },
                care: {
                    emoji: '🥰',
                    label: 'Thương thương'
                },
                haha: {
                    emoji: '😂',
                    label: 'Haha'
                },
                wow: {
                    emoji: '😮',
                    label: 'Wow'
                },
                sad: {
                    emoji: '😢',
                    label: 'Buồn'
                },
                angry: {
                    emoji: '😡',
                    label: 'Phẫn nộ'
                },
            };

            /**
             * Hiển thị từng loại cảm xúc có ít nhất 1 lượt cùng số đếm.
             * Luôn có `data-comment-reaction-summary` để cập nhật DOM sau POST reaction (không cần load lại cả trang comment).
             * @param {Record<string, number>|undefined} counts
             * @param {string|number} commentId
             */
            const renderReactionSummary = (counts, commentId) => {
                const c = counts || {};
                const parts = [];
                for (const key of REACTION_ORDER) {
                    const n = Number(c[key] || 0);
                    if (n <= 0) continue;
                    const meta = REACTION_META[key] || {
                        emoji: '',
                        label: key
                    };
                    parts.push(
                        `<span class="inline-flex items-center gap-0.5 rounded-full bg-bgc-layer2 px-1.5 py-0.5" title="${escapeHtml(meta.label)}">` +
                        `<span class="text-[0.75rem] leading-none" aria-hidden="true">${meta.emoji}</span>` +
                        `<span class="font-sans text-[0.65rem] leading-[0.95rem] font-medium">${n}</span>` +
                        `</span>`
                    );
                }
                const hiddenClass = parts.length ? '' : ' hidden';
                return `<div class="flex flex-wrap items-center gap-1 text-txt-secondary mt-2${hiddenClass}" data-comment-reaction-summary="${commentId}">${parts.join('')}</div>`;
            };

            /** Máy có hover chuột (desktop): mở menu cảm xúc khi hover; mobile dùng chạm. */
            const canHover = window.matchMedia('(hover: hover)').matches;

            const formatRepliesLabel = (n) =>
                `Xem ${new Intl.NumberFormat('vi-VN').format(Number(n || 0))} phản hồi`;

            const optionsMenuHtml = (commentId) => {
                return `
                        <div class="absolute right-0 top-5 z-50 min-w-[130px] rounded-lg border border-bd-default bg-bgc-layer2 shadow-lg py-1 text-sm hidden"
                             role="menu"
                             aria-label="Tùy chọn"
                             data-comment-menu="${commentId}">
                            <button type="button"
                                data-comment-report-open="${commentId}"
                                class="flex w-full items-center gap-2 px-3 py-1.5 text-txt-primary hover:bg-bgc-layer1 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-flag h-3.5 w-3.5 text-txt-secondary" aria-hidden="true">
                                    <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                                    <line x1="4" x2="4" y1="22" y2="15"></line>
                                </svg>
                                <span>Báo cáo</span>
                            </button>
                        </div>
                    `;
            };

            /**
             * Một dòng bình luận (gốc hoặc phản hồi lồng).
             * @param {object} item
             * @param {boolean} compact — avatar nhỏ hơn cho danh sách phản hồi
             */
            /** Hai waifu độ hiếm cao nhất (API user.top_waifus) — bố cục giống Radix mẫu. */
            const renderWaifuInline = (user, profileUrl, compact) => {
                const list = Array.isArray(user.top_waifus) ? user.top_waifus : [];
                const withImg = list
                    .filter((w) => w && String(w.image_url || '').trim() !== '')
                    .slice(0, 2);
                if (withImg.length === 0) {
                    return '';
                }
                const h = compact ? 32 : 40;
                const imgs = withImg
                    .map(
                        (w) =>
                            `<img alt="Waifu" class="w-auto rounded-[2px]" loading="lazy" decoding="async" src="${escapeHtml(String(w.image_url))}" style="height: ${h}px;">`,
                    )
                    .join('');
                return `<a href="${profileUrl}#waifu" class="relative cursor-pointer select-none no-underline" aria-label="Xem waifu"><span class="text-txt-secondary inline-flex items-center gap-1 align-middle text-[12px] leading-none" style="height: ${h}px;"><span class="text-txt-secondary/70">|</span><span>Waifu:</span><span class="inline-flex items-center gap-1" style="height: ${h}px;">${imgs}</span></span></a>`;
            };

            const renderCommentRow = (item, compact) => {
                const user = item.user || {};
                const name = escapeHtml(user.name || 'Người dùng');
                const badgeSrc = user.badge_src || '';
                const profileUrl = `/profile/${user.id || ''}`;
                const time = escapeHtml(item.created_at_human || '');
                const isOwnComment = Boolean(authUserId && Number(item.user?.id) === Number(authUserId));
                const contentHtml = String(item.content || '');
                const avatarCls = compact ? 'mt-1.5 h-8 w-8 flex-shrink-0' : 'mt-1.5 h-10 w-10 flex-shrink-0';
                const badgeCls = compact ? 'h-8' : 'h-10';
                const nameCls = compact ?
                    'text-txt-primary font-sans text-[0.95rem] leading-tight font-medium hover:underline focus:underline flex items-center touch-manipulation [touch-action:manipulation]' :
                    'text-txt-primary font-sans text-[1.0rem] leading-tight font-medium hover:underline focus:underline flex items-center touch-manipulation [touch-action:manipulation]';

                const rc = Number(item.replies_count || 0);
                const viewRepliesBtn =
                    rc > 0 ?
                    `<button type="button"
                            class="text-purple-400 hover:text-purple-300 flex cursor-pointer items-center justify-start gap-1 transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                            title="Xem phản hồi"
                            data-comment-replies-toggle="${item.id}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-messages-square h-3 w-3" aria-hidden="true">
                                <path d="M14 9a2 2 0 0 1-2 2H6l-4 4V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2z"></path>
                                <path d="M18 9h2a2 2 0 0 1 2 2v11l-4-4h-6a2 2 0 0 1-2-2v-1"></path>
                            </svg>
                            <div class="font-sans text-[0.65rem] leading-[0.95rem] font-medium">${formatRepliesLabel(rc)}</div>
                        </button>` :
                    '';

                const chapterLabelRaw = item.chapter_label != null && String(item.chapter_label).trim() !== '' ?
                    String(item.chapter_label).trim() :
                    '';
                const chapterBadgeHtml = chapterLabelRaw ?
                    `<span class="ml-1 inline-flex items-center rounded-full bg-lav-500/15 px-2 py-0.5 text-[0.6rem] font-semibold text-lav-400 leading-none">${escapeHtml(chapterLabelRaw)}</span>` :
                    '';

                return `
                    <div class="flex items-start justify-start gap-4 self-stretch" data-comment-row="${item.id}">
                        <a href="${profileUrl}" aria-label="Xem trang của ${name}" class="touch-manipulation [touch-action:manipulation]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user ${avatarCls}" aria-hidden="true">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </a>

                        <div class="flex min-w-0 flex-1 flex-col items-start justify-center gap-2">
                            <div class="bg-bgc-layer2 border-bd-default flex flex-col items-start justify-start self-stretch rounded-lg border overflow-hidden">
                                <div class="flex flex-col items-start justify-start gap-1.5 self-stretch px-3 py-1.5">
                                    <div class="flex items-center justify-between self-stretch">
                                        <div class="flex items-center justify-start" style="gap: 1px;">
                                            <a href="${profileUrl}" class="${nameCls}" title="Xem trang của ${name}">${name}</a>
                                            ${
                                                badgeSrc
                                                    ? `<img class="${badgeCls} transition-transform duration-200 will-change-transform md:hover:scale-150 md:hover:z-10 relative" alt="User badge" src="${badgeSrc}" style="top: -2px;">`
                                                    : ''
                                            }
                                            ${renderWaifuInline(user, profileUrl, compact)}
                                        </div>

                                        ${
                                            !isOwnComment
                                                ? `<div class="flex items-center gap-2">
                                                <div class="relative">
                                                    <button type="button"
                                                            class="relative h-4 flex-shrink-0 cursor-pointer overflow-hidden transition-colors text-txt-secondary hover:text-txt-primary"
                                                            title="Tùy chọn"
                                                            data-comment-options="${item.id}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ellipsis-vertical h-full" aria-hidden="true">
                                                            <circle cx="12" cy="12" r="1"></circle>
                                                            <circle cx="12" cy="5" r="1"></circle>
                                                            <circle cx="12" cy="19" r="1"></circle>
                                                        </svg>
                                                    </button>
                                                    ${optionsMenuHtml(item.id)}
                                                </div>
                                            </div>`
                                                : ''
                                        }
                                    </div>
                                </div>

                                <div class="flex items-center justify-center gap-2.5 self-stretch bg-bgc-layer2 px-3 pb-3 pt-2">
                                    <div class="text-txt-primary flex-1 font-sans text-sm leading-tight font-medium">
                                        ${contentHtml}
                                        ${renderReactionSummary(item.reaction_counts, item.id)}
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-start gap-4" data-comment-actions="${item.id}">
                                <div class="relative inline-flex items-center gap-2">
                                    <div class="relative" data-reaction-wrapper="${item.id}">
                                        <div class="absolute bottom-full left-0 z-20 mb-1 origin-bottom-left rounded-full border border-bd-default bg-bgc-layer2 px-2 py-1 shadow-lg backdrop-blur transition-all duration-150 hidden" role="menu" aria-label="Chọn cảm xúc" data-reaction-menu="${item.id}">
                                            <div class="flex items-center gap-1">
                                                <button type="button" data-comment-reaction="${item.id}" data-reaction="like" class="flex h-8 w-8 items-center justify-center rounded-full text-lg transition-transform duration-150 hover:scale-125 focus:scale-125 focus:outline-none " title="Like"><span aria-hidden="true">👍</span></button>
                                                <button type="button" data-comment-reaction="${item.id}" data-reaction="love" class="flex h-8 w-8 items-center justify-center rounded-full text-lg transition-transform duration-150 hover:scale-125 focus:scale-125 focus:outline-none " title="Yêu thích"><span aria-hidden="true">❤️</span></button>
                                                <button type="button" data-comment-reaction="${item.id}" data-reaction="care" class="flex h-8 w-8 items-center justify-center rounded-full text-lg transition-transform duration-150 hover:scale-125 focus:scale-125 focus:outline-none " title="Thương thương"><span aria-hidden="true">🥰</span></button>
                                                <button type="button" data-comment-reaction="${item.id}" data-reaction="haha" class="flex h-8 w-8 items-center justify-center rounded-full text-lg transition-transform duration-150 hover:scale-125 focus:scale-125 focus:outline-none " title="Haha"><span aria-hidden="true">😂</span></button>
                                                <button type="button" data-comment-reaction="${item.id}" data-reaction="wow" class="flex h-8 w-8 items-center justify-center rounded-full text-lg transition-transform duration-150 hover:scale-125 focus:scale-125 focus:outline-none " title="Wow"><span aria-hidden="true">😮</span></button>
                                                <button type="button" data-comment-reaction="${item.id}" data-reaction="sad" class="flex h-8 w-8 items-center justify-center rounded-full text-lg transition-transform duration-150 hover:scale-125 focus:scale-125 focus:outline-none " title="Buồn"><span aria-hidden="true">😢</span></button>
                                                <button type="button" data-comment-reaction="${item.id}" data-reaction="angry" class="flex h-8 w-8 items-center justify-center rounded-full text-lg transition-transform duration-150 hover:scale-125 focus:scale-125 focus:outline-none " title="Phẫn nộ"><span aria-hidden="true">😡</span></button>
                                            </div>
                                        </div>
                                        <button type="button" class="flex items-center gap-1 text-txt-secondary transition-colors hover:text-txt-primary disabled:cursor-not-allowed disabled:opacity-50 text-[0.65rem]" title="Thả cảm xúc" data-reaction-toggle="${item.id}"><span class="leading-[0.95rem]">Cảm xúc</span></button>
                                    </div>
                                </div>
                                <button type="button" class="flex cursor-pointer items-center justify-start gap-1 text-txt-secondary transition-colors hover:text-txt-primary disabled:cursor-not-allowed disabled:opacity-50" data-comment-reply-open="${item.id}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-circle h-3 w-3" aria-hidden="true">
                                        <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"></path>
                                    </svg>
                                    <div class="font-sans text-[0.65rem] leading-[0.95rem] font-medium">Trả lời</div>
                                </button>
                                ${viewRepliesBtn}
                                <div class="text-txt-secondary font-sans text-[0.65rem] leading-[0.95rem] font-medium" data-comment-time="${item.id}">${time}</div>
                                ${chapterBadgeHtml}
                            </div>

                            <div class="mt-2 hidden w-full" data-reply-slot="${item.id}"></div>
                            <div class="mt-4 hidden flex flex-col gap-4 self-stretch" data-comment-replies-container="${item.id}"></div>
                        </div>
                    </div>
                `;
            };

            const renderItem = (item) => renderCommentRow(item, false);

            /**
             * HTML phân trang (Đầu / ±2 trang / ... / Cuối + form Đi) — giữ khớp components/pagination.blade.php.
             */
            const buildCommentsPaginationHtml = (cp, lp) => {
                let c = Number(cp) || 1;
                let l = Math.max(1, Number(lp) || 1);
                if (c > l) c = l;
                const windowStart = Math.max(1, c - 2);
                const windowEnd = Math.min(l, c + 2);
                const showLeftEllipsis = windowStart > 2;
                const showRightEllipsis = windowEnd < l - 1;

                let innerPages = '';
                if (showLeftEllipsis) {
                    innerPages +=
                        '<div><div class="inline-flex h-10 w-9 flex-col items-center justify-center rounded-lg p-2">' +
                        '<div class="text-txt-primary text-center font-sans text-sm font-semibold leading-tight">...</div>' +
                        '</div></div>';
                }
                for (let n = windowStart; n <= windowEnd; n++) {
                    if (n === c) {
                        innerPages +=
                            `<div><button type="button" class="inline-flex h-10 w-9 cursor-default flex-col items-center justify-center rounded-lg bg-btn-primary p-2" aria-current="page" title="Trang ${n}" disabled>` +
                            `<span class="text-center font-sans text-sm font-semibold leading-tight text-bgc-layer1">${n}</span></button></div>`;
                    } else {
                        innerPages +=
                            `<div><button type="button" class="hover:bg-bgc-layer2 inline-flex h-10 w-9 cursor-pointer flex-col items-center justify-center rounded-lg p-2" title="Trang ${n}" data-pagination-page="${n}">` +
                            `<div class="text-center font-sans text-sm font-semibold leading-tight text-txt-primary">${n}</div></button></div>`;
                    }
                }
                if (showRightEllipsis) {
                    innerPages +=
                        '<div><div class="inline-flex h-10 w-9 flex-col items-center justify-center rounded-lg p-2">' +
                        '<div class="text-txt-primary text-center font-sans text-sm font-semibold leading-tight">...</div>' +
                        '</div></div>';
                }

                const firstDis = c <= 1 ? 'disabled' : '';
                const lastDis = c >= l ? 'disabled' : '';

                return (
                    `<div class="flex flex-col items-center gap-2" data-comments-pagination>` +
                    `<div class="bg-bgc-layer1 border-bd-default inline-flex items-center justify-start gap-2 rounded-lg border px-2 py-1">` +
                    `<button type="button" class="hover:bg-bgc-layer2 inline-flex h-10 w-9 cursor-pointer flex-col items-center justify-center rounded-lg p-2 disabled:cursor-not-allowed disabled:opacity-50" aria-label="Về trang đầu" title="Về trang đầu" data-pagination-first ${firstDis}>` +
                    `<div class="text-txt-secondary text-center font-sans text-sm font-semibold leading-tight">Đầu</div></button>` +
                    `<div class="inline-flex items-center gap-1">${innerPages}</div>` +
                    `<button type="button" class="hover:bg-bgc-layer2 inline-flex h-10 w-9 cursor-pointer flex-col items-center justify-center rounded-lg p-2 disabled:cursor-not-allowed disabled:opacity-50" aria-label="Tới trang cuối" title="Tới trang cuối" data-pagination-last ${lastDis}>` +
                    `<div class="text-txt-secondary text-center font-sans text-sm font-semibold leading-tight">Cuối</div></button>` +
                    `</div>` +
                    `<form class="inline-flex items-center gap-2" data-pagination-jump-form data-comments-pagination-jump>` +
                    `<input type="number" inputmode="numeric" min="1" max="${l}" placeholder="Trang" class="h-10 w-20 rounded-lg border border-bd-default bg-bgc-layer1 px-2 text-center font-sans text-sm font-semibold text-txt-primary focus:outline-none focus:ring-2 focus:ring-btn-primary" value="" data-pagination-jump-input aria-label="Số trang" />` +
                    `<button type="submit" disabled class="inline-flex h-10 min-w-12 cursor-pointer items-center justify-center rounded-lg bg-bgc-layer2 px-3 font-sans text-sm font-semibold leading-tight text-txt-secondary opacity-50 disabled:cursor-not-allowed disabled:opacity-50 enabled:cursor-pointer enabled:bg-btn-primary enabled:text-txt-primary enabled:opacity-100" title="Nhập trang hợp lệ" data-pagination-jump-submit>Đi</button>` +
                    `</form></div>`
                );
            };

            const renderCommentsPager = () => {
                if (!pagerHost) return;
                pagerHost.innerHTML = buildCommentsPaginationHtml(currentPage, lastPage);
            };

            const load = async (page) => {
                if (!itemsEl) return;
                itemsEl.innerHTML = `<div class="p-3 text-txt-secondary text-sm">Đang tải bình luận...</div>`;

                const res = await fetch(`${apiBase}/${mangaId}/comments?page=${page}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) {
                    itemsEl.innerHTML =
                        `<div class="p-3 text-txt-secondary text-sm">Không thể tải bình luận.</div>`;
                    renderCommentsPager();
                    return;
                }

                const payload = await res.json();
                const items = payload.data || [];
                const meta = payload.meta || {};

                currentPage = Number(meta.current_page || 1);
                lastPage = Number(meta.last_page || 1);

                if (totalEl) {
                    totalEl.textContent =
                        `${new Intl.NumberFormat('vi-VN').format(Number(meta.total || 0))} bình luận`;
                }

                if (!items.length) {
                    itemsEl.innerHTML = `<div class="text-txt-secondary py-8 text-center font-sans text-sm">Chưa có bình luận nào. Hãy là người đầu tiên bình luận!</div>`;
                    renderCommentsPager();
                    return;
                }

                itemsEl.innerHTML =
                    `<div class="flex flex-col items-start justify-start gap-6 self-stretch">${items.map(renderItem).join('')}</div>`;
                bindReactionHover();
                renderCommentsPager();
            };

            const ensureJquery = async () => {
                if (window.jQuery) {
                    return window.jQuery;
                }

                await new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    script.src = 'https://code.jquery.com/jquery-3.7.1.min.js';
                    script.onload = () => resolve(true);
                    script.onerror = reject;
                    document.head.appendChild(script);
                });

                return window.jQuery;
            };

            const gifMemeRootEl = () => root.querySelector('[data-gif-meme-root]');

            /**
             * Khối GIF trong HTML lưu DB — khớp layout hiển thị comment (chữ trên, GIF trong hàng flex).
             */
            const buildCommentStoredGifHtml = (url) =>
                '<div class="mt-2 flex flex-wrap gap-2"><img alt="GIF" class="max-h-32 rounded-md" loading="lazy" decoding="async" src="' +
                escapeAttr(url) +
                '" /></div>';

            const estimateCommentPayloadLength = () => {
                const plain = textareaEl ? String(textareaEl.value || '') : '';
                if (!commentGifUrl) return plain.length;
                const gifBlock = buildCommentStoredGifHtml(commentGifUrl);
                if (!plain.trim()) return gifBlock.length;
                return plain.length + gifBlock.length;
            };

            /** Độ dài payload phản hồi (text + GIF), khớp khi gửi (xuống dòng → thẻ br) */
            const estimateReplyPayloadLength = (parentId, editor) => {
                const plain = editor ? String(editor.innerText || '').trim() : '';
                const gifUrl = replyGifUrlByParentId.get(parentId);
                if (!gifUrl) return plain.length;
                const gifBlock = buildCommentStoredGifHtml(gifUrl);
                const textPart = plain.replace(/\n/g, '<br>');
                if (!textPart) return gifBlock.length;
                return textPart.length + gifBlock.length;
            };

            const setCommentGif = (url) => {
                commentGifUrl = url && String(url).trim() ? String(url).trim() : null;
                if (!gifPreviewEl) {
                    updateInputState();
                    return;
                }
                if (!commentGifUrl) {
                    gifPreviewEl.innerHTML = '';
                    gifPreviewEl.classList.add('hidden');
                    updateInputState();
                    return;
                }
                gifPreviewEl.classList.remove('hidden');
                gifPreviewEl.innerHTML =
                    '<div class="relative inline-block">' +
                    '<img src="' +
                    escapeAttr(commentGifUrl) +
                    '" alt="GIF" class="h-20 w-auto max-w-[150px] rounded-md border border-bd-default object-cover" loading="lazy" decoding="async" />' +
                    '<button type="button" class="absolute right-1 top-1 rounded-md bg-black/50 px-1.5 py-0.5 text-xs text-white hover:bg-black/70" title="Gỡ GIF" data-comment-gif-remove>Gỡ</button>' +
                    '</div>';
                updateInputState();
            };

            /** Preview + state GIF cho một ô trả lời */
            const setReplyGif = (parentId, url) => {
                const u = url && String(url).trim() ? String(url).trim() : null;
                if (!u) {
                    replyGifUrlByParentId.delete(parentId);
                } else {
                    replyGifUrlByParentId.set(parentId, u);
                }
                const prev = root.querySelector(`[data-reply-gif-preview="${parentId}"]`);
                if (!prev) return;
                if (!u) {
                    prev.innerHTML = '';
                    prev.classList.add('hidden');
                    return;
                }
                prev.classList.remove('hidden');
                prev.innerHTML =
                    '<img src="' +
                    escapeAttr(u) +
                    '" alt="GIF" class="h-16 w-auto max-w-[120px] rounded-md border border-bd-default object-cover" loading="lazy" decoding="async" />' +
                    '<button type="button" class="text-xs text-txt-secondary hover:text-txt-primary" title="Gỡ GIF" data-reply-gif-remove="' +
                    escapeAttr(parentId) +
                    '">Gỡ GIF</button>';
            };

            const closeGifMemeModal = () => {
                const layer = gifMemeRootEl();
                if (!layer) return;
                const ov = layer.querySelector('[data-gif-meme-overlay]');
                const dlg = layer.querySelector('[data-gif-meme-dialog]');
                layer.classList.add('hidden');
                layer.setAttribute('aria-hidden', 'true');
                if (ov) ov.setAttribute('data-state', 'closed');
                if (dlg) dlg.setAttribute('data-state', 'closed');
            };

            const renderGifMemeCategories = () => {
                const catEl = root.querySelector('[data-gif-meme-categories]');
                if (!catEl) return;
                catEl.innerHTML = gifMemeCategories
                    .map((c) => {
                        const active = c.id === gifMemeSelectedCategoryId;
                        const ring = active
                            ? 'border-lav-500 ring-2 ring-lav-500'
                            : 'border-bd-default hover:border-lav-400';
                        return (
                            '<button type="button" data-gif-meme-tab="' +
                            escapeHtml(c.id) +
                            '" class="group relative flex h-14 w-14 flex-shrink-0 items-center justify-center overflow-hidden rounded-md border text-[10px] font-medium transition-colors ' +
                            ring +
                            '" title="' +
                            escapeHtml(c.label) +
                            '"><img alt="" class="h-full w-full object-cover" loading="lazy" decoding="async" src="' +
                            escapeAttr(c.thumb) +
                            '" /></button>'
                        );
                    })
                    .join('');
            };

            const renderGifMemeGrid = (categoryId) => {
                const grid = root.querySelector('[data-gif-meme-grid]');
                if (!grid) return;
                const cat = gifMemeCategories.find((x) => x.id === categoryId);
                if (!cat || !cat.images) {
                    grid.innerHTML = '';
                    return;
                }
                grid.innerHTML = cat.images
                    .map(
                        (img) =>
                            '<button type="button" data-gif-meme-cell data-gif-url="' +
                            escapeAttr(img.url) +
                            '" class="group relative aspect-square overflow-hidden rounded-md border border-bd-default" title="' +
                            escapeHtml(img.path) +
                            '"><img alt="" class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-[1.02]" loading="lazy" decoding="async" src="' +
                            escapeAttr(img.url) +
                            '" /></button>'
                    )
                    .join('');
            };

            const openGifMemeModal = async () => {
                const layer = gifMemeRootEl();
                if (!layer) return;
                try {
                    const res = await fetch(gifMemeManifestUrl, {
                        headers: { Accept: 'application/json' },
                    });
                    if (!res.ok) throw new Error('fetch');
                    const data = await res.json();
                    gifMemeCategories = data.categories || [];
                    if (!gifMemeCategories.length) {
                        window.alert(
                            'Chưa có GIF meme. Hãy publish asset theme: php artisan vendor:publish --tag=theme-vinahentai-assets'
                        );
                        return;
                    }
                    gifMemeSelectedCategoryId = gifMemeCategories[0].id;
                    renderGifMemeCategories();
                    renderGifMemeGrid(gifMemeSelectedCategoryId);
                    const ov = layer.querySelector('[data-gif-meme-overlay]');
                    const dlg = layer.querySelector('[data-gif-meme-dialog]');
                    layer.classList.remove('hidden');
                    layer.setAttribute('aria-hidden', 'false');
                    if (ov) ov.setAttribute('data-state', 'open');
                    if (dlg) dlg.setAttribute('data-state', 'open');
                } catch (_) {
                    window.alert('Không tải được danh sách GIF.');
                }
            };

            const updateInputState = () => {
                if (!textareaEl || !countEl || !submitEl) return;
                const len = estimateCommentPayloadLength();
                const plain = String(textareaEl.value || '').trim();
                countEl.textContent = `${len}/1000 ký tự`;
                const hasPlain = plain.length > 0;
                const hasGif = !!commentGifUrl;
                submitEl.disabled = (!hasPlain && !hasGif) || len > 1000;
            };

            const bindSubmitWithJquery = async () => {
                if (!formEl || !textareaEl || !submitEl) return;

                const $ = await ensureJquery();
                if (!$) return;

                $(formEl).on('submit', function(event) {
                    event.preventDefault();
                    const plain = String(textareaEl.value || '').trim();
                    let content = '';
                    if (commentGifUrl) {
                        const gifBlock = buildCommentStoredGifHtml(commentGifUrl);
                        content = plain ? plain + gifBlock : gifBlock;
                    } else {
                        content = plain;
                    }
                    if (!content.trim()) return;
                    if (content.length > 1000) return;

                    submitEl.disabled = true;

                    $.ajax({
                        url: `${apiBase}/${mangaId}/comments`,
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            content,
                            _token: csrf,
                            ...(chapterIdForApi !== null ? {
                                chapter_id: chapterIdForApi
                            } : {}),
                        },
                    }).done(function(data) {
                        if (data && data.dn_bonus && data.dn_bonus.awarded && data.dn_bonus.message) {
                            showDamNgocToast(data.dn_bonus.message);
                        }
                        textareaEl.value = '';
                        setCommentGif(null);
                        updateInputState();
                        load(1);
                    }).always(function() {
                        updateInputState();
                    });
                });
            };

            /** Mở/đóng menu cảm xúc khi hover (chỉ thiết bị có hover chuột). */
            const bindReactionHover = (scope = root) => {
                if (!canHover) return;
                scope.querySelectorAll('[data-reaction-wrapper]').forEach((wrapper) => {
                    if (wrapper.dataset.reactionHoverBound === '1') return;
                    wrapper.dataset.reactionHoverBound = '1';
                    const id = wrapper.getAttribute('data-reaction-wrapper');
                    if (!id) return;
                    const menu = root.querySelector(`[data-reaction-menu="${id}"]`);
                    if (!menu) return;
                    wrapper.addEventListener('mouseenter', () => {
                        menu.classList.remove('hidden');
                    });
                    wrapper.addEventListener('mouseleave', () => {
                        menu.classList.add('hidden');
                    });
                });
            };

            /** Form trả lời inline (contenteditable) — bố cục: editor → hàng GIF (khi chọn) → nút. */
            const buildReplyFormHtml = (parentId) => `
                <div class="mt-2 w-full" data-reply-form-wrap="${parentId}">
                    <div role="textbox" contenteditable="true" aria-multiline="false" data-placeholder="Viết phản hồi..."
                        data-reply-editor="${parentId}"
                        class="
                            w-full bg-transparent outline-none
                            border-b border-txt-secondary
                            focus:border-txt-primary
                            text-base font-medium text-txt-primary
                            pb-1
                            [white-space:pre-wrap] [word-break:break-word]
                            empty:before:text-txt-secondary empty:before:content-[attr(data-placeholder)]
                        "
                        style="min-height: 1.75rem;"></div>
                    <div class="mt-2 hidden w-full flex items-center gap-2" data-reply-gif-preview="${parentId}"></div>
                    <div class="mt-2 flex items-center justify-end gap-3">
                        <button type="button" class="text-txt-secondary hover:text-txt-primary" title="Chèn GIF" data-reply-gif="${parentId}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-image h-4 w-4" aria-hidden="true">
                                <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                                <circle cx="9" cy="9" r="2"></circle>
                                <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
                            </svg>
                        </button>
                        <button type="button" class="text-sm text-txt-secondary hover:text-txt-primary" data-reply-cancel="${parentId}">Hủy</button>
                        <button type="button" class="
                            rounded-full px-4 py-1.5 text-sm font-semibold
                            bg-btn-primary text-txt-primary
                            hover:bg-btn-primary/80 disabled:opacity-50
                        " data-reply-submit="${parentId}" disabled>Gửi</button>
                    </div>
                </div>
            `;

            const wireReplyForm = (parentId, slot) => {
                const editor = slot.querySelector(`[data-reply-editor="${parentId}"]`);
                const submitBtn = slot.querySelector(`[data-reply-submit="${parentId}"]`);
                if (!editor || !submitBtn) return;
                const sync = () => {
                    const plain = editor.innerText.trim();
                    const len = estimateReplyPayloadLength(parentId, editor);
                    const hasGif = !!replyGifUrlByParentId.get(parentId);
                    submitBtn.disabled = (!plain && !hasGif) || len > 1000;
                };
                editor.addEventListener('input', sync);
                sync();
            };

            const bumpRepliesToggleLabel = (parentId) => {
                const btn = root.querySelector(`[data-comment-replies-toggle="${parentId}"]`);
                if (btn) {
                    const label = btn.querySelector('.font-sans');
                    if (label) {
                        const cur = Number(String(label.textContent).replace(/[^\d]/g, '')) || 0;
                        label.textContent = formatRepliesLabel(cur + 1);
                    }
                    return;
                }
                const timeEl = root.querySelector(`[data-comment-time="${parentId}"]`);
                const actions = root.querySelector(`[data-comment-actions="${parentId}"]`);
                if (!timeEl || !actions) return;
                timeEl.insertAdjacentHTML(
                    'beforebegin',
                    `<button type="button"
                        class="text-purple-400 hover:text-purple-300 flex cursor-pointer items-center justify-start gap-1 transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                        title="Xem phản hồi"
                        data-comment-replies-toggle="${parentId}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-messages-square h-3 w-3" aria-hidden="true">
                            <path d="M14 9a2 2 0 0 1-2 2H6l-4 4V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2z"></path>
                            <path d="M18 9h2a2 2 0 0 1 2 2v11l-4-4h-6a2 2 0 0 1-2-2v-1"></path>
                        </svg>
                        <div class="font-sans text-[0.65rem] leading-[0.95rem] font-medium">${formatRepliesLabel(1)}</div>
                    </button>`
                );
            };

            const toggleRepliesPanel = async (commentId) => {
                const box = root.querySelector(`[data-comment-replies-container="${commentId}"]`);
                if (!box) return;
                if (!box.classList.contains('hidden')) {
                    box.classList.add('hidden');
                    return;
                }
                box.classList.remove('hidden');
                if (box.dataset.repliesLoaded === '1') return;
                box.innerHTML = '<div class="text-txt-secondary text-sm p-1">Đang tải phản hồi...</div>';
                const res = await fetch(commentRepliesUrl(commentId), {
                    method: 'GET',
                    headers: {
                        Accept: 'application/json'
                    },
                    credentials: 'same-origin',
                });
                if (!res.ok) {
                    box.innerHTML =
                    '<div class="text-txt-secondary text-sm p-1">Không tải được phản hồi.</div>';
                    return;
                }
                const payload = await res.json();
                const items = payload.data || [];
                if (!items.length) {
                    box.innerHTML = '<div class="text-txt-secondary text-sm p-1">Chưa có phản hồi.</div>';
                    box.dataset.repliesLoaded = '1';
                    return;
                }
                box.innerHTML = items.map((it) => renderCommentRow(it, true)).join('');
                bindReactionHover(box);
                box.dataset.repliesLoaded = '1';
            };

            /** Hộp thoại báo cáo — đồng bộ markup với Blade phía trên */
            const reportLayerEl = () => root.querySelector('[data-comment-report-root]');
            const syncReportSubmit = () => {
                const layer = reportLayerEl();
                if (!layer) return;
                const ta = layer.querySelector('[data-comment-report-text]');
                const sb = layer.querySelector('[data-comment-report-submit]');
                if (!ta || !sb) return;
                const len = ta.value.trim().length;
                sb.disabled = len < 5 || len > 2000;
            };
            const closeReportModal = () => {
                const layer = reportLayerEl();
                if (!layer) return;
                const ov = layer.querySelector('[data-comment-report-overlay]');
                const dlg = layer.querySelector('[data-comment-report-dialog]');
                layer.classList.add('hidden');
                layer.setAttribute('aria-hidden', 'true');
                if (ov) ov.setAttribute('data-state', 'closed');
                if (dlg) dlg.setAttribute('data-state', 'closed');
                delete layer.dataset.reportCommentId;
                const ta = layer.querySelector('[data-comment-report-text]');
                if (ta) ta.value = '';
                const sb = layer.querySelector('[data-comment-report-submit]');
                if (sb) sb.disabled = true;
            };
            const openReportModal = (commentId) => {
                const layer = reportLayerEl();
                if (!layer) return;
                const ov = layer.querySelector('[data-comment-report-overlay]');
                const dlg = layer.querySelector('[data-comment-report-dialog]');
                layer.dataset.reportCommentId = String(commentId);
                layer.classList.remove('hidden');
                layer.setAttribute('aria-hidden', 'false');
                if (ov) ov.setAttribute('data-state', 'open');
                if (dlg) dlg.setAttribute('data-state', 'open');
                const ta = layer.querySelector('[data-comment-report-text]');
                if (ta) {
                    ta.value = '';
                    syncReportSubmit();
                    ta.focus();
                }
            };

            document.addEventListener('keydown', (e) => {
                if (e.key !== 'Escape') return;
                const gifLayer = gifMemeRootEl();
                if (gifLayer && !gifLayer.classList.contains('hidden')) {
                    closeGifMemeModal();
                    return;
                }
                const layer = reportLayerEl();
                if (!layer || layer.classList.contains('hidden')) return;
                closeReportModal();
            });

            root.addEventListener('input', (e) => {
                const rt = e.target?.closest?.('[data-comment-report-text]');
                if (rt && root.contains(rt)) {
                    syncReportSubmit();
                    return;
                }
                const inp = e.target?.closest?.('[data-pagination-jump-input]');
                if (!inp || !pagerHost?.contains(inp)) return;
                const form = inp.closest('form');
                const btn = form?.querySelector('[data-pagination-jump-submit]');
                if (!btn) return;
                const max = Number(inp.getAttribute('max') || 1);
                const min = Number(inp.getAttribute('min') || 1);
                const raw = String(inp.value || '').trim();
                if (raw === '') {
                    btn.disabled = true;
                    return;
                }
                const n = Number(raw);
                const ok = Number.isInteger(n) && n >= min && n <= max;
                btn.disabled = !ok;
            });

            root.addEventListener('submit', (e) => {
                const form = e.target;
                if (!(form instanceof HTMLFormElement) || !form.matches('[data-comments-pagination-jump]'))
                    return;
                if (!pagerHost?.contains(form)) return;
                e.preventDefault();
                const input = form.querySelector('[data-pagination-jump-input]');
                const n = Number(String(input?.value || '').trim());
                if (!Number.isInteger(n) || n < 1 || n > lastPage) return;
                if (n !== currentPage) load(n);
            });

            root.addEventListener('submit', async (e) => {
                const form = e.target;
                if (!(form instanceof HTMLFormElement) || !form.matches('[data-comment-report-form]'))
                    return;
                if (!root.contains(form)) return;
                e.preventDefault();
                const layer = reportLayerEl();
                const cid = layer?.dataset.reportCommentId;
                if (!cid) return;
                const ta = form.querySelector('[data-comment-report-text]');
                const body = String(ta?.value || '').trim();
                if (body.length < 5) return;
                const submitBtn = form.querySelector('[data-comment-report-submit]');
                if (submitBtn) submitBtn.disabled = true;
                const res = await fetch(commentReportUrl(cid), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        content: body,
                        _token: csrf
                    }),
                });
                if (submitBtn) submitBtn.disabled = false;
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
                    window.alert(msg);
                    return;
                }
                closeReportModal();
            });

            root.addEventListener('click', async (e) => {
                const target = e.target;
                if (!target?.closest) return;

                if (target.matches('[data-comment-report-overlay]')) {
                    closeReportModal();
                    return;
                }
                const repClose = target.closest('[data-comment-report-close]');
                if (repClose && root.contains(repClose)) {
                    e.preventDefault();
                    closeReportModal();
                    return;
                }

                const gifOverlayHit = target.closest('[data-gif-meme-overlay]');
                if (gifOverlayHit && gifMemeRootEl()?.contains(gifOverlayHit)) {
                    closeGifMemeModal();
                    return;
                }
                const gifCloseBtn = target.closest('[data-gif-meme-close]');
                if (gifCloseBtn && root.contains(gifCloseBtn)) {
                    e.preventDefault();
                    closeGifMemeModal();
                    return;
                }
                const gifTab = target.closest('[data-gif-meme-tab]');
                if (gifTab && gifMemeRootEl()?.contains(gifTab)) {
                    e.preventDefault();
                    const id = gifTab.getAttribute('data-gif-meme-tab');
                    if (id) {
                        gifMemeSelectedCategoryId = id;
                        renderGifMemeCategories();
                        renderGifMemeGrid(id);
                    }
                    return;
                }
                const gifCell = target.closest('[data-gif-meme-cell]');
                if (gifCell && gifMemeRootEl()?.contains(gifCell)) {
                    e.preventDefault();
                    const u = gifCell.getAttribute('data-gif-url');
                    if (u) {
                        if (gifMemeModalTarget.kind === 'reply' && gifMemeModalTarget.parentId) {
                            setReplyGif(gifMemeModalTarget.parentId, u);
                            const ed = root.querySelector(
                                `[data-reply-editor="${gifMemeModalTarget.parentId}"]`);
                            ed?.dispatchEvent(new Event('input'));
                        } else {
                            setCommentGif(u);
                        }
                        closeGifMemeModal();
                    }
                    return;
                }
                const replyGifOpenBtn = target.closest('[data-reply-gif]');
                if (replyGifOpenBtn && root.contains(replyGifOpenBtn)) {
                    e.preventDefault();
                    const pid = replyGifOpenBtn.getAttribute('data-reply-gif');
                    if (!pid) return;
                    gifMemeModalTarget = {
                        kind: 'reply',
                        parentId: pid
                    };
                    openGifMemeModal();
                    return;
                }
                const gifMainOpen = target.closest('[data-comment-gif-open]');
                if (gifMainOpen && root.contains(gifMainOpen)) {
                    e.preventDefault();
                    gifMemeModalTarget = {
                        kind: 'main'
                    };
                    openGifMemeModal();
                    return;
                }
                const replyGifRemove = target.closest('[data-reply-gif-remove]');
                if (replyGifRemove && root.contains(replyGifRemove)) {
                    e.preventDefault();
                    const rpid = replyGifRemove.getAttribute('data-reply-gif-remove');
                    if (rpid) {
                        setReplyGif(rpid, null);
                        const ed = root.querySelector(`[data-reply-editor="${rpid}"]`);
                        ed?.dispatchEvent(new Event('input'));
                    }
                    return;
                }
                const gifRemove = target.closest('[data-comment-gif-remove]');
                if (gifRemove && root.contains(gifRemove)) {
                    e.preventDefault();
                    setCommentGif(null);
                    return;
                }

                const reportOpenBtn = target.closest('[data-comment-report-open]');
                if (reportOpenBtn && root.contains(reportOpenBtn)) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (!isLoggedIn) {
                        window.location.href =
                            `${loginUrl}?redirect=${encodeURIComponent(window.location.href)}`;
                        return;
                    }
                    const cid = reportOpenBtn.getAttribute('data-comment-report-open');
                    if (cid) {
                        openReportModal(cid);
                        const menu = reportOpenBtn.closest('[data-comment-menu]');
                        if (menu) menu.classList.add('hidden');
                    }
                    return;
                }

                const inPager = (el) => !!(el && pagerHost && pagerHost.contains(el));

                const pagFirst = target.closest('[data-pagination-first]');
                if (pagFirst && inPager(pagFirst)) {
                    e.preventDefault();
                    if (currentPage > 1) load(1);
                    return;
                }
                const pagLast = target.closest('[data-pagination-last]');
                if (pagLast && inPager(pagLast)) {
                    e.preventDefault();
                    if (currentPage < lastPage) load(lastPage);
                    return;
                }
                const pagPage = target.closest('[data-pagination-page]');
                if (pagPage && inPager(pagPage)) {
                    e.preventDefault();
                    const p = Number(pagPage.getAttribute('data-pagination-page'));
                    if (p >= 1 && p <= lastPage && p !== currentPage) load(p);
                    return;
                }

                const replyOpen = target.closest('[data-comment-reply-open]');
                if (replyOpen && root.contains(replyOpen)) {
                    e.preventDefault();
                    if (!isLoggedIn) {
                        window.location.href =
                            `${loginUrl}?redirect=${encodeURIComponent(window.location.href)}`;
                        return;
                    }
                    const pid = replyOpen.getAttribute('data-comment-reply-open');
                    if (!pid) return;
                    const slot = root.querySelector(`[data-reply-slot="${pid}"]`);
                    if (!slot) return;
                    slot.classList.remove('hidden');
                    if (!slot.querySelector('[data-reply-editor]')) {
                        slot.innerHTML = buildReplyFormHtml(pid);
                        wireReplyForm(pid, slot);
                    }
                    return;
                }

                const cancel = target.closest('[data-reply-cancel]');
                if (cancel && root.contains(cancel)) {
                    e.preventDefault();
                    const pid = cancel.getAttribute('data-reply-cancel');
                    if (!pid) return;
                    replyGifUrlByParentId.delete(pid);
                    const slot = root.querySelector(`[data-reply-slot="${pid}"]`);
                    if (slot) {
                        slot.innerHTML = '';
                        slot.classList.add('hidden');
                    }
                    return;
                }

                const replySendBtn = target.closest('[data-reply-submit]');
                if (replySendBtn && root.contains(replySendBtn)) {
                    e.preventDefault();
                    const pid = replySendBtn.getAttribute('data-reply-submit');
                    if (!pid) return;
                    const slot = root.querySelector(`[data-reply-slot="${pid}"]`);
                    const editor = slot?.querySelector(`[data-reply-editor="${pid}"]`);
                    if (!editor) return;
                    const plain = editor.innerText.trim();
                    const gifUrl = replyGifUrlByParentId.get(pid);
                    let content = '';
                    if (gifUrl) {
                        const gifBlock = buildCommentStoredGifHtml(gifUrl);
                        const textPart = plain.replace(/\n/g, '<br>');
                        content = textPart ? textPart + gifBlock : gifBlock;
                        const len = estimateReplyPayloadLength(pid, editor);
                        if (!len || len > 1000) return;
                    } else {
                        if (!plain.length || plain.length > 1000) return;
                        content = editor.innerHTML.trim();
                    }
                    replySendBtn.disabled = true;
                    const res = await fetch(commentsStoreUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            content,
                            parent_id: Number(pid),
                            _token: csrf,
                            ...(chapterIdForApi !== null ? {
                                chapter_id: chapterIdForApi
                            } : {}),
                        }),
                    });
                    replySendBtn.disabled = false;
                    if (res.status === 401) {
                        window.location.href =
                            `${loginUrl}?redirect=${encodeURIComponent(window.location.href)}`;
                        return;
                    }
                    if (!res.ok) return;
                    const payload = await res.json();
                    if (payload.dn_bonus && payload.dn_bonus.awarded && payload.dn_bonus.message) {
                        showDamNgocToast(payload.dn_bonus.message);
                    }
                    const newRow = payload.comment;
                    replyGifUrlByParentId.delete(pid);
                    if (slot) {
                        slot.innerHTML = '';
                        slot.classList.add('hidden');
                    }
                    bumpRepliesToggleLabel(pid);
                    const box = root.querySelector(`[data-comment-replies-container="${pid}"]`);
                    if (box && newRow && !box.classList.contains('hidden') && box.dataset.repliesLoaded ===
                        '1') {
                        box.insertAdjacentHTML('beforeend', renderCommentRow(newRow, true));
                        bindReactionHover(box);
                    } else if (box) {
                        delete box.dataset.repliesLoaded;
                    }
                    return;
                }

                const toggleReplies = target.closest('[data-comment-replies-toggle]');
                if (toggleReplies && root.contains(toggleReplies)) {
                    e.preventDefault();
                    const cid = toggleReplies.getAttribute('data-comment-replies-toggle');
                    if (cid) await toggleRepliesPanel(cid);
                }
            });

            const bindReactionWithJquery = async () => {
                const $ = await ensureJquery();
                if (!$) return;

                $(root).on('click', '[data-comment-reaction]', function(event) {
                    event.preventDefault();
                    event.stopPropagation();

                    const commentId = $(this).attr('data-comment-reaction');
                    const reaction = $(this).attr('data-reaction');
                    if (!commentId || !reaction) return;

                    $.ajax({
                        url: `{{ url('/api/comments') }}/${commentId}/reaction`,
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            reaction,
                            _token: csrf,
                        },
                    }).done(function(data) {
                        // Cập nhật từ JSON API — tránh load lại toàn bộ danh sách bình luận.
                        const summaryEl = root.querySelector(
                            `[data-comment-reaction-summary="${commentId}"]`);
                        if (summaryEl && data && data.reaction_counts !== undefined) {
                            const html = renderReactionSummary(data.reaction_counts, commentId)
                                .trim();
                            const tmp = document.createElement('div');
                            tmp.innerHTML = html;
                            const next = tmp.firstElementChild;
                            if (next) {
                                summaryEl.replaceWith(next);
                            }
                        } else {
                            load(currentPage);
                        }
                        const menu = root.querySelector(`[data-reaction-menu="${commentId}"]`);
                        if (menu) menu.classList.add('hidden');
                    }).fail(function(xhr) {
                        if (xhr.status === 401) {
                            const redirect = encodeURIComponent(window.location.href);
                            window.location.href = `${loginUrl}?redirect=${redirect}`;
                        }
                    });
                });
            };

            // Toggle menu "Tùy chọn" + menu "Cảm xúc" cho từng comment.
            document.addEventListener('click', (e) => {
                const target = e.target;
                const btn = target?.closest?.('[data-comment-options]');
                const isBtn = !!btn;
                const reactionToggleBtn = target?.closest?.('[data-reaction-toggle]');
                const isReactionToggle = !!reactionToggleBtn;

                // Nếu click vào trong menu thì không tắt.
                const menuEl = target?.closest?.('[data-comment-menu]');
                const isInsideMenu = !!menuEl;
                const reactionMenuEl = target?.closest?.('[data-reaction-menu]');
                const isInsideReactionMenu = !!reactionMenuEl;

                const allMenus = root.querySelectorAll('[data-comment-menu]');
                allMenus.forEach((m) => {
                    if (isInsideMenu) return;
                    m.classList.add('hidden');
                });
                const allReactionMenus = root.querySelectorAll('[data-reaction-menu]');
                allReactionMenus.forEach((m) => {
                    if (isInsideReactionMenu) return;
                    m.classList.add('hidden');
                });

                if (isBtn && btn) {
                    const commentId = btn.getAttribute('data-comment-options');
                    const menu = root.querySelector('[data-comment-menu="' + commentId + '"]');
                    if (menu) menu.classList.toggle('hidden');
                }
                // Mobile / không có hover: bấm "Cảm xúc" để mở menu; desktop dùng hover qua bindReactionHover.
                if (isReactionToggle && reactionToggleBtn && !canHover) {
                    const commentId = reactionToggleBtn.getAttribute('data-reaction-toggle');
                    const menu = root.querySelector('[data-reaction-menu="' + commentId + '"]');
                    if (menu) menu.classList.toggle('hidden');
                }
            });

            if (textareaEl) {
                textareaEl.addEventListener('input', updateInputState);
                updateInputState();
            }

            bindSubmitWithJquery();
            bindReactionWithJquery();

            load(1);
        })();
    </script>
@endpush
