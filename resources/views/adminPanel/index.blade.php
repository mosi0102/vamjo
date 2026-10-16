@extends('userPanel.layouts.layout')

@section('title', 'داشبورد مدیریت')
@section('page_title', 'داشبورد')
@section('body_class', 'pn-admin pn-dashboard')

@php
    // داده‌ها از Admin\DashboardController::index می‌آید: $kpis, $chart, $kyc, $ads, $tickets, $dist, $acts
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $isAdmin = \App\Support\PanelRole::isAdmin();
    $prio = ['normal' => 'متوسط', 'high' => 'زیاد', 'urgent' => 'فوری'];
    $distTotal = max(1, collect($dist)->sum(1));
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
                    <header><h3>تیکت های بی پاسخ</h3><a href="{{ route('admin.tickets.index') }}" class="pn-link-arrow sm">همه @include('userPanel.partials.icon', ['name' => 'arrow'])</a></header>
                    @foreach ($tickets as $r)
                        <a href="{{ route('admin.tickets.show', $r[3]) }}" class="at-q-row"><span class="at-av" style="background:#7a5ac8">{{ mb_substr($r[1], 0, 1) }}</span><span><b>{{ $r[0] }}</b><small>{{ $r[1] }}</small></span><span class="tk-prio {{ $r[2] }}">{{ $prio[$r[2]] ?? $r[2] }}</span></a>
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
