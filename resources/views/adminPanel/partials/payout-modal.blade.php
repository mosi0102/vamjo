{{-- مودال ثبت واریز به فروشنده | داده‌ها از data-* دکمه پر می‌شود --}}
<div class="modal fade app-modal" id="payoutModal" tabindex="-1" aria-labelledby="poTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sheet">
        <div class="modal-content">
            <div class="sheet-handle d-sm-none" aria-hidden="true"></div>
            <button type="button" class="btn-close m-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            <div class="modal-body">
                <h2 class="m-title text-center" id="poTitle">ثبت واریز به فروشنده</h2>
                <div class="rc-rows">
                    <div class="rc-row"><span>فروشنده</span><b id="poSeller"></b></div>
                    <div class="rc-row"><span>وام</span><b id="poLoan"></b></div>
                    <div class="rc-row"><span>شماره شبا</span><b id="poSheba" class="rc-copy" dir="ltr" tabindex="0" role="button" title="کپی"></b></div>
                    <div class="rc-row"><span>مبلغ پرداختی خریدار</span><b id="poGross"></b></div>
                    <div class="rc-row"><span>کمیسیون سایت</span><b id="poFee"></b></div>
                    <div class="rc-row po-net"><span>مبلغ قابل واریز (تومان)</span><b id="poNet"></b></div>
                </div>
                <form method="POST" action="#" id="poForm" novalidate>
                    @csrf
                    <div class="f-field" data-field="ref"><label for="poRef">کد پیگیری واریز <span class="req">*</span></label><input type="text" id="poRef" name="reference" class="f-input ltr" dir="ltr" inputmode="numeric" maxlength="24" placeholder="مثال: 123456789012" autocomplete="off"><p class="f-err">کد پیگیری را وارد کنید (حداقل ۶ رقم).</p></div>
                    <div class="m-actions">
                        <button type="submit" class="btn btn-brand btn-lg flex-grow-1"><span class="btn-label">ثبت واریز</span><span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
                        <button type="button" class="btn btn-ghost btn-lg" data-bs-dismiss="modal">انصراف</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>