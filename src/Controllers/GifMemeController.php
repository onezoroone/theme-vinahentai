<?php

namespace Nqt\ThemeVinahentai\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use SplFileInfo;

final class GifMemeController extends Controller
{
    /**
     * Danh sách thư mục meme + ảnh (đọc từ resources/assets/images/gif-meme của theme).
     * Cần publish asset: php artisan vendor:publish --tag=theme-vinahentai-assets
     */
    public function manifest(): JsonResponse
    {
        $data = Cache::remember('theme_vinahentai.gif_meme_manifest_v1', 3600, function (): array {
            return $this->buildManifest();
        });

        return response()->json($data);
    }

    /**
     * @return array{categories: array<int, array<string, mixed>>}
     */
    private function buildManifest(): array
    {
        $base = realpath(__DIR__.'/../../resources/assets/images/gif-meme');
        if ($base === false || ! is_dir($base)) {
            return ['categories' => []];
        }

        $categories = [];

        foreach (File::directories($base) as $dirPath) {
            $slug = basename($dirPath);
            if (str_starts_with($slug, '.')) {
                continue;
            }

            $files = collect(File::files($dirPath))
                ->filter(fn (SplFileInfo $f): bool => (bool) preg_match('/\.(gif|jpe?g|png|webp)$/iu', $f->getFilename()))
                ->sortBy(fn (SplFileInfo $f): string => $f->getFilename())
                ->values();

            if ($files->isEmpty()) {
                continue;
            }

            $thumbFile = $files->first(
                fn (SplFileInfo $f): bool => (bool) preg_match('/thumb|thumnail/i', $f->getFilename())
            ) ?? $files->first();

            $images = $files->map(function (SplFileInfo $f) use ($slug): array {
                $name = $f->getFilename();

                return [
                    'path' => $slug.'/'.$name,
                    'url' => asset('vendor/theme-vinahentai/images/gif-meme/'.$slug.'/'.rawurlencode($name)),
                ];
            })->all();

            $categories[] = [
                'id' => $slug,
                'label' => Str::title(str_replace('-', ' ', $slug)),
                'thumb' => asset('vendor/theme-vinahentai/images/gif-meme/'.$slug.'/'.rawurlencode($thumbFile->getFilename())),
                'images' => $images,
            ];
        }

        usort($categories, fn (array $a, array $b): int => strcmp((string) $a['id'], (string) $b['id']));

        return ['categories' => $categories];
    }
}
