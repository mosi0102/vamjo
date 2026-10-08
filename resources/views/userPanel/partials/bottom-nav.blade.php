{{-- نویگیشن پایین پنل (موبایل/تبلت): ۴ آیتم اصلی نقش + دکمه «منو» برای کشو --}}
<nav class="bottom-nav d-lg-none" aria-label="ناوبری پنل">
    @foreach (collect(config('panel.menu'))->filter(fn ($i) => in_array($role, $i['roles']) && ! empty($i['bottom']))->take(4) as $item)
        <a href="{{ url($item['url']) }}" class="{{ request()->is(...$item['active']) ? 'active' : '' }}">
            @include('userPanel.partials.icon', ['name' => $item['icon']])<span>{{ \Illuminate\Support\Str::of($item['label'])->replace(' های من', '')->replace(' من', '') }}</span>
        </a>
    @endforeach
    <button type="button" id="pnMenuBtn" aria-label="باز کردن منو" aria-controls="pnSidebar">
        @include('userPanel.partials.icon', ['name' => 'menu'])<span>منو</span>
    </button>
</nav>
