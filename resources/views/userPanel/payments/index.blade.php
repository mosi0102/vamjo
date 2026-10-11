@extends('userPanel.layouts.layout')

@section('title', 'پرداخت های من')
@section('page_title', 'پرداخت ها')
@section('body_class', 'pn-payments')

@php
    // نمونه داده؛ در پروژه واقعی از کنترلر پاس بدهید: compact('payments')
    // status: escrow (در امانت) | released (تسویه شده) | refunded (برگشت داده شد) | failed (ناموفق) | age = دقیقه از پرداخت (برای مرتب‌سازی)
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $faId = fn ($n) => strtr((string) $n, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $payments = [
      ['tx' => '58963215', 'loan' => 1235896, 'bank' => 'رسالت','image'=>'8',   'l' => 'ر', 'c' => '#1f8a9e', 'amount' => 300000000, 'paid' => 30000000, 'date' => '۷ مهر ۱۴۰۵ – ۱۴:۲۳',  'age' => 1200,  'status' => 'escrow'],
      ['tx' => '58963101', 'loan' => 1235897, 'bank' => 'شهر','image'=>'7',     'l' => 'ش', 'c' => '#d6322c', 'amount' => 300000000, 'paid' => 32000000, 'date' => '۵ مهر ۱۴۰۵ – ۱۰:۰۵',  'age' => 4300,  'status' => 'escrow'],
      ['tx' => '58962877', 'loan' => 1235898, 'bank' => 'جاویدان','image'=>'4', 'l' => 'ج', 'c' => '#e07b00', 'amount' => 300000000, 'paid' => 30000000, 'date' => '۲۸ شهریور ۱۴۰۵ – ۱۶:۴۰', 'age' => 14400, 'status' => 'released'],
      ['tx' => '58962410', 'loan' => 1235890, 'bank' => 'مهر','image'=>'3',     'l' => 'م', 'c' => '#2e9a4f', 'amount' => 100000000, 'paid' => 12000000, 'date' => '۱۹ شهریور ۱۴۰۵ – ۱۱:۱۲', 'age' => 27000, 'status' => 'refunded'],
      ['tx' => '58962055', 'loan' => 1235885, 'bank' => 'تجارت','image'=>'9',   'l' => 'ت', 'c' => '#3e4ea0', 'amount' => 250000000, 'paid' => 45000000, 'date' => '۱۲ شهریور ۱۴۰۵ – ۰۹:۳۰', 'age' => 37000, 'status' => 'failed'],
    ];
    $statusMap = ['escrow' => ['pending', 'در امانت'], 'released' => ['approved', 'تسویه شده'], 'refunded' => ['refund', 'برگشت داده شد'], 'failed' => ['rejected', 'ناموفق']];
    $flows = [
      'escrow'   => [['پرداخت', 'done'], ['امانت', 'done'], ['انتقال امتیاز', 'current'], ['تسویه', 'todo']],
      'released' => [['پرداخت', 'done'], ['امانت', 'done'], ['انتقال امتیاز', 'done'], ['تسویه', 'done']],
      'refunded' => [['پرداخت', 'done'], ['امانت', 'done'], ['برگشت وجه', 'refund']],
    ];
    $hints = ['escrow' => 'مبلغ تا تایید انتقال امتیاز نزد وامجو امانت می‌ماند.', 'refunded' => 'معامله انجام نشد و مبلغ به حساب شما برگشت داده شد.', 'failed' => 'پرداخت در درگاه انجام نشد؛ مبلغی از حساب شما کسر نشده است.'];
    $tabs = ['all' => 'همه', 'escrow' => 'در امانت', 'released' => 'تسویه شده', 'refunded' => 'برگشت داده شد', 'failed' => 'ناموفق'];
    $counts = collect($payments)->groupBy('status')->map->count(); $counts['all'] = count($payments);
    $col = collect($payments);
    $paidTotal = $col->whereIn('status', ['escrow', 'released'])->sum('paid');
    $escrowSum = $col->where('status', 'escrow')->sum('paid');
    $refundSum = $col->where('status', 'refunded')->sum('paid');
@endphp

@section('main')

    <div class="pn-head pn-reveal">
        <span class="pn-head-ic">@include('userPanel.partials.icon', ['name' => 'card'])</span>
        <div>
            <h2>پرداخت های من</h2>
            <p>تمامی پرداخت‌ها و تراکنش‌های شما برای خرید امتیاز وام در این صفحه دیده می‌شود.</p>
        </div>
        <button type="button" class="btn btn-ghost pn-head-act"
                id="pmExport">@include('userPanel.partials.icon', ['name' => 'download']) دانلود گزارش (CSV)
        </button>
    </div>

    {{-- خلاصه مالی --}}
    <section class="of-stats" aria-label="خلاصه پرداخت‌ها">
        @foreach ([
          ['کل مبلغ پرداخت شده', $fa($paidTotal) . ' <small>تومان</small>', 'wallet', 'green'],
          ['در امانت وامجو', $fa($escrowSum) . ' <small>تومان</small>', 'shield', 'amber'],
          ['برگشت داده شده', $fa($refundSum) . ' <small>تومان</small>', 'undo', ''],
          ['تعداد تراکنش ها', $faId(count($payments)), 'card', ''],
        ] as $i => $s)
            <article class="of-stat pn-reveal {{ $s[3] }}" style="--d:{{ .08 + $i * .06 }}s">
                <span class="of-stat-ic">@include('userPanel.partials.icon', ['name' => $s[2]])</span>
                <div><small>{{ $s[0] }}</small><b>{!! $s[1] !!}</b></div>
            </article>
        @endforeach
    </section>

    <section class="pn-panel pn-reveal" style="--d:.2s">

        <div class="of-note" role="note">
            @include('userPanel.partials.icon', ['name' => 'shield'])
            <span>مبلغ پرداختی شما تا <b>انتقال قطعی امتیاز</b> به‌صورت امانت نزد وامجو نگهداری می‌شود و در صورت انجام نشدن معامله به حساب شما برمی‌گردد.</span>
        </div>

        <div class="rq-toolbar">
            <div class="rq-tabs" role="tablist" aria-label="فیلتر وضعیت">
                @foreach ($tabs as $key => $label)
                    <button type="button" class="rq-tab {{ $loop->first ? 'active' : '' }}" data-group="{{ $key }}"
                            role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $label }}<span
                            class="rq-count">{{ $faId($counts[$key] ?? 0) }}</span></button>
                @endforeach
            </div>
            <div class="of-tools">
                <label class="rq-search">
                    @include('userPanel.partials.icon', ['name' => 'search'])
                    <input type="search" id="rqSearch" placeholder="جستجو شناسه تراکنش، وام یا بانک..."
                           autocomplete="off" aria-label="جستجو در پرداخت‌ها">
                </label>
                <label class="of-sort"><span class="visually-hidden">مرتب سازی</span>
                    <select id="ofSort" aria-label="مرتب سازی">
                        <option value="new">جدیدترین</option>
                        <option value="amount">بیشترین مبلغ</option>
                    </select>
                </label>
            </div>
        </div>

        <div class="rq-list" id="rqList">
            @forelse ($payments as $p)
                @php
                    [$pc, $pt] = $statusMap[$p['status']]; $title = 'وام ' . $fa($p['amount']) . ' تومان بانک ' . $p['bank'];
                    $txFa = $faId($p['tx']);
                @endphp
                <article class="rq-card pm-card pn-reveal" data-group="{{ $p['status'] }}"
                         data-amount="{{ $p['paid'] }}" data-age="{{ $p['age'] }}"
                         data-search="{{ $p['tx'] }} {{ $p['loan'] }} {{ $p['bank'] }} {{ $p['paid'] }}"
                         data-csv="{{ $p['tx'] }}|{{ $p['loan'] }}|{{ $p['bank'] }}|{{ $p['paid'] }}|{{ $p['date'] }}|{{ $pt }}"
                         style="--d:{{ .22 + $loop->index * .06 }}s">
                    <div class="rq-main">
                        <div class="rq-head">

                            <img src="{{asset('website/img/bank/'.$p['image'].'.png')}}" alt="" height="90">
                            <div class="rq-info">
                                <h3>{{ $title }}</h3>
                                <div class="rq-meta">
                                    <button type="button" class="pn-id rq-id" data-copy="{{ $p['loan'] }}"
                                            title="کپی شناسه وام">شناسه وام
                                        <b>#{{ $faId($p['loan']) }}</b>@include('userPanel.partials.icon', ['name' => 'copy'])
                                    </button>
                                    <span
                                        class="rq-meta-i">@include('userPanel.partials.icon', ['name' => 'calendar']){{ $p['date'] }}</span>
                                    <button type="button" class="rq-meta-i pm-tx rq-id" data-copy="{{ $p['tx'] }}"
                                            data-copy-label="شماره تراکنش کپی شد"
                                            title="کپی شماره تراکنش">@include('userPanel.partials.icon', ['name' => 'card'])
                                        تراکنش <span dir="ltr">{{ $txFa }}</span></button>
                                </div>
                                @if (isset($flows[$p['status']]))
                                    <ol class="pm-flow" aria-label="مراحل پرداخت">
                                        @foreach ($flows[$p['status']] as $fi => [$fl, $fc])
                                            <li class="{{ $fc }}" style="--i:{{ $fi }}"><i></i><span>{{ $fl }}</span>
                                            </li>
                                        @endforeach
                                    </ol>
                                @endif
                                @if (isset($hints[$p['status']]))
                                    <p class="pm-hint {{ $p['status'] }}">{{ $hints[$p['status']] }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="rq-side">
                            <div class="rq-price"><b>{{ $fa($p['paid']) }}</b> <small>تومان</small></div>
                            <span class="pn-pill {{ $pc }}">{{ $pt }}</span>
                        </div>
                    </div>
                    <div class="ad-actions">
                        @unless ($p['status'] === 'failed')
                            <button type="button" class="ad-act" data-bs-toggle="modal" data-bs-target="#receiptModal"
                                    data-amount="{{ $fa($p['paid']) }}" data-date="{{ $p['date'] }}"
                                    data-tx="{{ $txFa }}" data-title="{{ $title }}" data-loan="#{{ $faId($p['loan']) }}"
                                    data-status="{{ $pt }}" data-status-class="{{ $pc }}"
                                    data-gateway="زرین پال">@include('userPanel.partials.icon', ['name' => 'doc'])
                                مشاهده رسید
                            </button>
                        @endunless
                        <a href="{{ url('/panel/requests') }}"
                           class="ad-act">@include('userPanel.partials.icon', ['name' => 'clipboard']) مشاهده
                            درخواست</a>
                        @if ($p['status'] === 'failed')
                            <a href="{{ url('/panel/requests') }}"
                               class="ad-act highlight">@include('userPanel.partials.icon', ['name' => 'undo']) تلاش
                                مجدد پرداخت</a>
                        @endif
                        <a href="{{ url('/panel/tickets/create') }}"
                           class="ad-act ms-lg-auto">@include('userPanel.partials.icon', ['name' => 'chat']) گزارش
                            مشکل</a>
                    </div>
                </article>
            @empty
                <div class="rq-empty">
                    <div class="rq-empty-ic"
                         aria-hidden="true">@include('userPanel.partials.icon', ['name' => 'card'])</div>
                    <h3>هنوز پرداختی انجام نداده‌اید</h3>
                    <p>بعد از تایید درخواست‌هایتان توسط فروشنده، پرداخت‌ها در اینجا دیده می‌شود.</p>
                    <a href="{{ url('/ads') }}" class="btn btn-brand">مشاهده آگهی ها</a>
                </div>
            @endforelse
        </div>

        <div class="rq-empty" id="rqEmpty" hidden>
            <div class="rq-empty-ic" aria-hidden="true">@include('userPanel.partials.icon', ['name' => 'card'])</div>
            <h3>تراکنشی پیدا نشد</h3>
            <p>فیلتر یا عبارت جستجو را تغییر دهید.</p>
        </div>
    </section>

    @include('userPanel.partials.receipt-modal')
@endsection
