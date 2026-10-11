@extends('userPanel.layouts.layout')

@section('title', 'مشاهده تیکت')
@section('page_title', 'تیکت')
@section('body_class', 'pn-tickets pn-chat')

@php
    // نمونه داده؛ در پروژه واقعی از کنترلر پاس بدهید: compact('ticket', 'messages')
    $faId = fn ($n) => strtr((string) $n, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $ticket = ['id' => 136548, 'title' => 'عدم ثبت آگهی انتقال وام', 'status' => 'answered', 'priority' => 'high', 'dept' => 'آگهی ها', 'date' => '۲۴ اردیبهشت ۱۴۰۵ – ۲۳:۱۶'];
    $messages = [
      ['from' => 'user',    'time' => '۲۳:۱۶', 'text' => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده از طراحان گرافیک است. چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است و برای شرایط فعلی تکنولوژی مورد نیاز و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد. کتابهای زیادی در شصت و سه درصد گذشته حال و آینده.'],
      ['from' => 'support', 'time' => '۰۹:۴۲', 'text' => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده از طراحان گرافیک است. چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است و برای شرایط فعلی تکنولوژی مورد نیاز و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد. کتابهای زیادی در شصت و سه درصد گذشته حال و آینده و شناخت فراوان جامعه و متخصصان را می طلبد.'],
      ['from' => 'user',    'time' => '۱۰:۰۵', 'text' => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده از طراحان گرافیک است. چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است.', 'file' => ['name' => 'IMG-20261218.JPG', 'size' => '۴.۶M', 'url' => '#']],
    ];
    $closed = $ticket['status'] === 'closed';
    $statusMap = ['pending' => ['pending', 'در انتظار پاسخ'], 'answered' => ['approved', 'پاسخ داده شده'], 'closed' => ['expired', 'بسته شده']];
    $prioMap = ['low' => 'کم', 'normal' => 'متوسط', 'high' => 'زیاد', 'urgent' => 'فوری'];
    [$pc, $pt] = $statusMap[$ticket['status']];
@endphp

@section('main')

    <a href="{{ url('/userPanel/tickets') }}"
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
                    <span class="tk-id">#{{ $faId($ticket['id']) }}</span>
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
                        data-confirm-action="{{ url('/panel/tickets/' . $ticket['id'] . '/close') }}"
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
                <a href="{{ url('/panel/tickets/create') }}" class="btn btn-brand">تیکت جدید</a>
            </div>
        @else
            <form id="tkReply" class="tk-reply" method="POST"
                  action="{{ url('/panel/tickets/' . $ticket['id'] . '/reply') }}" enctype="multipart/form-data"
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
                    <input type="file" id="tkFile" name="attachment" accept="image/png,image/jpeg" hidden>
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
