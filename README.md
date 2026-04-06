# theme-vinahentai

Package Composer **`nqt/theme-vinahentai`**: theme giao diện VinaHentai cho Laravel 13.

| Thành phần                         | Giá trị                           |
| ---------------------------------- | --------------------------------- |
| Thư mục monorepo                   | `packages/theme-vinahentai`       |
| Namespace PHP                      | `Nqt\ThemeVinahentai`             |
| Tiền tố Blade                      | `theme-vinahentai::`              |
| Slug đăng ký theme (dynamic theme) | `theme-vinahentai`                |
| Asset đã publish                   | `public/vendor/theme-vinahentai/` |
| Tag publish Composer               | `theme-vinahentai-assets`         |

## Cài trong dự án

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "./packages/theme-vinahentai",
      "options": { "symlink": true }
    }
  ],
  "require": {
    "nqt/theme-vinahentai": "^1.0"
  }
}
```

```bash
composer update nqt/theme-vinahentai
php artisan vendor:publish --tag=theme-vinahentai-assets --force
php artisan view:clear
```

Nếu trước đây bạn dùng `public/vendor/theme-sample/`, hãy **publish lại** hoặc copy sang `public/vendor/theme-vinahentai/` để `asset('vendor/theme-vinahentai/...')` hoạt động.

Trong bảng `themes` (Backpack / dynamic theme), cập nhật **slug** theme đang bật thành `theme-vinahentai` nếu trước đó là `theme-sample`.

## Build JS (tùy chọn)

```bash
cd packages/theme-vinahentai
npm install
npm run build:js
```

File nguồn: `resources/assets/js/main.entry.js` → bundle `resources/assets/js/main.js` (sau đó publish asset).

## Seed trong package

**Cửa hàng (4 vật phẩm):**

```bash
php artisan db:seed --class=Nqt\\ThemeVinahentai\\Database\\Seeders\\ShopItemSeeder
```

**Waifu (JSON + ảnh):**

```bash
php artisan db:seed --class=Nqt\\ThemeVinahentai\\Database\\Seeders\\WaifuCollectionSeeder
php artisan db:seed --class=Nqt\\ThemeVinahentai\\Database\\Seeders\\ShopItemSeeder && php artisan db:seed --class=Nqt\\ThemeVinahentai\\Database\\Seeders\\WaifuCollectionSeeder && php artisan db:seed --class=Nqt\\ThemeVinahentai\\Database\\Seeders\\ThemeVinahentaiMenuSeeder && php artisan db:seed --class=Nqt\\ThemeVinahentai\\Database\\Seeders\\ThemeVinahentaiLevelSeeder
```

**Menu / level (khi theme active slug = `theme-vinahentai`):**

- `Nqt\ThemeVinahentai\Database\Seeders\ThemeVinahentaiMenuSeeder`
- `Nqt\ThemeVinahentai\Database\Seeders\ThemeVinahentaiLevelSeeder`

## Lệnh sinh sitemap

Sinh **sitemap index** + các file sitemap con:
- `sitemap-static.xml`
- `sitemap-genres.xml`
- `sitemap-authors.xml`
- `sitemap-translators.xml`
- `sitemap-manga-1.xml`, `sitemap-manga-2.xml`, ...
- `sitemap-chapters-1.xml`, `sitemap-chapters-2.xml`, ...

```bash
php artisan theme-vinahentai:sitemap
```

Tùy chỉnh file index và kích thước tách trang:

```bash
php artisan theme-vinahentai:sitemap --index=sitemap.xml
php artisan theme-vinahentai:sitemap --index=seo/sitemap-index.xml --manga-per-file=15000 --chapter-per-file=15000
```

### Ảnh cửa hàng (`ShopItemSeeder`)

Đặt WebP tại `public/vendor/theme-vinahentai/images/` (tên file khớp `image_path` trong seeder), hoặc sửa seeder cho đúng đường dẫn thực tế.

## Yêu cầu

- PHP ^8.3
- laravel/framework ^13.0

## License

MIT
