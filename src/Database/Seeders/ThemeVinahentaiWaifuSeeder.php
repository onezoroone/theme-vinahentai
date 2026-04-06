<?php

namespace Nqt\ThemeVinahentai\Database\Seeders;

use App\Models\Waifu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Seed waifu từ ảnh có sẵn trong theme: resources/assets/images/waifu.
 * Gán rarity 1–5 sao luân phiên (5,4,3,2,1,5,4,...) theo thứ tự tên file.
 *
 * Sau khi chạy: php artisan vendor:publish --tag=theme-vinahentai-assets (ảnh vào public/vendor/theme-vinahentai).
 */
class ThemeVinahentaiWaifuSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('waifus')) {
            $this->command?->warn('ThemeVinahentaiWaifuSeeder: bỏ qua (chưa có bảng waifus).');

            return;
        }

        $waifuDir = realpath(__DIR__.'/../../../resources/assets/images/waifu');
        if ($waifuDir === false || ! is_dir($waifuDir)) {
            $this->command?->warn('ThemeVinahentaiWaifuSeeder: không tìm thấy thư mục resources/assets/images/waifu trong package theme.');

            return;
        }

        $paths = [];
        foreach (['webp', 'jpg', 'jpeg', 'png'] as $ext) {
            $paths = array_merge($paths, glob($waifuDir.'/*.'.$ext) ?: []);
        }

        $paths = collect($paths)
            ->filter(function (string $path): bool {
                $base = basename($path);

                // Bỏ qua asset UI (vd: 5-exp.webp, 10-exp.webp).
                return ! preg_match('/-exp\./i', $base);
            })
            ->sort()
            ->values();

        if ($paths->isEmpty()) {
            $this->command?->warn('ThemeVinahentaiWaifuSeeder: không có file ảnh waifu hợp lệ.');

            return;
        }

        // Luân phiên đủ 5 mức sao theo thứ tự file.
        $rarityCycle = [5, 4, 3, 2, 1];

        foreach ($paths as $index => $fullPath) {
            $filename = basename($fullPath);
            $stem = pathinfo($filename, PATHINFO_FILENAME);
            $slug = Str::slug($stem);
            if ($slug === '') {
                continue;
            }

            $rarity = $rarityCycle[$index % 5];
            $name = Str::headline(str_replace(['-', '_'], ' ', $stem));

            // Đường dẫn public sau khi publish tag theme-vinahentai-assets.
            $image = 'vendor/theme-vinahentai/images/waifu/'.$filename;

            Waifu::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'rarity' => $rarity,
                    'image' => $image,
                    'description' => null,
                    'is_active' => true,
                ],
            );
        }

        $this->command?->info('ThemeVinahentaiWaifuSeeder: đã đồng bộ '.$paths->count().' waifu.');
    }
}
