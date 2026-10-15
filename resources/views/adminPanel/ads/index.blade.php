@extends('userPanel.layouts.layout')

@section('title', 'مدیریت آگهی ها')
@section('page_title', 'آگهی ها')
@section('body_class', 'pn-admin')

@php
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $faId = fn ($s) => strtr((string) $s, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $isAdmin = \App\Support\PanelRole::isAdmin();
    $map = ['pending' => ['pending', 'منتظر تایید'], 'published' => ['approved', 'منتشر شده'], 'transfer' => ['pending', 'در حال معامله'], 'completed' => ['approved', 'تکمیل شده'], 'rejected' => ['rejected', 'رد شده']];
    $tabs = ['pending' => 'منتظر تایید', 'published' => 'منتشر شده', 'featured' => 'ویژه', 'rejected' => 'رد شده', 'all' => 'همه'];
    $c = collect($ads);
    $cnt = [
        'pending'   => $c->where('status', 'pending')->count(),
        'published' => $c->where('status', 'published')->count(),
        'featured'  => $c->where('featured', true)->count(),
        'rejected'  => $c->where('status', 'rejected')->count(),
        'all'       => $c->count(),
    ];
@endphp

@section('main')

    <div class="pn-head pn-reveal">
        <span class="pn-head-ic">@include('userPanel.partials.icon', ['name' => 'book'])</span>
        <div><h2>مدیریت آگهی ها</h2><p>آگهی‌های ثبت‌شده را بررسی کنید؛ تایید و انتشار، رد با ذکر دلیل و انتخاب آگهی ویژه.</p></div>
    </div>

    @if (session('success'))
        <div class="alert alert-success pn-reveal" role="alert">{{ session('success') }}</div>
    @endif

    <section class="pn-panel pn-reveal at-box" style="--d:.08s" data-table data-page-size="8">
        <div class="rq-toolbar">
            <div class="rq-tabs" role="tablist" aria-label="فیلتر آگهی ها">
                @foreach ($tabs as $k => $l)<button type="button" class="rq-tab {{ $k === 'pending' ? 'active' : '' }}" data-tgroup="{{ $k }}" role="tab" aria-selected="{{ $k === 'pending' ? 'true' : 'false' }}">{{ $l }}<span class="rq-count">{{ $faId($cnt[$k] ?? 0) }}</span></button>@endforeach
            </div>
            <label class="rq-search">@include('userPanel.partials.icon', ['name' => 'search'])<input type="search" data-tsearch placeholder="جستجو شناسه، بانک یا فروشنده..." autocomplete="off" aria-label="جستجو"></label>
        </div>

        <div class="at-scroll">
            <table class="at-table">
                <thead><tr><th>آگهی</th><th>فروشنده</th><th data-sort="amount">مبلغ وام</th><th data-sort="price">قیمت فروش</th><th data-sort="age">ثبت</th><th>وضعیت</th><th>ویژه</th><th></th></tr></thead>
                <tbody>
                @forelse ($ads as $a)
                    @php
                        [$pc, $pt] = $map[$a['status']] ?? ['pending', $a['status']];
                        $title = 'وام ' . $fa($a['amount']) . ' تومان بانک ' . $a['bank'];
                        $checkable = $isAdmin && in_array($a['status'], ['pending', 'published']);
                    @endphp
                    <tr data-groups="{{ $a['status'] }}{{ $a['featured'] ? ' featured' : '' }}" data-search="{{ $a['id'] }} {{ $a['code'] }} {{ $a['bank'] }} {{ $a['seller'] }} {{ $a['amount'] }}" data-amount="{{ $a['amount'] }}" data-price="{{ $a['price'] }}" data-age="{{ $a['age'] }}">
                        <td data-label="آگهی"><div class="at-user"><span class="at-av sq" style="background:{{ $a['c'] }}">{{ $a['l'] }}</span><span class="at-name"><b>{{ $title }}</b><small>شناسه #{{ $faId($a['id']) }} · {{ $a['city'] }}</small></span></div></td>
                        <td data-label="فروشنده">{{ $a['seller'] }}</td>
                        <td data-label="مبلغ وام">{{ $fa($a['amount']) }}</td>
                        <td data-label="قیمت فروش"><b>{{ $fa($a['price']) }}</b></td>
                        <td data-label="ثبت">{{ $a['ago'] }}</td>
                        <td data-label="وضعیت"><span class="pn-pill {{ $pc }}">{{ $pt }}</span></td>
                        <td data-label="ویژه">
                            @if ($checkable)
                                <label class="rd-switch" title="آگهی ویژه" data-name="آگهی {{ $faId($a['id']) }}" data-url="{{ route('admin.ads.feature', $a['id']) }}" data-switch-group="featured"><input type="checkbox" {{ $a['featured'] ? 'checked' : '' }} aria-label="ویژه بودن آگهی {{ $faId($a['id']) }}"><i></i></label>
                            @else<span class="text-muted">{{ $a['featured'] ? '★ ویژه' : '—' }}</span>@endif
                        </td>
                        <td class="at-actions">
                            <button type="button" class="btn {{ $a['status'] === 'pending' ? 'btn-brand' : 'btn-ghost' }} at-btn" data-bs-toggle="modal" data-bs-target="#adReviewModal"
                                    data-id="{{ $a['id'] }}" data-title="{{ $title }}" data-bank="{{ $a['bank'] }}" data-amount="{{ $fa($a['amount']) }}" data-amount-raw="{{ $a['amount'] }}" data-price="{{ $fa($a['price']) }}" data-price-raw="{{ $a['price'] }}"
                                    data-months="{{ $faId($a['months']) }}" data-rate="{{ $faId($a['rate']) }}" data-seller="{{ $a['seller'] }}" data-city="{{ $a['city'] }}" data-sheba="{{ $a['sheba'] }}"
                                    data-note="{{ $a['note'] }}" data-status="{{ $a['status'] }}" data-featured="{{ $a['featured'] ? 1 : 0 }}" data-admin-panel="{{ $isAdmin ? 1 : 0 }}" data-doc1="{{ $a['doc1'] ?? '' }}" data-doc2="{{ $a['doc2'] ?? '' }}">{{ $a['status'] === 'pending' ? 'بررسی' : 'مشاهده' }}</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">
                        <div class="rq-empty"><div class="rq-empty-ic" aria-hidden="true">@include('userPanel.partials.icon', ['name' => 'book'])</div><h3>آگهی ای وجود ندارد</h3><p>در این دسته آگهی ای پیدا نشد.</p></div>
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="at-foot"><span data-tinfo></span><div class="at-pager" data-tpager></div></div>
        <div class="rq-empty" data-tempty hidden><div class="rq-empty-ic" aria-hidden="true">@include('userPanel.partials.icon', ['name' => 'book'])</div><h3>آگهی ای وجود ندارد</h3><p>در این دسته آگهی ای پیدا نشد.</p></div>
    </section>

    @include('adminPanel.partials.ad-review-modal')
@endsection
