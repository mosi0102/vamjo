{{-- سایدبار پنل (دسکتاپ: قابل جمع شدن | موبایل: کشوی کناری) – منو از config/panel.php و بر اساس نقش --}}
<aside class="pn-sidebar" id="pnSidebar" aria-label="منوی پنل">

    <div class="pn-drawer-head d-lg-none">
        <img src="{{ asset('website/img/logo.png') }}" alt="وام‌جو" height="38">
        <button type="button" class="pn-icon-btn pn-drawer-close" aria-label="بستن منو">@include('userPanel.partials.icon', ['name' => 'close'])</button>
    </div>

    <a href="{{ url($role === 'user' ? '/panel/verify' : '/admin') }}" class="pn-user" title="{{ $pn->name }}">
    <span class="pn-avatar">
      @if ($pn->avatar)<img src="{{ $pn->avatar }}" alt="">@else @include('userPanel.partials.icon', ['name' => 'user']) @endif
    </span>
        <span class="pn-user-info">
      <b>{{ $pn->name }}</b>
      @if ($role === 'user')
                <small class="{{ $pn->verified ? 'ok' : 'bad' }}">{{ $pn->verified ? 'حساب شما احراز شده است' : 'حساب شما احراز نشده است' }}</small>
            @else
                <small class="ok">{{ $pn->role_label }}</small>
            @endif
    </span>
        <span class="pn-user-arrow">@include('userPanel.partials.icon', ['name' => 'arrow'])</span>
    </a>

    <nav class="pn-nav">
        @foreach (config('panel.menu') as $item)
            @continue(! in_array($role, $item['roles']))
            @php $active = request()->is(...$item['active']); @endphp
            <a href="{{ $item['url'] }}" class="pn-link {{ $active ? 'active' : '' }}" data-label="{{ $item['label'] }}" @if ($active) aria-current="page" @endif>
                <span class="pn-link-ic">@include('userPanel.partials.icon', ['name' => $item['icon']])</span>
                <span class="pn-link-text">{{ $item['label'] }}</span>
            </a>
        @endforeach

        <button type="button" class="pn-link danger" data-label="خروج از حساب" data-bs-toggle="modal" data-bs-target="#logoutModal">
            <span class="pn-link-ic">@include('userPanel.partials.icon', ['name' => 'power'])</span>
            <span class="pn-link-text">خروج از حساب</span>
        </button>
    </nav>
</aside>
