@extends('userPanel.layouts.layout')

@section('title', 'داشبورد')
@section('page_title', 'داشبورد')
@section('body_class', 'pn-dashboard')

@php
    // نمونه داده؛ در پروژه واقعی از کنترلر پاس بدهید: compact('stats', 'requests', 'auction')
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $isVerified = (bool) (auth()->user()->is_verified ?? false);
    $isUser = (auth()->user()->role ?? 'user') === 'user';

    $stats = [
      ['label' => 'تعداد آگهی های من',  'icon' => 'doc',       'value' => 5,         'prefix' => '+', 'tone' => ''],
      ['label' => 'تعداد درخواست های من','icon' => 'clipboard', 'value' => 10,        'prefix' => '+', 'tone' => ''],
      ['label' => 'کل مبلغ پرداختی من',  'icon' => 'wallet',    'value' => 150000000, 'unit' => 'تومان', 'tone' => 'red'],
      ['label' => 'کل مبلغ دریافتی من',  'icon' => 'bag',       'value' => 150000000, 'unit' => 'تومان', 'tone' => 'green'],
    ];
    $requests = [
      ['id' => 1235896, 'bank' => 'رسالت',  'l' => 'ر', 'c' => '#1f8a9e', 'amount' => 300000000, 'price' => 30000000, 'status' => 'pending'],
      ['id' => 1235897, 'bank' => 'جاویدان', 'l' => 'ج', 'c' => '#e07b00', 'amount' => 100000000, 'price' => 30000000, 'status' => 'pending'],
      ['id' => 1235898, 'bank' => 'مهر',    'l' => 'م', 'c' => '#2e9a4f', 'amount' => 300000000, 'price' => 30000000, 'status' => 'pending'],
    ];
    $statusMap = ['pending' => 'در انتظار تایید', 'approved' => 'تایید شده', 'rejected' => 'رد شده'];
    $auction = ['bank' => 'مهر', 'l' => 'م', 'c' => '#2e9a4f', 'amount' => 1000000000, 'months' => 40, 'rate' => 23,
                'min_buy' => 100000000, 'offers' => 8, 'remaining' => 2 * 86400 + 23 * 3600 + 12 * 60 + 57];
@endphp

