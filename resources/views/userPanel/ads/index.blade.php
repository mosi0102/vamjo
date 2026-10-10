@extends('userPanel.layouts.layout')

@section('title', 'آگهی های من')
@section('page_title', 'آگهی های من')
@section('body_class', 'pn-myads')

@php
    // نمونه داده؛ در پروژه واقعی از کنترلر پاس بدهید: compact('ads')
    // state: review (در انتظار ثبت) | transfer (در انتظار انتقال امتیاز) | completed | rejected
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $faId = fn ($n) => strtr((string) $n, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $ads = [
      ['id' => 1235896, 'bank' => 'رسالت','image'=>'8',   'l' => 'ر', 'c' => '#1f8a9e', 'amount' => 300000000, 'price' => 30000000, 'ago' => '۳ روز پیش', 'city' => 'سبزوار', 'views' => 124, 'offers' => 0, 'state' => 'review'],
      ['id' => 1235897, 'bank' => 'جاویدان','image'=>'4', 'l' => 'ج', 'c' => '#e07b00', 'amount' => 300000000, 'price' => 30000000, 'ago' => '۳ روز پیش', 'city' => 'سبزوار', 'views' => 418, 'offers' => 8, 'new_offers' => 2, 'state' => 'transfer', 'deadline' => 7 * 86400 - 3600],
      ['id' => 1235898, 'bank' => 'صادرات','image'=>'6', 'l' => 'ج', 'c' => '#e07b00', 'amount' => 300000000, 'price' => 30000000, 'ago' => '۳ روز پیش', 'city' => 'سبزوار', 'views' => 902, 'offers' => 5, 'state' => 'completed'],
      ['id' => 1235899, 'bank' => 'مهر','image'=>'3', 'l' => 'ج', 'c' => '#e07b00', 'amount' => 300000000, 'price' => 30000000, 'ago' => '۳ روز پیش', 'city' => 'سبزوار', 'views' => 37, 'offers' => 0, 'state' => 'rejected', 'note' => 'تصویر مدرک خوانا نیست؛ آگهی را ویرایش و دوباره ارسال کنید.'],
    ];
    $stateMap = ['review' => ['pending', 'در انتظار ثبت', 'review'], 'transfer' => ['pending', 'در انتظار انتقال امتیاز', 'progress'], 'completed' => ['approved', 'تکمیل شده', 'done'], 'rejected' => ['rejected', 'رد شده', 'rejected']];
    $tabs = ['all' => 'همه', 'review' => 'در انتظار ثبت', 'progress' => 'در حال انجام', 'done' => 'تکمیل شده', 'rejected' => 'رد شده'];
    $counts = collect($ads)->groupBy(fn ($a) => $stateMap[$a['state']][2])->map->count();
    $counts['all'] = count($ads);
@endphp

@section('main')

    <div class="pn-head pn-reveal">
        <span class="pn-head-ic">@include('userPanel.partials.icon', ['name' => 'book'])</span>
        <div>
            <h2>آگهی های من</h2>
            <p>تمامی آگهی هایی که گذاشته اید در این قسمت قابل مشاهده می باشد.</p>
        </div>
        <a href="{{ url('/add') }}" target="_blank"
           class="btn btn-brand pn-head-act">@include('userPanel.partials.icon', ['name' => 'plus']) ثبت آگهی جدید</a>
    </div>

    <section class="pn-panel pn-reveal" style="--d:.08s">

        <div class="rq-toolbar">
            <div class="rq-tabs" role="tablist" aria-label="فیلتر وضعیت">
                @foreach ($tabs as $key => $label)
                    <button type="button" class="rq-tab {{ $loop->first ? 'active' : '' }}" data-group="{{ $key }}"
                            role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                        {{ $label }}<span class="rq-count">{{ $faId($counts[$key] ?? 0) }}</span>
                    </button>
                @endforeach
            </div>
            <label class="rq-search">
                @include('userPanel.partials.icon', ['name' => 'search'])
                <input type="search" id="rqSearch" placeholder="جستجو شناسه، بانک یا مبلغ..." autocomplete="off"
                       aria-label="جستجو در آگهی‌ها">
            </label>
        </div>

        <div class="rq-list" id="rqList">
            @forelse ($ads as $a)
                @php [$pillCls, $pillText, $group] = $stateMap[$a['state']]; $title = 'وام ' . $fa($a['amount']) . ' تومان بانک ' . $a['bank']; @endphp
                <article class="rq-card ad-card-my pn-reveal" data-group="{{ $group }}"
                         data-search="{{ $a['id'] }} {{ $a['bank'] }} {{ $a['amount'] }} {{ $a['price'] }}"
                         style="--d:{{ .12 + $loop->index * .07 }}s">
                    <div class="rq-main">
                        <div class="rq-head">
                            <img src="{{asset('website/img/bank/'.$a['image'].'.png')}}" alt="" height="90">
                            <div class="rq-info">
                                <h3>{{ $title }}</h3>
                                <div class="rq-meta">
                                    <button type="button" class="pn-id rq-id" data-copy="{{ $a['id'] }}"
                                            title="کپی شناسه وام">شناسه وام
                                        <b>#{{ $faId($a['id']) }}</b>@include('userPanel.partials.icon', ['name' => 'copy'])
                                    </button>
                                    <span
                                        class="rq-meta-i">@include('userPanel.partials.icon', ['name' => 'calendar']){{ $a['ago'] }}</span>
                                    <span
                                        class="rq-meta-i">@include('userPanel.partials.icon', ['name' => 'pin']){{ $a['city'] }}</span>
                                    <span class="rq-meta-i"
                                          title="تعداد بازدید">@include('userPanel.partials.icon', ['name' => 'eye']){{ $faId($a['views']) }} بازدید</span>
                                </div>
                                @if (! empty($a['note']))
                                    <p class="rq-note">{{ $a['note'] }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="rq-side">
                            <div class="rq-price"><b>{{ $fa($a['price']) }}</b> <small>تومان</small></div>
                            <div class="rq-act">
                                <span class="pn-pill {{ $pillCls }}">{{ $pillText }}</span>
                                @if ($a['state'] === 'transfer')
                                    <button type="button" class="btn btn-brand rq-cta" data-bs-toggle="modal"
                                            data-bs-target="#sellerTransferModal"
                                            data-id="{{ $a['id'] }}" data-title="{{ $title }}"
                                            data-bank-l="{{ $a['l'] }}" data-bank-c="{{ $a['c'] }}"
                                            data-deadline="{{ $a['deadline'] }}"
                                            data-action="{{ url('/panel/ads/' . $a['id'] . '/transfer') }}">
                                        انتقال امتیاز @include('userPanel.partials.icon', ['name' => 'arrow'])
                                    </button>
                                @endif
                            </div>
                            @if ($a['state'] === 'transfer')
                                <small class="rq-deadline">@include('userPanel.partials.icon', ['name' => 'clock']) ۷
                                    روز مهلت انتقال امتیاز</small>
                            @endif
                        </div>
                    </div>

                    {{-- عملیات --}}
                    <div class="ad-actions">
                        <a href="{{ url('/ads/' . $a['id']) }}"
                           class="ad-act">@include('userPanel.partials.icon', ['name' => 'eye']) مشاهده آگهی</a>
                        @if ($a['offers'] > 0)
                            <a href="{{ url('/userPanel/ads/' . $a['id'] . '/offers') }}" class="ad-act highlight">
                                @include('userPanel.partials.icon', ['name' => 'clipboard']) درخواست ها
                                <span class="ad-badge">{{ $faId($a['offers']) }}</span>
                                @if (! empty($a['new_offers']))
                                    <span class="ad-new">{{ $faId($a['new_offers']) }} جدید</span>
                                @endif
                            </a>
                        @endif
                        @if (in_array($a['state'], ['review', 'rejected']))
                            <a href="{{ url('/userPanel/ads/' . $a['id'] . '/edit') }}"
                               class="ad-act">@include('userPanel.partials.icon', ['name' => 'edit']) ویرایش</a>
                            <button type="button" class="ad-act danger ms-lg-auto" data-bs-toggle="modal"
                                    data-bs-target="#confirmModal"
                                    data-confirm-title="حذف آگهی"
                                    data-confirm-text="آیا از حذف «{{ $title }}» مطمئن هستید؟ این کار قابل بازگشت نیست."
                                    data-confirm-action="{{ url('/panel/ads/' . $a['id']) }}"
                                    data-confirm-method="DELETE" data-confirm-label="بله، حذف شود"
                                    data-confirm-tone="danger">
                                @include('userPanel.partials.icon', ['name' => 'trash']) حذف
                            </button>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rq-empty">
                    <div class="rq-empty-ic"
                         aria-hidden="true">@include('userPanel.partials.icon', ['name' => 'book'])</div>
                    <h3>هنوز آگهی ثبت نکرده‌اید</h3>
                    <p>اولین آگهی فروش امتیاز وام خود را ثبت کنید.</p>
                    <a href="{{ url('/ads/create') }}" class="btn btn-brand">ثبت آگهی جدید</a>
                </div>
            @endforelse
        </div>

        <div class="rq-empty" id="rqEmpty" hidden>
            <div class="rq-empty-ic" aria-hidden="true">@include('userPanel.partials.icon', ['name' => 'book'])</div>
            <h3>آگهی‌ای پیدا نشد</h3>
            <p>فیلتر یا عبارت جستجو را تغییر دهید.</p>
        </div>
    </section>

    @include('userPanel.partials.seller-transfer-modal')
    @include('userPanel.partials.confirm-modal')
@endsection
