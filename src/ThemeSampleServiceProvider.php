<?php

namespace Nqt\ThemeSample;

use Illuminate\Support\ServiceProvider;

class ThemeSampleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $viewPath = __DIR__.'/../resources/views';

        // Load view theo namespace để tiện debug khi cần.
        $this->loadViewsFrom($viewPath, 'theme-sample');

        // Đăng ký theme vào bảng themes nếu app có helper.
        if (function_exists('theme_register')) {
            theme_register(
                name: 'Theme Sample',
                slug: 'theme-sample',
                package: 'nqt/theme-sample',
                provider: self::class,
                viewPath: $viewPath,
                meta: [
                    'author' => 'NQT',
                    'description' => 'Theme mẫu demo override view welcome.',
                ]
            );
        }
    }
}
