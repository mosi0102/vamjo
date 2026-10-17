{{-- هدر پنل: لوگو + دکمه باز/بسته کردن منو + خروج --}}
<header class="pn-top">
    <div class="pn-brand">
        <a href="{{ url('/') }}" class="pn-logo" aria-label="وام‌جو">
            <img src="{{ asset('website/img/logo.png') }}" alt="وام‌جو" height="46" class="lazy-img">
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
            {{-- اعلان‌ها = آخرین رویدادهای ثبت‌شده کاربر؛ «جدید» یعنی امروز --}}
            @php
                $notifs = auth()->user()
                    ?->activityLogs()->with('actor')->latest()->limit(5)
                    ->get()
                    ->map(fn ($log) => [
                        'icon' => $log->icon(),
                        'text' => $log->description ?? $log->action,
                        'time' => \App\Support\Fa::ago($log->created_at),
                        'new'  => $log->created_at->isToday(),
                    ])->all();
            @endphp
            <div class="pn-notif" id="pnNotif">
                <button type="button" class="pn-icon-btn pn-bell" id="pnBell" aria-label="اعلان‌ها" aria-expanded="false" aria-controls="pnNotifPanel">
                    @include('userPanel.partials.icon', ['name' => 'bell'])
                    @if (collect($notifs)->where('new', true)->count())<span class="pn-bell-dot">{{ collect($notifs)->where('new', true)->count() }}</span>@endif
                </button>
                <div class="pn-notif-panel" id="pnNotifPanel" role="region" aria-label="اعلان‌ها">
                    <div class="pn-notif-head"><b>اعلان‌ها</b><button type="button" class="link-btn" id="pnReadAll">خوانده شد</button></div>
                    @foreach ($notifs as $n)
                        <a href="#" class="pn-notif-item {{ $n['new'] ? 'new' : '' }}">
                            <span class="pn-notif-ic">@include('userPanel.partials.icon', ['name' => $n['icon']])</span>
                            <span><span class="pn-notif-text">{{ $n['text'] }}</span><small>{{ $n['time'] }}</small></span>
                        </a>
                    @endforeach
                </div>
            </div>

            <button type="button" class="pn-icon-btn pn-logout" aria-label="خروج از حساب" title="خروج از حساب" data-bs-toggle="modal" data-bs-target="#logoutModal">
                @include('userPanel.partials.icon', ['name' => 'power'])
            </button>
        </div>
    </div>
</header>
