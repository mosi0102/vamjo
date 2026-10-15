@extends('userPanel.layouts.layout')

@section('title', 'ثبت آگهی جدید')
@section('page_title', 'ثبت آگهی جدید')
@section('body_class', 'pn-create')

@php
    $fa = fn ($n) => strtr(number_format($n), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
@endphp

@section('main')

    <a href="{{ route('user.ads.index') }}"
       class="of-back pn-reveal">@include('userPanel.partials.icon', ['name' => 'arrow']) بازگشت به آگهی های من</a>

    <div class="pn-head pn-reveal">
        <span class="pn-head-ic">@include('userPanel.partials.icon', ['name' => 'plus'])</span>
        <div>
            <h2>ثبت آگهی فروش امتیاز وام</h2>
            <p>اطلاعات وام خود را با دقت وارد کنید. آگهی شما پس از تایید اپراتور منتشر خواهد شد.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success pn-reveal" role="alert">{{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning pn-reveal" role="alert">{{ session('warning') }}</div>
    @endif

    <div class="ed-wrap mx-auto dz-scope">

        <form id="adForm" action="{{ route('user.ads.store') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf

            {{-- ===== اطلاعات وام ===== --}}
            <section class="f-card pn-reveal">
                <header class="f-card-head"><span class="f-ic"><svg viewBox="0 0 24 24"><path d="M4 21V5a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v16M15 9h4a1 1 0 0 1 1 1v11M2 21h20M8 8h3M8 12h3M8 16h3"/></svg></span><h2>اطلاعات وام</h2></header>
                <div class="f-grid">
                    <div class="f-field" data-field="bank">
                        <label for="fBank">بانک یا صندوق <span class="req">*</span></label>
                        <select id="fBank" name="bank" class="f-input f-select @error('bank') is-invalid @enderror">
                            <option value="" @selected(old('bank', '') === '') disabled>انتخاب کنید...</option>
                            @foreach ($banks as $b)
                                <option value="{{ $b }}" @selected(old('bank') === $b)>{{ $b }}</option>
                            @endforeach
                        </select>
                        <p class="f-err">@error('bank') {{ $message }} @else بانک یا صندوق را انتخاب کنید. @enderror</p>
                    </div>
                    <div class="f-field" data-field="amount">
                        <label for="fAmount">مبلغ وام (تومان) <span class="req">*</span></label>
                        <input type="text" id="fAmount" name="amount" class="f-input @error('amount') is-invalid @enderror" data-money inputmode="numeric" autocomplete="off" value="{{ old('amount') }}" placeholder="مثلا: ۳۰۰,۰۰۰,۰۰۰">
                        <p class="f-err">@error('amount') {{ $message }} @else مبلغ وام را وارد کنید. @enderror</p>
                    </div>
                    <div class="f-field" data-field="months">
                        <label for="fMonths">تعداد اقساط (ماه) <span class="req">*</span></label>
                        <input type="text" id="fMonths" name="months" class="f-input @error('months') is-invalid @enderror" data-int inputmode="numeric" autocomplete="off" value="{{ old('months') }}" placeholder="مثلا: ۶۰">
                        <p class="f-err">@error('months') {{ $message }} @else تعداد اقساط را بین ۱ تا ۱۲۰ ماه وارد کنید. @enderror</p>
                    </div>
                    <div class="f-field" data-field="rate">
                        <label for="fRate">نرخ سود بانکی (درصد) <span class="req">*</span></label>
                        <input type="text" id="fRate" name="rate" class="f-input @error('rate') is-invalid @enderror" data-decimal inputmode="decimal" autocomplete="off" value="{{ old('rate') }}" placeholder="مثلا: ۴">
                        <p class="f-err">@error('rate') {{ $message }} @else نرخ سود را بین ۰ تا ۱۰۰ وارد کنید. @enderror</p>
                    </div>
                </div>
            </section>

            {{-- ===== قیمت‌گذاری ===== --}}
            <section class="f-card pn-reveal">
                <header class="f-card-head"><span class="f-ic"><svg viewBox="0 0 24 24"><path d="M3 12V4a1 1 0 0 1 1-1h8l9 9-9 9z"/><circle cx="8" cy="8" r="1.5"/></svg></span><h2>قیمت گذاری</h2></header>
                <div class="f-grid">
                    <div class="f-field" data-field="price">
                        <label for="fPrice">قیمت پیشنهادی فروش (تومان) <span class="req">*</span></label>
                        <input type="text" id="fPrice" name="price" class="f-input @error('price') is-invalid @enderror" data-money inputmode="numeric" autocomplete="off" value="{{ old('price') }}" placeholder="مثلا: ۳۰۰,۰۰۰,۰۰۰">
                        <p class="f-err">@error('price') {{ $message }} @else قیمت پیشنهادی فروش را وارد کنید. @enderror</p>
                        <div class="reco-box" id="recoBox" aria-live="polite">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 18h6M10 21h4M12 3a6 6 0 0 0-3.5 10.9c.6.5 1 1.2 1 2.1h5c0-.9.4-1.6 1-2.1A6 6 0 0 0 12 3z"/></svg>
                            <span>توصیه سیستم: <b id="recoText">** تا ** میلیون تومان</b></span>
                        </div>
                    </div>
                    <div class="f-field" data-field="sheba">
                        <label for="fSheba">شماره شبا <span class="req">*</span></label>
                        <input type="text" id="fSheba" name="sheba" class="f-input ltr @error('sheba') is-invalid @enderror" dir="ltr" inputmode="numeric" autocomplete="off" value="{{ old('sheba') }}" placeholder="IR------------------------">
                        <p class="f-err">@error('sheba') {{ $message }} @else شماره شبا باید ۲۴ رقم (بعد از IR) باشد. @enderror</p>
                        <p class="f-hint">برای واریز وجه پس از تکمیل معامله</p>
                    </div>
                </div>
            </section>

            {{-- ===== توضیحات ===== --}}
            <section class="f-card pn-reveal">
                <header class="f-card-head"><span class="f-ic"><svg viewBox="0 0 24 24"><path d="M4 20h4L19 9a2.1 2.1 0 0 0-4-4L4 16zM14 6l4 4M4 6h6M4 10h3"/></svg></span><h2>توضیحات تکمیلی</h2></header>
                <div class="f-field" data-field="note">
                    <label for="fNote">شرایط ضامن و توضیحات <span class="req">*</span></label>
                    <textarea id="fNote" name="note" class="f-input @error('note') is-invalid @enderror" rows="4" maxlength="500" placeholder="توضیحات مربوط به شرایط ضامن، شهر و نحوه انتقال را بنویسید...">{{ old('note') }}</textarea>
                    <div class="ed-count"><span id="fNoteCount">۰</span> / ۵۰۰</div>
                    <p class="f-err">@error('note') {{ $message }} @else توضیحات را بنویسید (حداقل ۱۰ کاراکتر). @enderror</p>
                </div>
            </section>

            {{-- ===== آپلود مدارک ===== --}}
            <section class="f-card pn-reveal">
                <header class="f-card-head"><span class="f-ic"><svg viewBox="0 0 24 24"><path d="M12 15V4M8 8l4-4 4 4M5 14v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4"/></svg></span><h2>آپلود مدارک</h2></header>
                <div class="f-grid">
                    <div class="f-field" data-field="doc_balance">
                        <label class="dz" for="fDocBalance" tabindex="0" data-title="تصویر موجودی/امتیاز وام">
                            <input type="file" id="fDocBalance" name="doc_balance" accept="image/png,image/jpeg" hidden>
                            <span class="dz-ic">@include('userPanel.partials.icon', ['name' => 'doc'])</span>
                            <img class="dz-thumb" alt="" hidden>
                            <b class="dz-title">تصویر موجودی/امتیاز وام</b>
                            <small class="dz-sub">JPG, PNG (حداکثر ۵MB)</small>
                            <button type="button" class="dz-remove" aria-label="حذف فایل" hidden>×</button>
                        </label>
                        <p class="f-err">تصویر موجودی/امتیاز وام را بارگذاری کنید.</p>
                    </div>
                    <div class="f-field" data-field="doc_id">
                        <label class="dz" for="fDocId" tabindex="0" data-title="تصویر کارت ملی">
                            <input type="file" id="fDocId" name="doc_id" accept="image/png,image/jpeg" hidden>
                            <span class="dz-ic">@include('userPanel.partials.icon', ['name' => 'idcard'])</span>
                            <img class="dz-thumb" alt="" hidden>
                            <b class="dz-title">تصویر کارت ملی</b>
                            <small class="dz-sub">جهت تطبیق با حساب بانکی</small>
                            <button type="button" class="dz-remove" aria-label="حذف فایل" hidden>×</button>
                        </label>
                        <p class="f-err">تصویر کارت ملی را بارگذاری کنید.</p>
                    </div>
                </div>
            </section>

            {{-- ===== قوانین ===== --}}
            <div class="agree-box f-field pn-reveal" data-field="agree">
                <label class="chk">
                    <input type="checkbox" name="agree" id="fAgree" value="1" @checked(old('agree'))>
                    <span class="box"></span>
                    <span class="chk-text">من <a href="{{ route('site.rules') }}" target="_blank">قوانین سیستم معامله امن</a> و شرایط انتقال وجه پلتفرم "وام‌جو" را مطالعه کرده و می‌پذیرم. می‌دانم که اطلاعات دستگاه و آی‌پی من جهت جلوگیری از کلاهبرداری ثبت می‌شود.</span>
                </label>
                <p class="f-err">برای ادامه باید قوانین را بپذیرید.</p>
            </div>

            <button type="submit" class="btn btn-brand btn-lg w-100 mt-3 mb-2" id="adSubmit">
                <span class="btn-label">ثبت و ارسال برای تایید</span>
                <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span>
            </button>
        </form>
    </div>
@endsection
