@extends('website.layouts.layout')

@section('title', 'وام‌جو | پلتفرم خرید و فروش امن امتیاز وام')
@section('description', 'با تضمین امنیت معامله، امتیاز وام خود را نقد کنید یا وام مورد نیاز خود را پیدا کنید.')
@section('body_class', 'page-home')

@php
    // نمونه داده؛ در پروژه واقعی از کنترلر پاس بدهید: return view('home.main', compact('ads', 'banks'));
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹', ',' => '،']);
    $banks = [
      ['bank_name'=>' ملت','c'=>'#d62828','l'=>'م','image'=>'1'], ['bank_name'=>' ملی','c'=>'#c28a1a','l'=>'ل','image'=>'2'],
      ['bank_name'=>'قرض‌الحسنه مهر ایران','c'=>'#2a7ab0','l'=>'ق','image'=>'3'], ['bank_name'=>'جاویدان','c'=>'#e07b00','l'=>'ج','image'=>'4'],
      ['bank_name'=>'کشاورزی','c'=>'#4a7c3a','l'=>'ک','image'=>'5'], ['bank_name'=>' صادرات','c'=>'#1b3a8a','l'=>'ص','image'=>'6'],
      ['bank_name'=>' شهر','c'=>'#d6322c','l'=>'ش','image'=>'7'], ['bank_name'=>'رسالت','c'=>'#2e9a4f','l'=>'ر','image'=>'8'],
      ['bank_name'=>'تجارت','c'=>'#3e4ea0','l'=>'ت','image'=>'9'], ['bank_name'=>' سپه','c'=>'#8a6b2a','l'=>'س','image'=>'10'],
    ];
    $ads = [
      ['id'=>1,'bank_name'=>'شهر','c'=>'#e07b00','l'=>'ج','amount'=>100000000,'buy'=>20000000,'months'=>24,'rate'=>23,'offers'=>8,'image'=>'1'],
      ['id'=>2,'bank_name'=>'ملی','c'=>'#3e4ea0','l'=>'ت','amount'=>250000000,'buy'=>45000000,'months'=>24,'rate'=>23,'offers'=>8,'image'=>'2'],
      ['id'=>3,'bank_name'=>' مهر ایران','c'=>'#2a7ab0','l'=>'ق','amount'=>120000000,'buy'=>25000000,'months'=>24,'rate'=>23,'offers'=>8,'image'=>'3'],
      ['id'=>4,'bank_name'=>'جاویدان','c'=>'#d6322c','l'=>'ش','amount'=>100000000,'buy'=>20000000,'months'=>24,'rate'=>23,'offers'=>8,'image'=>'4'],
      ['id'=>5,'bank_name'=>' کشاورزی','c'=>'#2e9a4f','l'=>'ر','amount'=>250000000,'buy'=>45000000,'months'=>24,'rate'=>23,'offers'=>8,'image'=>'5'],
      ['id'=>6,'bank_name'=>'صادرات','c'=>'#4a7c3a','l'=>'ک','amount'=>120000000,'buy'=>25000000,'months'=>24,'rate'=>23,'offers'=>8,'image'=>'6'],
    ];
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
                <a href="#" class="link-more">مشاهده همه <span aria-hidden="true">←</span></a>
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
                <div class="col-6 col-lg-3">
                    <div class="stat-num"><span class="count" data-count="500">۰</span>+</div>
                    <p>آگهی ثبت شده<br>واگذاری وام</p></div>
                <div class="col-6 col-lg-3">
                    <div class="stat-num"><span class="count" data-count="1300">۰</span>+</div>
                    <p>کاربر احراز هویت<br>شده در سامانه</p></div>
                <div class="col-6 col-lg-3">
                    <div class="stat-num"><span class="count" data-count="10">۰</span>+</div>
                    <p>بانک‌های تحت پوشش<br>سامانه وام اینجا</p></div>
                <div class="col-6 col-lg-3">
                    <div class="stat-num"><span class="count" data-count="650">۰</span>+</div>
                    <p>ثبت معامله موفق و<br>امن در سامانه</p></div>
            </div>
        </div>
    </section>

    <!-- ============ Steps ============ -->
    <section class="section steps">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title d-inline-block">راهنمای ثبت درخواست امتیاز</h2>
                <p class="section-sub">لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده از طراحان
                    گرافیک است</p>
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
                        <div class="faq-item open">
                            <button class="faq-q" type="button" aria-expanded="true">در صورتی که شخص فروشنده وام امتیاز
                                انتقال ندهد چه میشود؟<span class="chev" aria-hidden="true"></span></button>
                            <div class="faq-a"><p>هزینه پرداخت شده شما در سامانه به امانت می ماند و تا انتقال قطعی
                                    امتیاز و تایید طرفین بر انجام معامله هزینه انتقال داده نمیشود. در صورت عدم انتقال
                                    معامله تا ۷ روز تمام هزینه به شما بازگشت داده میشود.</p></div>
                        </div>
                        <div class="faq-item">
                            <button class="faq-q" type="button" aria-expanded="false">چه تضمینی برای انتقال امتیاز وام
                                هست؟<span class="chev" aria-hidden="true"></span></button>
                            <div class="faq-a"><p>اشخاص فروشنده امتیاز توسط سامانه احراز و بررسی میشوند و با توجه به
                                    مدارک ارائه شده امکان ثبت آگهی برایشان فراهم میشود. در صورت عدم توان انجام معامله
                                    حساب شخص به حالت تعلیق در می آید.</p></div>
                        </div>
                        <div class="faq-item">
                            <button class="faq-q" type="button" aria-expanded="false">سامانه وام اینجا چه اعتباراتی برای
                                اعتماد سازی دارد؟<span class="chev" aria-hidden="true"></span></button>
                            <div class="faq-a"><p>این سامانه دارای مجوز فعالیت میباشد و همچنین دارای نماد اعتماد
                                    الکترونیک می باشد. همچنین تمامی معاملات انجام شده ثبت میشود و در اختیار طرفین قرار
                                    میگیرد.</p></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
