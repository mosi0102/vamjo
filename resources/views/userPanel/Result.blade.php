@extends('userPanel.layouts.Blank')

@php
    // نمونه داده؛ در پروژه واقعی از کنترلر (بعد از Verify درگاه) پاس بدهید: compact('status', 'payment')
    // تست: /payment/result?status=failed
    $status = request('status') === 'failed' ? 'failed' : 'success';
    $ok = $status === 'success';
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $payment = [
      'amount' => 30000000, 'paid_at' => '۷ مهر ۱۴۰۵ – ۱۴:۲۳', 'ref' => '58963215', 'tx' => '58963215',
      'request_id' => 1235896, 'reason' => 'انصراف از پرداخت در درگاه',
    ];
@endphp

@section('title', $ok ? 'پرداخت موفق' : 'پرداخت ناموفق')
@section('body_class', 'pr-' . $status)

@section('main')
    <section class="pr-card {{ $status }}" id="prCard" data-status="{{ $status }}" role="status" aria-live="polite">

        <div class="pr-ic" aria-hidden="true">
            <svg viewBox="0 0 132 116">
                <ellipse cx="66" cy="108" rx="46" ry="6" fill="{{ $ok ? '#1e5a45' : '#b01e1e' }}" opacity=".1"/>
                @if ($ok)
                    <rect x="42" y="4" width="34" height="44" rx="4" fill="#f6f1e4" stroke="#e6dfc9" stroke-width="1.5"/>
                    <circle cx="59" cy="22" r="9" fill="#27795b"/><path d="M54.5 22l3.2 3.2 6-6.6" stroke="#fff" stroke-width="2.2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M46 44l3 3 3-3 3 3 3-3 3 3 3-3 3 3 2-2" stroke="#e6dfc9" stroke-width="1.5" fill="none"/>
                    <rect x="14" y="34" width="92" height="64" rx="9" fill="#27795b"/><rect x="14" y="44" width="92" height="14" fill="#f6f1e4"/>
                    <rect x="24" y="66" width="14" height="12" rx="3" fill="#f6f1e4"/><path d="M24 86h24M52 86h8" stroke="#f6f1e4" stroke-width="3" stroke-linecap="round"/>
                    <circle cx="92" cy="82" r="22" fill="#f6f1e4" stroke="#1e5a45" stroke-width="4"/>
                    <path class="pr-draw" d="M81 82l8 8 14-16" stroke="#1e5a45" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round" pathLength="1"/>
                @else
                    <rect x="14" y="26" width="92" height="64" rx="9" fill="#b0393b"/><rect x="14" y="36" width="92" height="14" fill="#f6e4e4"/>
                    <rect x="24" y="58" width="14" height="12" rx="3" fill="#f6e4e4"/><path d="M24 78h24M52 78h8" stroke="#f6e4e4" stroke-width="3" stroke-linecap="round"/>
                    <circle cx="92" cy="76" r="22" fill="#fff4f4" stroke="#8f1d1f" stroke-width="4"/>
                    <path class="pr-draw" d="M83 67l18 18M101 67L83 85" stroke="#8f1d1f" stroke-width="6" fill="none" stroke-linecap="round" pathLength="1"/>
                @endif
            </svg>
        </div>

        <h1 class="pr-title">{{ $ok ? 'پرداخت موفق' : 'پرداخت ناموفق' }}</h1>
        <p class="pr-sub">{{ $ok ? 'پرداخت شما با موفقیت انجام شد.' : 'متاسفانه پرداخت شما انجام نشد.' }}</p>

        @unless ($ok)
            <div class="pr-note">
                @include('userPanel.partials.icon', ['name' => 'shield'])
                <span>در صورتی که مبلغی از حساب شما کسر شده باشد، حداکثر تا ۷۲ ساعت آینده به حساب شما بازگردانده می‌شود.</span>
            </div>
        @endunless

        <div class="pr-rows">
            <div class="pr-row"><span>{{ $ok ? 'مبلغ پرداخت' : 'مبلغ درخواستی' }}</span><div><b class="amt">{{ $fa($payment['amount']) }}</b> <small>تومان</small></div></div>
            <div class="pr-row"><span>زمان پرداخت</span><b>{{ $payment['paid_at'] }}</b></div>
            @if ($ok)
                <div class="pr-row"><span>شماره تراکنش</span><b class="pr-copy" data-copy="{{ $payment['tx'] }}" tabindex="0" role="button" title="کپی">{{ strtr($payment['tx'], ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']) }} @include('userPanel.partials.icon', ['name' => 'copy'])</b></div>
                <div class="pr-row"><span>شناسه تراکنش</span><b class="pr-copy" data-copy="{{ $payment['ref'] }}" tabindex="0" role="button" title="کپی">{{ strtr($payment['ref'], ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']) }} @include('userPanel.partials.icon', ['name' => 'copy'])</b></div>
            @else
                <div class="pr-row"><span>علت خطا</span><b class="pr-reason">{{ $payment['reason'] }}</b></div>
            @endif
        </div>

        <div class="pr-actions">
            @if ($ok)
                <a href="{{ url('/') }}" class="btn btn-brand btn-lg">بازگشت به سایت</a>
                <div class="pr-links">
                    <a href="{{ url('/panel/requests') }}" class="btn btn-ghost">مشاهده درخواست ها</a>
                    <button type="button" class="btn btn-ghost" id="prPrint">@include('userPanel.partials.icon', ['name' => 'doc']) چاپ رسید</button>
                </div>
            @else
                <a href="{{ url('/panel/requests') }}" class="btn btn-brand btn-lg">تلاش مجدد برای پرداخت</a>
                <div class="pr-links">
                    <a href="{{ url('/panel/requests') }}" class="btn btn-ghost">بازگشت به درخواست ها</a>
                    <a href="{{ url('/panel/tickets') }}" class="btn btn-ghost">@include('userPanel.partials.icon', ['name' => 'chat']) تماس با پشتیبانی</a>
                </div>
            @endif
        </div>
    </section>
@endsection
