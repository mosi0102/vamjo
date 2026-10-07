@extends('website.layouts.layout')

@section('title', 'وام ۳۰۰,۰۰۰,۰۰۰ تومانی بانک رسالت | وام‌جو')
@section('description', 'جزئیات آگهی وام ۳۰۰ میلیون تومانی بانک رسالت؛ نرخ سود، تعداد اقساط و شرایط ضامن.')
@section('body_class', 'page-detail')

@php
    // نمونه داده؛ در پروژه واقعی از کنترلر پاس بدهید: return view('ads.detail.main', compact('ad', 'similar'));
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹', ',' => ',']);
    $faDigits = fn ($s) => strtr((string) $s, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $ad = [
      'id' => 1, 'code' => '1235896','bank'=>'resalat', 'bank_name' => 'رسالت', 'city' => 'سبزوار','image'=>'8', 'ago' => '۳ روز پیش',
      'amount' => 300000000, 'rate' => 2, 'months' => 60, 'installment' => 5100000, 'total' => 306000000,
      'guarantor' => 'یک ضامن کارمند رسمی یا کاسب دارای جواز کسب معتبر',
      'price' => 30000000, 'max_offer' => 45000000, 'offers' => 8, 'phone' => '05144444400'
    ];
    $svg = [
      'wallet' => '<path d="M3 7a2 2 0 0 1 2-2h12v4"/><path d="M3 7v11a2 2 0 0 0 2 2h14a1 1 0 0 0 1-1v-9a1 1 0 0 0-1-1H5a2 2 0 0 1-2-2z"/><circle cx="16.5" cy="14.5" r="1.2"/>',
      'percent' => '<path d="M19 5L5 19"/><circle cx="7" cy="7" r="2.2"/><circle cx="17" cy="17" r="2.2"/>',
      'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
      'calc' => '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M8 7h8M8 12h.01M12 12h.01M16 12h.01M8 16h.01M12 16h.01M16 16h.01"/>',
      'doc' => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/>',
      'user' => '<circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0M16 11l2 2 4-4"/>',
    ];
    $rows = [
      ['wallet', 'مبلغ وام', $fa($ad['amount']) . ' تومان'],
      ['percent', 'نرخ سود', $faDigits($ad['rate']) . '%'],
      ['calendar', 'تعداد اقساط', $faDigits($ad['months']) . ' ماهه'],
      ['calc', 'مبلغ هر قسط', $fa($ad['installment']) . ' تومان'],
      ['doc', 'مبلغ بازپرداخت نهایی', $fa($ad['total']) . ' تومان'],
      ['user', 'شرایط ضامن', $ad['guarantor']],
    ];
    $similar = [
      ['id'=>11,'c'=>'#e07b00','l'=>'ج','amount'=>100000000,'buy'=>20000000,'months'=>24,'rate'=>23,'offers'=>8,'image'=>'8', 'bank_name' => 'رسالت'],
      ['id'=>12,'c'=>'#3e4ea0','l'=>'ت','amount'=>250000000,'buy'=>45000000,'months'=>24,'rate'=>23,'offers'=>8,'image'=>'8', 'bank_name' => 'رسالت'],
      ['id'=>13,'c'=>'#1f8a9e','l'=>'ر','amount'=>120000000,'buy'=>25000000,'months'=>24,'rate'=>23,'offers'=>8,'image'=>'8', 'bank_name' => 'رسالت'],
    ];
@endphp

@section('main')
    <div class="container detail-page">

        <nav class="crumbs d-none d-md-flex" aria-label="مسیر صفحه">
            <a href="{{ url('/') }}">صفحه نخست</a><span class="sep-ic" aria-hidden="true"><i class="la la-angle-left"></i> </span>
            <a href="{{ url('/ads') }}">آگهی‌های وام</a><span class="sep-ic" aria-hidden="true"><i class="la la-angle-left"></i> </span>
            <span>وام {{ $fa($ad['amount']) }} تومانی {{ $ad['bank_name'] }}</span>
        </nav>

        <div class="row g-3 g-lg-4">

            {{-- ===== ستون اصلی (راست) ===== --}}
            <div class="col-lg-9">

                {{-- عنوان آگهی --}}
                <div class="d-card title-card reveal">
                    <span class="ribbon"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5 9-10"/></svg> تایید شده</span>
                    <img src="{{asset('website/img/bank/'.$ad['image'].'.png')}}" style="height: 60px; "/>
                    <div class="title-info">
                        <h1>وام {{ $fa($ad['amount']) }} تومان بانک {{ $ad['bank_name'] }}</h1>
                        <div class="meta">
                            <span class="id-pill">شناسه وام <b>#{{ $faDigits($ad['code']) }}</b></span>
                            <span class="meta-i"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18M8 2v4M16 2v4"/></svg>{{ $ad['ago'] }}</span>
                            <span class="meta-i"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s7-6.2 7-11.5A7 7 0 0 0 5 9.5C5 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>{{ $ad['city'] }}</span>
                        </div>
                    </div>
                </div>

                {{-- مشخصات وام --}}
                <div class="d-card spec-table reveal">
                    @foreach ($rows as $r)
                        <div class="spec-row">
                            <div class="spec-key"><span class="spec-ic"><svg viewBox="0 0 24 24" aria-hidden="true">{!! $svg[$r[0]] !!}</svg></span>{{ $r[1] }}</div>
                            <div class="spec-val">{{ $r[2] }}</div>
                        </div>
                    @endforeach
                </div>

                {{-- هشدار --}}
                <div class="warn-card reveal" role="note">
                    <div class="warn-ic" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3l10 18H2z"/><path d="M12 10v5M12 18v.01"/></svg></div>
                    <div>
                        <h2>هشدار امنیتی</h2>
                        <p>برای جلوگیری از کلاهبرداری، از سیستم <b>معامله امن</b> استفاده کنید. پلتفرم در قبال واریز مستقیم مسئولیتی ندارد.</p>
                    </div>
                </div>
            </div>

            {{-- ===== ستون کناری (چپ) ===== --}}
            <aside class="col-lg-3">

                <div class="d-card buy-card reveal">
                    <div class="secure-box">
                        <div class="secure-ic" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3l8 3v6c0 4.5-3.3 8.3-8 9-4.7-.7-8-4.5-8-9V6z"/><rect x="9" y="11" width="6" height="5" rx="1"/><path d="M10 11V9.5a2 2 0 0 1 4 0V11"/></svg></div>
                        <div>
                            <h2>تضمین امنیت معامله</h2>
                            <p>کاربر گرامی، وجه شما تا تایید نهایی وام، در <b>وامینجا</b> بلوکه می‌ماند.</p>
                        </div>
                    </div>

                    <div class="price-row">
                        <span class="price-label">قیمت پیشنهادی<br>فروشنده</span>
                        <div class="price-val"><b>{{ $fa($ad['price']) }}</b><small>تومان</small></div>
                    </div>

                    <div class="buy-actions">
                        <button type="button" class="btn btn-brand btn-lg flex-grow-1" id="requestBtn">ثبت درخواست</button>
                        <button type="button" class="bookmark-btn" id="bookmarkBtn" aria-pressed="false" aria-label="ذخیره آگهی" data-login-required>
                            <svg viewBox="0 0 24 24"><path d="M6 4h12a1 1 0 0 1 1 1v15l-7-4-7 4V5a1 1 0 0 1 1-1z"/></svg>
                        </button>
                    </div>
                </div>

                <div class="d-card support-card reveal">
                    <p>در صورت نیاز به <b>مشاوره</b>، برای ارتباط با <b>پشتیبانی</b> با شماره زیر <b>تماس بگیرید.</b></p>
                    <a class="support-phone" dir="ltr" href="tel:{{ $ad['phone'] }}">{{ $faDigits('051 44 44 44 00') }}</a>
                </div>

                <div class="offers-card reveal">
                    <svg viewBox="0 0 24 24" class="offers-ic" aria-hidden="true"><path d="M4 7a2 2 0 0 1 2-2h4l2 2h6a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"/><path d="M8 13h8M8 16h5"/></svg>
                    <b>{{ $faDigits($ad['offers']) }}</b>
                    <span>پیشنهاد برای این وام ثبت شده است</span>
                </div>
            </aside>
        </div>

        {{-- ===== توضیحات ===== --}}
        <article class="seo-text desc reveal">
            <h2>وام فوری بانک رسالت</h2>
            <p>برای دریافت وام فوری رسالت بانک لازم نیست به بانک رسالت مراجعه کنید. آخرین مرحله، مراجعه به سامانه‌ی اعتبارسنجی مرآت و مشاهده‌ی میزان اعتبار است. حالا می‌توانید مراحل دریافت وام را شروع کنید. مراحل دریافت وام در شعبه‌های بانک رسالت انجام می‌شوند. معمولا یک تا دو هفته بعد از تشکیل پرونده، وام شما واریز می‌شود. وام این بانک را می‌توانید بین ۱ تا ۵ سال پرداخت کنید.</p>

            <h3>پیگیری وام قرض‌الحسنه بانک رسالت</h3>
            <p>وام قرض الحسنه رسالت ۲ درصد سود دارد و با کارمزد دو درصد نیز به شما تعلق می‌گیرد. سقف این وام ۲۰۰ میلیون تومان است و برای دریافت آن به سپرده احتیاج دارید. برای دریافت وام قرض‌الحسنه‌ی بانک رسالت، سپرده‌ی شما حداقل ۳ و حداقل ۶ ماه باید در حساب بماند. نسبت مبلغ وام به میزان سپرده ۱۰۰ درصد است؛ یعنی اگر خواب سپرده ۶ ماه باشد، نسبت مبلغ وام ۱۰۰ درصد است و باید مبلغ آن را در ۱۲ ماه پرداخت کنید.</p>
            <p>درصورتی‌که خواب سپرده بیشتر از ۶ ماه باشد، ۲ قسط به طول مدت بازپرداخت افزوده می‌شود. برای ثبت‌نام در وام قرض‌الحسنه‌ی بانک رسالت باید در پیش‌خوان مجازی بانک رسالت ثبت‌نام کنید. ثبت‌نام در این پیش‌خوان با کد ملی و شماره همراه احتیاج دارد. بعد از ثبت‌نام غیرحضوری، کارشناس‌های بانک با شما تماس می‌گیرند و مامور بانک برای انجام مراحل احراز هویت و تطبیق مدارک به محل شما مراجعه می‌کند.</p>
        </article>

        {{-- ===== آگهی‌های مشابه ===== --}}
        <section class="similar">
            <div class="section-head">
                <h2 class="section-title">آگهی های مشابه</h2>
                <a href="{{ url('/ads') }}" class="link-more">مشاهده همه <span aria-hidden="true">←</span></a>
            </div>
            <div class="row g-3 g-lg-4 similar-row">
                @foreach ($similar as $a)
                    <div class="col-md-6 col-lg-4 reveal">@include('website.partials.ad-card', ['a' => $a])</div>
                @endforeach
            </div>
        </section>
    </div>

    {{-- نوار ثبت درخواست چسبان (فقط موبایل/تبلت) --}}
    <div class="buy-bar d-lg-none">
        <div class="bb-price"><small>قیمت پیشنهادی فروشنده</small><b>{{ $fa($ad['price']) }} <span>تومان</span></b></div>
        <button type="button" class="btn btn-brand" id="requestBtnBar">ثبت درخواست</button>
    </div>

    @include('website.partials.modals.request', ['ad' => $ad, 'fa' => $fa])
@endsection
