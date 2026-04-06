<!DOCTYPE html>
<html lang="{{ config('app.locale') }}" style="--cdn-base:{{ config('app.url') }};--cdn-bg-body:url({{ asset('vendor/theme-vinahentai/images/bgreal2.webp') }})">

<head>
    <meta charset="utf-8">
    {!! active_theme_config('custom_head_tags', '') !!}
    <meta http-equiv="content-language" content="{{ config('app.locale') }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta property="fb:app_id" content="{{ active_theme_config('social_facebook_app_id') }}" />
    <link rel="shortcut icon" href="{{ active_theme_config('seo_home_shortcut_icon') }}" type="image/png" />

    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600;700&amp;display=swap">
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Irish+Grover&amp;display=swap">
    <link rel="stylesheet" href="{{ asset('vendor/theme-vinahentai/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/theme-vinahentai/css/toast.css') }}">
    {!! SEO::generate() !!}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
    @stack('header')
    {!! active_theme_config('custom_css', '') !!}
    {!! active_theme_config('custom_js', '') !!}
</head>

<body {!! active_theme_config('body_attributes', '') !!}>
    <div id="fui-toast"></div>
    @include('theme-vinahentai::layout.header')
    @yield('body')

    {{-- @include('theme-vinahentai::layout.footer') --}}
    {!! active_theme_config('custom_html_footer', '') !!}
    {!! active_theme_config('custom_js', '') !!}

    <script src="{{ asset('vendor/theme-vinahentai/js/main.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lozad.js/1.0.8/lozad.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/gh/lelinh014756/fui-toast-js@master/assets/js/toast@1.0.1/fuiToast.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
          const observer = lozad();
          observer.observe();
        });
    </script>
    @stack('scripts')
    {!! active_theme_config('custom_js', '') !!}
</body>

</html>
