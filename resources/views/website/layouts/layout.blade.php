<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1e5a45">
    <title>@yield('title', 'وام‌جو')</title>
    <meta name="description" content="@yield('description')">

    {{-- Bootstrap 5 RTL + فونت پشتیبان (در صورت نبودن فایل‌های یکان بخ) --}}

    <link rel="stylesheet" href="{{ asset('website/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('website/css/line-awesome.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('website/css/fonts.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('website/css/main.css') }}" type="text/css">

    @stack('styles')
</head>
<body class="@yield('body_class')" data-auth="{{ auth()->check() ? 1 : 0 }}"
      data-verified="{{ auth()->check() && auth()->user()->is_verified ? 1 : 0 }}">

@include('website.partials.Header')

<main>
    @yield('main')
</main>

@include('website.partials.Footer')

{{--@guest--}}
{{--    @include('website.partials.modals.login')--}}
{{--@endguest--}}

@include('website.partials.mobile-nav')
@include('website.partials.modals.login')

<script src="{{ asset('website/js/jquery-3.6.4.min.js') }}"></script>
<script src="{{ asset('website/js/bootstrap.bundle.js') }}"></script>
<script src="{{ asset('website/js/notyf.min.js') }}"></script>
<script src="{{ asset('website/js/main.js') }}"></script>
@stack('scripts')
</body>
</html>
