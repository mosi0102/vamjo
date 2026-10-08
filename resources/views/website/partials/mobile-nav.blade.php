{{-- نویگیشن پایین موبایل/تبلت (۵ صفحه اولویت اول) --}}
<nav class="bottom-nav d-lg-none" aria-label="ناوبری اصلی">
    <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24"><path d="M3 11l9-8 9 8v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></svg><span>خانه</span>
    </a>
    <a href="{{ url('/ads') }}" class="{{ request()->is('ads') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg><span>آگهی‌ها</span>
    </a>
    <a href="{{ url('/ads/create') }}" data-login-required data-verified-required class="bn-main {{ request()->is('ads/create') ? 'active' : '' }}" aria-label="ثبت آگهی">
        <b><svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></b><span>ثبت آگهی</span>
    </a>
    <a href="{{ url('/userPanel/requests') }}" data-login-required class="{{ request()->is('userPanel/requests*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/></svg><span>درخواست‌های من</span>
    </a>
    <a href="{{ url('/userPanel') }}" data-login-required class="{{ request()->is('userPanel') || request()->is('login') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg><span>حساب کاربری</span>
    </a>
</nav>
