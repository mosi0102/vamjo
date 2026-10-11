@extends('userPanel.layouts.layout')

@section('title', 'اطلاعات هویتی')
@section('page_title', 'اطلاعات هویتی')
@section('body_class', 'pn-verify')

@php
    // نمونه داده؛ در پروژه واقعی از کنترلر پاس بدهید: compact('state', 'profile', 'reason')
    // state: unverified | pending | rejected | verified   (برای تست: /panel/verify?state=pending)
    $faId = fn ($n) => strtr((string) $n, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    $state = in_array(request('state'), ['pending', 'rejected', 'verified']) ? request('state') : 'unverified';
    $locked = in_array($state, ['pending', 'verified']);
    $reason = 'تصویر سلفی همراه با کارت ملی واضح نیست؛ لطفاً با نور مناسب دوباره عکس بگیرید.';
    $profile = ['first' => 'محمد حسین', 'last' => 'طاهری', 'mobile' => '09301021373', 'nid' => '0499370899', 'birth' => '1400-01-01', 'birth_fa' => '۱۴۰۰ – ۰۱ – ۰۱', 'sheba' => 'IR062960000000100324200001', 'bank' => 'ملت'];
    $steps = [['base', 'اطلاعات پایه'], ['birth', 'تاریخ تولد'], ['sheba', 'شماره شبا'], ['id', 'کارت ملی'], ['selfie', 'سلفی با کارت']];
    $mask = fn ($s, $a = 2, $b = 3) => $faId(substr($s, 0, $a) . str_repeat('•', max(strlen($s) - $a - $b, 0)) . substr($s, -$b));
    $stateInfo = [
      'unverified' => ['info', 'idcard', 'احراز هویت انجام نشده است', 'برای ثبت آگهی و درخواست وام، اطلاعات زیر را تکمیل کنید. بررسی اطلاعات حداکثر ۲۴ ساعت زمان می‌برد.'],
      'pending'    => ['warn', 'clock', 'اطلاعات شما در حال بررسی است', 'کارشناسان ما در حال بررسی مدارک شما هستند. نتیجه تا ۲۴ ساعت آینده با پیامک و اعلان پنل اعلام می‌شود.'],
      'rejected'   => ['bad', 'alert', 'احراز هویت رد شد', $reason],
      'verified'   => ['ok', 'shield', 'هویت شما تایید شده است', 'اکنون می‌توانید آگهی ثبت کنید و برای وام‌ها درخواست بدهید.'],
    ][$state];
@endphp

@section('main')

    <div class="pn-head pn-reveal">
        <span class="pn-head-ic">@include('userPanel.partials.icon', ['name' => 'idcard'])</span>
        <div>
            <h2>اطلاعات هویتی</h2>
            <p>اطلاعات هویتی خودتان را در این بخش تکمیل نمایید.</p>
        </div>
    </div>

    <div class="vf-wrap">

        {{-- وضعیت احراز هویت --}}
        <div class="vf-status {{ $stateInfo[0] }} pn-reveal" role="status" style="--d:.06s">
            <span class="vf-status-ic">@include('userPanel.partials.icon', ['name' => $stateInfo[1]])</span>
            <div><b>{{ $stateInfo[2] }}</b>
                <p>{{ $stateInfo[3] }}</p></div>
            @if ($state === 'pending')
                <ol class="vf-timeline" aria-label="مراحل بررسی">
                    <li class="done">ارسال شد</li>
                    <li class="now">در حال بررسی</li>
                    <li>نتیجه</li>
                </ol>
            @endif
        </div>

        @if ($locked)
            {{-- خلاصه اطلاعات (فقط‌خواندنی) --}}
            <section class="pn-panel pn-reveal" style="--d:.12s">
                <h3 class="vf-sum-title">اطلاعات ثبت شده</h3>
                <dl class="vf-sum">
                    <div>
                        <dt>نام و نام خانوادگی</dt>
                        <dd>{{ $profile['first'] }} {{ $profile['last'] }}</dd>
                    </div>
                    <div>
                        <dt>شماره همراه</dt>
                        <dd dir="ltr">{{ $faId($profile['mobile']) }}</dd>
                    </div>
                    <div>
                        <dt>کد ملی</dt>
                        <dd dir="ltr">{{ $mask($profile['nid'], 2, 3) }}</dd>
                    </div>
                    <div>
                        <dt>تاریخ تولد</dt>
                        <dd>{{ $profile['birth_fa'] }}</dd>
                    </div>
                    <div>
                        <dt>شماره شبا</dt>
                        <dd dir="ltr">IR{{ $mask(substr($profile['sheba'], 2), 4, 4) }} <span
                                    class="vf-bank">{{ $profile['bank'] }}</span></dd>
                    </div>
                    <div>
                        <dt>مدارک</dt>
                        <dd>@include('userPanel.partials.icon', ['name' => 'check']) کارت ملی و سلفی بارگذاری شده</dd>
                    </div>
                </dl>
                <p class="vf-lock-note">@include('userPanel.partials.icon', ['name' => 'lock']) برای تغییر اطلاعات پس از
                    تایید، با پشتیبانی تماس بگیرید. <a href="{{ url('/panel/tickets/create') }}">ثبت تیکت</a></p>
            </section>
        @else
            {{-- پیشرفت تکمیل --}}
            <div class="pn-progress vf-progress pn-reveal" style="--d:.1s" role="progressbar" aria-valuenow="0"
                 aria-valuemin="0" aria-valuemax="100" aria-label="پیشرفت تکمیل اطلاعات" id="vfProgress">
                <div class="pn-progress-top"><span>پیشرفت تکمیل اطلاعات</span><b id="vfPct">۰٪</b></div>
                <div class="pn-progress-bar"><i id="vfBar" style="--w:0%"></i></div>
                <ul class="pn-steps">
                    @foreach ($steps as $s)
                        <li data-step="{{ $s[0] }}"><span><i
                                        class="ic-ok">@include('userPanel.partials.icon', ['name' => 'check'])</i><i
                                        class="ic-wait">@include('userPanel.partials.icon', ['name' => 'clock'])</i></span>{{ $s[1] }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <section class="pn-panel pn-reveal" style="--d:.14s">
                <form id="vfForm" class="dz-scope" action="{{ url('/panel/verify') }}" method="POST"
                      enctype="multipart/form-data" novalidate>
                    @csrf

                    <div class="vf-grid">
                        <div class="f-field" data-field="first">
                            <label for="vfFirst">نام <span class="req">*</span></label>
                            <input type="text" id="vfFirst" name="first_name" class="f-input"
                                   value="{{ $profile['first'] }}" placeholder="نام خود را وارد کنید"
                                   autocomplete="given-name">
                            <p class="f-err">نام را به فارسی وارد کنید.</p>
                        </div>
                        <div class="f-field" data-field="last">
                            <label for="vfLast">نام خانوادگی <span class="req">*</span></label>
                            <input type="text" id="vfLast" name="last_name" class="f-input"
                                   value="{{ $profile['last'] }}" placeholder="نام خانوادگی خود را وارد کنید"
                                   autocomplete="family-name">
                            <p class="f-err">نام خانوادگی را به فارسی وارد کنید.</p>
                        </div>

                        <div class="f-field">
                            <label for="vfMobile">شماره همراه</label>
                            <input type="text" id="vfMobile" name="mobile" class="f-input"
                                   value="{{ $faId($profile['mobile']) }}" dir="ltr" readonly
                                   aria-describedby="vfMobileHint">
                            <p class="f-hint" id="vfMobileHint">شماره‌ای که با آن وارد شده‌اید؛ قابل تغییر نیست.</p>
                        </div>
                        <div class="f-field" data-field="nid">
                            <label for="vfNid">کدملی <span class="req">*</span></label>
                            <input type="text" id="vfNid" name="national_code" class="f-input" dir="ltr"
                                   inputmode="numeric" maxlength="10" value="{{ $profile['nid'] }}"
                                   placeholder="کد ملی خود را وارد کنید" autocomplete="off">
                            <p class="f-err">کد ملی معتبر نیست.</p>
                        </div>

                        <div class="f-field" data-field="birth">
                            <label for="vfBirth">تاریخ تولد <span class="req">*</span></label>
                            <div class="chip-wrap vf-date">
                                <input type="text" id="vfBirth" class="f-input" readonly placeholder="۱۴۰۰ – ۰۱ – ۰۱"
                                       aria-haspopup="dialog" aria-expanded="false">
                                <input type="hidden" id="vfBirthVal" name="birth_date">
                                <button type="button" class="chip-now" id="vfBirthBtn">انتخاب تاریخ</button>
                                <div class="vf-pop" id="vfPop" role="dialog" aria-label="انتخاب تاریخ تولد" hidden>
                                    <div class="vf-pop-grid">
                                        <label>سال<select id="vfY" class="f-input f-select"></select></label>
                                        <label>ماه<select id="vfM" class="f-input f-select"></select></label>
                                        <label>روز<select id="vfD" class="f-input f-select"></select></label>
                                    </div>
                                    <div class="vf-pop-act">
                                        <button type="button" class="btn btn-brand" id="vfPopOk">تایید تاریخ</button>
                                        <button type="button" class="btn btn-ghost" id="vfPopCancel">انصراف</button>
                                    </div>
                                </div>
                            </div>
                            <p class="f-err" id="vfBirthErr">تاریخ تولد را انتخاب کنید (سن حداقل ۱۸ سال).</p>
                        </div>
                        <div class="f-field" data-field="sheba">
                            <label for="vfSheba">شماره شبا حساب <span class="req">*</span></label>
                            <input type="text" id="vfSheba" name="sheba" class="f-input ltr" dir="ltr"
                                   inputmode="numeric" maxlength="26" placeholder="IR------------------------"
                                   autocomplete="off">
                            <div class="vf-sheba-meta"><span
                                        class="f-hint vf-warn">حساب باید به نام خودتان باشد</span><span class="vf-bank"
                                                                                                        id="vfBankChip"
                                                                                                        hidden></span>
                            </div>
                            <p class="f-err">شماره شبا معتبر نیست (۲۴ رقم بعد از IR).</p>
                        </div>

                        @foreach ([['selfie', 'تصویر سلفی با کارت ملی', 'JPG, PNG (حداکثر ۵MB)', 'idcard'], ['id_card', 'تصویر کارت ملی', 'JPG, PNG (حداکثر ۵MB)', 'doc']] as [$key, $label, $sub, $ic])
                            <div class="f-field" data-field="{{ $key }}">
                                <label class="dz" for="vf_{{ $key }}" tabindex="0">
                                    <input type="file" id="vf_{{ $key }}" name="{{ $key }}"
                                           accept="image/png,image/jpeg" hidden>
                                    <span class="dz-ic">@include('userPanel.partials.icon', ['name' => $ic])</span>
                                    <img class="dz-thumb" alt="" hidden>
                                    <b class="dz-title">{{ $label }}</b>
                                    <small class="dz-sub">{{ $sub }}</small>
                                    <button type="button" class="dz-remove" aria-label="حذف فایل" hidden>×</button>
                                </label>
                                <p class="f-err">{{ $label }} را بارگذاری کنید.</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="vf-tips">
                        <b>@include('userPanel.partials.icon', ['name' => 'shield']) راهنمای تصویر سلفی</b>
                        <ul>
                            <li>کارت ملی را کنار صورت خود نگه دارید و هر دو در یک قاب باشند.</li>
                            <li>در نور کافی عکس بگیرید؛ اطلاعات کارت باید کاملاً خوانا باشد.</li>
                            <li>از عینک آفتابی، ماسک یا فیلتر استفاده نکنید.</li>
                        </ul>
                    </div>

                    <button type="submit" class="btn btn-brand btn-lg w-100" id="vfSubmit"><span class="btn-label">ثبت و تایید</span><span
                                class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
                    <p class="vf-privacy">@include('userPanel.partials.icon', ['name' => 'lock']) اطلاعات شما محرمانه
                        است و فقط برای احراز هویت استفاده می‌شود.</p>
                </form>
            </section>
        @endif
    </div>

    {{-- مودال موفقیت --}}
    <div class="modal fade app-modal" id="vfDone" tabindex="-1" aria-labelledby="vfDoneTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sheet">
            <div class="modal-content">
                <div class="sheet-handle d-sm-none" aria-hidden="true"></div>
                <div class="modal-body text-center">
                    <div class="m-illus round ok" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M5 12.5l4.5 4.5L19 7.5"/>
                        </svg>
                    </div>
                    <h2 class="m-title" id="vfDoneTitle">اطلاعات شما ارسال شد</h2>
                    <p class="m-text">کارشناسان ما اطلاعات و مدارک شما را بررسی می‌کنند. نتیجه تا ۲۴ ساعت آینده با پیامک
                        و اعلان پنل اعلام می‌شود.</p>
                    <div class="m-actions">
                        <a href="{{ url('/panel') }}" class="btn btn-brand btn-lg flex-grow-1">بازگشت به داشبورد</a>
                        <a href="{{ url('/panel/verify?state=pending') }}" class="btn btn-ghost btn-lg" data-no-loader>مشاهده وضعیت</a>
                    </div>
                </div>
            </div></div>
    </div>
@endsection
