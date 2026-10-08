@extends('website.layouts.layout')

@section('title', 'ثبت آگهی فروش امتیاز وام | وام‌جو')
@section('description', 'اطلاعات وام خود را وارد کنید؛ آگهی شما پس از تایید اپراتور منتشر خواهد شد.')
@section('body_class', 'page-create')

@php
    // نمونه داده؛ در پروژه واقعی از کنترلر پاس بدهید: compact('banks')
    $banks = ['بانک جاویدان', 'بانک رسالت', 'بانک صادرات', 'بانک شهر', 'بانک کشاورزی', 'بانک قرض الحسنه مهر ایران', 'بانک تجارت', 'بانک ملت', 'بانک ملی', 'بانک سپه'];
@endphp

@section('main')
    <div class="container create-page">

        <nav class="crumbs d-none d-md-flex" aria-label="مسیر صفحه">
            <a href="{{ url('/') }}">صفحه نخست</a><span class="sep-ic" aria-hidden="true">‹</span><span>ثبت آگهی وام</span>
        </nav>

        <div class="form-wrap">
            <h1 class="section-title">ثبت آگهی فروش امتیاز وام</h1>
            <p class="section-sub mb-4">اطلاعات وام خود را با دقت وارد کنید. آگهی شما پس از تایید اپراتور منتشر خواهد شد.</p>

            <form id="adForm" action="{{ url('/ads') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf

                {{-- ===== اطلاعات وام ===== --}}
                <section class="f-card reveal">
                    <header class="f-card-head"><span class="f-ic"><svg viewBox="0 0 24 24"><path d="M4 21V5a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v16M15 9h4a1 1 0 0 1 1 1v11M2 21h20M8 8h3M8 12h3M8 16h3"/></svg></span><h2>اطلاعات وام</h2></header>
                    <div class="f-grid">
                        <div class="f-field" data-field="bank">
                            <label for="fBank">بانک یا صندوق <span class="req">*</span></label>
                            <select id="fBank" name="bank" class="f-input f-select" required>
                                <option value="" selected disabled>انتخاب کنید...</option>
                                @foreach ($banks as $b)<option value="{{ $b }}">{{ $b }}</option>@endforeach
                            </select>
                            <p class="f-err">بانک یا صندوق را انتخاب کنید.</p>
                        </div>
                        <div class="f-field" data-field="amount">
                            <label for="fAmount">مبلغ وام (تومان) <span class="req">*</span></label>
                            <input type="text" id="fAmount" name="amount" class="f-input" data-money inputmode="numeric" autocomplete="off" placeholder="مثلا: ۳۰۰,۰۰۰,۰۰۰">
                            <p class="f-err">مبلغ وام را وارد کنید.</p>
                        </div>
                        <div class="f-field" data-field="months">
                            <label for="fMonths">تعداد اقساط (ماه) <span class="req">*</span></label>
                            <input type="text" id="fMonths" name="months" class="f-input" data-int inputmode="numeric" autocomplete="off" placeholder="مثلا: ۶۰">
                            <p class="f-err">تعداد اقساط را بین ۱ تا ۱۲۰ ماه وارد کنید.</p>
                        </div>
                        <div class="f-field" data-field="rate">
                            <label for="fRate">نرخ سود بانکی (درصد) <span class="req">*</span></label>
                            <input type="text" id="fRate" name="rate" class="f-input" data-decimal inputmode="decimal" autocomplete="off" placeholder="مثلا: ۴">
                            <p class="f-err">نرخ سود را بین ۰ تا ۱۰۰ وارد کنید.</p>
                        </div>
                    </div>
                </section>

                {{-- ===== قیمت‌گذاری ===== --}}
                <section class="f-card reveal">
                    <header class="f-card-head"><span class="f-ic"><svg viewBox="0 0 24 24"><path d="M3 12V4a1 1 0 0 1 1-1h8l9 9-9 9z"/><circle cx="8" cy="8" r="1.5"/></svg></span><h2>قیمت گذاری</h2></header>
                    <div class="f-grid">
                        <div class="f-field" data-field="price">
                            <label for="fPrice">قیمت پیشنهادی فروش (تومان) <span class="req">*</span></label>
                            <input type="text" id="fPrice" name="price" class="f-input" data-money inputmode="numeric" autocomplete="off" placeholder="مثلا: ۳۰۰,۰۰۰,۰۰۰">
                            <p class="f-err">قیمت پیشنهادی فروش را وارد کنید.</p>
                            <div class="reco-box" id="recoBox" aria-live="polite">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 18h6M10 21h4M12 3a6 6 0 0 0-3.5 10.9c.6.5 1 1.2 1 2.1h5c0-.9.4-1.6 1-2.1A6 6 0 0 0 12 3z"/></svg>
                                <span>توصیه سیستم: <b id="recoText">** تا ** میلیون تومان</b></span>
                            </div>
                        </div>
                        <div class="f-field" data-field="sheba">
                            <label for="fSheba">شماره شبا <span class="req">*</span></label>
                            <input type="text" id="fSheba" name="sheba" class="f-input ltr" dir="ltr" inputmode="numeric" maxlength="26" autocomplete="off" placeholder="IR------------------------">
                            <p class="f-err">شماره شبا باید ۲۴ رقم (بعد از IR) باشد.</p>
                            <p class="f-hint">برای واریز وجه پس از تکمیل معامله</p>
                        </div>
                    </div>
                </section>

                {{-- ===== توضیحات ===== --}}
                <section class="f-card reveal">
                    <header class="f-card-head"><span class="f-ic"><svg viewBox="0 0 24 24"><path d="M4 20h4L19 9a2.1 2.1 0 0 0-4-4L4 16zM14 6l4 4M4 6h6M4 10h3"/></svg></span><h2>توضیحات تکمیلی</h2></header>
                    <div class="f-field" data-field="note">
                        <label for="fNote">شرایط ضامن و توضیحات <span class="req">*</span></label>
                        <textarea id="fNote" name="note" class="f-input" rows="4" placeholder="توضیحات مربوط به شرایط ضامن، شهر و نحوه انتقال را بنویسید..."></textarea>
                        <p class="f-err">توضیحات را بنویسید (حداقل ۱۰ کاراکتر).</p>
                    </div>
                </section>

                {{-- ===== آپلود مدارک ===== --}}
                <section class="f-card reveal">
                    <header class="f-card-head"><span class="f-ic"><svg viewBox="0 0 24 24"><path d="M12 15V4M8 8l4-4 4 4M5 14v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4"/></svg></span><h2>آپلود مدارک</h2></header>
                    <div class="f-grid">
                        <div class="f-field" data-field="doc_balance">
                            <label class="dz" for="fDocBalance" tabindex="0">
                                <input type="file" id="fDocBalance" name="doc_balance" accept="image/png,image/jpeg" hidden>
                                <span class="dz-ic"><svg viewBox="0 0 24 24"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h4"/></svg></span>
                                <img class="dz-thumb" alt="" hidden>
                                <b class="dz-title">تصویر موجودی/امتیاز وام</b>
                                <small class="dz-sub">JPG, PNG (حداکثر ۵MB)</small>
                                <button type="button" class="dz-remove" aria-label="حذف فایل" hidden>×</button>
                            </label>
                            <p class="f-err">تصویر موجودی/امتیاز وام را بارگذاری کنید.</p>
                        </div>
                        <div class="f-field" data-field="doc_id">
                            <label class="dz" for="fDocId" tabindex="0">
                                <input type="file" id="fDocId" name="doc_id" accept="image/png,image/jpeg" hidden>
                                <span class="dz-ic"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M6 16c.6-1.8 5.4-1.8 6 0M14 10h4M14 14h3"/></svg></span>
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
                <div class="agree-box f-field reveal" data-field="agree">
                    <label class="chk">
                        <input type="checkbox" name="agree" id="fAgree" value="1">
                        <span class="box"></span>
                        <span class="chk-text">من <a href="{{ url('/rules') }}" target="_blank">قوانین سیستم معامله امن</a> و شرایط انتقال وجه پلتفرم "وام اینجا" را مطالعه کرده و می‌پذیرم. می‌دانم که اطلاعات دستگاه و آی‌پی من جهت جلوگیری از کلاهبرداری ثبت می‌شود.</span>
                    </label>
                    <p class="f-err">برای ادامه باید قوانین را بپذیرید.</p>
                </div>

                <button type="submit" class="btn btn-brand btn-lg w-100 mt-3" id="adSubmit"><span class="btn-label">ثبت و ارسال برای تایید</span><span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
            </form>
        </div>
    </div>

    {{-- مودال موفقیت --}}
    <div class="modal fade app-modal" id="adSuccessModal" tabindex="-1" aria-labelledby="adSuccessTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sheet">
            <div class="modal-content">
                <div class="sheet-handle d-sm-none" aria-hidden="true"></div>
                <div class="modal-body text-center">
                    <div class="m-illus round ok" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></div>
                    <h2 class="m-title" id="adSuccessTitle">آگهی شما ثبت شد</h2>
                    <p class="m-text">آگهی شما پس از بررسی و تایید اپراتور منتشر می‌شود. وضعیت آن را از «حساب کاربری &gt; آگهی‌های من» پیگیری کنید.</p>
                    <div class="m-actions">
                        <a href="{{ url('/userPanel/ads') }}" class="btn btn-brand btn-lg flex-grow-1">آگهی‌های من</a>
                        <button type="button" class="btn btn-ghost btn-lg" data-bs-dismiss="modal">بستن</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
