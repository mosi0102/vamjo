@extends('userPanel.layouts.layout')

@section('title', 'مدیریت آگهی ها')
@section('page_title', 'آگهی ها')
@section('body_class', 'pn-admin')

@php
    // نمونه داده؛ در پروژه واقعی از کنترلر پاس بدهید: compact('ads')
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $faId = fn ($s) => strtr((string) $s, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $isAdmin = \App\Support\PanelRole::isAdmin();
    $ph = 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="480" height="300"><rect width="480" height="300" fill="#e4ebe6"/><rect x="120" y="30" width="240" height="240" rx="14" fill="#fff" stroke="#b9cbc0" stroke-width="5"/><path d="M160 90h160M160 130h160M160 170h100" stroke="#9fb8ab" stroke-width="12" stroke-linecap="round"/></svg>');
    $banks = [['رسالت', 'ر', '8'], ['جاویدان', 'ج', '4'], ['شهر', 'ش', '7'], ['تجارت', 'ت', '9'], ['مهر', 'م', '3'], ['کشاورزی', 'ک', '5']];
    $sellers = ['سارا حسینی', 'امیر جعفری', 'فاطمه کاظمی', 'علی رضایی', 'نرگس صادقی', 'مهدی رحیمی', 'لیلا موسوی', 'رضا نوری', 'محمد طاهری', 'زهرا محمدی'];
    $status = ['pending', 'pending', 'pending', 'published', 'published', 'published', 'transfer', 'completed', 'rejected', 'published'];
    $ads = [];
    foreach ($status as $i => $st) {
      $b = $banks[$i % 6]; $amount = [300, 100, 250, 300, 120, 100, 300, 250, 100, 200][$i] * 1000000;
      $ads[] = ['id' => 1235890 + $i, 'bank' => $b[0], 'l' => $b[1],'image'=>$b[2], 'amount' => $amount, 'price' => (int) ($amount * [0.1, 0.2, 0.18, 0.1, 0.25, 0.2, 0.1, 0.18, 0.5, 0.15][$i]), 'months' => [24, 60, 36, 48, 24, 24, 60, 36, 24, 48][$i], 'rate' => [23, 4, 18, 23, 20, 23, 4, 18, 23, 20][$i],
        'seller' => $sellers[$i], 'city' => ['سبزوار', 'مشهد', 'تهران', 'سبزوار', 'نیشابور', 'مشهد', 'تهران', 'سبزوار', 'اصفهان', 'شیراز'][$i], 'status' => $st, 'featured' => in_array($i, [3, 5]), 'ago' => ['۱ ساعت پیش', '۴ ساعت پیش', 'دیروز', '۲ روز پیش', '۳ روز پیش', '۴ روز پیش', '۵ روز پیش', '۶ روز پیش', '۷ روز پیش', '۸ روز پیش'][$i], 'age' => $i * 1300 + 60];
    }
    $map = ['pending' => ['pending', 'منتظر تایید'], 'published' => ['approved', 'منتشر شده'], 'transfer' => ['pending', 'در حال معامله'], 'completed' => ['approved', 'تکمیل شده'], 'rejected' => ['rejected', 'رد شده']];
    $c = collect($ads);
    $cnt = ['pending' => $c->where('status', 'pending')->count(), 'published' => $c->where('status', 'published')->count(), 'featured' => $c->where('featured', true)->count(), 'rejected' => $c->where('status', 'rejected')->count(), 'all' => $c->count()];
    $tabs = ['pending' => 'منتظر تایید', 'published' => 'منتشر شده', 'featured' => 'ویژه', 'rejected' => 'رد شده', 'all' => 'همه'];
@endphp

@section('main')

    <div class="pn-head pn-reveal">
        <span class="pn-head-ic">@include('userPanel.partials.icon', ['name' => 'book'])</span>
        <div><h2>مدیریت آگهی ها</h2><p>آگهی‌های ثبت‌شده را بررسی کنید؛ تایید و انتشار، رد با ذکر دلیل و انتخاب آگهی ویژه.</p></div>
    </div>

    <section class="pn-panel pn-reveal at-box" style="--d:.08s" data-table data-page-size="8">
        <div class="rq-toolbar">
            <div class="rq-tabs" role="tablist" aria-label="فیلتر آگهی ها">
                @foreach ($tabs as $k => $l)<button type="button" class="rq-tab {{ $k === 'pending' ? 'active' : '' }}" data-tgroup="{{ $k }}" role="tab" aria-selected="{{ $k === 'pending' ? 'true' : 'false' }}">{{ $l }}<span class="rq-count">{{ $faId($cnt[$k]) }}</span></button>@endforeach
            </div>
            <label class="rq-search">@include('userPanel.partials.icon', ['name' => 'search'])<input type="search" data-tsearch placeholder="جستجو شناسه، بانک یا فروشنده..." autocomplete="off" aria-label="جستجو"></label>
        </div>

        <div class="at-scroll">
            <table class="at-table">
                <thead><tr><th>آگهی</th><th>فروشنده</th><th data-sort="amount">مبلغ وام</th><th data-sort="price">قیمت فروش</th><th data-sort="age">ثبت</th><th>وضعیت</th><th>ویژه</th><th></th></tr></thead>
                <tbody>
                @foreach ($ads as $a)
                    @php [$pc, $pt] = $map[$a['status']]; $title = 'وام ' . $fa($a['amount']) . ' تومان بانک ' . $a['bank']; @endphp
                    <tr data-groups="{{ $a['status'] }}{{ $a['featured'] ? ' featured' : '' }}" data-search="{{ $a['id'] }} {{ $a['bank'] }} {{ $a['seller'] }} {{ $a['amount'] }}" data-amount="{{ $a['amount'] }}" data-price="{{ $a['price'] }}" data-age="{{ $a['age'] }}">
                        <td data-label="آگهی"><div class="at-user"><span class="at-av sq" style="background:{{ $a['c'] }}">{{ $a['l'] }}</span><span class="at-name"><b>{{ $title }}</b><small>شناسه #{{ $faId($a['id']) }} · {{ $a['city'] }}</small></span></div></td>
                        <td data-label="فروشنده">{{ $a['seller'] }}</td>
                        <td data-label="مبلغ وام">{{ $fa($a['amount']) }}</td>
                        <td data-label="قیمت فروش"><b>{{ $fa($a['price']) }}</b></td>
                        <td data-label="ثبت">{{ $a['ago'] }}</td>
                        <td data-label="وضعیت"><span class="pn-pill {{ $pc }}">{{ $pt }}</span></td>
                        <td data-label="ویژه">
                            @if ($isAdmin && in_array($a['status'], ['published', 'pending']))
                                <label class="rd-switch" title="آگهی ویژه" data-name="آگهی {{ $faId($a['id']) }}" data-url="{{ url('/adminPanel/ads/' . $a['id'] . '/feature') }}" data-switch-group="featured"><input type="checkbox" {{ $a['featured'] ? 'checked' : '' }} aria-label="ویژه بودن آگهی {{ $faId($a['id']) }}"><i></i></label>
                            @else<span class="text-muted">{{ $a['featured'] ? '★ ویژه' : '—' }}</span>@endif
                        </td>
                        <td class="at-actions">
                            <button type="button" class="btn {{ $a['status'] === 'pending' ? 'btn-brand' : 'btn-ghost' }} at-btn" data-bs-toggle="modal" data-bs-target="#adReviewModal"
                                    data-id="{{ $a['id'] }}" data-title="{{ $title }}" data-bank="{{ $a['bank'] }}" data-amount="{{ $fa($a['amount']) }}" data-amount-raw="{{ $a['amount'] }}" data-price="{{ $fa($a['price']) }}" data-price-raw="{{ $a['price'] }}"
                                    data-months="{{ $faId($a['months']) }}" data-rate="{{ $faId($a['rate']) }}" data-seller="{{ $a['seller'] }}" data-city="{{ $a['city'] }}" data-sheba="IR062960000000100324200001"
                                    data-note="ضامن کارمند رسمی است. معامله در {{ $a['city'] }} انجام می‌شود." data-status="{{ $a['status'] }}" data-featured="{{ $a['featured'] ? 1 : 0 }}" data-admin="{{ $isAdmin ? 1 : 0 }}" data-doc1="{{ $ph }}" data-doc2="{{ $ph }}">{{ $a['status'] === 'pending' ? 'بررسی' : 'مشاهده' }}</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="at-foot"><span data-tinfo></span><div class="at-pager" data-tpager></div></div>
        <div class="rq-empty" data-tempty hidden><div class="rq-empty-ic" aria-hidden="true">@include('userPanel.partials.icon', ['name' => 'book'])</div><h3>آگهی‌ای وجود ندارد</h3><p>در این دسته آگهی‌ای پیدا نشد.</p></div>
    </section>

    @include('adminPanel.partials.ad-review-modal')
@endsection
