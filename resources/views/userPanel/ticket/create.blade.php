@extends('userPanel.layouts.layout')

@section('title', 'تیکت جدید')
@section('page_title', 'تیکت جدید')
@section('body_class', 'pn-tickets')

@php
    $depts = $depts ?? ['پرداخت ها', 'آگهی ها', 'درخواست ها', 'احراز هویت', 'حساب کاربری', 'مشکل فنی', 'سایر'];
@endphp

@section('main')

    <a href="{{ route('user.tickets.index') }}" class="of-back pn-reveal">@include('userPanel.partials.icon', ['name' => 'arrow']) بازگشت به تیکت ها</a>

    <div class="pn-head pn-reveal">
        <span class="pn-head-ic">@include('userPanel.partials.icon', ['name' => 'chat'])</span>
        <div>
            <h2>تیکت جدید</h2>
            <p>پیام ها و تیکت های خود را به پشتیبانی وامجو ارسال نمایید و با پشتیبانی گفت و گو داشته باشید.</p>
        </div>
    </div>

    <section class="pn-panel pn-reveal" style="--d:.08s">
        <form id="tkForm" class="tk-form dz-scope" action="{{ route('user.tickets.store') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf

            <div class="tk-sla">@include('userPanel.partials.icon', ['name' => 'clock']) پشتیبانی معمولاً ظرف <b>۲۴ ساعت</b> پاسخ می‌دهد. برای موارد فوری اولویت «فوری» را انتخاب کنید.</div>

            <div class="st-grid">
                <div class="f-field" data-field="priority">
                    <label for="tkPriority">اولویت تیکت</label>
                    <select id="tkPriority" name="priority" class="f-input f-select">
                        <option value="normal">مهم نیست</option><option value="low">کم</option><option value="high">زیاد</option><option value="urgent">فوری</option>
                    </select>
                </div>
                <div class="f-field" data-field="dept">
                    <label for="tkDept">بخش مرتبط</label>
                    <select id="tkDept" name="department" class="f-input f-select">
                        @foreach ($depts as $d)<option value="{{ $d }}">{{ $d }}</option>@endforeach
                    </select>
                </div>
            </div>

            <div class="f-field mt-3" data-field="subject">
                <label for="tkSubject">موضوع تیکت <span class="req">*</span></label>
                <input type="text" id="tkSubject" name="subject" class="f-input" maxlength="80" placeholder="موضوع تیکت را وارد نمایید" autocomplete="off">
                <p class="f-err">موضوع تیکت را وارد کنید (حداقل ۴ کاراکتر).</p>
            </div>

            <div class="f-field mt-3" data-field="message">
                <label for="tkMessage">توضیحات <span class="req">*</span></label>
                <textarea id="tkMessage" name="message" class="f-input" rows="5" maxlength="1500" placeholder="توضیحات خود را بنویسید..."></textarea>
                <div class="ed-count"><span id="tkCount">۰</span> / ۱۵۰۰</div>
                <p class="f-err">توضیحات را بنویسید (حداقل ۱۰ کاراکتر).</p>
            </div>

            <div class="f-field mt-3" data-field="attachment">
                <label class="dz" for="tkFile" tabindex="0">
                    <input type="file" id="tkFile" name="attachment" accept="image/png,image/jpeg" hidden>
                    <span class="dz-ic">@include('userPanel.partials.icon', ['name' => 'doc'])</span>
                    <img class="dz-thumb" alt="" hidden>
                    <b class="dz-title">تصویر فایل ضمیمه (اختیاری)</b>
                    <small class="dz-sub">JPG, PNG (حداکثر ۵MB)</small>
                    <button type="button" class="dz-remove" aria-label="حذف فایل" hidden>×</button>
                </label>
                <p class="f-err">فایل انتخاب‌شده معتبر نیست.</p>
            </div>

            <button type="submit" class="btn btn-brand btn-lg w-100 mt-3" id="tkSubmit"><span class="btn-label">ثبت و ارسال تیکت</span><span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
        </form>
    </section>
@endsection
