<?php

use Illuminate\Support\Facades\Route;
use Nqt\ThemeVinahentai\Controllers\AuthController;
use Nqt\ThemeVinahentai\Controllers\CommentController;
use Nqt\ThemeVinahentai\Controllers\GifMemeController;
use Nqt\ThemeVinahentai\Controllers\FollowController;
use Nqt\ThemeVinahentai\Controllers\LeaderboardController;
use Nqt\ThemeVinahentai\Controllers\ThemeVinahentaiController;
use Nqt\ThemeVinahentai\Controllers\WaifuSummonController;
use Nqt\ThemeVinahentai\Controllers\UserChapterController;
use Nqt\ThemeVinahentai\Controllers\UserMangaController;
use Nqt\ThemeVinahentai\Controllers\UserProfileController;
use Nqt\ThemeVinahentai\Controllers\UserProfileLibraryController;
use Nqt\ThemeVinahentai\Controllers\UserLeaderboardController;
use Nqt\ThemeVinahentai\Controllers\ChapterReactionController;
use Nqt\ThemeVinahentai\Controllers\ChapterReportController;
use Nqt\ThemeVinahentai\Controllers\ChapterViewController;
use Nqt\ThemeVinahentai\Controllers\ReadingHistoryController;
use Nqt\ThemeVinahentai\Controllers\ShopController;

