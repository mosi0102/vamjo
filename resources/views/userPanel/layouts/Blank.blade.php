<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1e5a45">
    <title>@yield('title') | وام‌جو</title>
    <script>document.documentElement.classList.add('js');</script>
    <link rel="stylesheet" href="{{ asset('website/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('website/css/main.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('panel/css/main.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('website/css/fonts.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('website/css/loader.css') }}" type="text/css">
    @stack('styles')
</head>
<body class="panel-body pn-blank @yield('body_class')">
@include('website.partials.loader')
<header class="pr-top">
    <a href="{{ url('/') }}" aria-label="وام‌جو"><img src="{{ asset('Website/img/logo.png') }}" alt="وام‌جو" height="46"></a>
</header>
<main class="pr-wrap">@yield('main')</main>

<script src="{{ asset('website/js/jquery-3.6.4.min.js') }}"></script>
<script src="{{ asset('website/js/bootstrap.bundle.js') }}"></script>
<script src="{{ asset('panel/js/main.js') }}"></script>
<script src="{{ asset('website/js/loader.js') }}"></script>
@stack('scripts')
</body>
</html>
