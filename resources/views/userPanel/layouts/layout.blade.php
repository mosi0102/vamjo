<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1e5a45">
    <title>@yield('title', 'پنل کاربری') | وام‌جو</title>

    {{-- جلوگیری از پرش منو هنگام لود: وضعیت جمع‌شدن سایدبار قبل از رندر اعمال می‌شود --}}
    <script>
        (function (d) { d.classList.add('js');
            try { if (localStorage.getItem('pn-collapsed') === '1' && innerWidth >= 992) d.classList.add('pn-collapsed'); } catch (e) {}
        })(document.documentElement);
    </script>

    <link rel="stylesheet" href="{{ asset('website/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('website/css/line-awesome.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('website/css/fonts.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('website/css/main.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('panel/css/main.css') }}" type="text/css">
    @stack('styles')
</head>
<body class="panel-body @yield('body_class')">
@php
    // کاربر جاری (برای دمو مقدار پیش‌فرض دارد). نقش: user | admin | support
    $u = auth()->user();
    $role = $u->role ?? 'user';
    $pn = (object) [
      'name' => $u->name ?? 'محمد حسین طاهری',
      'verified' => (bool) ($u->is_verified ?? false),
      'role' => $role,
      'role_label' => config('panel.roles')[$role] ?? '',
      'avatar' => $u->avatar ?? null,
    ];
@endphp

<div class="pn-app">
    @include('userPanel.partials.header')

    <div class="pn-body">
        @include('userPanel.partials.sidebar')
        <div class="pn-overlay" id="pnOverlay"></div>

        <main class="pn-main" id="pnMain">
            @yield('main')
        </main>
    </div>
</div>

@include('userPanel.partials.bottom-nav')

<script src="{{ asset('website/js/jquery-3.6.4.min.js') }}"></script>
<script src="{{ asset('website/js/bootstrap.bundle.js') }}"></script>
<script src="{{ asset('website/js/notyf.min.js') }}"></script>
<script src="{{ asset('panel/js/main.js') }}"></script>
@stack('scripts')
</body>
</html>