Route::middleware('web')->group(function () {
    $homeRoute = '/';
    $mangaRoute = '/truyen-hentai/{manga}';
    $chapterRoute = '/truyen-hentai/{manga}/{chapter}';
    $genreRoute = '/the-loai/{genre}';
    $authorRoute = '/tac-gia/{author}';
    $searchRoute = '/search';
    $searchAdvancedRoute = '/search/advanced';
    $loginRoute = '/login';
    $registerRoute = '/register';
    $translatorRoute = '/dich-gia/{translator}';
    if (function_exists('active_theme_config')) {
        $homeRoute = (string) call_user_func('active_theme_config', 'site_routes_home', '/');
        $mangaRoute = (string) call_user_func('active_theme_config', 'site_routes_manga', '/truyen-hentai/{manga}');
        $chapterRoute = (string) call_user_func('active_theme_config', 'site_routes_chapter', '/truyen-hentai/{manga}/{chapter}');
        $genreRoute = (string) call_user_func('active_theme_config', 'site_routes_genre', '/the-loai/{genre}');
        $authorRoute = (string) call_user_func('active_theme_config', 'site_routes_author', '/tac-gia/{author}');
        $searchRoute = (string) call_user_func('active_theme_config', 'site_routes_search', '/search');
        $searchAdvancedRoute = (string) call_user_func('active_theme_config', 'site_routes_search_advanced', '/search/advanced');
        $loginRoute = (string) call_user_func('active_theme_config', 'site_routes_login', '/login');
        $registerRoute = (string) call_user_func('active_theme_config', 'site_routes_register', '/register');
        $translatorRoute = (string) call_user_func('active_theme_config', 'site_routes_translator', '/dich-gia/{translator}');
    }

    Route::middleware('guest')->group(function () use ($loginRoute, $registerRoute) {
        Route::get($loginRoute, [AuthController::class, 'showLogin'])->name('login');
        Route::get($registerRoute, [AuthController::class, 'showRegister'])->name('register');
        Route::post($loginRoute, [AuthController::class, 'postLogin'])->name('login.post');
        Route::post($registerRoute, [AuthController::class, 'postRegister'])->name('register.post');
    });

    Route::get('waifu/summon', [ThemeVinahentaiController::class, 'showWaifuSummon'])->name('waifu.summon');
    Route::get('danh-sach', [ThemeVinahentaiController::class, 'showDanhSach'])->name('danh-sach');
    Route::get('gioi-thieu', [ThemeVinahentaiController::class, 'showGioiThieu'])->name('gioi-thieu');
    Route::get('genres', [ThemeVinahentaiController::class, 'showGenres'])->name('genres');

    Route::middleware('auth')->group(function () {
        Route::post('waifu/summon/perform', [WaifuSummonController::class, 'perform'])
            ->middleware('throttle:30,1')
            ->name('waifu.summon.perform');
        Route::post('waifu/summon/milestone-claim', [WaifuSummonController::class, 'claimMilestone'])
            ->middleware('throttle:30,1')
            ->name('waifu.summon.milestone-claim');
        Route::get('waifu/summon/rewards-history', [WaifuSummonController::class, 'rewardsHistory'])
            ->middleware('throttle:60,1')
            ->name('waifu.summon.rewards-history');
        Route::get('profile/{user}', [ThemeVinahentaiController::class, 'showProfile'])->name('profile');
        Route::get('cua-hang', [ThemeVinahentaiController::class, 'showShop'])->name('shop');
        Route::post('cua-hang/mua', [ShopController::class, 'purchase'])
            ->middleware('throttle:30,1')
            ->name('shop.purchase');
        Route::get('user/profile-edit', [ThemeVinahentaiController::class, 'showProfileEdit'])->name('user.profile-edit');
        Route::get('user/dot-pha', [ThemeVinahentaiController::class, 'showLevelBreakthrough'])->name('user.level-breakthrough');
        Route::post('user/dot-pha', [ThemeVinahentaiController::class, 'attemptLevelBreakthrough'])
            ->middleware('throttle:15,1')
            ->name('user.level-breakthrough.attempt');
        Route::get('truyen-hentai/manage', [ThemeVinahentaiController::class, 'showManageManga'])->name('user.manage-manga');
        Route::get('truyen-hentai/create', [ThemeVinahentaiController::class, 'showCreateManga'])->name('user.create-manga');
        Route::post('truyen-hentai/create', [UserMangaController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('user.store-manga');
        Route::get('truyen-hentai/preview/{manga}', [ThemeVinahentaiController::class, 'showMangaPreview'])->name('mangas.preview');
        Route::get('truyen-hentai/edit/{manga}', [ThemeVinahentaiController::class, 'showMangaEdit'])->name('mangas.edit');
        Route::put('truyen-hentai/edit/{mangaSlug}', [UserMangaController::class, 'update'])
            ->middleware('throttle:10,1')
            ->name('user.update-manga');
        Route::get('truyen-hentai/chapter/create/{manga}', [ThemeVinahentaiController::class, 'showCreateChapter'])->name('user.create-chapter');
        Route::get('truyen-hentai/chapter/edit/{manga}/{chapter}', [ThemeVinahentaiController::class, 'showEditChapter'])->name('user.edit-chapter');
        Route::get('/api/user/mangas/{mangaSlug}/chapter-check', [UserChapterController::class, 'checkDuplicate'])
            ->middleware('throttle:60,1')
            ->name('user.chapter-check');
        Route::post('/api/user/mangas/{mangaSlug}/chapters', [UserChapterController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('user.store-chapter');
        Route::post('/api/user/mangas/{mangaSlug}/chapters/{chapter}', [UserChapterController::class, 'update'])
            ->middleware('throttle:10,1')
            ->name('user.update-chapter');
        Route::delete('/api/user/mangas/{mangaSlug}/chapters/{chapter}', [UserChapterController::class, 'destroy'])
            ->middleware('throttle:15,1')
            ->name('user.destroy-chapter');
        Route::post('/api/user/manga-cover', [UserMangaController::class, 'uploadCover'])
            ->middleware('throttle:20,1')
            ->name('user.manga-cover.upload');
        Route::get('/api/user/authors/search', [UserMangaController::class, 'searchAuthors'])
            ->middleware('throttle:60,1')
            ->name('user.authors.search');
        Route::get('/api/user/translators/search', [UserMangaController::class, 'searchTranslators'])
            ->middleware('throttle:60,1')
            ->name('user.translators.search');
        Route::get('user/blacklist-tags', [ThemeVinahentaiController::class, 'showBlacklistTags'])->name('user.blacklist-tags');
        Route::post('user/blacklist-tags', [ThemeVinahentaiController::class, 'updateBlacklistTags'])->name('user.blacklist-tags.update');
        Route::post('/api/user/profile', [UserProfileController::class, 'updateProfile'])
            ->middleware('throttle:20,1')
            ->name('api.user.profile.update');
        Route::post('/api/user/password', [UserProfileController::class, 'updatePassword'])
            ->middleware('throttle:10,1')
            ->name('api.user.password.update');
        Route::post('/api/user/companion-waifu', [UserProfileController::class, 'updateCompanionWaifu'])
            ->middleware('throttle:30,1')
            ->name('api.user.companion-waifu');

        Route::get('/api/user/library/followed-mangas', [UserProfileLibraryController::class, 'followedMangas'])
            ->middleware('throttle:60,1')
            ->name('api.user.library.followed-mangas');
        Route::get('/api/user/library/reading-history', [UserProfileLibraryController::class, 'readingHistory'])
            ->middleware('throttle:60,1')
            ->name('api.user.library.reading-history');
        Route::get('/api/user/library/followed-translators', [UserProfileLibraryController::class, 'followedTranslators'])
            ->middleware('throttle:60,1')
            ->name('api.user.library.followed-translators');
        Route::get('/api/user/library/followed-authors', [UserProfileLibraryController::class, 'followedAuthors'])
            ->middleware('throttle:60,1')
            ->name('api.user.library.followed-authors');
        Route::get('/api/user/library/my-comments', [UserProfileLibraryController::class, 'myComments'])
            ->middleware('throttle:60,1')
            ->name('api.user.library.my-comments');
        Route::get('logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout.post');

        Route::post('/api/manga/{manga}/comments', [CommentController::class, 'store'])->name('manga.comments.store');
        Route::post('/api/comments/{comment}/reaction', [CommentController::class, 'react'])->name('comments.react');
        Route::post('/api/comments/{comment}/report', [CommentController::class, 'report'])
            ->middleware('throttle:15,1')
            ->name('comments.report');
        Route::get('/api/manga/{manga}/follow', [FollowController::class, 'status'])->name('manga.follow.status');
        Route::post('/api/manga/{manga}/follow', [FollowController::class, 'toggle'])->name('manga.follow.toggle');

        Route::get('/api/manga/{manga}/reading-history/latest', [ReadingHistoryController::class, 'latestForManga'])
            ->middleware('throttle:60,1')
            ->name('manga.reading-history.latest');

        Route::delete('/api/manga/{manga}', [UserMangaController::class, 'destroy'])
            ->middleware('throttle:15,1')
            ->name('api.manga.destroy');

        // ===== CHAPTER LIKE/DISLIKE (JSON) =====
        Route::get('/api/chapters/{chapter}/reaction/status', [ChapterReactionController::class, 'status'])
            ->name('chapters.reaction.status');
        Route::post('/api/chapters/{chapter}/reaction', [ChapterReactionController::class, 'react'])
            ->middleware('throttle:15,1')
            ->name('chapters.reaction.react');

        // ===== CHAPTER REPORT (JSON) =====
        Route::post('/api/chapters/{chapter}/report', [ChapterReportController::class, 'report'])
            ->middleware('throttle:15,1')
            ->name('chapters.report');

        // Lịch sử đọc (GET khi vào trang, POST đồng bộ định kỳ)
        Route::get('/api/chapters/{chapter}/reading-history', [ReadingHistoryController::class, 'show'])
            ->middleware('throttle:60,1')
            ->name('chapters.reading-history.show');
        Route::post('/api/chapters/{chapter}/reading-history', [ReadingHistoryController::class, 'store'])
            ->middleware('throttle:60,1')
            ->name('chapters.reading-history.store');
    });

    // ===== GOOGLE =====
    Route::get('/auth/google', [AuthController::class, 'redirectGoogle'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogle'])->name('auth.google.callback');

    // ===== DISCORD =====
    Route::get('/auth/discord', [AuthController::class, 'redirectDiscord'])->name('auth.discord.redirect');
    Route::get('/auth/discord/callback', [AuthController::class, 'handleDiscord'])->name('auth.discord.callback');

    Route::get($homeRoute, [ThemeVinahentaiController::class, 'showHome'])->name('home');

    Route::get($mangaRoute, [ThemeVinahentaiController::class, 'showManga'])->name('mangas.show');

    Route::get($genreRoute, [ThemeVinahentaiController::class, 'showGenre'])->name('genres.show');

    Route::get($authorRoute, [ThemeVinahentaiController::class, 'showAuthor'])->name('authors.show');

    Route::get($translatorRoute, [ThemeVinahentaiController::class, 'showTranslator'])->name('translators.show');

    Route::get($searchRoute, [ThemeVinahentaiController::class, 'showSearch'])->name('search');

    Route::get($chapterRoute, [ThemeVinahentaiController::class, 'showChapter'])->name('chapters.show');

    Route::get('/random', [ThemeVinahentaiController::class, 'showRandom'])->name('random');

    Route::prefix('leaderboard')->group(function () {
        Route::get('/manga', [LeaderboardController::class, 'showManga'])->name('leaderboard.manga');
        Route::get('/member', [LeaderboardController::class, 'showMember'])->name('leaderboard.member');
        Route::get('/waifu', [LeaderboardController::class, 'showWaifu'])->name('leaderboard.waifu');
        Route::get('/translator', [LeaderboardController::class, 'showTranslator'])->name('leaderboard.translator');
    });

    Route::post('/api/chapters/{chapter}/view', [ChapterViewController::class, 'record'])
        ->middleware('throttle:30,1')
        ->name('chapters.view.record');

    Route::get($searchAdvancedRoute, [ThemeVinahentaiController::class, 'showSearchAdvanced'])->name('search.advanced');

    // ===== LEADERBOARD (JSON cho sidebar xếp hạng) =====
    Route::get('/api/leaderboard/{period}', [LeaderboardController::class, 'top'])
        ->whereIn('period', ['daily', 'weekly', 'monthly'])
        ->name('leaderboard.top');

    // ===== USER LEADERBOARD (JSON cho BXH dịch giả) =====
    Route::get('/api/user-leaderboard/{period}', [UserLeaderboardController::class, 'top'])
        ->whereIn('period', ['all-time', 'weekly', 'monthly']);

    // ===== COMMENTS (JSON) =====
    Route::get('/api/manga/{manga}/comments', [CommentController::class, 'index'])->name('manga.comments.index');
    Route::get('/api/comments/{comment}/replies', [CommentController::class, 'replies'])->name('comments.replies');

    Route::get('/api/gif-meme/manifest', [GifMemeController::class, 'manifest'])->name('gif-meme.manifest');
});
