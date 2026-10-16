@extends('userPanel.layouts.layout')

@section('title', 'لیست کاربران')
@section('page_title', 'کاربران')
@section('body_class', 'pn-admin')

@php
    // داده‌ها از Admin\UserController::index می‌آید: $users (صفحه جاری), $summary, $paginator
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $isAdmin = \App\Support\PanelRole::isAdmin();
    $colors = ['#1f8a9e', '#7a5ac8', '#d6322c', '#2e9a4f', '#e07b00', '#3e4ea0'];
    $ver = ['verified' => ['approved', 'احراز شده'], 'pending' => ['pending', 'در انتظار بررسی'], 'unverified' => ['expired', 'احراز نشده'], 'rejected' => ['rejected', 'رد شده']];
    $faId = fn ($s) => strtr((string) $s, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $c = collect($users);
    $tabs = ['all' => 'همه', 'verified' => 'احراز شده', 'pending' => 'در انتظار بررسی', 'unverified' => 'احراز نشده', 'blocked' => 'مسدود'];
    $cnt = ['all' => $c->count(), 'verified' => $c->where('ver', 'verified')->count(), 'pending' => $c->where('ver', 'pending')->count(), 'unverified' => $c->whereIn('ver', ['unverified', 'rejected'])->count(), 'blocked' => $c->where('blocked', true)->count()];
@endphp

@section('main')

    <div class="pn-head pn-reveal">
        <span class="pn-head-ic">@include('userPanel.partials.icon', ['name' => 'users'])</span>
        <div><h2>کاربران</h2><p>لیست کاربران ثبت‌نام‌شده؛ جستجو، فیلتر و مشاهده جزئیات هر کاربر.</p></div>
        <button type="button" class="btn btn-ghost pn-head-act" data-texport="users">@include('userPanel.partials.icon', ['name' => 'download']) خروجی CSV</button>
    </div>

    <section class="of-stats">
        @foreach ([['کل کاربران', $faId($summary['total']), 'users', ''], ['احراز شده', $faId($summary['verified']), 'shield', 'green'], ['منتظر بررسی هویت', $faId($summary['pending']), 'clock', 'amber'], ['مسدود شده', $faId($summary['blocked']), 'ban', '']] as $i => $s)
            <article class="of-stat pn-reveal {{ $s[3] }}" style="--d:{{ .05 + $i * .05 }}s"><span class="of-stat-ic">@include('userPanel.partials.icon', ['name' => $s[2]])</span><div><small>{{ $s[0] }}</small><b>{{ $s[1] }}</b></div></article>
        @endforeach
    </section>

    <section class="pn-panel pn-reveal at-box" style="--d:.15s" data-table data-page-size="8">
        <div class="rq-toolbar">
            <div class="rq-tabs" role="tablist" aria-label="فیلتر کاربران">
                @foreach ($tabs as $k => $l)<button type="button" class="rq-tab {{ $loop->first ? 'active' : '' }}" data-tgroup="{{ $k }}" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $l }}<span class="rq-count">{{ $faId($cnt[$k]) }}</span></button>@endforeach
            </div>
            <label class="rq-search">@include('userPanel.partials.icon', ['name' => 'search'])<input type="search" data-tsearch placeholder="جستجو نام، موبایل یا کد ملی..." autocomplete="off" aria-label="جستجوی کاربر"></label>
        </div>

        <div class="at-scroll">
            <table class="at-table">
                <thead><tr>
                    <th>کاربر</th><th>کد ملی</th><th>احراز هویت</th><th>وضعیت حساب</th>
                    <th data-sort="ads">آگهی</th><th data-sort="reqs">درخواست</th><th data-sort="paid">کل پرداخت</th><th data-sort="age">عضویت</th><th></th>
                </tr></thead>
                <tbody>
                @foreach ($users as $u)
                    @php [$vc, $vt] = $ver[$u['ver']]; $groups = trim($u['ver'] . ($u['ver'] === 'rejected' ? ' unverified' : '') . ($u['blocked'] ? ' blocked' : '')); @endphp
                    <tr data-groups="{{ $groups }}" data-search="{{ $u['name'] }} {{ $u['mobile'] }} {{ $u['nid'] }}" data-ads="{{ $u['ads'] }}" data-reqs="{{ $u['reqs'] }}" data-paid="{{ $u['paid'] }}" data-age="{{ $u['age'] }}"
                        data-csv="{{ $u['id'] }}|{{ $u['name'] }}|{{ $u['mobile'] }}|{{ $u['nid'] }}|{{ $vt }}|{{ $u['blocked'] ? 'مسدود' : 'فعال' }}|{{ $u['joined'] }}">
                        <td data-label="کاربر"><a href="{{ url('/adminPanel/users/' . $u['id']) }}" class="at-user"><span class="at-av" style="background:{{ $colors[$u['id'] % 6] }}">{{ mb_substr($u['name'], 0, 1) }}</span><span class="at-name"><b>{{ $u['name'] }}</b><small dir="ltr">{{ $faId($u['mobile']) }}</small></span></a></td>
                        <td data-label="کد ملی"><span dir="ltr">{{ $faId($u['nid']) }}</span></td>
                        <td data-label="احراز هویت"><span class="pn-pill {{ $vc }}">{{ $vt }}</span></td>
                        <td data-label="وضعیت حساب"><span class="pn-pill {{ $u['blocked'] ? 'rejected' : 'approved' }}">{{ $u['blocked'] ? 'مسدود' : 'فعال' }}</span></td>
                        <td data-label="آگهی">{{ $faId($u['ads']) }}</td>
                        <td data-label="درخواست">{{ $faId($u['reqs']) }}</td>
                        <td data-label="کل پرداخت">{{ $u['paid'] ? $fa($u['paid']) . ' ت' : '—' }}</td>
                        <td data-label="عضویت">{{ $u['joined'] }}</td>
                        <td class="at-actions">
                            <a href="{{ url('/adminPanel/users/' . $u['id']) }}" class="rd-ic-btn" title="مشاهده جزئیات" aria-label="مشاهده {{ $u['name'] }}">@include('userPanel.partials.icon', ['name' => 'eye'])</a>
                            @if ($isAdmin)
                                <button type="button" class="rd-ic-btn danger" title="{{ $u['blocked'] ? 'رفع مسدودی' : 'مسدود کردن' }}" data-bs-toggle="modal" data-bs-target="#confirmModal"
                                        data-confirm-title="{{ $u['blocked'] ? 'رفع مسدودی کاربر' : 'مسدود کردن کاربر' }}" data-confirm-text="{{ $u['blocked'] ? 'دسترسی ' . $u['name'] . ' به حساب کاربری دوباره فعال شود؟' : $u['name'] . ' مسدود شود؟ کاربر دیگر نمی‌تواند وارد حساب خود شود.' }}"
                                        data-confirm-action="{{ url('/adminPanel/users/' . $u['id'] . '/' . ($u['blocked'] ? 'unblock' : 'block')) }}" data-confirm-method="POST" data-confirm-label="{{ $u['blocked'] ? 'رفع مسدودی' : 'مسدود شود' }}" data-confirm-tone="{{ $u['blocked'] ? 'brand' : 'danger' }}">@include('userPanel.partials.icon', ['name' => 'ban'])</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="at-foot"><span data-tinfo></span><div class="at-pager" data-tpager></div></div>
        <div class="rq-empty" data-tempty hidden><div class="rq-empty-ic" aria-hidden="true">@include('userPanel.partials.icon', ['name' => 'users'])</div><h3>کاربری پیدا نشد</h3><p>فیلتر یا عبارت جستجو را تغییر دهید.</p></div>
    </section>

    @include('userPanel.partials.confirm-modal')
@endsection
