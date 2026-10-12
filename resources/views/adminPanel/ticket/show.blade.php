@extends('userPanel.layouts.layout')

@section('title', 'پاسخ به تیکت')
@section('page_title', 'تیکت')
@section('body_class', 'pn-adminPanel pn-chat')

@php
    // نمونه داده؛ در پروژه واقعی از کنترلر پاس بدهید: compact('ticket', 'messages', 'user')
    $faId = fn ($s) => strtr((string) $s, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $ticket = ['id' => 136548, 'title' => 'عدم ثبت آگهی انتقال وام', 'status' => 'pending', 'priority' => 'urgent', 'dept' => 'آگهی ها', 'date' => '۲۴ اردیبهشت ۱۴۰۵ – ۲۳:۱۶', 'assignee' => ''];
    $user = ['id' => 2005, 'name' => 'رضا نوری', 'mobile' => '09171230001', 'ver' => 'احراز شده', 'ads' => 7, 'reqs' => 12];
    $messages = [
      ['from' => 'user',    'time' => '۲۳:۱۶', 'text' => 'سلام، من انتقال امتیاز وام را انجام دادم ولی در پنل هنوز گزینه ثبت اطلاعات انتقال فعال نشده. لطفاً بررسی کنید.'],
      ['from' => 'user',    'time' => '۲۳:۲۰', 'text' => 'تصویر رسید انتقال را هم ضمیمه کردم.', 'file' => ['name' => 'IMG-20261218.JPG', 'size' => '۴.۶M', 'url' => '#']],
    ];
    $closed = $ticket['status'] === 'closed';
    $canned = ['سلام و وقت بخیر؛ درخواست شما در حال بررسی است و نتیجه به‌زودی اعلام می‌شود.', 'لطفاً تصویر واضح‌تری از مدرک ارسال کنید.', 'مشکل شما برطرف شد. در صورت نیاز دوباره پیام بدهید.', 'برای پیگیری بیشتر، شماره تراکنش و شناسه وام را ارسال کنید.'];
    $prio = ['low' => 'کم', 'normal' => 'متوسط', 'high' => 'زیاد', 'urgent' => 'فوری'];
@endphp

@section('main')

    <a href="{{ url('/adminPanel/tickets') }}" class="of-back pn-reveal">@include('userPanel.partials.icon', ['name' => 'arrow']) بازگشت به تیکت ها</a>

    <div class="at-tk-layout">
        <section class="tk-chat pn-reveal" style="--d:.06s" data-closed="{{ $closed ? 1 : 0 }}">
            <header class="tk-top">
                <div class="tk-top-info">
                    <h3>{{ $ticket['title'] }}</h3>
                    <div class="rq-meta">
                        <span class="tk-id">#{{ $faId($ticket['id']) }}</span>
                        <span class="pn-pill pending" id="tkStatus">در انتظار پاسخ</span>
                        <span class="tk-tag">{{ $ticket['dept'] }}</span>
                        <span class="tk-prio {{ $ticket['priority'] }}">{{ $prio[$ticket['priority']] }}</span>
                        <span class="rq-meta-i">@include('userPanel.partials.icon', ['name' => 'calendar']){{ $ticket['date'] }}</span>
                    </div>
                </div>
            </header>

            <div class="tk-messages" id="tkMessages" tabindex="0" aria-live="polite" aria-label="گفت‌وگوی تیکت">
                @foreach ($messages as $m)
                    {{-- از دید پشتیبان: پیام‌های پشتیبان = me (راست) | پیام‌های کاربر = sup (چپ) --}}
                    <div class="tk-msg {{ $m['from'] === 'user' ? 'sup cust' : 'me' }}">
                        <b class="tk-who">{{ $m['from'] === 'user' ? $user['name'] : 'پشتیبانی (شما)' }}</b>
                        <div class="tk-bubble">{!! nl2br(e($m['text'])) !!}</div>
                        @if (! empty($m['file']))<div class="tk-file"><span class="tk-file-name"><span dir="ltr">{{ $m['file']['name'] }}</span> <small>({{ $m['file']['size'] }})</small></span><a href="{{ $m['file']['url'] }}" class="tk-dl" download>@include('userPanel.partials.icon', ['name' => 'download']) دانلود فایل</a></div>@endif
                        <small class="tk-time">{{ $m['time'] }}</small>
                    </div>
                @endforeach
            </div>

            @if ($closed)
                <div class="tk-closed">@include('userPanel.partials.icon', ['name' => 'lock'])<span>این تیکت بسته شده است.</span><button type="button" class="btn btn-brand">بازگشایی تیکت</button></div>
            @else
                <form id="tkReply" class="tk-reply" method="POST" action="{{ url('/adminPanel/tickets/' . $ticket['id'] . '/reply') }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    <div class="at-canned" aria-label="پاسخ‌های آماده">
                        @foreach ($canned as $i => $t)<button type="button" class="at-chip" data-canned="{{ $t }}">پاسخ آماده {{ $faId($i + 1) }}</button>@endforeach
                    </div>
                    <div class="tk-attached" id="tkAttached" hidden>@include('userPanel.partials.icon', ['name' => 'clip'])<span id="tkAttachedName" dir="ltr"></span><button type="button" id="tkAttachedRemove" aria-label="حذف فایل ضمیمه">×</button></div>
                    <div class="tk-bar">
                        <label class="tk-attach" for="tkFile" tabindex="0">@include('userPanel.partials.icon', ['name' => 'clip'])<span>انتخاب فایل ضمیمه</span></label>
                        <input type="file" id="tkFile" name="attachment" accept="image/png,image/jpeg" hidden>
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
                <a href="{{ url('/adminPanel/users/' . $user['id']) }}" class="at-user"><span class="at-av" style="background:#d6322c">{{ mb_substr($user['name'], 0, 1) }}</span><span class="at-name"><b>{{ $user['name'] }}</b><small dir="ltr">{{ $faId($user['mobile']) }}</small></span></a>
                <div class="at-side-kv"><span>هویت</span><b>{{ $user['ver'] }}</b></div>
                <div class="at-side-kv"><span>آگهی ها</span><b>{{ $faId($user['ads']) }}</b></div>
                <div class="at-side-kv"><span>درخواست ها</span><b>{{ $faId($user['reqs']) }}</b></div>
            </div>

            <form class="pn-card at-side-card" method="POST" action="{{ url('/adminPanel/tickets/' . $ticket['id'] . '/update') }}" id="tkMetaForm">
                @csrf
                <h4>مدیریت تیکت</h4>
                <div class="f-field"><label for="tkAssign">مسئول</label><select id="tkAssign" name="assignee" class="f-input f-select"><option value="">بدون مسئول</option><option value="me">من (شما)</option><option value="2">کارشناس ۲</option></select></div>
                <div class="f-field mt-2"><label for="tkPrio">اولویت</label><select id="tkPrio" name="priority" class="f-input f-select">@foreach ($prio as $k => $l)<option value="{{ $k }}" @selected($k === $ticket['priority'])>{{ $l }}</option>@endforeach</select></div>
                <div class="at-side-act">
                    <button type="submit" class="btn btn-brand">ذخیره تغییرات</button>
                    @unless ($closed)
                        <button type="button" class="btn btn-ghost of-reject" data-bs-toggle="modal" data-bs-target="#confirmModal" data-confirm-title="بستن تیکت" data-confirm-text="تیکت بسته شود؟ کاربر دیگر نمی‌تواند در آن پیام بدهد."
                                data-confirm-action="{{ url('/adminPanel/tickets/' . $ticket['id'] . '/close') }}" data-confirm-method="POST" data-confirm-label="بستن تیکت" data-confirm-tone="danger">بستن تیکت</button>
                    @endunless
                </div>
            </form>
        </aside>
    </div>

    @include('userPanel.partials.confirm-modal')
@endsection