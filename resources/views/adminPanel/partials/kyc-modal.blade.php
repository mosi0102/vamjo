{{-- مودال بررسی مدارک احراز هویت | داده‌ها از data-* ردیف پر می‌شود (panel.js) --}}
<div class="modal fade app-modal pay-modal kyc-modal" id="kycModal" tabindex="-1" aria-labelledby="kycTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sheet">
        <div class="modal-content">
            <div class="sheet-handle d-sm-none" aria-hidden="true"></div>
            <button type="button" class="btn-close m-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            <div class="modal-body">
                <h2 class="m-title text-center" id="kycTitle">بررسی مدارک هویتی</h2>
                <p class="text-center text-muted small mb-3"><b id="kycName"></b> · <span id="kycSent"></span></p>

                <p class="kyc-nodocs text-center" id="kycNoDocs" hidden>
                    @include('userPanel.partials.icon', ['name' => 'alert'])
                    برای این درخواست مدرکی بارگذاری نشده است.
                </p>

                <div class="at-docs kyc-docs">
                    <figure class="at-doc"><img id="kycSelfie" alt="تصویر سلفی با کارت ملی" class="zoomable"><figcaption>سلفی با کارت ملی <small>(برای بزرگنمایی کلیک کنید)</small></figcaption></figure>
                    <figure class="at-doc"><img id="kycCard" alt="تصویر کارت ملی" class="zoomable"><figcaption>کارت ملی</figcaption></figure>
                </div>

                <dl class="vf-sum kyc-data">
                    <div><dt>کد ملی</dt><dd dir="ltr" id="kycNid"></dd></div>
                    <div><dt>تاریخ تولد</dt><dd id="kycBirth"></dd></div>
                    <div><dt>شماره همراه</dt><dd dir="ltr" id="kycMobile"></dd></div>
                    <div><dt>شبا / بانک</dt><dd><span dir="ltr" id="kycSheba"></span> <span class="vf-bank" id="kycBank"></span></dd></div>
                </dl>

                {{-- نتیجه بررسی (برای درخواست‌های بررسی‌شده) --}}
                <dl class="vf-sum kyc-result" id="kycReviewed" hidden>
                    <div><dt>وضعیت</dt><dd><span class="pn-pill" id="kycStatusPill"></span></dd></div>
                    <div><dt>بررسی‌کننده</dt><dd id="kycReviewer"></dd></div>
                    <div><dt>زمان بررسی</dt><dd id="kycReviewedAt"></dd></div>
                    <div id="kycReasonWrap" hidden><dt>دلیل رد</dt><dd id="kycReasonShown"></dd></div>
                </dl>

                <form method="POST" action="#" id="kycForm">
                    @csrf
                    <div id="kycChecks" class="kyc-checks">
                        <label class="chk"><input type="checkbox"><span class="box"></span><span class="chk-text">مشخصات واردشده با کارت ملی مطابقت دارد.</span></label>
                        <label class="chk"><input type="checkbox"><span class="box"></span><span class="chk-text">چهره سلفی با عکس کارت ملی یکی است و کارت خوانا است.</span></label>
                        <label class="chk"><input type="checkbox"><span class="box"></span><span class="chk-text">شماره شبا متعلق به همین فرد است.</span></label>
                    </div>

                    <div class="kyc-reject d-none" id="kycRejectBox">
                        <div class="f-field"><label for="kycReason">دلیل رد (برای کاربر نمایش داده می‌شود) <span class="req">*</span></label>
                            <select id="kycReasonSel" class="f-input f-select"><option value="">انتخاب دلیل...</option><option>تصویر سلفی یا کارت ملی خوانا نیست</option><option>مشخصات با کارت ملی مطابقت ندارد</option><option>شماره شبا به نام کاربر نیست</option><option>سایر</option></select>
                            <textarea id="kycReason" name="reason" class="f-input mt-2" rows="2" maxlength="200" placeholder="توضیح بیشتر (اختیاری)"></textarea>
                            <p class="f-err">دلیل رد را انتخاب کنید.</p></div>
                    </div>

                    <div class="m-actions" id="kycActions">
                        <button type="submit" class="btn btn-brand btn-lg flex-grow-1" id="kycApprove" disabled><span class="btn-label">تایید هویت</span><span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
                        <button type="button" class="btn btn-outline-danger-soft btn-lg" id="kycRejectBtn">رد درخواست</button>
                    </div>
                    <div class="m-actions d-none" id="kycRejectActions">
                        <button type="submit" class="btn btn-danger-solid btn-lg flex-grow-1" id="kycRejectGo"><span class="btn-label">ثبت رد و اطلاع به کاربر</span><span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
                        <button type="button" class="btn btn-ghost btn-lg" id="kycBack">بازگشت</button>
                    </div>
                    <a href="#" class="kyc-profile-link" id="kycProfile">مشاهده پروفایل کامل کاربر</a>
                </form>
            </div>
        </div>
    </div>
</div>
