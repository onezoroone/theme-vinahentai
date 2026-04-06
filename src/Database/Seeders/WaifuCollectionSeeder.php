<?php

namespace Nqt\ThemeVinahentai\Database\Seeders;

use App\Models\Waifu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use JsonException;
use RuntimeException;

/**
 * Seed waifu từ JSON trong package (database/data/waifu_collection.json).
 * Ảnh đọc từ resources/assets/images/waifu/{waifuId}.webp; nếu có thì copy sang
 * public/vendor/theme-vinahentai/images/waifu/. Nếu không có trong theme nhưng đã có
 * sẵn trong public thì giữ nguyên (không tải mạng).
 *
 * Chạy: php artisan db:seed --class=Nqt\\ThemeVinahentai\\Database\\Seeders\\WaifuCollectionSeeder
 */
class WaifuCollectionSeeder extends Seeder
{
    private function packageRoot(): string
    {
        return dirname(__DIR__, 3);
    }

    public function run(): void
    {
        $path = $this->packageRoot().'/database/data/waifu_collection.json';
        if (! is_file($path)) {
            throw new RuntimeException('Thiếu file dữ liệu waifu trong theme: '.$path);
        }

        try {
            /** @var array<string, mixed> $data */
            $data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('File waifu_collection.json không hợp lệ: '.$e->getMessage(), 0, $e);
        }

        /** @var array<string, mixed> $tiers */
        $tiers = data_get($data, 'waifuCollection.tiers', []);
        if ($tiers === []) {
            throw new RuntimeException('waifuCollection.tiers rỗng.');
        }

        Waifu::query()->delete();

        $sourceDir = $this->packageRoot().'/resources/assets/images/waifu';
        $destDir = public_path('vendor/theme-vinahentai/images/waifu');
        File::ensureDirectoryExists($sourceDir);
        File::ensureDirectoryExists($destDir);

        foreach (['tier5', 'tier4', 'tier3'] as $tierKey) {
            $tier = $tiers[$tierKey] ?? null;
            if (! is_array($tier)) {
                continue;
            }
            $tierStars = (int) ($tier['stars'] ?? 0);
            $waifus = $tier['waifus'] ?? [];
            if (! is_array($waifus)) {
                continue;
            }
            foreach ($waifus as $w) {
                if (! is_array($w)) {
                    continue;
                }
                $waifuId = (string) ($w['waifuId'] ?? '');
                $name = (string) ($w['name'] ?? '');
                $imageUrl = (string) ($w['image'] ?? '');
                if ($waifuId === '' || $name === '' || $imageUrl === '') {
                    continue;
                }
                $rarity = (int) ($w['stars'] ?? $tierStars);
                $filename = $waifuId.'.webp';
                $fromTheme = $sourceDir.'/'.$filename;
                $inPublic = $destDir.'/'.$filename;

                if (File::exists($fromTheme)) {
                    File::copy($fromTheme, $inPublic);
                } elseif (! File::exists($inPublic)) {
                    throw new RuntimeException(
                        "Thiếu ảnh waifu {$filename}. Đặt file vào theme: {$fromTheme} hoặc {$inPublic}"
                    );
                }

                $description = null;
                if (array_key_exists('expBuff', $w) || array_key_exists('goldBuff', $w)) {
                    try {
                        $description = json_encode([
                            'exp_buff' => $w['expBuff'] ?? null,
                            'gold_buff' => $w['goldBuff'] ?? null,
                        ], JSON_THROW_ON_ERROR);
                    } catch (JsonException) {
                        $description = null;
                    }
                }

                Waifu::query()->create([
                    'name' => $name,
                    'slug' => 'w-'.$waifuId,
                    'rarity' => $rarity,
                    'image' => 'vendor/theme-vinahentai/images/waifu/'.$filename,
                    'description' => $description,
                    'is_active' => true,
                ]);
            }
        }

        $this->command?->info('WaifuCollectionSeeder: đã seed từ theme (ảnh trong package hoặc public).');
    }
}
