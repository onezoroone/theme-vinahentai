<?php

namespace Nqt\ThemeVinahentai\Console\Commands;

use App\Models\Author;
use App\Models\Chapter;
use App\Models\Genre;
use App\Models\Manga;
use App\Models\Translator;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Sitemap as SitemapTag;
use Spatie\Sitemap\Tags\Url;

class GenerateThemeVinahentaiSitemapCommand extends Command
{
    protected $signature = 'theme-vinahentai:sitemap
        {--index=sitemap.xml : File sitemap index (relative public/ hoặc tuyệt đối)}
        {--manga-per-file=20000 : So URL manga moi file}
        {--chapter-per-file=20000 : So URL chapter moi file}';

    protected $description = 'Sinh sitemapindex + cac sitemap con cho theme-vinahentai.';

    public function handle(): int
    {
        $indexPath = $this->resolveOutputPath((string) $this->option('index'));
        $outputDir = dirname($indexPath);
        if (! is_dir($outputDir)) {
            @mkdir($outputDir, 0775, true);
        }

        $files = [];
        $files[] = $this->writeStaticSitemap($outputDir);
        $files[] = $this->writeAuthorsSitemap($outputDir);
        $files[] = $this->writeTranslatorsSitemap($outputDir);
        $files[] = $this->writeGenresSitemap($outputDir);
        $files = array_merge($files, $this->writeMangaSitemaps($outputDir, (int) $this->option('manga-per-file')));
        $files = array_merge($files, $this->writeChapterSitemaps($outputDir, (int) $this->option('chapter-per-file')));

        $index = SitemapIndex::create();
        foreach (array_filter($files) as $filePath) {
            $loc = $this->publicUrlFromPath($filePath);
            if ($loc === null) {
                continue;
            }
            $index->add(
                SitemapTag::create($loc)->setLastModificationDate(
                    Carbon::createFromTimestamp((int) @filemtime($filePath))
                )
            );
        }
        $index->writeToFile($indexPath);

        $this->info('Da tao sitemap index: '.$indexPath);

        return self::SUCCESS;
    }

    private function writeStaticSitemap(string $outputDir): string
    {
        $sitemap = Sitemap::create();
        $staticRouteNames = [
            'home',
            'search',
            'search.advanced',
            'random',
            'leaderboard.manga',
            'leaderboard.member',
            'leaderboard.waifu',
            'leaderboard.translator',
        ];

        foreach ($staticRouteNames as $routeName) {
            if (! \Route::has($routeName)) {
                continue;
            }
            $sitemap->add(Url::create(route($routeName)));
        }

        $path = $outputDir.'/sitemap-static.xml';
        $sitemap->writeToFile($path);

        return $path;
    }

    private function writeMangaSitemaps(string $outputDir, int $perFile): array
    {
        $perFile = max(1000, $perFile);
        $query = Manga::query()->select(['id', 'slug', 'updated_at']);
        if (Schema::hasColumn('mangas', 'published_at')) {
            $query->whereNotNull('published_at');
        }

        $files = [];
        $page = 1;
        $countInFile = 0;
        $sitemap = Sitemap::create();

        $query->chunkById(1000, function ($mangas) use (&$sitemap, &$files, &$page, &$countInFile, $outputDir, $perFile): void {
            foreach ($mangas as $manga) {
                if ($countInFile >= $perFile) {
                    $path = $outputDir.'/sitemap-manga-'.$page.'.xml';
                    $sitemap->writeToFile($path);
                    $files[] = $path;
                    $page++;
                    $countInFile = 0;
                    $sitemap = Sitemap::create();
                }

                $url = Url::create($manga->getUrl());
                if ($manga->updated_at !== null) {
                    $url->setLastModificationDate($manga->updated_at);
                }
                $sitemap->add($url);
                $countInFile++;
            }
        });

        if ($countInFile > 0) {
            $path = $outputDir.'/sitemap-manga-'.$page.'.xml';
            $sitemap->writeToFile($path);
            $files[] = $path;
        }

        return $files;
    }

