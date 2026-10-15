@extends('userPanel.layouts.layout')

@section('title', 'جزئیات کاربر')
@section('page_title', 'جزئیات کاربر')
@section('body_class', 'pn-adminPanel')

@php
    // داده‌ها از Admin\UserController@show می‌آید: $u، $stats، $myAds، $myReqs، $logs، $notes، $kyc
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $faId = fn ($s) => strtr((string) $s, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $isAdmin = (auth()->user()->role ?? request('role', 'adminPanel')) === 'adminPanel';
    $ph = 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="320" height="200"><rect width="320" height="200" fill="#e4ebe6"/><rect x="60" y="30" width="200" height="140" rx="12" fill="#fff" stroke="#b9cbc0" stroke-width="4"/><circle cx="110" cy="85" r="18" fill="#9fb8ab"/><path d="M150 70h90M150 95h70M90 130h150" stroke="#9fb8ab" stroke-width="8" stroke-linecap="round"/></svg>');
    // وضعیت احراز هویت کاربر
    $verMap = [
        'verified' => ['approved', 'احراز شده'],
        'pending' => ['pending', 'در انتظار بررسی'],
        'rejected' => ['rejected', 'رد شده'],
        'unverified' => ['expired', 'احراز نشده'],
    ];
    [$verClass, $verText] = $verMap[$u['ver']] ?? ['expired', $u['ver']];
@endphp

@section('main')

    <a href="{{ url('/adminPanel/users') }}" class="of-back pn-reveal">@include('userPanel.partials.icon', ['name' => 'arrow']) بازگشت به لیست کاربران</a>

    {{-- سربرگ کاربر --}}
    <section class="at-profile pn-reveal" style="--d:.05s">
        <span class="at-av xl" style="background:#1f8a9e">{{ mb_substr($u['name'], 0, 1) }}</span>
        <div class="at-profile-info">
            <h2>{{ $u['name'] }} <span class="pn-pill {{ $verClass }}">{{ $verText }}</span> <span class="pn-pill {{ $u['blocked'] ? 'rejected' : 'approved' }}">{{ $u['blocked'] ? 'مسدود' : 'فعال' }}</span></h2>
            <div class="rq-meta">
                <span class="rq-meta-i">@include('userPanel.partials.icon', ['name' => 'phone'])<span dir="ltr">{{ $faId($u['mobile']) }}</span></span>
                <span class="rq-meta-i">@include('userPanel.partials.icon', ['name' => 'pin']){{ $u['city'] }}</span>
                <span class="rq-meta-i">@include('userPanel.partials.icon', ['name' => 'calendar']) عضویت: {{ $u['joined'] }}</span>
                <span class="rq-meta-i">@include('userPanel.partials.icon', ['name' => 'clock']) آخرین ورود: {{ $u['last'] }}</span>
            </div>
        </div>
        <div class="at-profile-act">
            <a href="{{ url('/adminPanel/tickets?user=' . $u['id']) }}" class="btn btn-ghost">@include('userPanel.partials.icon', ['name' => 'chat']) تیکت های کاربر</a>
            @if ($isAdmin && $u['blocked'])
                <button type="button" class="btn btn-ghost" data-bs-toggle="modal" data-bs-target="#confirmModal" data-confirm-title="رفع مسدودی کاربر" data-confirm-text="مسدودی {{ $u['name'] }} برداشته شود؟" data-confirm-action="{{ url('/adminPanel/users/' . $u['id'] . '/unblock') }}" data-confirm-method="POST" data-confirm-label="رفع مسدودی">@include('userPanel.partials.icon', ['name' => 'check']) رفع مسدودی</button>
            @elseif ($isAdmin)
                <button type="button" class="btn btn-ghost of-reject" data-bs-toggle="modal" data-bs-target="#confirmModal" data-confirm-title="مسدود کردن کاربر" data-confirm-text="{{ $u['name'] }} مسدود شود؟ کاربر دیگر نمی‌تواند وارد حساب خود شود." data-confirm-action="{{ url('/adminPanel/users/' . $u['id'] . '/block') }}" data-confirm-method="POST" data-confirm-label="مسدود شود" data-confirm-tone="danger" data-confirm-reason="1">@include('userPanel.partials.icon', ['name' => 'ban']) مسدود کردن</button>
            @endif
        </div>
    </section>

    <section class="of-stats">
        @foreach ($stats as $i => $s)<article class="of-stat pn-reveal" style="--d:{{ .08 + $i * .05 }}s"><span class="of-stat-ic">@include('userPanel.partials.icon', ['name' => $s[2]])</span><div><small>{{ $s[0] }}</small><b>{!! $s[1] !!}</b></div></article>@endforeach
    </section>

    <section class="pn-panel pn-reveal" style="--d:.2s" data-tabs>
        <div class="rq-tabs at-tabs" role="tablist">
            @foreach (['info' => 'اطلاعات هویتی', 'ads' => 'آگهی ها', 'reqs' => 'درخواست ها', 'log' => 'سابقه فعالیت', 'note' => 'یادداشت داخلی'] as $k => $l)
                <button type="button" class="rq-tab {{ $loop->first ? 'active' : '' }}" data-tab-target="{{ $k }}" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $l }}</button>
            @endforeach
        </div>

        <div class="at-pane" data-tab-pane="info">
            <dl class="vf-sum">
                <div><dt>نام و نام خانوادگی</dt><dd>{{ $u['name'] }}</dd></div>
                <div><dt>کد ملی</dt><dd dir="ltr">{{ $faId($u['nid']) }}</dd></div>
                <div><dt>تاریخ تولد</dt><dd>{{ $u['birth'] }}</dd></div>
                <div><dt>شماره همراه</dt><dd dir="ltr">{{ $faId($u['mobile']) }}</dd></div>
                <div><dt>شماره شبا</dt><dd dir="ltr">{{ $faId($u['sheba']) }} <span class="vf-bank">{{ $u['bank'] }}</span></dd></div>
                <div><dt>آی‌پی آخرین ورود</dt><dd dir="ltr">{{ $faId($u['ip']) }}</dd></div>
            </dl>
            <h4 class="at-sub">مدارک احراز هویت
                @if ($kyc)
                    <span class="pn-pill {{ ['pending' => 'pending', 'approved' => 'approved', 'rejected' => 'rejected'][$kyc->status] ?? 'expired' }}">{{ $kyc->statusLabel() }}</span>
                    <a href="{{ route('admin.verifications', ['state' => $kyc->status]) }}" class="at-sub-link">بررسی در صفحه احراز هویت</a>
                @endif
            </h4>
            @if ($kyc)
                <dl class="vf-sum kyc-data mb-3">
                    <div><dt>کد ملی ثبت‌شده</dt><dd dir="ltr">{{ $faId($kyc->national_id) }}</dd></div>
                    <div><dt>تاریخ تولد</dt><dd>{{ $kyc->birth_date ? $faId(str_replace('-', '/', $kyc->birth_date)) : '—' }}</dd></div>
                    <div><dt>شبا / بانک</dt><dd dir="ltr">{{ $faId((string) $kyc->sheba) }} <span class="vf-bank">{{ $kyc->bank_name }}</span></dd></div>
                    <div><dt>زمان ارسال</dt><dd>{{ \App\Support\Fa::dateTime($kyc->created_at, 'j F Y – H:i') }}</dd></div>
                    @if ($kyc->reviewed_at)
                        <div><dt>بررسی‌کننده</dt><dd>{{ $kyc->reviewer?->display_name ?? '—' }}</dd></div>
                        <div><dt>زمان بررسی</dt><dd>{{ \App\Support\Fa::dateTime($kyc->reviewed_at, 'j F Y – H:i') }}</dd></div>
                    @endif
                    @if ($kyc->reject_reason)
                        <div class="span2"><dt>دلیل رد</dt><dd>{{ $kyc->reject_reason }}</dd></div>
                    @endif
                </dl>
                <div class="at-docs">
                    @foreach ([[$kyc->selfieUrl(), 'تصویر سلفی با کارت ملی'], [$kyc->nationalCardUrl(), 'تصویر کارت ملی']] as [$src, $label])
                        <figure class="at-doc">
                            @if ($src)
                                <img src="{{ $src }}" alt="{{ $label }}" loading="lazy" class="lazy-img zoomable" data-full="{{ $src }}">
                            @else
                                <img src="{{ $ph }}" alt="{{ $label }}" loading="lazy">
                            @endif
                            <figcaption>{{ $label }}</figcaption>
                        </figure>
                    @endforeach
                </div>
            @else
                <p class="at-empty">این کاربر هنوز مدارک احراز هویت ارسال نکرده است.</p>
            @endif
        </div>

        <div class="at-pane" data-tab-pane="ads" hidden>
            @if (empty($myAds))
                <p class="at-empty">این کاربر آگهی‌ای ثبت نکرده است.</p>
            @else
                <div class="at-mini">
                    @foreach ($myAds as $a)<div class="at-mini-row"><div><b>{{ $a[0] }}</b><small>شناسه #{{ $a[1] }} · {{ $a[4] }}</small></div><span class="pn-pill {{ $a[3] }}">{{ $a[2] }}</span></div>@endforeach
                </div>
            @endif
        </div>
        <div class="at-pane" data-tab-pane="reqs" hidden>
            @if (empty($myReqs))
                <p class="at-empty">درخواست خرید/فروشی برای این کاربر ثبت نشده است.</p>
            @else
                <div class="at-mini">
                    @foreach ($myReqs as $a)<div class="at-mini-row"><div><b>{{ $a[0] }}</b><small>شناسه #{{ $a[1] }} · پیشنهاد {{ $a[4] }} تومان</small></div><span class="pn-pill {{ $a[3] }}">{{ $a[2] }}</span></div>@endforeach
                </div>
            @endif
        </div>
        <div class="at-pane" data-tab-pane="log" hidden>
            @if (empty($logs))
                <p class="at-empty">فعالیتی برای این کاربر ثبت نشده است.</p>
            @else
                <div class="pn-timeline at-log">
                    @foreach ($logs as $l)<div class="pn-tl-item"><span class="pn-tl-ic">@include('userPanel.partials.icon', ['name' => $l[0]])</span><div><p>{{ $l[1] }}</p><small>{{ $l[2] }}</small></div></div>@endforeach
                </div>
            @endif
        </div>
        <div class="at-pane" data-tab-pane="note" hidden>
            @if (session('success'))
                <div class="alert alert-success" role="alert">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
            @endif

            @if ($notes->isNotEmpty())
                <div class="at-mini mb-3">
                    @foreach ($notes as $n)
                        <div class="at-mini-row"><div><b>{{ $n->body }}</b><small>{{ $n->author?->display_name ?? '—' }} · {{ \App\Support\Fa::ago($n->created_at) }}</small></div></div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ url('/adminPanel/users/' . $u['id'] . '/note') }}" class="at-note">
                @csrf
                <div class="f-field"><label for="uNote">یادداشت داخلی (فقط برای مدیران و پشتیبان‌ها)</label><textarea id="uNote" name="body" class="f-input" rows="4" maxlength="500" placeholder="مثلا: کاربر تماس گرفت و درخواست بررسی مجدد داد...">{{ old('body') }}</textarea></div>
                <button type="submit" class="btn btn-brand">@include('userPanel.partials.icon', ['name' => 'check']) ذخیره یادداشت</button>
            </form>
        </div>
    </section>

    @include('userPanel.partials.confirm-modal')
@endsection