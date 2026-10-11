{{-- مودال رسید پرداخت | داده‌ها از data-* دکمه پر می‌شود (panel.js) --}}
<div class="modal fade app-modal" id="receiptModal" tabindex="-1" aria-labelledby="rcTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sheet">
        <div class="modal-content">
            <div class="sheet-handle d-sm-none" aria-hidden="true"></div>
            <button type="button" class="btn-close m-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            <div class="modal-body" id="rcPrintArea">
                <div class="rc-top text-center">
                    <div class="m-illus round ok" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></div>
                    <h2 class="m-title mb-1" id="rcTitle">رسید پرداخت</h2>
                    <span class="pn-pill" id="rcStatus"></span>
                    <div class="rc-amount"><b id="rcAmount"></b> <small>تومان</small></div>
                </div>
                <div class="rc-rows">
                    <div class="rc-row"><span>بابت</span><b id="rcLoan"></b></div>
                    <div class="rc-row"><span>شناسه وام</span><b id="rcLoanId"></b></div>
                    <div class="rc-row"><span>زمان پرداخت</span><b id="rcDate"></b></div>
                    <div class="rc-row"><span>شماره تراکنش</span><b id="rcTx" class="rc-copy" tabindex="0" role="button" title="کپی"></b></div>
                    <div class="rc-row"><span>درگاه پرداخت</span><b id="rcGateway"></b></div>
                </div>
                <div class="m-actions">
                    <button type="button" class="btn btn-brand btn-lg flex-grow-1" id="rcPrint">@include('userPanel.partials.icon', ['name' => 'doc']) چاپ رسید</button>
                    <button type="button" class="btn btn-ghost btn-lg" data-bs-dismiss="modal">بستن</button>
                </div>
            </div>
        </div>
    </div>
</div>