    private function writeChapterSitemaps(string $outputDir, int $perFile): array
    {
        $perFile = max(1000, $perFile);
        $query = Chapter::query()->select(['id', 'manga_id', 'slug', 'updated_at']);
        if (Schema::hasColumn('chapters', 'published_at')) {
            $query->whereNotNull('published_at');
        }

        $files = [];
        $page = 1;
        $countInFile = 0;
        $sitemap = Sitemap::create();

        $query->with(['manga:id,slug'])->chunkById(1000, function ($chapters) use (&$sitemap, &$files, &$page, &$countInFile, $outputDir, $perFile): void {
            foreach ($chapters as $chapter) {
                if ($chapter->manga === null) {
                    continue;
                }
                if ($countInFile >= $perFile) {
                    $path = $outputDir.'/sitemap-chapters-'.$page.'.xml';
                    $sitemap->writeToFile($path);
                    $files[] = $path;
                    $page++;
                    $countInFile = 0;
                    $sitemap = Sitemap::create();
                }
                $url = Url::create($chapter->getUrl());
                if ($chapter->updated_at !== null) {
                    $url->setLastModificationDate($chapter->updated_at);
                }
                $sitemap->add($url);
                $countInFile++;
            }
        });

        if ($countInFile > 0) {
            $path = $outputDir.'/sitemap-chapters-'.$page.'.xml';
            $sitemap->writeToFile($path);
            $files[] = $path;
        }

        return $files;
    }

    private function writeAuthorsSitemap(string $outputDir): string
    {
        $sitemap = Sitemap::create();
        Author::query()
            ->select(['id', 'slug', 'updated_at'])
            ->chunkById(500, function ($authors) use ($sitemap): void {
                foreach ($authors as $author) {
                    $url = Url::create($author->getUrl());
                    if ($author->updated_at !== null) {
                        $url->setLastModificationDate($author->updated_at);
                    }
                    $sitemap->add($url);
                }
            });

        $path = $outputDir.'/sitemap-authors.xml';
        $sitemap->writeToFile($path);

        return $path;
    }

    private function writeTranslatorsSitemap(string $outputDir): string
    {
        $sitemap = Sitemap::create();
        Translator::query()
            ->select(['id', 'slug', 'updated_at'])
            ->chunkById(500, function ($translators) use ($sitemap): void {
                foreach ($translators as $translator) {
                    $url = Url::create($translator->getUrl());
                    if ($translator->updated_at !== null) {
                        $url->setLastModificationDate($translator->updated_at);
                    }
                    $sitemap->add($url);
                }
            });

        $path = $outputDir.'/sitemap-translators.xml';
        $sitemap->writeToFile($path);

        return $path;
    }

    private function writeGenresSitemap(string $outputDir): string
    {
        $sitemap = Sitemap::create();
        Genre::query()
            ->select(['id', 'slug', 'updated_at'])
            ->chunkById(500, function ($genres) use ($sitemap): void {
                foreach ($genres as $genre) {
                    $url = Url::create($genre->getUrl());
                    if ($genre->updated_at !== null) {
                        $url->setLastModificationDate($genre->updated_at);
                    }
                    $sitemap->add($url);
                }
            });

        $path = $outputDir.'/sitemap-genres.xml';
        $sitemap->writeToFile($path);

        return $path;
    }

    private function resolveOutputPath(string $optionPath): string
    {
        $path = trim($optionPath);
        if ($path === '') {
            return public_path('sitemap.xml');
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        return public_path(ltrim($path, '/'));
    }

    private function publicUrlFromPath(string $absolutePath): ?string
    {
        $publicRoot = rtrim(public_path(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        if (! str_starts_with($absolutePath, $publicRoot)) {
            return null;
        }

        $relative = str_replace(DIRECTORY_SEPARATOR, '/', substr($absolutePath, strlen($publicRoot)));

        return rtrim((string) config('app.url', ''), '/').'/'.$relative;
    }
}