@section('main')

    {{-- عنوان صفحه --}}
    <div class="pn-head pn-reveal">
        <span class="pn-head-ic">@include('userPanel.partials.icon', ['name' => 'dashboard'])</span>
        <div>
            <h2>داشبورد</h2>
            <p>کاربر گرامی به سامانه وام جو خوش آمدید.</p>
        </div>
    </div>

    {{-- بنر احراز هویت (فقط کاربر احراز نشده) --}}
    @if ($isUser && ! $isVerified)
        <section class="pn-banner pn-reveal" style="--d:.08s">
            <div class="pn-banner-text">
                <h3>کاربر گرامی وامجو</h3>
                <p>برای بهره مندی از تمام امکانات سامانه (<b>ثبت آگهی وام</b> یا <b>درخواست وام</b>) اطلاعات احراز هویت حساب کاربری خود را تکمیل نمایید.</p>
                <a href="{{ url('/panel/verify') }}" class="pn-link-arrow">احراز هویت @include('userPanel.partials.icon', ['name' => 'arrow'])</a>
            </div>
            <div class="pn-banner-art" aria-hidden="true">
                <svg viewBox="0 0 300 230">
                    <ellipse cx="150" cy="212" rx="120" ry="12" fill="#1e5a45" opacity=".08"/>
                    <g class="art-phone"><rect x="150" y="14" width="104" height="184" rx="18" fill="#1e5a45"/><rect x="157" y="22" width="90" height="168" rx="13" fill="#f6f3ec"/>
                        <path d="M202 36l22 8v14c0 12-9 20-22 24-13-4-22-12-22-24V44z" fill="#2e9a4f"/><path d="M193 58l7 7 12-13" stroke="#fff" stroke-width="3.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="202" cy="116" r="26" fill="#d9ebe1"/><circle cx="202" cy="110" r="10" fill="#1e5a45"/><path d="M184 134c4-12 32-12 36 0" fill="#1e5a45"/>
                        <path d="M172 90v-8h8M232 90v-8h-8M172 142v8h8M232 142v8h-8" stroke="#1e5a45" stroke-width="2.5" fill="none" stroke-linecap="round"/><rect x="172" y="164" width="60" height="8" rx="4" fill="#1e5a45" opacity=".25"/></g>
                    <g class="art-card"><rect x="38" y="120" width="104" height="68" rx="10" fill="#fff" stroke="#1e5a45" stroke-width="2.5" transform="rotate(-8 90 154)"/>
                        <g transform="rotate(-8 90 154)"><rect x="48" y="132" width="26" height="30" rx="4" fill="#d9ebe1"/><circle cx="61" cy="143" r="6" fill="#1e5a45"/><path d="M82 138h46M82 148h36M82 158h42" stroke="#9fb8ab" stroke-width="3.5" stroke-linecap="round"/><circle cx="124" cy="174" r="8" fill="#2e9a4f"/><path d="M120 174l3 3 5-6" stroke="#fff" stroke-width="2" fill="none" stroke-linecap="round"/></g></g>
                    <path d="M270 200c-2-26 6-44 22-54-2 24-8 42-22 54zM262 204c-14-14-18-30-12-46 12 12 16 28 12 46z" fill="#6aa688" opacity=".7"/>
                </svg>
            </div>
        </section>
    @endif

    {{-- آمار --}}
    <section class="pn-stats" aria-label="آمار حساب">
        @foreach ($stats as $i => $s)
            <article class="pn-stat pn-reveal {{ $s['tone'] }}" style="--d:{{ .12 + $i * .07 }}s">
                <div class="pn-stat-top">
                    <span class="pn-stat-ic">@include('userPanel.partials.icon', ['name' => $s['icon']])</span>
                    <div class="pn-stat-num">
                        <b class="pn-count" data-count="{{ $s['value'] }}" data-prefix="{{ $s['prefix'] ?? '' }}">{{ ($s['prefix'] ?? '') . $fa($s['value']) }}</b>
                        @isset($s['unit'])<small>{{ $s['unit'] }}</small>@endisset
                    </div>
                </div>
                <p>{{ $s['label'] }}</p>
            </article>
        @endforeach
    </section>

    <div class="pn-cols">

        {{-- آخرین درخواست‌ها --}}
        <section class="pn-col-main pn-reveal" style="--d:.2s">
            <div class="pn-sec-head"><h3>آخرین درخواست ها</h3><a href="{{ url('/panel/requests') }}" class="pn-link-arrow sm">مشاهده همه @include('userPanel.partials.icon', ['name' => 'arrow'])</a></div>
            <div class="pn-card pn-reqs">
                @forelse ($requests as $r)
                    <a href="{{ url('/panel/requests/' . $r['id']) }}" class="pn-req">
                        <span class="pn-bank" style="background: {{ $r['c'] }}" aria-hidden="true">{{ $r['l'] }}</span>
                        <div class="pn-req-info">
                            <h4>وام {{ $fa($r['amount']) }} تومان بانک {{ $r['bank'] }}</h4>
                            <span class="pn-id">شناسه وام <b>#{{ $fa($r['id']) }}</b></span>
                        </div>
                        <div class="pn-req-end">
                            <span class="pn-pill {{ $r['status'] }}">{{ $statusMap[$r['status']] }}</span>
                            <div class="pn-price"><b>{{ $fa($r['price']) }}</b><small>تومان</small></div>
                        </div>
                    </a>
                @empty
                    <p class="pn-empty">هنوز درخواستی ثبت نکرده‌اید.</p>
                @endforelse
            </div>
        </section>

        {{-- آگهی مزایده --}}
        <section class="pn-col-side pn-reveal" style="--d:.28s">
            <div class="pn-sec-head"><h3>آگهی های مزایده</h3></div>
            <article class="pn-card pn-auction">
                <div class="pn-auction-top">
                    <span class="pn-bank lg" style="background: {{ $auction['c'] }}" aria-hidden="true">{{ $auction['l'] }}</span>
                    <div>
                        <div class="pn-auction-amount"><b>{{ $fa($auction['amount']) }}</b> <small>تومان</small></div>
                        <span class="ad-pill"><span>اقساط {{ $fa($auction['months']) }} ماه</span><i></i><span>سود {{ $fa($auction['rate']) }} درصد</span></span>
                    </div>
                </div>
                <div class="ad-bar"><span>حداقل مبلغ خرید:</span><span>{{ $fa($auction['min_buy']) }} تومان</span></div>
                <div class="ad-foot"><span>{{ $fa($auction['offers']) }} پیشنهاد ثبت شده</span><a href="{{ url('/ads/1') }}">اطلاعات بیشتر ←</a></div>

                <div class="pn-cd" data-remaining="{{ $auction['remaining'] }}" role="timer" aria-label="زمان باقی‌مانده مزایده">
                    @foreach ([['s', 'ثانیه'], ['m', 'دقیقه'], ['h', 'ساعت'], ['d', 'روز']] as $k => $u)
                        @if ($k) <span class="pn-cd-sep" aria-hidden="true">:</span> @endif
                        <div class="pn-cd-unit" data-unit="{{ $u[0] }}"><div class="pn-cd-num">0</div><small>{{ $u[1] }}</small></div>
                    @endforeach
                </div>
            </article>
        </section>
    </div>
@endsection
