@extends('userPanel.layouts.layout')

@section('title', 'آگهی های من')
@section('page_title', 'آگهی های من')
@section('body_class', 'pn-myads')

@php
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $faId = fn ($n) => strtr((string) $n, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $stateMap = [
        'review'    => ['pending', 'در انتظار ثبت', 'review'],
        'published' => ['approved', 'منتشر شده', 'progress'],
        'transfer'  => ['pending', 'در انتظار انتقال امتیاز', 'progress'],
        'completed' => ['approved', 'تکمیل شده', 'done'],
        'rejected'  => ['rejected', 'رد شده', 'rejected'],
    ];
    $tabs = ['all' => 'همه', 'review' => 'در انتظار ثبت', 'progress' => 'در حال انجام', 'done' => 'تکمیل شده', 'rejected' => 'رد شده'];
    $c = collect($ads);
    $counts = ['all' => $c->count()];
    foreach ($ads as $a) {
        $g = $stateMap[$a['state']][2] ?? null;
        if ($g) {
            $counts[$g] = ($counts[$g] ?? 0) + 1;
        }
    }
@endphp

@section('main')

    <div class="pn-head pn-reveal">
        <span class="pn-head-ic">@include('userPanel.partials.icon', ['name' => 'book'])</span>
        <div>
            <h2>آگهی های من</h2>
            <p>تمامی آگهی هایی که گذاشته اید در این قسمت قابل مشاهده می باشد.</p>
        </div>
        <a href="{{ route('user.ads.create') }}"
           class="btn btn-brand pn-head-act">@include('userPanel.partials.icon', ['name' => 'plus']) ثبت آگهی جدید</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success pn-reveal" role="alert">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger pn-reveal" role="alert">{{ $errors->first() }}</div>
    @endif

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
                                            data-deadline="{{ $a['deadline'] ?? 0 }}"
                                            data-action="{{ route('user.ads.transfer', $a['id']) }}">
                                        انتقال امتیاز @include('userPanel.partials.icon', ['name' => 'arrow'])
                                    </button>
                                @endif
                            </div>
                            @if ($a['state'] === 'transfer' && ! empty($a['deadline']))
                                <small class="rq-deadline">@include('userPanel.partials.icon', ['name' => 'clock']) مهلت
                                    انتقال امتیاز</small>
                            @endif
                        </div>
                    </div>

                    {{-- عملیات --}}
                    <div class="ad-actions">
                        @if (in_array($a['state'], ['published', 'transfer'], true))
                            <a href="{{ route('site.ads.show', $a['id']) }}"
                               class="ad-act">@include('userPanel.partials.icon', ['name' => 'eye']) مشاهده آگهی</a>
                        @endif
                        @if ($a['offers'] > 0)
                            <a href="{{ route('user.ads.offers', $a['id']) }}" class="ad-act highlight">
                                @include('userPanel.partials.icon', ['name' => 'clipboard']) درخواست ها
                                <span class="ad-badge">{{ $faId($a['offers']) }}</span>
                                @if (! empty($a['new_offers']))
                                    <span class="ad-new">{{ $faId($a['new_offers']) }} جدید</span>
                                @endif
                            </a>
                        @endif
                        @if ($a['state'] === 'review' || $a['state'] === 'rejected')
                            <a href="{{ route('user.ads.edit', $a['id']) }}"
                               class="ad-act">@include('userPanel.partials.icon', ['name' => 'edit']) ویرایش</a>
                            <button type="button" class="ad-act danger ms-lg-auto" data-bs-toggle="modal"
                                    data-bs-target="#confirmModal"
                                    data-confirm-title="حذف آگهی"
                                    data-confirm-text="آیا از حذف «{{ $title }}» مطمئن هستید؟ این کار قابل بازگشت نیست."
                                    data-confirm-action="{{ route('user.ads.destroy', $a['id']) }}"
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
                    <a href="{{ route('user.ads.create') }}" class="btn btn-brand">ثبت آگهی جدید</a>
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
