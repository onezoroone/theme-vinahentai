<?php

namespace Nqt\ThemeVinahentai\Database\Seeders;

use App\Models\ShopItem;
use Illuminate\Database\Seeder;

/**
 * Seed 4 vật phẩm cửa hàng mẫu (bảng shop_items).
 * Ảnh: đặt file WebP vào public/vendor/theme-vinahentai/images/ (xem README package).
 *
 * Chạy: php artisan db:seed --class=Nqt\\ThemeVinahentai\\Database\\Seeders\\ShopItemSeeder
 */
class ShopItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'code' => 'lo-vuong-bi-kip',
                'name' => 'Bí kíp Lọ Vương',
                'type' => 'exp_protection',
                'summary' => 'Vật phẩm tiêu hao, chỉ dùng khi đột phá Lọ Vương.',
                'description' => 'Khi bật sử dụng và đột phá thất bại, bí kíp này sẽ giữ lại 80% tu vi Bá Lọ rồi tự tiêu hao.',
                'image_path' => 'vendor/theme-vinahentai/images/bklv.webp',
                'price_points' => 50,
                'price_gold' => 0,
                'protection_percent' => 80,
                'success_rate_bonus' => 0,
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'code' => 'death-note',
                'name' => 'Death Note',
                'type' => 'other',
                'summary' => 'Đồ trang trí hiếm, mang khí chất tử thần tuyệt đối.',
                'description' => 'Bỏ qua mọi định luật vật lý và logic thông thường. Chỉ cần biết tên và khuôn mặt, bạn có thể định đoạt cái chết của bất kỳ ai — bất kể đối thủ mạnh đến đâu. Khi được trưng bày, vật phẩm này như một lời tuyên bố: bạn không cần sức mạnh, bạn là luật.',
                'image_path' => 'vendor/theme-vinahentai/images/death note.webp',
                'price_points' => 300,
                'price_gold' => 0,
                'protection_percent' => 0,
                'success_rate_bonus' => 0,
                'is_active' => true,
                'sort_order' => 20,
            ],
            [
                'code' => 'mera-mera-nomi',
                'name' => 'Mera Mera no Mi',
                'type' => 'other',
                'summary' => 'Đồ trang trí hệ hỏa, đại diện cho sức mạnh nguyên tố cấp cao.',
                'description' => 'Trái Ác Quỷ hệ Logia cho phép người sở hữu hóa thành lửa, điều khiển hỏa diễm và gần như vô hiệu đòn đánh vật lý thường nếu không có Haki hoặc năng lực khắc chế.',
                'image_path' => 'vendor/theme-vinahentai/images/meramera nomi.webp',
                'price_points' => 100,
                'price_gold' => 0,
                'protection_percent' => 0,
                'success_rate_bonus' => 0,
                'is_active' => true,
                'sort_order' => 30,
            ],
            [
                'code' => 'pokemon-ball',
                'name' => 'Pokemon Ball',
                'type' => 'other',
                'summary' => 'Đồ trang trí phổ thông, nhỏ gọn nhưng cực kỳ dễ nhận ra.',
                'description' => 'Quả cầu bắt quái quen thuộc của thế giới Pokemon. Dùng để trưng bày trong khung bình luận của bạn.',
                'image_path' => 'vendor/theme-vinahentai/images/pokemonball.webp',
                'price_points' => 50,
                'price_gold' => 0,
                'protection_percent' => 0,
                'success_rate_bonus' => 0,
                'is_active' => true,
                'sort_order' => 40,
            ],
        ];

        foreach ($items as $row) {
            ShopItem::query()->updateOrCreate(
                ['code' => $row['code']],
                $row
            );
        }
    }
}
