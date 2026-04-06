<?php

namespace Nqt\ThemeVinahentai\Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ThemeVinahentaiLevelSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('themes') || ! Schema::hasTable('levels')) {
            $this->command?->warn('ThemeVinahentaiLevelSeeder: bỏ qua (thiếu bảng themes hoặc levels).');

            return;
        }

        $activeTheme = Theme::query()->where('is_active', true)->first();
        if (! $activeTheme || $activeTheme->slug !== 'theme-vinahentai') {
            $this->command?->info('ThemeVinahentaiLevelSeeder: bỏ qua (theme active không phải theme-vinahentai).');

            return;
        }

        $requiredExperiences = [250, 750, 1500, 3000, 6000, 12000, 24000, 48000, 96000];
        $levelNames = [
            1 => 'Nhập Lọ',
            2 => 'Luyện Lọ',
            3 => 'Cuồng Lọ',
            4 => 'Bá Lọ',
            5 => 'Lọ Vương',
            6 => 'Lọ Tông',
            7 => 'Lọ Tôn',
            8 => 'Lọ Thánh',
            9 => 'Lọ Đế',
        ];

        $rows = [];
        foreach ($requiredExperiences as $index => $requiredExperience) {
            $level = $index + 1;
            // Đồng bộ với LevelBreakthroughService: cấp 1→2 = 100%, mỗi cấp sau giảm 10%.
            $breakthroughRate = max(0, 100 - ($level - 1) * 10);
            $rows[] = [
                'level' => $level,
                'name' => $levelNames[$level],
                'required_experience' => $requiredExperience,
                'reward_points' => 0,
                'requires_breakthrough' => true,
                'breakthrough_success_rate' => $breakthroughRate,
                'breakthrough_points_cost' => 0,
                'breakthrough_gold_cost' => 0,
                'badge' => $level === 4
                    ? 'vendor/theme-vinahentai/images/title/4-badge.webp'
                    : "vendor/theme-vinahentai/images/title/{$level}.webp",
                'image' => "vendor/theme-vinahentai/images/title/{$level}.webp",
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('levels')->upsert(
            $rows,
            ['level'],
            [
                'name',
                'required_experience',
                'reward_points',
                'requires_breakthrough',
                'breakthrough_success_rate',
                'breakthrough_points_cost',
                'breakthrough_gold_cost',
                'badge',
                'image',
                'updated_at',
            ]
        );

        $this->command?->info('ThemeVinahentaiLevelSeeder: đã seed 9 cấp bậc.');
    }
}
