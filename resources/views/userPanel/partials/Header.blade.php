{{-- هدر پنل: لوگو + دکمه باز/بسته کردن منو + خروج --}}
<header class="pn-top">
    <div class="pn-brand">
        <a href="{{ url('/') }}" class="pn-logo" aria-label="وام‌جو">
            <img src="{{ asset('assets/images/logo.png') }}" alt="وام‌جو" height="46">
            <span class="pn-mark" aria-hidden="true">و</span>
        </a>
    </div>

    <div class="pn-bar">
        <button type="button" class="pn-icon-btn pn-toggle" id="pnToggle" aria-label="باز و بسته کردن منو" aria-controls="pnSidebar" aria-expanded="true">
            <span class="t-desk">@include('userPanel.partials.icon', ['name' => 'arrow'])</span>
            <span class="t-mob">@include('userPanel.partials.icon', ['name' => 'menu'])</span>
        </button>

        <h1 class="pn-bar-title">@yield('page_title')</h1>

        <div class="pn-push">
            <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <button type="submit" class="pn-icon-btn pn-logout" aria-label="خروج از حساب" title="خروج از حساب">
                    @include('userPanel.partials.icon', ['name' => 'power'])
                </button>
            </form>
        </div>
    </div>
</header>
