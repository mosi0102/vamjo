@extends('userPanel.layouts.layout')

@section('title', 'داشبورد')
@section('page_title', 'داشبورد')
@section('body_class', 'pn-dashboard')

@php
    // نمونه داده؛ در پروژه واقعی از کنترلر پاس بدهید: compact('stats', 'requests', 'auction', 'chart', 'activities', 'verifySteps')
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $u = auth()->user();
    $isVerified = (bool) ($u->is_verified ?? false);
    $isUser = ($u->role ?? 'user') === 'user';
    $firstName = explode(' ', trim($u->name ?? 'محمد حسین طاهری'))[0] ?? '';

    // مسیر اسپارک‌لاین (SVG)
    $spark = function (array $v) {
      $w = 120; $h = 38; $min = min($v); $rng = max(max($v) - $min, 1); $n = count($v) - 1; $pts = [];
      foreach ($v as $i => $y) { $pts[] = round($i * $w / $n, 1) . ',' . round($h - 5 - ($y - $min) / $rng * ($h - 10), 1); }
      $line = 'M' . implode(' L', $pts);
      return [$line, $line . " L$w,$h L0,$h Z"];
    };

    $stats = [
      ['label' => 'تعداد آگهی های من',   'icon' => 'doc',       'value' => 5,         'prefix' => '+', 'tone' => '',      'trend' => '۲+ این ماه',  'up' => true, 'spark' => [1, 1, 2, 2, 3, 4, 5]],
      ['label' => 'تعداد درخواست های من','icon' => 'clipboard', 'value' => 10,        'prefix' => '+', 'tone' => '',      'trend' => '۴+ این ماه',  'up' => true, 'spark' => [2, 3, 3, 5, 6, 8, 10]],
      ['label' => 'کل مبلغ پرداختی من',  'icon' => 'wallet',    'value' => 150000000, 'unit' => 'تومان', 'tone' => 'red',  'trend' => '۱۲٪ بیشتر',   'up' => false, 'spark' => [20, 35, 30, 60, 55, 90, 150]],
      ['label' => 'کل مبلغ دریافتی من',  'icon' => 'bag',       'value' => 150000000, 'unit' => 'تومان', 'tone' => 'green', 'trend' => '۸٪ بیشتر',    'up' => true, 'spark' => [10, 25, 40, 38, 80, 110, 150]],
    ];

    $quick = [
      ['label' => 'ثبت آگهی جدید',  'sub' => 'وام خود را بفروشید', 'icon' => 'plus',  'url' => $isVerified ? '/ads/create' : '/panel/verify', 'locked' => ! $isVerified],
      ['label' => 'مشاهده آگهی ها', 'sub' => 'وام مورد نیاز را پیدا کنید', 'icon' => 'book',  'url' => '/ads', 'locked' => false],
      ['label' => 'رادار وام',      'sub' => 'اعلان آگهی‌های مرتبط',     'icon' => 'radar', 'url' => '/panel/radar', 'locked' => false],
      ['label' => 'تیکت پشتیبانی',  'sub' => 'پاسخ‌گویی ۲۴ ساعته',       'icon' => 'chat',  'url' => '/panel/tickets', 'locked' => false],
    ];

    $verifySteps = [['سلفی با کارت ملی', false], ['تصویر کارت ملی', false], ['شماره شبا', false], ['تاریخ تولد', false]];
    $verifyDone = collect($verifySteps)->where(1, true)->count();
    $verifyPct = (int) round($verifyDone / count($verifySteps) * 100);

    $requests = [
      ['id' => 1235896, 'bank' => 'رسالت',   'l' => 'ر','image'=>'8', 'c' => '#1f8a9e', 'amount' => 300000000, 'price' => 30000000, 'status' => 'pending'],
      ['id' => 1235897, 'bank' => 'جاویدان', 'l' => 'ج','image'=>'4', 'c' => '#e07b00', 'amount' => 100000000, 'price' => 30000000, 'status' => 'approved'],
      ['id' => 1235898, 'bank' => 'مهر',     'l' => 'م','image'=>'3', 'c' => '#2e9a4f', 'amount' => 300000000, 'price' => 30000000, 'status' => 'pending'],
    ];
    $statusMap = ['pending' => 'در انتظار تایید', 'approved' => 'تایید شده', 'rejected' => 'رد شده'];

    $auction = ['bank' => 'مهر','image'=>'3', 'l' => 'م', 'c' => '#2e9a4f', 'amount' => 1000000000, 'months' => 40, 'rate' => 23,
                'min_buy' => 100000000, 'offers' => 8, 'remaining' => 2 * 86400 + 23 * 3600 + 12 * 60 + 57];

    // نمودار: مبلغ‌ها به میلیون تومان
    $chart = [
      'labels' => ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور'],
      'pay'    => [20, 35, 15, 60, 45, 90],
      'recv'   => [10, 25, 40, 30, 70, 110],
    ];

    $activities = [
      ['icon' => 'clipboard', 'text' => 'درخواست وام ۳۰۰ میلیونی بانک رسالت ثبت شد.', 'time' => '۲ ساعت پیش'],
      ['icon' => 'check',     'text' => 'درخواست شما برای وام جاویدان تایید شد.',      'time' => 'دیروز'],
      ['icon' => 'card',      'text' => 'پرداخت ۳۰,۰۰۰,۰۰۰ تومان با موفقیت انجام شد.', 'time' => '۳ روز پیش'],
      ['icon' => 'user',      'text' => 'حساب کاربری شما ایجاد شد.',                   'time' => 'هفته پیش'],
    ];
