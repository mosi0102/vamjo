@extends('website.layouts.layout')

@section('title', 'آگهی‌های وام | وام‌جو')
@section('description', 'لیست آگهی‌های خرید و فروش امتیاز وام بانک‌های مختلف؛ فیلتر بر اساس بانک و نوع وام.')
@section('body_class', 'page-ads')

@php
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹', ',' => '،']);
    // فیلترهای فعال سمت سرور (قرارداد: bank[]=slug | type[]=key | urgent | pay | q | sort)
    $sel = ['banks' => array_filter((array) request()->query('bank')), 'types' => array_filter((array) request()->query('type'))];
    $sel['urgent'] = request()->boolean('urgent');
    $sel['pay'] = request()->boolean('pay');
    $sel['q'] = request()->string('q')->toString();
    $sel['sort'] = in_array(request()->query('sort'), ['new', 'amount-desc', 'amount-asc', 'rate-asc'], true) ? request()->query('sort') : 'new';
    $selectedBanks = collect($sel['banks']);
    $selectedTypes = collect($sel['types']);
@endphp

@section('main')
    <div class="container ads-page" id="adsPage">

        {{-- Breadcrumb --}}
        <nav class="crumbs d-none d-md-flex align-items-center" aria-label="مسیر صفحه">
            <a href="{{ url('/') }}">صفحه نخست</a><span class="sep-ic"
                                                        aria-hidden="true"><i
                    class="la la-angle-left"></i> </span><span>آگهی‌های وام</span>
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

                {{-- فیلترها به‌صورت فرم GET (ادغام سمت سرور + صفحه‌بndی) --}}
                <form id="adsFilterForm" action="{{ url('/ads') }}" method="GET" role="form" aria-label="فیلتر آگهی‌ها" autocomplete="off">
                    <input type="hidden" name="sort" id="sortInput" value="{{ $sel['sort'] }}">

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
                                        <input type="checkbox" name="bank[]" value="{{ $key }}" @checked($selectedBanks->contains($key))>
                                        <span class="box"></span>

                                        <div class="chk-text d-flex">
                                            <img src="{{asset('website/img/bank/'.$b['image'].'.png')}}" height="30"
                                                 class="me-2">
                                            {{ $b['n'] }}</div>
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
                                    <label class="chk"><input type="checkbox" name="type[]" value="{{ $key }}" @checked($selectedTypes->contains($key))><span
                                            class="box"></span><span class="chk-text">{{ $name }}</span></label>
                                @endforeach
                            </div>
                        </div>

                        {{-- گزینه‌های بیشتر --}}
                        <div class="f-group">
                            <h3 class="f-title">گزینه های بیشتر</h3>
                            <div class="f-list">
                                <label class="chk"><input type="checkbox" id="fUrgent" name="urgent" value="1" @checked($sel['urgent'])><span class="box"></span><span
                                        class="chk-text">وام فوری</span></label>
                                <label class="chk"><input type="checkbox" id="fPay" name="pay" value="1" @checked($sel['pay'])><span class="box"></span><span
                                        class="chk-text">پرداخت از مبلغ وام</span></label>
                            </div>
                        </div>
                    </div>
                </form>

                    <div class="filters-foot d-lg-none">
                        <button type="button" class="btn btn-brand w-100" id="applyFilters">
                            مشاهده <span id="sheetCount">{{ $fa($total) }}</span> آگهی
                        </button>
                    </div>
                </div>
            </aside>

            {{-- ===== Results ===== --}}
            <section class="col-lg-9">

                {{-- جستجو (پارامتر q سمت سرور + debounce در main.js) --}}
                <div class="search-box">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.3-4.3"/>
                    </svg>
                    <input type="search" id="adsSearch" name="q" form="adsFilterForm" value="{{ $sel['q'] }}"
                           placeholder="جستجو بر اساس نام بانک، نوع وام، مبلغ وام ..."
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
                            فیلترها <b class="f-badge" id="filterBadge" {{ count($selectedBanks) + count($selectedTypes) + (($sel['urgent'] ? 1 : 0) + ($sel['pay'] ? 1 : 0)) ? '' : 'hidden' }}>{{ $fa(count($selectedBanks) + count($selectedTypes) + ($sel['urgent'] ? 1 : 0) + ($sel['pay'] ? 1 : 0)) }}</b>
                        </button>
                        <div class="dropdown sort-dd">
                            <button class="tool-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="muted">مرتب سازی بر اساس :</span> <b id="sortLabel">{{ ['new' => 'جدیدترین', 'amount-desc' => 'بیشترین مبلغ وام', 'amount-asc' => 'کمترین مبلغ وام', 'rate-asc' => 'کمترین سود'][$sel['sort']] }}</b>
                                <span class="chev" aria-hidden="true"></span>
                            </button>

                            <ul class="dropdown-menu">
                                <li>
                                    <button type="button" class="dropdown-item sort-opt {{ $sel['sort'] === 'new' ? 'active' : '' }}" data-sort="new">
                                        جدیدترین
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item sort-opt {{ $sel['sort'] === 'amount-desc' ? 'active' : '' }}" data-sort="amount-desc">بیشترین
                                        مبلغ وام
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item sort-opt {{ $sel['sort'] === 'amount-asc' ? 'active' : '' }}" data-sort="amount-asc">کمترین
                                        مبلغ وام
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item sort-opt {{ $sel['sort'] === 'rate-asc' ? 'active' : '' }}" data-sort="rate-asc">کمترین
                                        سود
                                    </button>
                                </li>
                            </ul>
                        </div>

                    </div>
                </div>

                <p class="result-count"><span id="resultCount">{{ $fa($total) }}</span> آگهی پیدا شد</p>

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

                @php
                    $pageFa = fn ($n) => strtr((string) $n, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
                @endphp
                @if ($paginator->lastPage() > 1)
                    <nav class="mt-4" aria-label="صفحه‌بندی آگهی‌ها">
                        <ul class="pagination justify-content-center">
                            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $paginator->previousPageUrl() ?: '#' }}" rel="prev" aria-label="قبلی">‹</a>
                            </li>
                            @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                                <li class="page-item {{ $paginator->currentPage() == $page ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $url }}">{{ $pageFa($page) }}</a>
                                </li>
                            @endforeach
                            <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                                <a class="page-link" href="{{ $paginator->nextPageUrl() ?: '#' }}" rel="next" aria-label="بعدی">›</a>
                            </li>
                        </ul>
                    </nav>
                @endif
                <div class="empty-state" id="adsEmpty" {{ $total > 0 ? 'hidden' : '' }}>
                    <div class="empty-ic" aria-hidden="true">
                        <img src="{{asset('website/img/empty.png')}}" style="width: 200px"/>
                    </div>
                    <h3 class="mt-2">آگهی‌ای با این مشخصات پیدا نشد</h3>
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

    {{-- منطق تعاملی فیلترها (ارسال خودکار فرم، جستجو با debounce، مرتب‌سازی، حذف فیلترها) --}}
    <script src="{{ asset('website/js/ads-filters.js') }}?v=1" defer></script>
@endsection
