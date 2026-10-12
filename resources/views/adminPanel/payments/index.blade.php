@extends('userPanel.layouts.layout')

@section('title', 'امور مالی')
@section('page_title', 'امور مالی')
@section('body_class', 'pn-adminPanel')

@php
    // نمونه داده؛ در پروژه واقعی از کنترلر پاس بدهید: compact('transactions', 'summary')
    // type: payment (پرداخت خریدار → امانت) | payout (واریز به فروشنده) | refund (برگشت وجه) | failed
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $faId = fn ($s) => strtr((string) $s, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $rate = 5; // درصد کمیسیون
    $rows = [
      // [tx, group, user, role, loan, bank, paid, date, age, extra(sheba,seller)]
      ['58963301', 'payout_due', 'سارا حسینی',  'فروشنده', 1235896, 'رسالت',   30000000, '۷ مهر – ۱۴:۲۳',  60,   'IR062960000000100324200001'],
      ['58963298', 'payout_due', 'امیر جعفری',  'فروشنده', 1235897, 'شهر',     32000000, '۷ مهر – ۱۰:۰۵',  240,  'IR610610000000700140150007'],
      ['58963290', 'escrow',     'علی رضایی',   'خریدار',  1235897, 'شهر',     32000000, '۶ مهر – ۱۶:۴۰',  1500, ''],
      ['58963270', 'escrow',     'مریم احمدی',  'خریدار',  1235899, 'جاویدان', 20000000, '۶ مهر – ۱۱:۱۲',  1700, ''],
      ['58963201', 'paid',       'فاطمه کاظمی', 'فروشنده', 1235890, 'مهر',     12000000, '۴ مهر – ۰۹:۳۰',  4300, 'IR540540000000800160170008'],
      ['58963155', 'paid',       'نرگس صادقی',  'فروشنده', 1235885, 'تجارت',   45000000, '۳ مهر – ۱۷:۰۰',  5800, 'IR180120000000300600700003'],
      ['58963100', 'refund',     'حسین کریمی',  'خریدار',  1235880, 'رسالت',   15000000, '۲ مهر – ۱۳:۲۰',  7200, ''],
      ['58963044', 'failed',     'رضا نوری',    'خریدار',  1235875, 'کشاورزی',  25000000, '۱ مهر – ۱۲:۱۰',  8600, ''],
      ['58962990', 'paid',       'مهدی رحیمی',  'فروشنده', 1235870, 'جاویدان', 30000000, '۳۰ شهریور – ۱۰:۱۰', 10000, 'IR150150000000500100110005'],
    ];
    $type = ['payout_due' => ['واریز به فروشنده', 'pending', 'در انتظار واریز', 'send'], 'escrow' => ['پرداخت خریدار', 'pending', 'در امانت', 'card'], 'paid' => ['واریز به فروشنده', 'approved', 'واریز شده', 'check'], 'refund' => ['برگشت وجه', 'refund', 'برگشت داده شد', 'undo'], 'failed' => ['پرداخت خریدار', 'rejected', 'ناموفق', 'close']];
    $c = collect($rows);
    $cnt = ['all' => $c->count()] + $c->groupBy(1)->map->count()->all();
    $tabs = ['all' => 'همه', 'payout_due' => 'در انتظار واریز', 'escrow' => 'در امانت', 'paid' => 'واریز شده', 'refund' => 'برگشتی', 'failed' => 'ناموفق'];
    $dueSum = $c->where(1, 'payout_due')->sum(6);
@endphp

@section('main')

    <div class="pn-head pn-reveal">
        <span class="pn-head-ic">@include('userPanel.partials.icon', ['name' => 'card'])</span>
        <div><h2>امور مالی</h2><p>تراکنش‌های سایت، مبالغ در امانت و واریز به فروشنده‌ها پس از تایید انتقال امتیاز.</p></div>
        <button type="button" class="btn btn-ghost pn-head-act" data-texport="finance">@include('userPanel.partials.icon', ['name' => 'download']) خروجی CSV</button>
    </div>

    <section class="of-stats">
        @foreach ([['حجم تراکنش ها (این ماه)', $fa(2480000000) . ' <small>تومان</small>', 'trend', ''], ['موجودی امانت', $fa(480000000) . ' <small>تومان</small>', 'shield', 'amber'], ['کمیسیون کسب شده (' . $faId($rate) . '٪)', $fa(36500000) . ' <small>تومان</small>', 'wallet', 'green'], ['واریزی های در انتظار', $fa($dueSum) . ' <small>تومان</small>', 'clock', 'amber']] as $i => $s)
            <article class="of-stat pn-reveal {{ $s[3] }}" style="--d:{{ .05 + $i * .05 }}s"><span class="of-stat-ic">@include('userPanel.partials.icon', ['name' => $s[2]])</span><div><small>{{ $s[0] }}</small><b>{!! $s[1] !!}</b></div></article>
        @endforeach
    </section>

    <section class="pn-panel pn-reveal at-box" style="--d:.15s" data-table data-page-size="8">
        <div class="of-note" role="note">@include('userPanel.partials.icon', ['name' => 'shield'])<span>مبلغ پرداختی خریدار تا <b>تایید انتقال امتیاز</b> در امانت می‌ماند. پس از تایید، بعد از کسر کمیسیون در بخش «در انتظار واریز» ظاهر می‌شود و باید به حساب فروشنده واریز و کد پیگیری آن ثبت شود.</span></div>

        <div class="rq-toolbar">
            <div class="rq-tabs" role="tablist" aria-label="فیلتر تراکنش">
                @foreach ($tabs as $k => $l)<button type="button" class="rq-tab {{ $loop->first ? 'active' : '' }}" data-tgroup="{{ $k }}" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $l }}<span class="rq-count">{{ $faId($cnt[$k] ?? 0) }}</span></button>@endforeach
            </div>
            <label class="rq-search">@include('userPanel.partials.icon', ['name' => 'search'])<input type="search" data-tsearch placeholder="جستجو شماره تراکنش، کاربر یا وام..." autocomplete="off" aria-label="جستجو"></label>
        </div>

        <div class="at-scroll">
            <table class="at-table">
                <thead><tr><th>تراکنش</th><th>کاربر</th><th>وام</th><th data-sort="amount">مبلغ</th><th>کمیسیون</th><th>وضعیت</th><th data-sort="age">زمان</th><th></th></tr></thead>
                <tbody>
                @foreach ($rows as $r)
                    @php [$tn, $pc, $pt, $ic] = $type[$r[1]]; $fee = (int) round($r[6] * $rate / 100); $net = $r[6] - $fee; @endphp
                    <tr data-group="{{ $r[1] }}" data-search="{{ $r[0] }} {{ $r[2] }} {{ $r[4] }} {{ $r[5] }}" data-amount="{{ $r[6] }}" data-age="{{ $r[8] }}" data-csv="{{ $r[0] }}|{{ $tn }}|{{ $r[2] }}|{{ $r[4] }}|{{ $r[6] }}|{{ $r[1] === 'payout_due' || $r[1] === 'paid' ? $fee : 0 }}|{{ $pt }}|{{ $r[7] }}">
                        <td data-label="تراکنش"><div class="at-user"><span class="at-ic {{ $r[1] }}">@include('userPanel.partials.icon', ['name' => $ic])</span><span class="at-name"><b dir="ltr">{{ $faId($r[0]) }}</b><small>{{ $tn }}</small></span></div></td>
                        <td data-label="کاربر"><b>{{ $r[2] }}</b><small class="d-block text-muted">{{ $r[3] }}</small></td>
                        <td data-label="وام">#{{ $faId($r[4]) }} <span class="vf-bank">{{ $r[5] }}</span></td>
                        <td data-label="مبلغ"><b>{{ $fa($r[6]) }}</b></td>
                        <td data-label="کمیسیون">{{ in_array($r[1], ['payout_due', 'paid']) ? $fa($fee) : '—' }}</td>
                        <td data-label="وضعیت"><span class="pn-pill {{ $pc }}">{{ $pt }}</span></td>
                        <td data-label="زمان">{{ $r[7] }}</td>
                        <td class="at-actions">
                            @if ($r[1] === 'payout_due')
                                <button type="button" class="btn btn-brand at-btn" data-bs-toggle="modal" data-bs-target="#payoutModal" data-tx="{{ $faId($r[0]) }}" data-seller="{{ $r[2] }}" data-sheba="{{ $r[9] }}" data-loan="#{{ $faId($r[4]) }} · بانک {{ $r[5] }}"
                                        data-gross="{{ $fa($r[6]) }}" data-fee="{{ $fa($fee) }}" data-net="{{ $fa($net) }}" data-action="{{ url('/adminPanel/finance/payouts/' . $r[0]) }}">ثبت واریز</button>
                            @elseif ($r[1] === 'escrow')
                                <button type="button" class="btn btn-ghost of-reject at-btn" data-bs-toggle="modal" data-bs-target="#confirmModal" data-confirm-title="برگشت وجه به خریدار" data-confirm-text="مبلغ {{ $fa($r[6]) }} تومان به حساب {{ $r[2] }} برگشت داده شود و معامله لغو شود؟"
                                        data-confirm-action="{{ url('/adminPanel/finance/refunds/' . $r[0]) }}" data-confirm-method="POST" data-confirm-label="برگشت وجه" data-confirm-tone="danger" data-confirm-reason="1">برگشت وجه</button>
                            @else<a href="{{ url('/adminPanel/users/2001') }}" class="rd-ic-btn" title="مشاهده کاربر" aria-label="مشاهده کاربر">@include('userPanel.partials.icon', ['name' => 'eye'])</a>@endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="at-foot"><span data-tinfo></span><div class="at-pager" data-tpager></div></div>
        <div class="rq-empty" data-tempty hidden><div class="rq-empty-ic" aria-hidden="true">@include('userPanel.partials.icon', ['name' => 'card'])</div><h3>تراکنشی پیدا نشد</h3><p>فیلتر یا عبارت جستجو را تغییر دهید.</p></div>
    </section>

    @include('adminPanel.partials.payout-modal')
    @include('userPanel.partials.confirm-modal')
@endsection