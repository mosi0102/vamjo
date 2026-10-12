@extends('userPanel.layouts.layout')

@section('title', 'احراز هویت کاربران')
@section('page_title', 'احراز هویت')
@section('body_class', 'pn-adminPanel')

@php
    // نمونه داده؛ در پروژه واقعی از کنترلر پاس بدهید: compact('requests')
    $faId = fn ($s) => strtr((string) $s, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $ph = 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="480" height="300"><rect width="480" height="300" fill="#e4ebe6"/><rect x="90" y="45" width="300" height="210" rx="16" fill="#fff" stroke="#b9cbc0" stroke-width="5"/><circle cx="165" cy="125" r="28" fill="#9fb8ab"/><path d="M225 105h130M225 140h100M135 200h220" stroke="#9fb8ab" stroke-width="12" stroke-linecap="round"/></svg>');
    $colors = ['#1f8a9e', '#7a5ac8', '#d6322c', '#2e9a4f', '#e07b00', '#3e4ea0'];
    $rows = [
      [3001, 'علی رضایی',  '09301021000', '0499370800', '۱۳۷۸/۰۵/۱۴', 'IR062960000000100324200001', 'ملت',   '۲ ساعت پیش', 120,  'pending'],
      [3002, 'مریم احمدی', '09121234567', '0012345679', '۱۳۷۰/۱۱/۰۲', 'IR012170000000200400500002', 'ملی',   '۳ ساعت پیش', 180,  'pending'],
      [3003, 'حسین کریمی', '09153334455', '0923456781', '۱۳۸۱/۰۲/۲۰', 'IR180120000000300600700003', 'تجارت', '۵ ساعت پیش', 300,  'pending'],
      [3004, 'زهرا محمدی', '09351112233', '0456789123', '۱۳۷۵/۰۹/۰۹', 'IR190190000000400800900004', 'صادرات','دیروز',      1500, 'pending'],
      [3005, 'رضا نوری',   '09171230001', '0067891234', '۱۳۶۸/۰۱/۳۰', 'IR150150000000500100110005', 'سپه',   'دیروز',      1700, 'pending'],
      [3006, 'سارا حسینی', '09302223344', '0789123456', '۱۳۷۹/۰۷/۱۱', 'IR560560000000600120130006', 'سامان', '۲ روز پیش',  2900, 'approved'],
      [3007, 'امیر جعفری', '09125556677', '0891234567', '۱۳۷۲/۰۳/۲۵', 'IR610610000000700140150007', 'شهر',   '۳ روز پیش',  4400, 'approved'],
      [3008, 'فاطمه کاظمی','09391110022', '0912345678', '۱۳۸۳/۱۲/۰۱', 'IR540540000000800160170008', 'پارسیان','۴ روز پیش', 5800, 'rejected'],
    ];
    $map = ['pending' => ['pending', 'در انتظار بررسی'], 'approved' => ['approved', 'تایید شده'], 'rejected' => ['rejected', 'رد شده']];
    $cnt = collect($rows)->groupBy(9)->map->count(); $cnt['all'] = count($rows);
    $tabs = ['pending' => 'در انتظار بررسی', 'approved' => 'تایید شده', 'rejected' => 'رد شده', 'all' => 'همه'];
@endphp

@section('main')

    <div class="pn-head pn-reveal">
        <span class="pn-head-ic">@include('userPanel.partials.icon', ['name' => 'shield'])</span>
        <div><h2>احراز هویت کاربران</h2><p>مدارک کاربران تازه‌ثبت‌نام را بررسی و تایید یا رد کنید. کاربر تا تایید هویت نمی‌تواند آگهی ثبت کند.</p></div>
    </div>

    <section class="pn-panel pn-reveal at-box" style="--d:.08s" data-table data-page-size="8">
        <div class="rq-toolbar">
            <div class="rq-tabs" role="tablist" aria-label="فیلتر وضعیت">
                @foreach ($tabs as $k => $l)<button type="button" class="rq-tab {{ $k === 'pending' ? 'active' : '' }}" data-tgroup="{{ $k }}" role="tab" aria-selected="{{ $k === 'pending' ? 'true' : 'false' }}">{{ $l }}<span class="rq-count">{{ $faId($cnt[$k] ?? 0) }}</span></button>@endforeach
            </div>
            <label class="rq-search">@include('userPanel.partials.icon', ['name' => 'search'])<input type="search" data-tsearch placeholder="جستجو نام، موبایل یا کد ملی..." autocomplete="off" aria-label="جستجو"></label>
        </div>

        <div class="at-scroll">
            <table class="at-table">
                <thead><tr><th>کاربر</th><th>کد ملی</th><th>تاریخ تولد</th><th>شبا / بانک</th><th data-sort="age">زمان ارسال</th><th>وضعیت</th><th></th></tr></thead>
                <tbody>
                @foreach ($rows as $r)
                    @php [$pc, $pt] = $map[$r[9]]; @endphp
                    <tr data-group="{{ $r[9] }}" data-search="{{ $r[1] }} {{ $r[2] }} {{ $r[3] }}" data-age="{{ $r[8] }}">
                        <td data-label="کاربر"><a href="{{ url('/adminPanel/users/' . ($r[0] - 1000)) }}" class="at-user"><span class="at-av" style="background:{{ $colors[$r[0] % 6] }}">{{ mb_substr($r[1], 0, 1) }}</span><span class="at-name"><b>{{ $r[1] }}</b><small dir="ltr">{{ $faId($r[2]) }}</small></span></a></td>
                        <td data-label="کد ملی"><span dir="ltr">{{ $faId($r[3]) }}</span></td>
                        <td data-label="تاریخ تولد">{{ $r[4] }}</td>
                        <td data-label="شبا / بانک"><span class="vf-bank">{{ $r[6] }}</span> <small class="at-mono" dir="ltr">{{ $faId(substr($r[5], 0, 10)) }}…</small></td>
                        <td data-label="زمان ارسال">{{ $r[7] }}</td>
                        <td data-label="وضعیت"><span class="pn-pill {{ $pc }}">{{ $pt }}</span></td>
                        <td class="at-actions">
                            <button type="button" class="btn {{ $r[9] === 'pending' ? 'btn-brand' : 'btn-ghost' }} at-btn" data-bs-toggle="modal" data-bs-target="#kycModal"
                                    data-id="{{ $r[0] }}" data-name="{{ $r[1] }}" data-mobile="{{ $faId($r[2]) }}" data-nid="{{ $faId($r[3]) }}" data-birth="{{ $r[4] }}" data-sheba="{{ $r[5] }}" data-bank="{{ $r[6] }}" data-sent="{{ $r[7] }}"
                                    data-status="{{ $r[9] }}" data-selfie="{{ $ph }}" data-idcard="{{ $ph }}">{{ $r[9] === 'pending' ? 'بررسی' : 'مشاهده' }}</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="at-foot"><span data-tinfo></span><div class="at-pager" data-tpager></div></div>
        <div class="rq-empty" data-tempty hidden><div class="rq-empty-ic" aria-hidden="true">@include('userPanel.partials.icon', ['name' => 'shield'])</div><h3>درخواستی وجود ندارد</h3><p>همه درخواست‌ها بررسی شده‌اند.</p></div>
    </section>

    @include('adminPanel.partials.kyc-modal')
@endsection