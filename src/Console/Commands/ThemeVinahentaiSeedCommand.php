<?php

namespace Nqt\ThemeVinahentai\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Nqt\ThemeVinahentai\Database\Seeders\ShopItemSeeder;
use Nqt\ThemeVinahentai\Database\Seeders\ThemeVinahentaiLevelSeeder;
use Nqt\ThemeVinahentai\Database\Seeders\ThemeVinahentaiMenuSeeder;
use Nqt\ThemeVinahentai\Database\Seeders\ThemeVinahentaiWaifuSeeder;
use Nqt\ThemeVinahentai\Database\Seeders\WaifuCollectionSeeder;

class ThemeVinahentaiSeedCommand extends Command
{
    protected $signature = 'theme-vinahentai:seed {--force : Bỏ qua xác nhận khi đang production}';

    protected $description = 'Chạy toàn bộ 5 seeder của package theme-vinahentai.';

    public function handle(): int
    {
        $seeders = [
            ShopItemSeeder::class,
            WaifuCollectionSeeder::class,
            ThemeVinahentaiWaifuSeeder::class,
            ThemeVinahentaiMenuSeeder::class,
            ThemeVinahentaiLevelSeeder::class,
        ];

        foreach ($seeders as $seederClass) {
            $this->line("=> Seeding: {$seederClass}");

            $exitCode = Artisan::call('db:seed', [
                '--class' => $seederClass,
                '--force' => (bool) $this->option('force'),
            ], $this->output);

            if ($exitCode !== self::SUCCESS) {
                $this->error("Seeder thất bại: {$seederClass}");

                return self::FAILURE;
            }
        }

        $this->info('Đã chạy xong 5 seeder của theme-vinahentai.');

        return self::SUCCESS;
    }
}
