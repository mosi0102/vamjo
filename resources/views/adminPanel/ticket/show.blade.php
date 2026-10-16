@extends('userPanel.layouts.layout')

@section('title', 'پاسخ به تیکت')
@section('page_title', 'تیکت')
@section('body_class', 'pn-adminPanel pn-chat')

@php
    $faId = fn ($s) => strtr((string) $s, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $closed = $ticket['status'] === 'closed';
    $st = ['pending' => ['pending', 'در انتظار پاسخ'], 'answered' => ['approved', 'پاسخ داده شده'], 'closed' => ['expired', 'بسته شده']];
    [$pc, $pt] = $st[$ticket['status']] ?? ['pending', $ticket['status']];
    $prio = config('vamjou.ticket_priorities');
    $initial = fn ($n) => ($n && trim($n) !== '') ? mb_substr($n, 0, 1) : '؟';
    $agentName = fn ($a) => $a->name ?: trim(($a->first_name ?? '').' '.($a->last_name ?? ''));
    $avatarColor = fn ($id) => ['#1f8a9e', '#7a5ac8', '#d6322c', '#2e9a4f', '#e07b00', '#3e4ea0'][(int) $id % 6];
@endphp

@section('main')

    <a href="{{ route('admin.tickets.index') }}" class="of-back pn-reveal">@include('userPanel.partials.icon', ['name' => 'arrow']) بازگشت به تیکت ها</a>

    @if (session('success'))
        <div class="alert alert-success pn-reveal" role="alert">{{ session('success') }}</div>
    @endif

    <div class="at-tk-layout">
        <section class="tk-chat pn-reveal" style="--d:.06s" data-closed="{{ $closed ? 1 : 0 }}">
            <header class="tk-top">
                <div class="tk-top-info">
                    <h3>{{ $ticket['title'] }}</h3>
                    <div class="rq-meta">
                        <span class="tk-id">#{{ $faId($ticket['code'] ?: $ticket['id']) }}</span>
                        <span class="pn-pill {{ $pc }}" id="tkStatus">{{ $pt }}</span>
                        <span class="tk-tag">{{ $ticket['dept'] }}</span>
                        <span class="tk-prio {{ $ticket['priority'] }}">{{ $prio[$ticket['priority']] ?? $ticket['priority'] }}</span>
                        <span class="rq-meta-i">@include('userPanel.partials.icon', ['name' => 'calendar']){{ $ticket['date'] }}</span>
                        @if ($ticket['assignee'])
                            <span class="rq-meta-i">@include('userPanel.partials.icon', ['name' => 'user'])مسئول: {{ $ticket['assignee'] === 'me' ? 'من (شما)' : $ticket['assignee'] }}</span>
                        @endif
                    </div>
                </div>
            </header>

            <div class="tk-messages" id="tkMessages" tabindex="0" aria-live="polite" aria-label="گفت‌وگوی تیکت">
                @forelse ($messages as $m)
                    {{-- از دید پشتیبان: پیام‌های پشتیبان = me (راست) | پیام‌های کاربر = sup (چپ) --}}
                    <div class="tk-msg {{ $m['from'] === 'user' ? 'sup cust' : 'me' }}{{ ! empty($m['internal']) ? ' note' : '' }}">
                        <b class="tk-who">{{ ! empty($m['internal']) ? 'یادداشت داخلی' : ($m['from'] === 'user' ? $user['name'] : 'پشتیبانی (شما)') }}</b>
                        <div class="tk-bubble">{!! nl2br(e($m['text'])) !!}</div>
                        @if (! empty($m['file']))
                            <div class="tk-file">
                                <span class="tk-file-name"><span dir="ltr">{{ $m['file']['name'] }}</span> <small>({{ $m['file']['size'] }})</small></span>
                                <a href="{{ $m['file']['url'] }}" class="tk-dl" download>@include('userPanel.partials.icon', ['name' => 'download']) دانلود فایل</a>
                            </div>
                        @endif
                        <small class="tk-time">{{ $m['time'] }}</small>
                    </div>
                @empty
                    <div class="tk-msg sup cust"><b class="tk-who">{{ $user['name'] }}</b><div class="tk-bubble">پیامی در این تیکت ثبت نشده است.</div></div>
                @endforelse
            </div>

            @if ($closed)
                {{-- بازگشایی تیکت: POST به update با status=pending --}}
                <form method="POST" action="{{ route('admin.tickets.update', $ticket['id']) }}" class="tk-closed">
                    @csrf
                    <input type="hidden" name="status" value="pending">
                    @include('userPanel.partials.icon', ['name' => 'lock'])
                    <span>این تیکت بسته شده است.</span>
                    <button type="submit" class="btn btn-brand">بازگشایی تیکت</button>
                </form>
            @else
                <form id="tkReply" class="tk-reply" method="POST" action="{{ route('admin.tickets.reply', $ticket['id']) }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    <div class="at-canned" aria-label="پاسخ‌های آماده">
                        @foreach ($canned as $i => $t)<button type="button" class="at-chip" data-canned="{{ $t }}">پاسخ آماده {{ $faId($i + 1) }}</button>@endforeach
                    </div>
                    <div class="tk-attached" id="tkAttached" hidden>@include('userPanel.partials.icon', ['name' => 'clip'])<span id="tkAttachedName" dir="ltr"></span><button type="button" id="tkAttachedRemove" aria-label="حذف فایل ضمیمه">×</button></div>
                    <div class="tk-bar">
                        <label class="tk-attach" for="tkFile" tabindex="0">@include('userPanel.partials.icon', ['name' => 'clip'])<span>انتخاب فایل ضمیمه</span></label>
                        <input type="file" id="tkFile" name="attachment" accept="image/jpeg,image/png,application/pdf" hidden>
                        <textarea id="tkText" name="message" rows="1" maxlength="1500" placeholder="پاسخ خود را اینجا وارد نمایید" aria-label="متن پاسخ"></textarea>
                        <button type="submit" class="btn btn-brand tk-send" id="tkSend"><span class="btn-label">ارسال پاسخ</span>@include('userPanel.partials.icon', ['name' => 'send'])<span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
                    </div>
                    <label class="at-internal"><input type="checkbox" id="tkInternal" name="internal" value="1"><span class="box"></span> یادداشت داخلی (برای کاربر نمایش داده نمی‌شود)</label>
                    <p class="f-err tk-err" id="tkErr" role="alert"></p>
                </form>
            @endif
        </section>

        {{-- اطلاعات و کنترل‌ها --}}
        <aside class="at-tk-side pn-reveal" style="--d:.12s">
            <div class="pn-card at-side-card">
                <h4>کاربر</h4>
                <a href="{{ route('admin.users.show', $user['id']) }}" class="at-user">
                    <span class="at-av" style="background:{{ $avatarColor($user['id']) }}">{{ $initial($user['name']) }}</span>
                    <span class="at-name"><b>{{ $user['name'] }}</b><small dir="ltr">{{ $faId($user['mobile']) }}</small></span>
                </a>
                <div class="at-side-kv"><span>هویت</span><b>{{ $user['ver'] ?: '—' }}</b></div>
                <div class="at-side-kv"><span>آگهی ها</span><b>{{ $faId($user['ads']) }}</b></div>
                <div class="at-side-kv"><span>درخواست ها</span><b>{{ $faId($user['reqs']) }}</b></div>
            </div>

            <form class="pn-card at-side-card" method="POST" action="{{ route('admin.tickets.update', $ticket['id']) }}" id="tkMetaForm">
                @csrf
                <h4>مدیریت تیکت</h4>
                <div class="f-field">
                    <label for="tkAssign">مسئول</label>
                    <select id="tkAssign" name="assignee_id" class="f-input f-select">
                        <option value="">بدون مسئول</option>
                        @foreach ($agents as $agent)
                            <option value="{{ $agent->id }}" @selected((int) $ticket['assignee_id'] === (int) $agent->id)>{{ $agentName($agent) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="f-field mt-2">
                    <label for="tkPrio">اولویت</label>
                    <select id="tkPrio" name="priority" class="f-input f-select">
                        @foreach ($prio as $k => $l)<option value="{{ $k }}" @selected($k === $ticket['priority'])>{{ $l }}</option>@endforeach
                    </select>
                </div>
                <div class="at-side-act">
                    <button type="submit" class="btn btn-brand">ذخیره تغییرات</button>
                    @unless ($closed)
                        <button type="button" class="btn btn-ghost of-reject" data-bs-toggle="modal" data-bs-target="#confirmModal"
                                data-confirm-title="بستن تیکت"
                                data-confirm-text="تیکت بسته شود؟ کاربر دیگر نمی‌تواند در آن پیام بدهد."
                                data-confirm-action="{{ route('admin.tickets.close', $ticket['id']) }}"
                                data-confirm-method="POST" data-confirm-label="بستن تیکت" data-confirm-tone="danger">بستن تیکت</button>
                    @endunless
                </div>
            </form>
        </aside>
    </div>

    @include('userPanel.partials.confirm-modal')
@endsection
