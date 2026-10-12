{{-- مودال بررسی آگهی: تایید و انتشار / رد / ویژه | داده‌ها از data-* ردیف پر می‌شود --}}
<div class="modal fade app-modal pay-modal kyc-modal" id="adReviewModal" tabindex="-1" aria-labelledby="arTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sheet">
        <div class="modal-content">
            <div class="sheet-handle d-sm-none" aria-hidden="true"></div>
            <button type="button" class="btn-close m-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            <div class="modal-body">
                <h2 class="m-title text-center mb-1" id="arTitle"></h2>
                <p class="text-center text-muted small mb-3">شناسه <b id="arId"></b> · فروشنده: <b id="arSeller"></b> · <span id="arCity"></span></p>

                <div class="ar-price-check" id="arCheck"></div>

                <dl class="vf-sum kyc-data">
                    <div><dt>بانک</dt><dd id="arBank"></dd></div>
                    <div><dt>مبلغ وام (تومان)</dt><dd id="arAmount"></dd></div>
                    <div><dt>اقساط / نرخ سود</dt><dd><span id="arMonths"></span> ماه · <span id="arRate"></span>٪</dd></div>
                    <div><dt>قیمت پیشنهادی فروش (تومان)</dt><dd id="arPrice"></dd></div>
                    <div class="span2"><dt>شماره شبا فروشنده</dt><dd dir="ltr" id="arSheba"></dd></div>
                    <div class="span2"><dt>توضیحات فروشنده</dt><dd class="fw-normal" id="arNote"></dd></div>
                </dl>

                <div class="at-docs kyc-docs">
                    <figure class="at-doc"><img id="arDoc1" alt="تصویر موجودی وام" class="zoomable"><figcaption>تصویر موجودی/امتیاز وام</figcaption></figure>
                    <figure class="at-doc"><img id="arDoc2" alt="تصویر کارت ملی" class="zoomable"><figcaption>کارت ملی فروشنده</figcaption></figure>
                </div>

                <form method="POST" action="#" id="arForm">
                    @csrf
                    <div class="ar-feature" id="arFeatureBox">
                        <label class="chk"><input type="checkbox" name="featured" id="arFeatured" value="1"><span class="box"></span><span class="chk-text"><b>انتشار به‌عنوان آگهی ویژه</b> <small>(در بالای لیست و صفحه نخست نمایش داده می‌شود)</small></span></label>
                        <select name="featured_days" id="arDays" class="f-input f-select d-none"><option value="7">۷ روز</option><option value="14">۱۴ روز</option><option value="30">۳۰ روز</option></select>
                    </div>

                    <div class="kyc-reject d-none" id="arRejectBox">
                        <div class="f-field"><label for="arReason">دلیل رد آگهی (برای کاربر نمایش داده می‌شود) <span class="req">*</span></label>
                            <select id="arReasonSel" class="f-input f-select"><option value="">انتخاب دلیل...</option><option>تصویر مدرک خوانا نیست</option><option>قیمت پیشنهادی نامتعارف است</option><option>اطلاعات وام مطابقت ندارد</option><option>سایر</option></select>
                            <textarea id="arReason" name="reason" class="f-input mt-2" rows="2" maxlength="200" placeholder="توضیح بیشتر (اختیاری)"></textarea>
                            <p class="f-err">دلیل رد را انتخاب کنید.</p></div>
                    </div>

                    <div class="m-actions" id="arActions">
                        <button type="submit" class="btn btn-brand btn-lg flex-grow-1" id="arApprove"><span class="btn-label">تایید و انتشار</span><span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
                        <button type="button" class="btn btn-outline-danger-soft btn-lg" id="arRejectBtn">رد آگهی</button>
                    </div>
                    <div class="m-actions d-none" id="arRejectActions">
                        <button type="submit" class="btn btn-danger-solid btn-lg flex-grow-1" id="arRejectGo"><span class="btn-label">ثبت رد و اطلاع به کاربر</span><span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
                        <button type="button" class="btn btn-ghost btn-lg" id="arBack">بازگشت</button>
                    </div>
                    <p class="kyc-done" id="arReadonly" hidden>این آگهی در وضعیت «<span id="arStatusTxt"></span>» است و امکان تایید یا رد ندارد.</p>
                </form>
            </div>
        </div>
    </div>
</div>N