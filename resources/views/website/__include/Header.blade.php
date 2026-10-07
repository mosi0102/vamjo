{{-- Header: دسکتاپ = منوی کامل | موبایل/تبلت = هدر ساده اپلیکیشنی + شیت «بیشتر» --}}
<header class="site-header" id="header">

    {{-- Desktop --}}
    <div class="container d-none d-lg-flex align-items-center desk-bar">
        <a class="brand-link" href="{{ url('/') }}" aria-label="وام‌جو">
            <img class="logo" src="{{ asset('website/img/logo.png') }}" alt="وام‌جو" height="44">
        </a>
        <ul class="nav-menu list-unstyled mb-0">
            <li><a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}">صفحه نخست</a></li>
            <li><a class="nav-link {{ request()->is('ads*') ? 'active' : '' }}" href="{{ url('/ads') }}">آگهی‌های وام</a></li>
            <li><a class="nav-link" href="{{ url('/#banks') }}">بانک‌های تحت پوشش</a></li>
            <li><a class="nav-link {{ request()->is('rules') ? 'active' : '' }}" href="{{ url('/rules') }}">قوانین و مقررات</a></li>
            <li><a class="nav-link" href="{{ url('/#faq') }}">سوالات متداول</a></li>
        </ul>
        <div class="header-actions ms-auto">
            @guest
                <a href="{{ url('/login') }}" class="btn btn-ghost" data-bs-toggle="modal" data-bs-target="#loginModal">ورود | ثبت نام</a>
            @else
                <a href="{{ url('/panel') }}" class="btn btn-ghost">پنل کاربری</a>
            @endguest
            <a href="{{ url('/ads/create') }}" class="btn btn-brand" data-login-required><span class="plus-icon" aria-hidden="true">+</span> ثبت آگهی</a>
        </div>

    </div>

    {{-- Mobile / Tablet --}}
    <div class="container d-flex d-lg-none align-items-center m-bar">
        <a href="{{ url('/') }}" aria-label="وام‌جو"><img class="logo" src="{{ asset('assets/images/logo.png') }}" alt="وام‌جو" height="36"></a>
        <div class="ms-auto d-flex align-items-center gap-2">
            @guest
                <a href="{{ url('/login') }}" class="btn btn-brand btn-sm m-login" data-bs-toggle="modal" data-bs-target="#loginModal">ورود | ثبت نام</a>
            @endguest
            <button class="icon-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#moreSheet" aria-controls="moreSheet" aria-label="منوی بیشتر">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="4" width="6.5" height="6.5" rx="2"/><rect x="13.5" y="4" width="6.5" height="6.5" rx="2"/><rect x="4" y="13.5" width="6.5" height="6.5" rx="2"/><rect x="13.5" y="13.5" width="6.5" height="6.5" rx="2"/></svg>
            </button>
        </div>
    </div>
</header>


{{-- شیت پایین‌آمدنی برای صفحات اولویت دوم --}}
<div class="offcanvas offcanvas-bottom sheet d-lg-none" tabindex="-1" id="moreSheet" aria-labelledby="moreSheetTitle">
    <div class="sheet-handle" aria-hidden="true"></div>
    <div class="offcanvas-header pb-0">
        <h2 class="offcanvas-title h6 fw-bold" id="moreSheetTitle">بیشتر</h2>
        <button type="button" class="btn-close ms-0 me-auto" data-bs-dismiss="offcanvas" aria-label="بستن"></button>
    </div>
    <div class="offcanvas-body">
        <div class="sheet-grid">
            <a href="{{ url('/#banks') }}" data-bs-dismiss="offcanvas"><span class="s-ic">🏦</span>بانک‌های تحت پوشش</a>
            <a href="{{ url('/rules') }}"><span class="s-ic">📜</span>قوانین و مقررات</a>
            <a href="{{ url('/#faq') }}" data-bs-dismiss="offcanvas"><span class="s-ic">💬</span>سوالات متداول</a>
            <a href="{{ url('/contact') }}"><span class="s-ic">☎️</span>تماس با ما</a>
        </div>
    </div>
</div>
