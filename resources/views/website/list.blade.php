@extends('website.layouts.layout')

@section('title', 'آگهی‌های وام | وام‌جو')
@section('description', 'لیست آگهی‌های خرید و فروش امتیاز وام بانک‌های مختلف؛ فیلتر بر اساس بانک و نوع وام.')
@section('body_class', 'page-ads')

@php
    // نمونه داده؛ در پروژه واقعی از کنترلر پاس بدهید: return view('ads.main', compact('ads', 'banks', 'types'));
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹', ',' => '،']);
    $banks = [
      'javidan' => ['n'=>'بانک جاویدان', 'c'=>'#e07b00', 'l'=>'ج','image'=>'4'],
      'resalat' => ['n'=>'بانک رسالت', 'c'=>'#1f8a9e', 'l'=>'ر','image'=>'8'],
      'saderat' => ['n'=>'بانک صادرات', 'c'=>'#1b3a8a', 'l'=>'ص','image'=>'6'],
      'shahr'   => ['n'=>'بانک شهر', 'c'=>'#d6322c', 'l'=>'ش','image'=>'7'],
      'keshavarzi' => ['n'=>'بانک کشاورزی', 'c'=>'#4a7c3a', 'l'=>'ک','image'=>'5'],
      'mehr'    => ['n'=>'بانک قرض الحسنه مهر', 'c'=>'#2e9a4f', 'l'=>'م','image'=>'3'],
      'tejarat' => ['n'=>'بانک تجارت', 'c'=>'#3e4ea0', 'l'=>'ت','image'=>'9'],
    ];
    $types = ['qarz'=>'قرض الحسنه', 'marriage'=>'ازدواج', 'child'=>'فرزند آوری', 'housing'=>'مسکن', 'mehrbani'=>'مهربانی', 'commercial'=>'تجاری', 'medical'=>'درمانی'];
    $rows = [
      [1, 'javidan', 'qarz', 100000000, 20000000, 0, 1],
      [2, 'resalat', 'marriage', 100000000, 20000000, 1, 0],
      [3, 'tejarat', 'housing', 250000000, 20000000, 0, 0],
      [4, 'shahr', 'child', 100000000, 20000000, 1, 0],
      [5, 'mehr', 'mehrbani', 100000000, 20000000, 0, 1],
      [6, 'keshavarzi', 'commercial', 100000000, 20000000, 0, 0],
    ];
    $ads = collect($rows)->map(fn ($r) => [
      'id' => $r[0], 'bank' => $r[1], 'bank_name' => $banks[$r[1]]['n'], 'c' => $banks[$r[1]]['c'], 'l' => $banks[$r[1]]['l'],
      'type' => $r[2], 'type_name' => $types[$r[2]], 'amount' => $r[3], 'buy' => $r[4], 'months' => 24, 'rate' => 23,
      'offers' => 8, 'urgent' => $r[5], 'pay' => $r[6],'image'=>$banks[$r[1]]['image']
    ])->all();


@endphp

