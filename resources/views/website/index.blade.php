@extends('website.layouts.layout')

@section('title', 'وام‌جو | پلتفرم خرید و فروش امن امتیاز وام')
@section('description', 'با تضمین امنیت معامله، امتیاز وام خود را نقد کنید یا وام مورد نیاز خود را پیدا کنید.')
@section('body_class', 'page-home')

@php
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹', ',' => '،']);
@endphp

@section('main')
    <!-- ============ Hero ============ -->
    <section class="hero" id="home">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-6 hero-text">
                    <h1>اولین پلتفرم<br><span class="c-green">خرید</span> و <span class="c-green">فروش</span> امن<br>امتیاز
                        وام</h1>
                    <p>با تضمین امنیت معامله، امتیاز وام خود را نقد کنید یا وام مورد نیاز خود را پیدا کنید.</p>
                    <div class="hero-buttons">
                        <a href="#ads" class="btn btn-brand btn-lg">فروش امتیاز وام</a>
                        <a href="#ads" class="btn btn-ghost btn-lg">خرید امتیاز وام</a>
                    </div>
                </div>

                <div class="col-lg-6 hero-art" aria-hidden="true">
                    <div class="parallax" id="parallax">
                        <div class="layer layer-wallet" data-speed="-0.05" data-move="-14">
                            <img class="float-b" src="{{ asset('website/img/hero-wallet.png') }}" alt="" width="752"
                                 height="279">
                        </div>
                        <div class="layer layer-coin" data-speed="0.07" data-move="22">
                            <img class="float-a" src="{{ asset('website/img/hero-coin.png') }}" alt="" width="752"
                                 height="362">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ Banks strip ============ -->
    <section class="banks" id="banks" aria-label="بانک‌های تحت پوشش">
        <div class="banks-track">
            @foreach (array_merge($banks, $banks) as $b)
                <div class="bank"><img src="{{asset('website/img/bank/'.$b['image'].'.png')}}"
                                       style="height: 60px; "/> {{ $b['bank_name'] }}</div>
            @endforeach
        </div>
    </section>

    <!-- ============ Featured ads ============ -->
    <section class="section" id="ads">
        <div class="container">
            <div class="section-head">
                <div>
                    <h2 class="section-title">آگهی‌های ویژه</h2>
                    <p class="section-sub">جدیدترین و پربازدیدترین آگهی‌ها</p>
                </div>
                <a href="{{ route('site.ads') }}" class="link-more">مشاهده همه <span aria-hidden="true">←</span></a>
            </div>
            <div class="row g-4">
                @foreach ($ads as $a)
                    <div class="col-md-6 col-lg-4 reveal">
                        @include('website.partials.ad-card', ['a' => $a])
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============ Stats ============ -->
    <section class="stats">
        <div class="container">
            <div class="row g-4 text-center">
                {{-- آمار از دیتابیس: Site\HomeController@index --}}
                <div class="col-6 col-lg-3">
                    <div class="stat-num"><span class="count" data-count="{{ $stats['ads'] }}">۰</span>+</div>
                    <p>آگهی ثبت شده<br>واگذاری وام</p></div>
                <div class="col-6 col-lg-3">
                    <div class="stat-num"><span class="count" data-count="{{ $stats['users'] }}">۰</span>+</div>
                    <p>کاربر احراز هویت<br>شده در سامانه</p></div>
                <div class="col-6 col-lg-3">
                    <div class="stat-num"><span class="count" data-count="{{ $stats['banks'] }}">۰</span>+</div>
                    <p>بانک‌های تحت پوشش<br>سامانه وام اینجا</p></div>
                <div class="col-6 col-lg-3">
                    <div class="stat-num"><span class="count" data-count="{{ $stats['deals'] }}">۰</span>+</div>
                    <p>ثبت معامله موفق و<br>امن در سامانه</p></div>
            </div>
        </div>
    </section>

    <!-- ============ Steps ============ -->
    <section class="section steps">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title d-inline-block">راهنمای ثبت درخواست امتیاز</h2>
                <p class="section-sub">در چهار گام ساده، امتیاز وام خود را بفروشید یا وام مورد نیاز خود را با خیال
                    راحت خریداری کنید</p>
            </div>
            <div class="row g-4 steps-row">
                <div class="col-6 col-lg-3 step">
                    <div class="step-num">۱</div>
                    <h3>ثبت نام و احراز هویت</h3>
                    <p>ثبت نام در سامانه و تکمیل اطلاعات برای تایید و احراز هویت شما</p></div>
                <div class="col-6 col-lg-3 step">
                    <div class="step-num">۲</div>
                    <h3>ثبت درخواست</h3>
                    <p>انتخاب وام مورد نظر و ثبت درخواست خرید امتیاز</p></div>
                <div class="col-6 col-lg-3 step">
                    <div class="step-num">۳</div>
                    <h3>بررسی و تایید درخواست</h3>
                    <p>بررسی درخواست و شرایط طرفین توسط کارشناسان و تایید انجام معامله</p></div>
                <div class="col-6 col-lg-3 step">
                    <div class="step-num">۴</div>
                    <h3>انتقال هزینه و امتیاز</h3>
                    <p>پرداخت هزینه وام و انتقال امتیاز به حساب شما برای</p></div>
            </div>
        </div>
    </section>

    <!-- ============ FAQ ============ -->
    <section class="section" id="faq">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-5">
                    <h2 class="section-title">سوالات متداول شما</h2>
                    <p class="section-sub mb-4">ما در اینجا با سیستم معامله امن در حال خدمت رسانی به شما عزیزان
                        می‌باشیم. برای اطمینان شما از این سامانه به مهمترین و متداول ترین سوالات شما پاسخ داده ایم.</p>

                    <div class="contact-box">
                        <h3>اگر نیاز به پشتیبانی اختصاصی دارید، کارشناسان ما در کنار شما هستند.</h3>
                        <ul class="list-unstyled mb-0">
                            <li><span class="ic">✆</span><b>شماره تماس:</b><span dir="ltr">۰۵۱ ۳۸ – ۳۳ ۲۲ ۴۴ ۴۴</span>
                            </li>
                            <li><span class="ic">✉</span><b>ایمیل:</b><span dir="ltr">@teodel_support</span></li>
                            <li><span class="ic">⌖</span><b>آدرس:</b><span>خراسان رضوی – سبزوار – خیابان بیهق – کوچه بیهق ۱۴ – شرکت وامینجا</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="faq-list" id="faqList">
                        {{-- سوالات از جدول Faqs (فعال و مرتب‌شده) --}}
                        @forelse ($faqs as $i => $f)
                            <div class="faq-item {{ $i === 0 ? 'open' : '' }}">
                                <button class="faq-q" type="button" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}">{{ $f->question }}<span class="chev" aria-hidden="true"></span></button>
                                <div class="faq-a"><p>{{ $f->answer }}</p></div>
                            </div>
                        @empty
                            <p class="text-muted">فعلا سوال ثبت نشده است.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
