@extends('userPanel.layouts.layout')

@section('title', 'مشاهده تیکت')
@section('page_title', 'تیکت')
@section('body_class', 'pn-tickets pn-chat')

@php
    $faId = fn ($n) => strtr((string) $n, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $closed = $ticket['status'] === 'closed';
    $statusMap = ['pending' => ['pending', 'در انتظار پاسخ'], 'answered' => ['approved', 'پاسخ داده شده'], 'closed' => ['expired', 'بسته شده']];
    $prioMap = ['low' => 'کم', 'normal' => 'متوسط', 'high' => 'زیاد', 'urgent' => 'فوری'];
    [$pc, $pt] = $statusMap[$ticket['status']] ?? ['pending', $ticket['status']];
@endphp

@section('main')

    <a href="{{ route('user.tickets.index') }}"
       class="of-back pn-reveal">@include('userPanel.partials.icon', ['name' => 'arrow']) بازگشت به تیکت ها</a>

    <div class="pn-head pn-reveal">
        <span class="pn-head-ic">@include('userPanel.partials.icon', ['name' => 'chat'])</span>
        <div>
            <h2>تیکت های پشتیبانی</h2>
            <p>پیام ها و تیکت های خود را به پشتیبانی وامجو ارسال نمایید و با پشتیبانی گفت و گو داشته باشید.</p>
        </div>
    </div>

    <section class="tk-chat pn-reveal" style="--d:.08s" data-closed="{{ $closed ? 1 : 0 }}">

        {{-- اطلاعات تیکت --}}
        <header class="tk-top">
            <div class="tk-top-info">
                <h3>{{ $ticket['title'] }}</h3>
                <div class="rq-meta">
                    <span class="tk-id">#{{ $faId($ticket['code'] ?? $ticket['id']) }}</span>
                    <span class="pn-pill {{ $pc }}" id="tkStatus">{{ $pt }}</span>
                    <span class="tk-tag">{{ $ticket['dept'] }}</span>
                    <span class="tk-prio {{ $ticket['priority'] }}">{{ $prioMap[$ticket['priority']] }}</span>
                    <span class="rq-meta-i">@include('userPanel.partials.icon', ['name' => 'calendar']){{ $ticket['date'] }}</span>
                </div>
            </div>
            @unless ($closed)
                <button type="button" class="ad-act danger" data-bs-toggle="modal" data-bs-target="#confirmModal"
                        data-confirm-title="بستن تیکت"
                        data-confirm-text="با بستن تیکت دیگر نمی‌توانید پیام جدید ارسال کنید. تیکت بسته شود؟"
                        data-confirm-action="{{ route('user.tickets.close', $ticket['id']) }}"
                        data-confirm-method="POST" data-confirm-label="بستن تیکت" data-confirm-tone="danger">
                    @include('userPanel.partials.icon', ['name' => 'close']) بستن تیکت
                </button>
            @endunless
        </header>

        {{-- پیام‌ها --}}
        <div class="tk-messages" id="tkMessages" tabindex="0" aria-live="polite" aria-label="گفت‌وگوی تیکت">
            @foreach ($messages as $m)
                <div class="tk-msg {{ $m['from'] === 'user' ? 'me' : 'sup' }}">
                    <b class="tk-who">{{ $m['from'] === 'user' ? 'پیام شما' : 'پشتیبانی وامجو' }}</b>
                    <div class="tk-bubble">{!! nl2br(e($m['text'])) !!}</div>
                    @if (! empty($m['file']))
                        <div class="tk-file">
                            <span class="tk-file-name"><span dir="ltr">{{ $m['file']['name'] }}</span> <small>({{ $m['file']['size'] }})</small></span>
                            <a href="{{ $m['file']['url'] }}" class="tk-dl"
                               download>@include('userPanel.partials.icon', ['name' => 'download']) دانلود فایل</a>
                        </div>
                    @endif
                    <small class="tk-time">{{ $m['time'] }}</small>
                </div>
            @endforeach
        </div>

        {{-- پاسخ --}}
        @if ($closed)
            <div class="tk-closed">
                @include('userPanel.partials.icon', ['name' => 'lock'])
                <span>این تیکت بسته شده است. برای ادامه، تیکت جدید ثبت کنید.</span>
                <a href="{{ route('user.tickets.create') }}" class="btn btn-brand">تیکت جدید</a>
            </div>
        @else
            <form id="tkReply" class="tk-reply" method="POST"
                  action="{{ route('user.tickets.reply', $ticket['id']) }}" enctype="multipart/form-data"
                  novalidate>
                @csrf
                <div class="tk-attached" id="tkAttached" hidden>
                    @include('userPanel.partials.icon', ['name' => 'clip'])<span id="tkAttachedName" dir="ltr"></span>
                    <button type="button" id="tkAttachedRemove" aria-label="حذف فایل ضمیمه">×</button>
                </div>
                <div class="tk-bar">
                    <label class="tk-attach" for="tkFile"
                           tabindex="0">@include('userPanel.partials.icon', ['name' => 'clip'])
                        <span>انتخاب فایل ضمیمه</span></label>
                    <input type="file" id="tkFile" name="attachment" accept="image/png,image/jpeg,application/pdf" hidden>
                    <textarea id="tkText" name="message" rows="1" maxlength="1500"
                              placeholder="پاسخ خود را اینجا وارد نمایید" aria-label="متن پاسخ"></textarea>
                    <button type="submit" class="btn btn-brand tk-send" id="tkSend"><span
                                class="btn-label">ارسال پاسخ</span>@include('userPanel.partials.icon', ['name' => 'send'])
                        <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
                </div>
                <p class="f-err tk-err" id="tkErr" role="alert"></p>
            </form>
        @endif
    </section>

    @include('userPanel.partials.confirm-modal')
@endsection
