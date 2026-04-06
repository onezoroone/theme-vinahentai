<?php

namespace Nqt\ThemeVinahentai\Database\Seeders;

use App\Models\Theme;
use Backpack\MenuCRUD\app\Models\MenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Seed menu mặc định cho MenuCRUD — chỉ chạy khi theme đang active là theme-vinahentai.
 * Gọi qua: php artisan db:seed (hoặc --class=...).
 */
class ThemeVinahentaiMenuSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('themes')) {
            $this->command?->warn('ThemeVinahentaiMenuSeeder: bỏ qua (chưa có bảng themes).');

            return;
        }

        $active = Theme::query()->where('is_active', true)->first();
        if (! $active || $active->slug !== 'theme-vinahentai') {
            $this->command?->info('ThemeVinahentaiMenuSeeder: bỏ qua (theme đang active không phải theme-vinahentai).');

            return;
        }

        if (! Schema::hasTable('menu_items')) {
            $this->command?->warn('ThemeVinahentaiMenuSeeder: bỏ qua (chưa có bảng menu_items).');

            return;
        }

        if (MenuItem::query()->count() > 0) {
            $this->command?->info('ThemeVinahentaiMenuSeeder: bỏ qua (đã có menu).');

            return;
        }

        MenuItem::create([
            'name' => 'Trang chủ',
            'type' => 'internal_link',
            'link' => '/',
            'page_id' => null,
            'parent_id' => null,
        ]);

        $theLoai = MenuItem::create([
            'name' => 'Thể loại',
            'type' => 'internal_link',
            'link' => '#',
            'page_id' => null,
            'parent_id' => null,
        ]);

        foreach ([
            ['name' => '🏆 BXH', 'link' => '/leaderboard/manga'],
            ['name' => 'Triệu hồi Waifu', 'link' => '/waifu/summon'],
            ['name' => 'Đăng truyện', 'link' => '/truyen-hentai/manage'],
            ['name' => 'Random', 'link' => '/random'],
        ] as $row) {
            MenuItem::create([
                'name' => $row['name'],
                'type' => 'internal_link',
                'link' => $row['link'],
                'page_id' => null,
                'parent_id' => null,
            ]);
        }

        $this->command?->info('ThemeVinahentaiMenuSeeder: đã tạo menu mặc định.');
    }
}
