@extends('userPanel.layouts.layout')

@section('title', 'داشبورد مدیریت')
@section('page_title', 'داشبورد')
@section('body_class', 'pn-admin pn-dashboard')

@php
    // نمونه داده؛ در پروژه واقعی از کنترلر پاس بدهید. نقش: admin | support (با ?role=support تست کنید)
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $isAdmin = \App\Support\PanelRole::isAdmin();
    $kpis = [
      ['کل کاربران', $fa(1324), 'users', ''],
      ['ثبت نام امروز', $fa(18), 'plus', 'green'],
      ['احراز هویت منتظر بررسی', $fa(7), 'shield', 'amber', '/adminPanel/verifications'],
      ['آگهی منتظر تایید', $fa(5), 'book', 'amber', '/adminPanel/ads'],
      ['آگهی های فعال', $fa(214), 'check', ''],
      ['تیکت های بی پاسخ', $fa(4), 'chat', 'amber', '/adminPanel/tickets'],
    ];
    if ($isAdmin) { $kpis[] = ['موجودی امانت', $fa(480000000) . ' <small>تومان</small>', 'wallet', 'green', '/adminPanel/finance']; $kpis[] = ['کمیسیون این ماه', $fa(36500000) . ' <small>تومان</small>', 'trend', 'green', '/adminPanel/finance']; }
    $chart = ['labels' => ['شنبه', 'یکشنبه', 'دوشنبه', 'سه شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه'], 'pay' => [120, 95, 180, 140, 210, 160, 75], 'recv' => [14, 9, 22, 18, 26, 18, 11]];
    $kyc = [['علی رضایی', '۲ ساعت پیش'], ['مریم احمدی', '۳ ساعت پیش'], ['حسین کریمی', '۵ ساعت پیش'], ['زهرا محمدی', 'دیروز']];
    $ads = [['وام ۳۰۰,۰۰۰,۰۰۰ بانک رسالت', 'سارا حسینی', '۱ ساعت پیش'], ['وام ۱۰۰,۰۰۰,۰۰۰ بانک شهر', 'امیر جعفری', '۴ ساعت پیش'], ['وام ۲۵۰,۰۰۰,۰۰۰ بانک تجارت', 'فاطمه کاظمی', 'دیروز']];
    $tickets = [['عدم ثبت آگهی انتقال وام', 'رضا نوری', 'high'], ['پیگیری واریزی فروش امتیاز', 'نرگس صادقی', 'urgent'], ['مشکل در بارگذاری مدارک', 'مهدی رحیمی', 'normal']];
    $prio = ['normal' => 'متوسط', 'high' => 'زیاد', 'urgent' => 'فوری'];
    $dist = [['منتشر شده', 214, '#2e9a4f'], ['منتظر تایید', 5, '#d9a93f'], ['در حال معامله', 31, '#3e4ea0'], ['تکمیل شده', 98, '#1e5a45'], ['رد شده', 12, '#d33']];
    $distTotal = collect($dist)->sum(1);
    $acts = [['shield', 'هویت «علی رضایی» توسط شما تایید شد.', '۱۰ دقیقه پیش'], ['book', 'آگهی ۱۲۳۵۸۹۶ منتشر و به‌عنوان ویژه ثبت شد.', '۱ ساعت پیش'], ['card', 'واریز ۲۷,۰۰۰,۰۰۰ تومان به فروشنده ثبت شد.', '۳ ساعت پیش'], ['chat', 'به تیکت ۱۳۶۵۴۸ پاسخ داده شد.', 'دیروز']];
@endphp