@endphp

@section('main')

    {{-- خوش‌آمدگویی (سلام بر اساس ساعت و تاریخ شمسی با JS) --}}
    <div class="pn-head pn-reveal">
        <div class="pn-hello">
            <h2><span id="pnGreet" data-name="{{ $firstName }}">سلام، {{ $firstName }}</span> <span class="pn-wave"
                                                                                                    aria-hidden="true">👋</span>
            </h2>
            <p><span id="pnDate" class="pn-date"></span><span class="dot" aria-hidden="true"></span>به سامانه وام جو خوش
                آمدید.</p>
        </div>
    </div>

    {{-- بنر احراز هویت + پیشرفت (فقط کاربر احراز نشده) --}}
    @if ($isUser && ! $isVerified)
        <section class="pn-banner pn-reveal" style="--d:.08s">
            <div class="pn-banner-text">

                <h3 class="d-flex align-items-center">کاربر گرامی وامجو
                    <span
                            class="pn-badge-warn mx-2">@include('userPanel.partials.icon', ['name' => 'lock']) نیازمند احراز هویت</span>
                </h3>
                <p>برای بهره مندی از تمام امکانات سامانه (<b>ثبت آگهی وام</b> یا <b>درخواست وام</b>) اطلاعات احراز هویت
                    حساب کاربری خود را تکمیل نمایید.</p>

                {{--                <div class="pn-progress" role="progressbar" aria-valuenow="{{ $verifyPct }}" aria-valuemin="0"--}}
                {{--                     aria-valuemax="100" aria-label="پیشرفت احراز هویت">--}}
                {{--                    <div class="pn-progress-top"><span>پیشرفت احراز هویت</span><b>{{ $fa($verifyPct) }}٪</b></div>--}}
                {{--                    <div class="pn-progress-bar"><i style="--w:{{ $verifyPct }}%"></i></div>--}}
                {{--                    <ul class="pn-steps">--}}
                {{--                        @foreach ($verifySteps as $st)--}}
                {{--                            <li class="{{ $st[1] ? 'done' : '' }}">--}}
                {{--                                <span>@include('userPanel.partials.icon', ['name' => $st[1] ? 'check' : 'clock'])</span>{{ $st[0] }}--}}
                {{--                            </li>--}}
                {{--                        @endforeach--}}
                {{--                    </ul>--}}
                {{--                </div>--}}

                <a href="{{ url('/panel/verify') }}" class="btn btn-brand btn-lg pn-banner-btn">شروع احراز
                    هویت @include('userPanel.partials.icon', ['name' => 'arrow'])</a>
            </div>
            <div class="pn-banner-art" aria-hidden="true">
                <svg viewBox="0 0 300 230">
                    <ellipse cx="150" cy="212" rx="120" ry="12" fill="#1e5a45" opacity=".08"/>
                    <g class="art-phone">
                        <rect x="150" y="14" width="104" height="184" rx="18" fill="#1e5a45"/>
                        <rect x="157" y="22" width="90" height="168" rx="13" fill="#f6f3ec"/>
                        <path d="M202 36l22 8v14c0 12-9 20-22 24-13-4-22-12-22-24V44z" fill="#2e9a4f"/>
                        <path d="M193 58l7 7 12-13" stroke="#fff" stroke-width="3.5" fill="none" stroke-linecap="round"
                              stroke-linejoin="round"/>
                        <circle cx="202" cy="116" r="26" fill="#d9ebe1"/>
                        <circle cx="202" cy="110" r="10" fill="#1e5a45"/>
                        <path d="M184 134c4-12 32-12 36 0" fill="#1e5a45"/>
                        <path d="M172 90v-8h8M232 90v-8h-8M172 142v8h8M232 142v8h-8" stroke="#1e5a45" stroke-width="2.5"
                              fill="none" stroke-linecap="round"/>
                        <rect x="172" y="164" width="60" height="8" rx="4" fill="#1e5a45" opacity=".25"/>
                    </g>
                    <g class="art-card">
                        <rect x="38" y="120" width="104" height="68" rx="10" fill="#fff" stroke="#1e5a45"
                              stroke-width="2.5" transform="rotate(-8 90 154)"/>
                        <g transform="rotate(-8 90 154)">
                            <rect x="48" y="132" width="26" height="30" rx="4" fill="#d9ebe1"/>
                            <circle cx="61" cy="143" r="6" fill="#1e5a45"/>
                            <path d="M82 138h46M82 148h36M82 158h42" stroke="#9fb8ab" stroke-width="3.5"
                                  stroke-linecap="round"/>
                            <circle cx="124" cy="174" r="8" fill="#2e9a4f"/>
                            <path d="M120 174l3 3 5-6" stroke="#fff" stroke-width="2" fill="none"
                                  stroke-linecap="round"/>
                        </g>
                    </g>
                    <path d="M270 200c-2-26 6-44 22-54-2 24-8 42-22 54zM262 204c-14-14-18-30-12-46 12 12 16 28 12 46z"
                          fill="#6aa688" opacity=".7"/>
                    <circle class="spark-a" cx="40" cy="60" r="5" fill="#2e9a4f"/>
                    <circle class="spark-b" cx="270" cy="70" r="4" fill="#d9a62e"/>
                </svg>
            </div>
        </section>
    @endif

    {{-- آمار با اسپارک‌لاین --}}
    <section class="pn-stats" aria-label="آمار حساب">
        @foreach ($stats as $i => $s)
            @php [$line, $area] = $spark($s['spark']); $gid = 'sg' . $i; @endphp
            <article class="pn-stat pn-reveal {{ $s['tone'] }}" style="--d:{{ .12 + $i * .07 }}s">
                <div class="pn-stat-top">
                    <span class="pn-stat-ic">@include('userPanel.partials.icon', ['name' => $s['icon']])</span>
                    <div class="pn-stat-num">
                        <b class="pn-count" data-count="{{ $s['value'] }}"
                           data-prefix="{{ $s['prefix'] ?? '' }}">{{ ($s['prefix'] ?? '') . $fa($s['value']) }}</b>
                        @isset($s['unit'])
                            <small>{{ $s['unit'] }}</small>
                        @endisset
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span>
                        {{ $s['label'] }}
                    </span>
                    <span
                            class="pn-trend {{ $s['up'] ? 'up' : 'down' }}">@include('userPanel.partials.icon', ['name' => 'trend']){{ $s['trend'] }}</span>
                </div>
                {{--                <div class="pn-stat-foot">--}}

                {{--                    <svg class="pn-spark" viewBox="0 0 120 38" preserveAspectRatio="none" aria-hidden="true">--}}
                {{--                        <defs>--}}
                {{--                            <linearGradient id="{{ $gid }}" x1="0" x2="0" y1="0" y2="1">--}}
                {{--                                <stop offset="0" stop-color="currentColor" stop-opacity=".25"/>--}}
                {{--                                <stop offset="1" stop-color="currentColor" stop-opacity="0"/>--}}
                {{--                            </linearGradient>--}}
                {{--                        </defs>--}}
                {{--                        <path d="{{ $area }}" fill="url(#{{ $gid }})" class="spark-area"/>--}}
                {{--                        <path d="{{ $line }}" class="spark-line" pathLength="1" fill="none" stroke="currentColor"--}}
                {{--                              stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>--}}
                {{--                    </svg>--}}
                {{--                </div>--}}
            </article>
        @endforeach
    </section>

    {{-- دسترسی سریع --}}
    <section class="pn-quick" aria-label="دسترسی سریع">
        @foreach ($quick as $i => $q)
            <a href="{{ url($q['url']) }}" class="pn-quick-item pn-reveal {{ $q['locked'] ? 'locked' : '' }}"
               style="--d:{{ .15 + $i * .06 }}s">
                <span class="pn-quick-ic">@include('userPanel.partials.icon', ['name' => $q['icon']])</span>
                <span
                        class="pn-quick-text"><b>{{ $q['label'] }}</b><small>{{ $q['locked'] ? 'ابتدا احراز هویت کنید' : $q['sub'] }}</small></span>
                @if ($q['locked'])
                    <span class="pn-quick-lock">@include('userPanel.partials.icon', ['name' => 'lock'])</span>
                @endif
            </a>
        @endforeach
    </section>

    <div class="pn-cols">

        <div class="pn-col-main">
            {{-- آخرین درخواست‌ها --}}
            <section class="pn-reveal" style="--d:.2s">
                <div class="pn-sec-head"><h3>آخرین درخواست ها</h3><a href="{{ url('/panel/requests') }}"
                                                                     class="pn-link-arrow sm">مشاهده
                        همه @include('userPanel.partials.icon', ['name' => 'arrow'])</a></div>
                <div class="pn-card pn-reqs">
                    @forelse ($requests as $r)
                        <a href="{{ url('/panel/requests/' . $r['id']) }}" class="pn-req">

                            <img src="{{asset('website/img/bank/'.$r['image'].'.png')}}" alt="" height="90">
                            <div class="pn-req-info">
                                <h4>وام {{ $fa($r['amount']) }} تومان بانک {{ $r['bank'] }}</h4>
                                <span class="pn-id">شناسه وام <b>#{{($r['id']) }}</b></span>
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

            {{-- نمودار مالی --}}
            <section class="pn-reveal pn-chart-wrap" style="--d:.26s">
                <div class="pn-sec-head">
                    <h3>گزارش مالی ۶ ماه اخیر</h3>
                    <div class="pn-tabs" role="tablist">
                        <button type="button" class="pn-tab active" data-set="pay" role="tab" aria-selected="true">
                            پرداختی
                        </button>
                        <button type="button" class="pn-tab" data-set="recv" role="tab" aria-selected="false">دریافتی
                        </button>
                    </div>
                </div>
                <div class="pn-card pn-chart" id="pnChart" data-labels='@json($chart['labels'])'
                     data-pay='@json($chart['pay'])' data-recv='@json($chart['recv'])'>
                    <div class="pn-chart-sum"><small>مجموع <span id="pnChartTitle">پرداختی</span></small><b><span
                                    id="pnChartTotal">۰</span> <small>میلیون تومان</small></b></div>
                    <div class="pn-bars" id="pnBars" role="img" aria-label="نمودار ستونی مبلغ ماهانه"></div>
                </div>
            </section>
        </div>

        <div class="pn-col-side">
            {{-- مزایده --}}
            <section class="pn-reveal" style="--d:.28s">
                <div class="pn-sec-head"><h3>آگهی های مزایده</h3></div>
                <article class="pn-card pn-auction">
                    <div class="pn-auction-top">
                        <img src="{{asset('website/img/bank/'.$r['image'].'.png')}}" alt="" height="90">
                        <div>
                            <div class="pn-auction-amount"><b>{{ $fa($auction['amount']) }}</b> <small>تومان</small>
                                <b>
                                    بانک
                                    {{ $r['bank'] }}
                                </b>

                            </div>
                            <span class="ad-pill"><span>اقساط {{ $fa($auction['months']) }} ماه</span><i></i><span>سود {{ $fa($auction['rate']) }} درصد</span></span>
                        </div>
                    </div>
                    <div class="ad-bar"><span>حداقل مبلغ خرید:</span><span>{{ $fa($auction['min_buy']) }} تومان</span>
                    </div>
                    <div class="ad-foot"><span>{{ $fa($auction['offers']) }} پیشنهاد ثبت شده</span><a
                                href="{{ url('/ads/1') }}">اطلاعات بیشتر ←</a></div>
                    <div class="pn-cd" data-remaining="{{ $auction['remaining'] }}" role="timer"
                         aria-label="زمان باقی‌مانده مزایده">
                        @foreach ([['s', 'ثانیه'], ['m', 'دقیقه'], ['h', 'ساعت'], ['d', 'روز']] as $k => $un)
                            @if ($k)
                                <span class="pn-cd-sep" aria-hidden="true">:</span>
                            @endif
                            <div class="pn-cd-unit" data-unit="{{ $un[0] }}">
                                <div class="pn-cd-num">0</div>
                                <small>{{ $un[1] }}</small></div>
                        @endforeach
                    </div>
                </article>
            </section>

            {{-- فعالیت‌های اخیر --}}
            <section class="pn-reveal" style="--d:.34s">
                <div class="pn-sec-head"><h3>فعالیت های اخیر</h3></div>
                <div class="pn-card pn-timeline">
                    @foreach ($activities as $a)
                        <div class="pn-tl-item">
                            <span class="pn-tl-ic">@include('userPanel.partials.icon', ['name' => $a['icon']])</span>
                            <div><p>{{ $a['text'] }}</p><small>{{ $a['time'] }}</small></div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
@endsection
