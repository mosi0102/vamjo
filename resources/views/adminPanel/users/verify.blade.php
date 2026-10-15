@extends('userPanel.layouts.layout')

@section('title', 'احراز هویت کاربران')
@section('page_title', 'احراز هویت')
@section('body_class', 'pn-adminPanel')

@php
    $faId = fn ($s) => strtr((string) $s, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    // تصویر جانشین وقتی کاربر مدرکی بارگذاری نکرده است
    $ph = 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="480" height="300"><rect width="480" height="300" fill="#e4ebe6"/><rect x="90" y="45" width="300" height="210" rx="16" fill="#fff" stroke="#b9cbc0" stroke-width="5"/><circle cx="165" cy="125" r="28" fill="#9fb8ab"/><path d="M225 105h130M225 140h100M135 200h220" stroke="#9fb8ab" stroke-width="12" stroke-linecap="round"/></svg>');
    $colors = ['#1f8a9e', '#7a5ac8', '#d6322c', '#2e9a4f', '#e07b00', '#3e4ea0'];
    $tabs = ['pending' => 'در انتظار بررسی', 'approved' => 'تایید شده', 'rejected' => 'رد شده', 'all' => 'همه'];
    $activeTab = $state !== 'all' ? $state : ($counts['pending'] > 0 ? 'pending' : 'all');
@endphp

@section('main')

    <div class="pn-head pn-reveal">
        <span class="pn-head-ic">@include('userPanel.partials.icon', ['name' => 'shield'])</span>
        <div><h2>احراز هویت کاربران</h2><p>مدارک کاربران را بررسی و تایید یا رد کنید. کاربر تا تایید هویت نمی‌تواند آگهی ثبت کند.</p></div>
    </div>

    @if (session('success'))
        <div class="alert alert-success pn-reveal" role="alert" style="--d:.04s">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger pn-reveal" role="alert" style="--d:.04s">{{ $errors->first() }}</div>
    @endif

    <section class="of-stats">
        @foreach ([
            ['کل درخواست‌ها', $faId($counts['all']), 'shield', ''],
            ['در انتظار بررسی', $faId($counts['pending']), 'clock', 'amber'],
            ['تایید شده', $faId($counts['approved']), 'check', 'green'],
            ['رد شده', $faId($counts['rejected']), 'alert', ''],
        ] as $i => $s)
            <article class="of-stat pn-reveal {{ $s[3] }}" style="--d:{{ .05 + $i * .05 }}s">
                <span class="of-stat-ic">@include('userPanel.partials.icon', ['name' => $s[2]])</span>
                <div><small>{{ $s[0] }}</small><b>{{ $s[1] }}</b></div>
            </article>
        @endforeach
    </section>

    <section class="pn-panel pn-reveal at-box" style="--d:.08s" data-table data-page-size="8">
        <div class="rq-toolbar">
            <div class="rq-tabs" role="tablist" aria-label="فیلتر وضعیت">
                @foreach ($tabs as $k => $l)
                    <button type="button" class="rq-tab {{ $k === $activeTab ? 'active' : '' }}" data-tgroup="{{ $k }}"
                            role="tab" aria-selected="{{ $k === $activeTab ? 'true' : 'false' }}">{{ $l }}<span
                            class="rq-count">{{ $faId($counts[$k]) }}</span></button>
                @endforeach
            </div>
            <label class="rq-search">@include('userPanel.partials.icon', ['name' => 'search'])<input type="search"
                    data-tsearch placeholder="جستجو نام، موبایل یا کد ملی..." autocomplete="off" aria-label="جستجو"></label>
        </div>

        <div class="at-scroll">
            <table class="at-table">
                <thead>
                <tr>
                    <th>کاربر</th>
                    <th>کد ملی</th>
                    <th>تاریخ تولد</th>
                    <th>شبا / بانک</th>
                    <th data-sort="age">زمان ارسال</th>
                    <th>وضعیت</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($rows as $r)
                    <tr data-group="{{ $r['status'] }}"
                        data-search="{{ $r['name'] }} {{ $r['mobile'] }} {{ $r['nid'] }}"
                        data-age="{{ $r['age'] }}">
                        <td data-label="کاربر">
                            <a href="{{ route('admin.users.show', $r['user_id']) }}" class="at-user">
                                <span class="at-av"
                                      style="background:{{ $colors[$r['id'] % 6] }}">{{ mb_substr($r['name'], 0, 1) }}</span>
                                <span class="at-name">
                                    <b>{{ $r['name'] }}</b>
                                    <small dir="ltr">{{ $faId($r['mobile']) }}</small>
                                </span>
                            </a>
                        </td>
                        <td data-label="کد ملی"><span dir="ltr">{{ $faId($r['nid']) }}</span></td>
                        <td data-label="تاریخ تولد">{{ $r['birth'] ?: '—' }}</td>
                        <td data-label="شبا / بانک">
                            <span class="vf-bank">{{ $r['bank'] ?: '—' }}</span>
                            @if ($r['sheba'])
                                <small class="at-mono" dir="ltr">{{ $faId(substr($r['sheba'], 0, 10)) }}…</small>
                            @endif
                        </td>
                        <td data-label="زمان ارسال">{{ $r['ago'] }}</td>
                        <td data-label="وضعیت"><span class="pn-pill {{ $r['status'] }}">{{ $r['status_label'] }}</span></td>
                        <td class="at-actions">
                            <button type="button"
                                    class="btn {{ $r['status'] === 'pending' ? 'btn-brand' : 'btn-ghost' }} at-btn"
                                    data-bs-toggle="modal" data-bs-target="#kycModal"
                                    data-id="{{ $r['id'] }}" data-name="{{ $r['name'] }}"
                                    data-mobile="{{ $faId($r['mobile']) }}" data-nid="{{ $faId($r['nid']) }}"
                                    data-birth="{{ $r['birth'] ?: '—' }}" data-sheba="{{ $r['sheba'] ?: '—' }}"
                                    data-bank="{{ $r['bank'] }}" data-sent="{{ $r['sent'] }}"
                                    data-status="{{ $r['status'] }}" data-reviewer="{{ $r['reviewer'] ?? '' }}"
                                    data-reviewed="{{ $r['reviewed'] ?? '' }}" data-reason="{{ $r['reason'] ?? '' }}"
                                    data-selfie="{{ $r['selfie'] ?: $ph }}" data-idcard="{{ $r['idcard'] ?: $ph }}"
                                    data-has-docs="{{ $r['selfie'] || $r['idcard'] ? 1 : 0 }}"
                                    data-profile="{{ route('admin.users.show', $r['user_id']) }}"
                            >{{ $r['status'] === 'pending' ? 'بررسی' : 'مشاهده' }}</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="at-foot"><span data-tinfo></span><div class="at-pager" data-tpager></div></div>
        <div class="rq-empty" data-tempty hidden>
            <div class="rq-empty-ic" aria-hidden="true">@include('userPanel.partials.icon', ['name' => 'shield'])</div>
            <h3>درخواستی وجود ندارد</h3>
            <p>در این وضعیت هیچ درخواست احراز هویتی ثبت نشده است.</p>
        </div>
    </section>

    @include('adminPanel.partials.kyc-modal')
@endsection
