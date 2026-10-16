@extends('userPanel.layouts.layout')

@section('title', 'تیکت های پشتیبانی')
@section('page_title', 'تیکت ها')
@section('body_class', 'pn-adminPanel')

@php
    // status: pending (بی‌پاسخ) | answered | closed | assignee: me | نام | null
    $faId = fn ($s) => strtr((string) $s, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $colors = ['#1f8a9e', '#7a5ac8', '#d6322c', '#2e9a4f', '#e07b00', '#3e4ea0'];
    $st = ['pending' => ['pending', 'در انتظار پاسخ'], 'answered' => ['approved', 'پاسخ داده شده'], 'closed' => ['expired', 'بسته شده']];
    $prio = ['low' => 'کم', 'normal' => 'متوسط', 'high' => 'زیاد', 'urgent' => 'فوری'];
    $c = collect($rows);
    $cnt = ['all' => $c->count(), 'pending' => $c->where(5, 'pending')->count(), 'answered' => $c->where(5, 'answered')->count(), 'closed' => $c->where(5, 'closed')->count(), 'mine' => $c->where(6, 'me')->count()];
    $tabs = ['pending' => 'در انتظار پاسخ', 'mine' => 'تیکت های من', 'answered' => 'پاسخ داده شده', 'closed' => 'بسته شده', 'all' => 'همه'];
@endphp

@section('main')

    <div class="pn-head pn-reveal">
        <span class="pn-head-ic">@include('userPanel.partials.icon', ['name' => 'chat'])</span>
        <div><h2>تیکت های پشتیبانی</h2><p>پیام‌های کاربران را پاسخ دهید؛ اولویت‌ها را بررسی و تیکت‌ها را به خودتان اختصاص دهید.</p></div>
    </div>

    <section class="pn-panel pn-reveal at-box" style="--d:.08s" data-table data-page-size="8">
        <div class="rq-toolbar">
            <div class="rq-tabs" role="tablist" aria-label="فیلتر تیکت ها">
                @foreach ($tabs as $k => $l)<button type="button" class="rq-tab {{ $k === 'pending' ? 'active' : '' }}" data-tgroup="{{ $k }}" role="tab" aria-selected="{{ $k === 'pending' ? 'true' : 'false' }}">{{ $l }}<span class="rq-count">{{ $faId($cnt[$k]) }}</span></button>@endforeach
            </div>
            <div class="of-tools">
                <label class="rq-search">@include('userPanel.partials.icon', ['name' => 'search'])<input type="search" data-tsearch placeholder="جستجو موضوع، شناسه یا کاربر..." autocomplete="off" aria-label="جستجو"></label>
                <label class="of-sort"><span class="visually-hidden">فیلتر اولویت</span><select data-tfilter="prio" aria-label="فیلتر اولویت"><option value="">همه اولویت ها</option>@foreach ($prio as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></label>
            </div>
        </div>

        <div class="at-scroll">
            <table class="at-table">
                <thead><tr><th>تیکت</th><th>کاربر</th><th>بخش</th><th>اولویت</th><th>وضعیت</th><th>مسئول</th><th data-sort="age">آخرین به‌روزرسانی</th><th></th></tr></thead>
                <tbody>
                @foreach ($rows as $r)
                    @php [$pc, $pt] = $st[$r[5]]; $g = $r[5] . ($r[6] === 'me' ? ' mine' : ''); @endphp
                    <tr data-groups="{{ $g }}" data-prio="{{ $r[4] }}" data-search="{{ $r[0] }} {{ $r[1] }} {{ $r[2] }}" data-age="{{ $r[8] }}">
                        <td data-label="تیکت"><a href="{{ route('admin.tickets.show', $r[0]) }}" class="at-name"><b>{{ $r[1] }}</b><small>#{{ $faId($r[0]) }}</small></a></td>
                        <td data-label="کاربر"><span class="at-user"><span class="at-av sm" style="background:{{ $colors[$r[0] % 6] }}">{{ mb_substr($r[2], 0, 1) }}</span>{{ $r[2] }}</span></td>
                        <td data-label="بخش"><span class="tk-tag">{{ $r[3] }}</span></td>
                        <td data-label="اولویت"><span class="tk-prio {{ $r[4] }}">{{ $prio[$r[4]] }}</span></td>
                        <td data-label="وضعیت"><span class="pn-pill {{ $pc }}">{{ $pt }}</span></td>
                        <td data-label="مسئول">{{ $r[6] === 'me' ? 'شما' : ($r[6] ?: '—') }}</td>
                        <td data-label="به‌روزرسانی">{{ $r[7] }}</td>
                        <td class="at-actions"><a href="{{ route('admin.tickets.show', $r[0]) }}" class="btn {{ $r[5] === 'pending' ? 'btn-brand' : 'btn-ghost' }} at-btn">{{ $r[5] === 'closed' ? 'مشاهده' : 'پاسخ' }}</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="at-foot"><span data-tinfo></span><div class="at-pager" data-tpager></div></div>
        <div class="rq-empty" data-tempty hidden><div class="rq-empty-ic" aria-hidden="true">@include('userPanel.partials.icon', ['name' => 'chat'])</div><h3>تیکتی پیدا نشد</h3><p>همه‌چیز مرتب است 🎉</p></div>
    </section>
@endsection