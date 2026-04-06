<?php

namespace Nqt\ThemeVinahentai;

use App\Models\Genre;
use App\Models\UserNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Nqt\ThemeVinahentai\Console\Commands\GenerateThemeVinahentaiSitemapCommand;
use Nqt\ThemeVinahentai\Console\Commands\ThemeVinahentaiSeedCommand;

class ThemeVinahentaiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $viewPath = __DIR__.'/../resources/views';
        $routePath = __DIR__.'/../routes/web.php';
        $assetPath = __DIR__.'/../resources/assets';

        // Load view theo namespace để tiện debug khi cần.
        $this->loadViewsFrom($viewPath, 'theme-vinahentai');
        $this->loadRoutesFrom($routePath);

        View::composer('theme-vinahentai::layout.header', function ($view): void {
            $buildGenresByLetter = static function (): Collection {
                return Genre::query()
                    ->orderBy('name')
                    ->get(['id', 'name', 'slug', 'description'])
                    ->groupBy(function (Genre $genre): string {
                        $first = mb_strtoupper(mb_substr(trim($genre->name), 0, 1, 'UTF-8'));
                        if ($first === '' || preg_match('/^[0-9]/u', $first)) {
                            return '#';
                        }

                        return $first;
                    })
                    ->sortKeysUsing(function (string $a, string $b): int {
                        if ($a === '#' && $b !== '#') {
                            return -1;
                        }
                        if ($b === '#' && $a !== '#') {
                            return 1;
                        }

                        return strcmp($a, $b);
                    })
                    ->map(function ($group) {
                        if ($group instanceof Collection) {
                            return $group;
                        }
                        if (is_array($group)) {
                            return collect($group);
                        }

                        return collect();
                    });
            };

            $genresByLetter = Cache::rememberForever('theme_vinahentai.genres_by_letter_v3', $buildGenresByLetter);

            if (! $genresByLetter instanceof Collection) {
                Cache::forget('theme_vinahentai.genres_by_letter_v3');
                $genresByLetter = $buildGenresByLetter();
                Cache::forever('theme_vinahentai.genres_by_letter_v3', $genresByLetter);
            }

            $headerInAppNotifications = collect();
            $headerInAppNotificationsUnread = 0;
            if (Auth::check() && Schema::hasTable('user_notifications')) {
                $uid = (int) Auth::id();
                $headerInAppNotifications = UserNotification::query()
                    ->where('user_id', $uid)
                    ->orderByDesc('created_at')
                    ->limit(20)
                    ->get();
                $headerInAppNotificationsUnread = UserNotification::query()
                    ->where('user_id', $uid)
                    ->where('is_read', false)
                    ->count();
            }

            $view->with('genresByLetter', $genresByLetter);
            $view->with('headerInAppNotifications', $headerInAppNotifications);
            $view->with('headerInAppNotificationsUnread', $headerInAppNotificationsUnread);
        });
        $this->publishes([
            $assetPath => public_path('vendor/theme-vinahentai'),
        ], 'theme-vinahentai-assets');

        if (function_exists('theme_register')) {
            theme_register(
                name: 'VinaHentai',
                slug: 'theme-vinahentai',
                package: 'nqt/theme-vinahentai',
                provider: self::class,
                viewPath: $viewPath,
                meta: [
                    'author' => 'NQT',
                    'description' => 'Theme mẫu demo override view welcome.',
                ],
                config: [
                    'body_attributes' => 'class="overflow-x-hidden"',
                    'seo_home_shortcut_icon' => '/vendor/theme-vinahentai/favicon.png',
                    'seo_manga_title' => '{manga.title} | ' . env('APP_NAME'),
                    'seo_manga_keywords' => '{manga.title}, {manga.tags}, {manga.genres}, ' . env('APP_NAME'),
                    'seo_manga_description' => '{manga.description}',
                    'seo_chapter_title' => '{chapter.title} | {manga.title} - ' . env('APP_NAME'),
                    'seo_chapter_description' => '{manga.description}',
                    'seo_chapter_keywords' => '{manga.tags}, {manga.genres}, ' . env('APP_NAME'),
                    'seo_genre_title' => '{genre.name} | ' . env('APP_NAME'),
                    'seo_genre_description' => "Tổng hợp truyện hentai {genre.name} vietsub không quảng cáo tại ". env('APP_NAME') . ". Thể loại toàn bộ truyện được tô màu; màu sắc sống động tăng kích thích thị giác. Cập nhật truyện nhanh nhất trên " . env('APP_NAME') . ".",
                    'seo_genre_keywords' => '{genre.name}, ' . env('APP_NAME'),
                    'seo_author_title' => '{author.name} | ' . env('APP_NAME'),
                    'seo_author_description' => 'Tổng hợp truyện của tác giả {author.name} vietsub không quảng cáo tại '. env('APP_NAME') . '. Cập nhật truyện nhanh nhất trên ' . env('APP_NAME') . '.',
                    'seo_author_keywords' => '{author.name}, ' . env('APP_NAME'),
                    'seo_translator_title' => '{translator.name} | ' . env('APP_NAME'),
                    'seo_translator_description' => 'Tổng hợp truyện của dịch giả {translator.name} vietsub không quảng cáo tại '. env('APP_NAME') . '. Cập nhật truyện nhanh nhất trên ' . env('APP_NAME') . '.',
                    'seo_translator_keywords' => '{translator.name}, ' . env('APP_NAME'),
                    'site_logo_html' => '<a href="/"><img src="'.asset('vendor/theme-vinahentai/images/logo2.webp').'" alt="VinaHentai Logo" class="h-auto w-50 cursor-pointer md:transition-transform md:scale-[0.90]" style="transform-origin:left center"></a>',
                    'custom_html_footer' => '<footer class="border-bd-default bg-bgc-layer1 border-t">
    <div class="container mx-auto px-4 py-8 md:px-8 md:py-12">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-[220px_1fr_1fr] md:gap-10">
            <div class="flex flex-col gap-5">
                <img src="'.asset('vendor/theme-vinahentai/images/logo.webp').'" alt="Vinahentai Logo" class="h-8 w-36 md:h-10 md:w-44">
                <nav class="flex flex-col gap-1.5"><a href="/gioi-thieu" class="text-txt-secondary hover:text-txt-primary text-sm font-medium transition-colors">Giới thiệu</a><a href="/danh-sach" class="text-txt-secondary hover:text-txt-primary text-sm font-medium transition-colors">Danh sách truyện</a><a href="/genres" class="text-txt-secondary hover:text-txt-primary text-sm font-medium transition-colors">Thể loại</a><a href="/leaderboard/manga" class="text-txt-secondary hover:text-txt-primary text-sm font-medium transition-colors">Bảng xếp hạng</a></nav>
                <div class="flex flex-col gap-2">
                    <p class="text-txt-primary text-sm font-semibold">Liên hệ</p>
                    <a href="mailto:vinahentai.contact@gmail.com" class="text-txt-secondary hover:text-pink-400 flex items-center gap-1.5 text-sm font-medium transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0">
                            <path d="M3 4a2 2 0 0 0-2 2v1.161l8.441 4.221a1.25 1.25 0 0 0 1.118 0L19 7.162V6a2 2 0 0 0-2-2H3Z"></path>
                            <path d="m19 8.839-7.77 3.885a2.75 2.75 0 0 1-2.46 0L1 8.839V14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8.839Z"></path>
                        </svg>
                        test@test.com
                    </a>
                </div>
                <p class="text-txt-muted text-xs">© 2025 Vinahentai</p>
            </div>
            <div class="flex flex-col gap-3">
                <p class="text-txt-primary text-sm font-semibold">Giới thiệu</p>
                <p class="text-txt-secondary text-sm leading-relaxed">Vinahentai - Trang đọc truyện hentai, manhwa 18+ vietsub KHÔNG QUẢNG CÁO, đa dạng thể loại. Truy cập Vinahentai.one nếu vinahentai.icu bị chặn.</p>
                <p class="text-txt-secondary text-sm leading-relaxed">Chúng tôi tôn trọng quyền tác giả và cam kết xử lý nhanh chóng nếu có nội dung vi phạm. Vui lòng liên hệ nếu bạn phát hiện nội dung chưa phù hợp.</p>
            </div>
            <div class="flex flex-col gap-3">
                <p class="text-txt-primary text-sm font-semibold">Khám phá thêm</p>
                <div class="flex flex-wrap gap-1.5">
                    <a href="/genres/ahegao" class="border-bd-default text-txt-secondary hover:border-pink-500/50 hover:text-pink-400 rounded border px-2 py-0.5 text-xs font-medium transition-colors">Ahegao</a>
                </div>
            </div>
        </div>
        <div class="border-bd-default my-6 border-t"></div>
        <div class="flex flex-col gap-3">

            <p class="text-txt-secondary text-sm">Quảng cáo liên hệ tele <a href="https://t.me/" target="_blank" rel="noopener noreferrer" class="text-sky-400 hover:text-sky-300 font-medium transition-colors">@test</a></p>
        </div>
    </div>
</footer>',
                    'site_routes_home' => '/',
                    'site_routes_manga' => '/truyen-hentai/{manga}',
                    'site_routes_chapter' => '/truyen-hentai/{manga}/{chapter}',
                    'site_routes_genre' => '/the-loai/{genre}',
                    'site_routes_author' => '/tac-gia/{author}',
                    'site_routes_search' => '/search',
                    'site_routes_search_advanced' => '/search/advanced',
                    'site_routes_login' => '/login',
                    'site_routes_register' => '/register',
                    'site_routes_translator' => '/dich-gia/{translator}',
                    'body_attributes' => 'class="overflow-x-hidden"',
                    'home_sections' => 'Truyện hentai mới||||40|/danh-sach',
                    'seo_home_site_name' => env('APP_NAME'),
                    'seo_home_shortcut_icon' => '/vendor/theme-vinahentai/favicon.png',
                    'seo_home_default_title' => env('APP_NAME') . ' - Đọc truyện hentai 18+ không quảng cáo',
                    'seo_home_description' => env('APP_NAME') . ' - Trang đọc truyện hentai, manhwa 18+ vietsub KHÔNG QUẢNG CÁO, đa dạng thể loại. Truy cập '. env('APP_NAME') .' nếu '. env('APP_NAME') .' bị chặn.',
                    'seo_home_keywords' => env('APP_NAME') . ', đọc truyện hentai, manhwa 18+, vietsub, không quảng cáo, đa dạng thể loại',
                    'seo_home_image' => '/vendor/theme-vinahentai/images/logo2.webp',
                ],
            );
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateThemeVinahentaiSitemapCommand::class,
                ThemeVinahentaiSeedCommand::class,
            ]);
        }
    }
}