@section('main')

    <div class="pn-head pn-reveal">
        <div class="pn-hello">
            <h2><span id="pnGreet" data-name="{{ $isAdmin ? 'مدیر' : 'همکار' }}">سلام</span> <span class="pn-wave" aria-hidden="true">👋</span></h2>
            <p><span id="pnDate" class="pn-date"></span><span class="dot" aria-hidden="true"></span>خلاصه وضعیت سایت و مواردی که نیاز به اقدام شما دارند.</p>
        </div>
    </div>

    {{-- شاخص‌ها --}}
    <section class="of-stats at-kpis" aria-label="شاخص‌های سایت">
        @foreach ($kpis as $i => $k)
            <a href="{{ url($k[4] ?? '#') }}" class="of-stat at-kpi pn-reveal {{ $k[3] }}" style="--d:{{ .05 + $i * .05 }}s" @if (empty($k[4])) tabindex="-1" @endif>
                <span class="of-stat-ic">@include('userPanel.partials.icon', ['name' => $k[2]])</span>
                <div><small>{{ $k[0] }}</small><b>{!! $k[1] !!}</b></div>
            </a>
        @endforeach
    </section>

    <div class="pn-cols">
        <div class="pn-col-main">

            {{-- نمودار --}}
            <section class="pn-reveal pn-chart-wrap at-chart" style="--d:.2s">
                <div class="pn-sec-head">
                    <h3>روند ۷ روز اخیر</h3>
                    <div class="pn-tabs" role="tablist">
                        <button type="button" class="pn-tab active" data-set="pay" role="tab" aria-selected="true">حجم معاملات</button>
                        <button type="button" class="pn-tab" data-set="recv" role="tab" aria-selected="false">ثبت نام کاربران</button>
                    </div>
                </div>
                <div class="pn-card pn-chart" id="pnChart" data-labels='@json($chart['labels'])' data-pay='@json($chart['pay'])' data-recv='@json($chart['recv'])'
                     data-names='{"pay":"حجم معاملات","recv":"ثبت نام"}' data-units='{"pay":"میلیون تومان","recv":"نفر"}'>
                    <div class="pn-chart-sum"><small>مجموع <span id="pnChartTitle">حجم معاملات</span></small><b><span id="pnChartTotal">۰</span> <small id="pnChartUnit">میلیون تومان</small></b></div>
                    <div class="pn-bars" id="pnBars" role="img" aria-label="نمودار ستونی هفتگی"></div>
                </div>
            </section>

            {{-- نیاز به اقدام --}}
            <section class="at-queues pn-reveal" style="--d:.26s">
                <article class="pn-card at-q">
                    <header><h3>احراز هویت منتظر</h3><a href="{{ url('/adminPanel/verifications') }}" class="pn-link-arrow sm">همه @include('userPanel.partials.icon', ['name' => 'arrow'])</a></header>
                    @foreach ($kyc as $r)
                        <a href="{{ url('/adminPanel/verifications') }}" class="at-q-row"><span class="at-av" style="background:#1f8a9e">{{ mb_substr($r[0], 0, 1) }}</span><span><b>{{ $r[0] }}</b><small>{{ $r[1] }}</small></span><span class="at-q-go">بررسی</span></a>
                    @endforeach
                </article>
                <article class="pn-card at-q">
                    <header><h3>آگهی منتظر تایید</h3><a href="{{ url('/adminPanel/ads') }}" class="pn-link-arrow sm">همه @include('userPanel.partials.icon', ['name' => 'arrow'])</a></header>
                    @foreach ($ads as $r)
                        <a href="{{ url('/adminPanel/ads') }}" class="at-q-row"><span class="at-av sq" style="background:#e07b00">{{ mb_substr($r[1], 0, 1) }}</span><span><b>{{ $r[0] }}</b><small>{{ $r[1] }} · {{ $r[2] }}</small></span><span class="at-q-go">بررسی</span></a>
                    @endforeach
                </article>
                <article class="pn-card at-q">
                    <header><h3>تیکت های بی پاسخ</h3><a href="{{ url('/adminPanel/tickets') }}" class="pn-link-arrow sm">همه @include('userPanel.partials.icon', ['name' => 'arrow'])</a></header>
                    @foreach ($tickets as $r)
                        <a href="{{ url('/adminPanel/tickets/136548') }}" class="at-q-row"><span class="at-av" style="background:#7a5ac8">{{ mb_substr($r[1], 0, 1) }}</span><span><b>{{ $r[0] }}</b><small>{{ $r[1] }}</small></span><span class="tk-prio {{ $r[2] }}">{{ $prio[$r[2]] }}</span></a>
                    @endforeach
                </article>
            </section>
        </div>

        <div class="pn-col-side">
            <section class="pn-reveal" style="--d:.28s">
                <div class="pn-sec-head"><h3>وضعیت آگهی ها</h3></div>
                <div class="pn-card at-dist">
                    @foreach ($dist as $d)
                        <div class="at-dist-row">
                            <div class="at-dist-top"><span><i style="background:{{ $d[2] }}"></i>{{ $d[0] }}</span><b>{{ $fa($d[1]) }}</b></div>
                            <div class="at-dist-bar"><i style="--w:{{ round($d[1] / $distTotal * 100) }}%; background:{{ $d[2] }}"></i></div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="pn-reveal" style="--d:.34s">
                <div class="pn-sec-head"><h3>فعالیت های اخیر</h3></div>
                <div class="pn-card pn-timeline">
                    @foreach ($acts as $a)
                        <div class="pn-tl-item"><span class="pn-tl-ic">@include('userPanel.partials.icon', ['name' => $a[0]])</span><div><p>{{ $a[1] }}</p><small>{{ $a[2] }}</small></div></div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
@endsection
