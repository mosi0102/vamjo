@extends('userPanel.layouts.layout')

@section('title', 'درخواست های ثبت شده برای آگهی')
@section('page_title', 'درخواست های آگهی')
@section('body_class', 'pn-offers')

@php
    // نمونه داده؛ در پروژه واقعی از کنترلر پاس بدهید: compact('ad', 'offers')
    // status: pending | accepted | rejected | expired | age = دقیقه از ثبت درخواست (برای مرتب‌سازی)
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $faId = fn ($n) => strtr((string) $n, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $ad = ['id' => 1235897, 'bank' => 'جاویدان','image'=>'4', 'l' => 'ج', 'c' => '#e07b00', 'amount' => 300000000, 'price' => 30000000, 'max' => 45000000, 'city' => 'سبزوار', 'state' => 'در انتظار انتقال امتیاز'];
    $offers = [
      ['id' => 11, 'name' => 'علی رضایی',   'amount' => 45000000, 'status' => 'pending',  'ago' => '۲ ساعت پیش',  'age' => 120,  'deals' => 12, 'rating' => 4.8, 'verified' => true,  'expires' => '۲۲ ساعت'],
      ['id' => 12, 'name' => 'مریم احمدی',  'amount' => 42000000, 'status' => 'pending',  'ago' => '۵ ساعت پیش',  'age' => 300,  'deals' => 5,  'rating' => 4.6, 'verified' => true,  'expires' => '۱۹ ساعت'],
      ['id' => 13, 'name' => 'حسین کریمی',  'amount' => 38500000, 'status' => 'pending',  'ago' => 'دیروز',       'age' => 1500, 'deals' => 0,  'rating' => null, 'verified' => false, 'expires' => '۶ ساعت'],
      ['id' => 14, 'name' => 'زهرا محمدی',  'amount' => 35000000, 'status' => 'rejected', 'ago' => '۲ روز پیش',   'age' => 2900, 'deals' => 3,  'rating' => 4.2, 'verified' => true],
      ['id' => 15, 'name' => 'رضا نوری',    'amount' => 30000000, 'status' => 'expired',  'ago' => '۳ روز پیش',   'age' => 4400, 'deals' => 1,  'rating' => 4.0, 'verified' => true],
    ];
    $statusMap = ['pending' => ['pending', 'در انتظار پاسخ'], 'accepted' => ['approved', 'تایید شده'], 'rejected' => ['rejected', 'رد شده'], 'expired' => ['expired', 'منقضی شده']];
    $groupOf = fn ($o) => in_array($o['status'], ['rejected', 'expired']) ? 'closed' : $o['status'];
    $tabs = ['all' => 'همه', 'pending' => 'در انتظار پاسخ', 'accepted' => 'تایید شده', 'closed' => 'رد / منقضی'];
    $counts = collect($offers)->groupBy($groupOf)->map->count(); $counts['all'] = count($offers);

    $pending = collect($offers)->where('status', 'pending');
    $best = collect($offers)->where('status', 'pending')->max('amount');
    $avg = (int) round(collect($offers)->avg('amount'));
    $title = 'وام ' . $fa($ad['amount']) . ' تومان بانک ' . $ad['bank'];
    $avatarColors = ['#1f8a9e', '#7a5ac8', '#d6322c', '#2e9a4f', '#e07b00', '#3e4ea0'];
@endphp

@section('main')

    <a href="{{ url('/userPanel/ads') }}" class="of-back pn-reveal">@include('userPanel.partials.icon', ['name' => 'arrow'])
        بازگشت به آگهی های من</a>

    <div class="pn-head pn-reveal">
        <span class="pn-head-ic">@include('userPanel.partials.icon', ['name' => 'clipboard'])</span>
        <div>
            <h2>درخواست های ثبت شده</h2>
            <p>درخواست‌هایی که خریداران برای آگهی شما ثبت کرده‌اند را بررسی و تایید یا رد کنید.</p>
        </div>
    </div>

    {{-- خلاصه آگهی --}}
    <section class="of-ad pn-reveal" style="--d:.06s">
        <img src="{{asset('website/img/bank/'.$ad['image'].'.png')}}" alt="" height="90">
        <div class="of-ad-info">
            <h3>{{ $title }}</h3>
            <div class="rq-meta">
                <span class="pn-id">شناسه وام <b>#{{ $faId($ad['id']) }}</b></span>
                <span class="rq-meta-i">@include('userPanel.partials.icon', ['name' => 'pin']){{ $ad['city'] }}</span>
                <span class="pn-pill pending">{{ $ad['state'] }}</span>
            </div>
        </div>
        <div class="of-ad-price"><small>قیمت پیشنهادی شما</small>
            <div><b>{{ $fa($ad['price']) }}</b> <span>تومان</span></div>
        </div>
        <a href="{{ url('/ads/' . $ad['id']) }}"
           class="btn btn-ghost of-ad-link">@include('userPanel.partials.icon', ['name' => 'eye']) مشاهده آگهی</a>
    </section>

    {{-- آمار --}}
    <section class="of-stats" aria-label="خلاصه درخواست‌ها">
        @foreach ([
          ['کل درخواست ها', $faId(count($offers)), 'clipboard', ''],
          ['بالاترین پیشنهاد', $fa($best) . ' <small>تومان</small>', 'trend', 'green'],
          ['میانگین پیشنهادها', $fa($avg) . ' <small>تومان</small>', 'card', ''],
          ['در انتظار پاسخ شما', $faId($pending->count()), 'clock', 'amber'],
        ] as $i => $s)
            <article class="of-stat pn-reveal {{ $s[3] }}" style="--d:{{ .1 + $i * .06 }}s">
                <span class="of-stat-ic">@include('userPanel.partials.icon', ['name' => $s[2]])</span>
                <div><small>{{ $s[0] }}</small><b>{!! $s[1] !!}</b></div>
            </article>
        @endforeach
    </section>

    <section class="pn-panel pn-reveal" style="--d:.2s">

        <div class="of-note" role="note">
            @include('userPanel.partials.icon', ['name' => 'shield'])
            <span>با تایید یک درخواست، خریدار <b>۷ روز</b> برای پرداخت مهلت دارد و سایر درخواست‌های در انتظار به‌صورت خودکار رد می‌شوند.</span>
        </div>

        <div class="rq-toolbar">
            <div class="rq-tabs" role="tablist" aria-label="فیلتر وضعیت">
                @foreach ($tabs as $key => $label)
                    <button type="button" class="rq-tab {{ $loop->first ? 'active' : '' }}" data-group="{{ $key }}"
                            role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                        {{ $label }}<span class="rq-count">{{ $faId($counts[$key] ?? 0) }}</span>
                    </button>
                @endforeach
            </div>
            <div class="of-tools">
                <label class="rq-search">
                    @include('userPanel.partials.icon', ['name' => 'search'])
                    <input type="search" id="rqSearch" placeholder="جستجو نام خریدار..." autocomplete="off"
                           aria-label="جستجوی خریدار">
                </label>
                <label class="of-sort"><span class="visually-hidden">مرتب سازی</span>
                    <select id="ofSort" aria-label="مرتب سازی">
                        <option value="amount">بیشترین مبلغ</option>
                        <option value="new">جدیدترین</option>
                    </select>
                </label>
            </div>
        </div>

        <div class="rq-list" id="rqList">
            @foreach ($offers as $o)
                @php
                    [$pc, $pt] = $statusMap[$o['status']];
                    $isBest = $o['status'] === 'pending' && $o['amount'] === $best;
                    $delta = round(($o['amount'] - $ad['price']) / $ad['price'] * 100);
                    $color = $avatarColors[$o['id'] % count($avatarColors)];
                    $initials = mb_substr($o['name'], 0, 1);
                @endphp
                <article
                    class="rq-card of-card pn-reveal {{ $isBest ? 'of-best' : '' }} {{ in_array($o['status'], ['rejected', 'expired']) ? 'of-muted' : '' }}"
                    data-group="{{ $groupOf($o) }}" data-search="{{ $o['name'] }} {{ $o['amount'] }}"
                    data-amount="{{ $o['amount'] }}" data-age="{{ $o['age'] }}"
                    style="--d:{{ .22 + $loop->index * .06 }}s">
                    @if ($isBest)
                        <span
                            class="of-ribbon">@include('userPanel.partials.icon', ['name' => 'star']) بهترین پیشنهاد</span>
                    @endif
                    <div class="rq-main">
                        <div class="rq-head">
                            <span class="of-avatar" style="background: {{ $color }}"
                                  aria-hidden="true">{{ $initials }}</span>
                            <div class="rq-info">
                                <h3>{{ $o['name'] }}
                                    @if ($o['verified'])
                                        <span class="of-verified" title="هویت احراز شده">@include('userPanel.partials.icon', ['name' => 'shield']) احراز شده</span>
                                    @else
                                        <span class="of-unverified">احراز نشده</span>
                                    @endif
                                </h3>
                                <div class="rq-meta">
                                    <span
                                        class="rq-meta-i">@include('userPanel.partials.icon', ['name' => 'clock']){{ $o['ago'] }}</span>
                                    <span
                                        class="rq-meta-i">@include('userPanel.partials.icon', ['name' => 'check']){{ $o['deals'] ? $faId($o['deals']) . ' معامله موفق' : 'اولین معامله' }}</span>
                                    @if ($o['rating'])
                                        <span
                                            class="rq-meta-i star">@include('userPanel.partials.icon', ['name' => 'star']){{ strtr((string) $o['rating'], ['.' => '٫', '0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']) }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="rq-side">
                            <div>
                                <div class="rq-price"><b>{{ $fa($o['amount']) }}</b> <small>تومان</small></div>
                                <span
                                    class="of-delta {{ $delta > 0 ? 'up' : '' }}">{{ $delta > 0 ? '↑ ' . $faId($delta) . '٪ بالاتر از قیمت شما' : 'هم‌قیمت با پیشنهاد شما' }}</span>
                            </div>
                            <div class="rq-act">
                                <span class="pn-pill {{ $pc }}">{{ $pt }}</span>
                                @if ($o['status'] === 'pending')
                                    <button type="button" class="btn btn-ghost of-reject" data-bs-toggle="modal"
                                            data-bs-target="#confirmModal"
                                            data-confirm-title="رد درخواست"
                                            data-confirm-text="درخواست {{ $o['name'] }} به مبلغ {{ $fa($o['amount']) }} تومان رد شود؟"
                                            data-confirm-action="{{ url('/panel/ads/' . $ad['id'] . '/offers/' . $o['id'] . '/reject') }}"
                                            data-confirm-method="POST"
                                            data-confirm-label="رد درخواست" data-confirm-tone="danger"
                                            data-confirm-reason="1">رد
                                    </button>
                                    <button type="button" class="btn btn-brand rq-cta" data-bs-toggle="modal"
                                            data-bs-target="#confirmModal"
                                            data-confirm-title="تایید درخواست"
                                            data-confirm-text="با تایید درخواست {{ $o['name'] }} (به مبلغ {{ $fa($o['amount']) }} تومان)، خریدار ۷ روز برای پرداخت مهلت دارد و سایر درخواست‌ها رد می‌شوند. ادامه می‌دهید؟"
                                            data-confirm-action="{{ url('/panel/ads/' . $ad['id'] . '/offers/' . $o['id'] . '/accept') }}"
                                            data-confirm-method="POST"
                                            data-confirm-label="بله، تایید شود" data-confirm-tone="brand">
                                        تایید درخواست <i class="la la-check"></i>
                                    </button>
                                @endif
                            </div>
                            @if ($o['status'] === 'pending')
                                <small
                                    class="rq-deadline">@include('userPanel.partials.icon', ['name' => 'clock']) {{ $o['expires'] }}
                                    تا پایان مهلت پاسخ</small>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="rq-empty" id="rqEmpty" hidden>
            <div class="rq-empty-ic"
                 aria-hidden="true">@include('userPanel.partials.icon', ['name' => 'clipboard'])</div>
            <h3>درخواستی پیدا نشد</h3>
            <p>فیلتر یا عبارت جستجو را تغییر دهید.</p>
        </div>
    </section>

    @include('userPanel.partials.confirm-modal')
@endsection