@section('main')
    <div class="container ads-page" id="adsPage">

        {{-- Breadcrumb --}}
        <nav class="crumbs d-none d-md-flex" aria-label="مسیر صفحه">
            <a href="{{ url('/') }}">صفحه نخست</a><span class="sep-ic"
                                                        aria-hidden="true">‹</span><span>آگهی‌های وام</span>
        </nav>

        <div class="row g-4">

            {{-- ===== Filters (دسکتاپ: ستون کناری | موبایل: شیت پایین‌آمدنی) ===== --}}
            <aside class="col-lg-3">
                <div class="offcanvas-lg offcanvas-bottom filters" tabindex="-1" id="filtersSheet"
                     aria-labelledby="filtersTitle">
                    <div class="sheet-handle d-lg-none" aria-hidden="true"></div>
                    <div class="filters-head">
                        <h2 id="filtersTitle">فیلترها</h2>
                        <button type="button" class="clear-all" id="clearAll"><span class="x-ic"
                                                                                    aria-hidden="true">×</span> حذف همه
                        </button>
                        <button type="button" class="btn-close d-lg-none ms-0 me-auto" data-bs-dismiss="offcanvas"
                                data-bs-target="#filtersSheet" aria-label="بستن"></button>
                    </div>

                    <div class="filters-body">
                        {{-- بانک --}}
                        <div class="f-group">
                            <h3 class="f-title">انتخاب بانک</h3>
                            <div class="f-search">
                                <input type="text" id="bankSearch" placeholder="جستجو بانک" autocomplete="off"
                                       aria-label="جستجو بانک">
                                <button type="button" id="bankSearchClear" aria-label="پاک کردن" hidden>×</button>
                            </div>
                            <div class="f-list f-scroll" id="bankList">
                                @foreach ($banks as $key => $b)
                                    <label class="chk bank-opt" data-name="{{ $b['n'] }}">
                                        <input type="checkbox" name="bank" value="{{ $key }}">
                                        <span class="box"></span>
                                        <span class="chk-logo" style="background: {{ $b['c'] }}"
                                              aria-hidden="true">{{ $b['l'] }}</span>
                                        <span class="chk-text">{{ $b['n'] }}</span>
                                    </label>
                                @endforeach
                                <p class="f-empty" id="bankEmpty" hidden>بانکی پیدا نشد</p>
                            </div>
                        </div>

                        {{-- نوع وام --}}
                        <div class="f-group">
                            <h3 class="f-title">نوع وام</h3>
                            <div class="f-list">
                                @foreach ($types as $key => $name)
                                    <label class="chk"><input type="checkbox" name="type" value="{{ $key }}"><span
                                            class="box"></span><span class="chk-text">{{ $name }}</span></label>
                                @endforeach
                            </div>
                        </div>

                        {{-- گزینه‌های بیشتر --}}
                        <div class="f-group">
                            <h3 class="f-title">گزینه های بیشتر</h3>
                            <div class="f-list">
                                <label class="chk"><input type="checkbox" id="fUrgent"><span class="box"></span><span
                                        class="chk-text">وام فوری</span></label>
                                <label class="chk"><input type="checkbox" id="fPay"><span class="box"></span><span
                                        class="chk-text">پرداخت از مبلغ وام</span></label>
                            </div>
                        </div>
                    </div>

                    <div class="filters-foot d-lg-none">
                        <button type="button" class="btn btn-brand w-100" data-bs-dismiss="offcanvas"
                                data-bs-target="#filtersSheet">مشاهده <span
                                id="sheetCount">{{ $fa(count($ads)) }}</span> آگهی
                        </button>
                    </div>
                </div>
            </aside>

            {{-- ===== Results ===== --}}
            <section class="col-lg-9">

                <div class="search-box">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.3-4.3"/>
                    </svg>
                    <input type="search" id="adsSearch" placeholder="جستجو بر اساس نام بانک، نوع وام، مبلغ وام ..."
                           autocomplete="off" aria-label="جستجو در آگهی‌ها">
                </div>

                <div class="list-head">
                    <h1 class="section-title">آگهی‌های ویژه</h1>
                    <div class="list-tools">
                        <button type="button" class="tool-btn d-lg-none" data-bs-toggle="offcanvas"
                                data-bs-target="#filtersSheet" aria-controls="filtersSheet">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                                 stroke-width="1.9" stroke-linecap="round" aria-hidden="true">
                                <path d="M4 6h16M7 12h10M10 18h4"/>
                            </svg>
                            فیلترها <b class="f-badge" id="filterBadge" hidden>۰</b>
                        </button>

                        <div class="dropdown sort-dd">
                            <button class="tool-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="muted">مرتب سازی بر اساس :</span> <b id="sortLabel">جدیدترین</b>
                                <span class="chev" aria-hidden="true"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <button type="button" class="dropdown-item sort-opt active" data-sort="new">
                                        جدیدترین
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item sort-opt" data-sort="amount-desc">بیشترین
                                        مبلغ وام
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item sort-opt" data-sort="amount-asc">کمترین
                                        مبلغ وام
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item sort-opt" data-sort="rate-asc">کمترین
                                        سود
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <p class="result-count"><span id="resultCount">{{ $fa(count($ads)) }}</span> آگهی پیدا شد</p>

                <div class="row g-3 g-lg-4" id="adsGrid">
                    @foreach ($ads as $a)
                        <div class="col-md-6 ad-item"
                             data-bank="{{ $a['bank'] }}" data-type="{{ $a['type'] }}"
                             data-urgent="{{ $a['urgent'] }}" data-pay="{{ $a['pay'] }}"
                             data-amount="{{ $a['amount'] }}" data-rate="{{ $a['rate'] }}"
                             data-order="{{ $loop->index }}"
                             data-search="{{ $a['bank_name'] }} {{ $a['type_name'] }} {{ $a['amount'] }}">
                            @include('website.partials.ad-card', ['a' => $a])
                        </div>
                    @endforeach
                </div>

                <div class="empty-state" id="adsEmpty" hidden>
                    <div class="empty-ic" aria-hidden="true">🔍</div>
                    <h3>آگهی‌ای با این مشخصات پیدا نشد</h3>
                    <p>فیلترها یا عبارت جستجو را تغییر دهید.</p>
                    <button type="button" class="btn btn-brand" id="emptyReset">حذف همه فیلترها</button>
                </div>
            </section>
        </div>

        {{-- ===== SEO article ===== --}}
        <article class="seo-text clamped" id="seoText">
            <h2>وام چیست؟ هر آنچه باید در مورد انواع وام بدانید!</h2>
            <p>در زندگی مالی اغلب ما، زمانی فرا می‌رسد که نیاز به منابع مالی بیشتر از موجودی حساب خود داریم؛ در این
                شرایط مفهوم وام به میان می‌آید. اما وام چیست و چرا در نظام اقتصادی اهمیت بالایی دارد؟ از وام فوری برای
                رفع نیازهای اضطراری تا تسهیلات بلندمدت برای خرید خانه یا راه‌اندازی کسب‌وکار، دنیای وام‌ها گسترده‌تر از
                آن چیزی است که تصور می‌کنیم.</p>
            <p>با مطالعه این مطلب از کاریزما لرنینگ، دید روشن‌تری نسبت به مفهوم وام و انواع آن به‌دست می‌آورید و با
                سازوکار دریافت تسهیلات آشنا خواهید شد؛ بنابراین دقایقی از وقت ارزشمند خود را به این مقاله اختصاص
                دهید.</p>

            <h3>وام فوری چیست؟</h3>
            <p>وام فوری نوعی تسهیلات مالی کوتاه‌مدت است که با هدف رفع نیازهای فوری و اضطراری به افراد اعطا می‌شود. این
                نوع وام معمولاً در مدت زمان کوتاهی (حتی در همان روز) پرداخت می‌شود و شرایط دریافت آن نسبت به وام‌های
                رایج ساده‌تر است. در این نوع وام‌ها نیازی به معرفی ضامن یا ارائه وثیقه نیست و تمام مراحل از واریز وام به
                حساب متقاضی، آنلاین است. وام‌های فوری اغلب با سقف مشخص، کارمزد یا سود بیشتر و دوره بازپرداخت کوتاه ارائه
                می‌شوند. امروزه در اغلب نئوبانک‌ها و اپلیکیشن‌های سرمایه‌گذاری، امکان دریافت وام فوری با شرایط آسان وجود
                دارد. برای مثال می‌توانید بر اساس گردش حساب خود در یک دوره سه‌ماهه، تا سقف مشخصی وام دریافت کنید.</p>

            <h3>کاریزماوام؛ دریافت وام فوری، بدون ضامن</h3>
            <p>کاریزماوام یک تسهیلات نقدی است که به پشتوانه دارایی شما در طرح‌های سرمایه‌گذاری کاریزما پرداخت می‌شود.
                اگر در طرح‌های طلا، نقره، مس، ملک، استاکس یا درآمد ثابت دارایی داشته باشید، می‌توانید بدون نیاز به ضامن،
                چک یا سفته درخواست وام ثبت کنید.</p>
            <p>در طرح‌های طلا، نقره، مس، ملک و استاکس، امکان دریافت وام تا ۶۰ درصد ارزش روز دارایی وجود دارد و این نسبت
                در طرح درآمد ثابت تا ۹۰ درصد ارزش دارایی می‌رسد. سقف هر وام نیز تا ۵۰۰ میلیون تومان است.</p>

            <h3>ویژگی‌های اصلی کاریزماوام</h3>
            <ul>
                <li>بدون نیاز به ضامن، چک یا سفته؛ دارایی موجود در طرح‌های کاریزما پشتوانه وام قرار می‌گیرد.</li>
                <li>سقف هر وام تا ۵۰۰ میلیون تومان؛ مبلغ قابل دریافت به ارزش و نوع دارایی پشتوانه بستگی دارد.</li>
                <li>وام تا ۶۰ درصد ارزش دارایی در طرح‌های طلا، نقره، مس، ملک و استاکس.</li>
                <li>وام تا ۹۰ درصد ارزش دارایی در طرح درآمد ثابت.</li>
                <li>دوره بازپرداخت ۳، ۶، ۹ یا ۱۲ ماهه با امکان انتخاب دوره متناسب با شرایط متقاضی.</li>
                <li>واریز نقدی وام به حساب بانکی اعلام‌شده.</li>
                <li>بدون نیاز به فروش دارایی؛ بخشی از دارایی متناسب با مبلغ وام تا زمان تسویه به‌عنوان پشتوانه بلوکه
                    می‌شود.
                </li>
                <li>امکان دریافت چند وام در صورت داشتن دارایی کافی و رعایت سقف‌های تعیین‌شده.</li>
            </ul>
        </article>
        <button type="button" class="seo-toggle d-lg-none" id="seoToggle">نمایش بیشتر</button>
    </div>
@endsection
