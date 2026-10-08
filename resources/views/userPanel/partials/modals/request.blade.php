{{-- مودال ثبت درخواست وام | ورودی: $ad و $fa --}}
<div class="modal fade app-modal" id="requestModal" tabindex="-1" aria-labelledby="requestModalTitle" aria-hidden="true"
     data-min="{{ $ad['price'] }}" data-max="{{ $ad['max_offer'] }}"
     data-url="{{ url('/ads/' . $ad['id'] . '/request') }}" data-pay="{{ url('/payment/' . $ad['id']) }}">
    <div class="modal-dialog modal-dialog-centered modal-sheet">
        <div class="modal-content">
            <div class="sheet-handle d-sm-none" aria-hidden="true"></div>
            <div class="modal-body text-center">
                <div class="m-illus" aria-hidden="true">
                    <svg viewBox="0 0 64 64"><rect x="12" y="8" width="34" height="46" rx="5" fill="#e4efe9" stroke="#1e5a45" stroke-width="2.5"/><rect x="22" y="4" width="14" height="9" rx="3" fill="#1e5a45"/><rect x="18" y="20" width="10" height="10" rx="2" fill="#1e5a45"/><path d="M32 22h10M32 28h10M18 38h24M18 44h16" stroke="#1e5a45" stroke-width="2.4" stroke-linecap="round"/><circle cx="47" cy="46" r="9" fill="#2a7a5f"/><path d="M43 46l3 3 5-6" stroke="#fff" stroke-width="2.4" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h2 class="m-title" id="requestModalTitle">ثبت درخواست وام</h2>
                <p class="m-text">کاربر عزیز ثبت درخواست شما به منزله قبول شدن درخواست وام شما نمیباشد و تایید درخواست شما به دست شخص فروشنده میباشد. در صورت تایید درخواست شما پیامکی از طریق سامانه ارسال میشود و یا میتوانید از طرق <b>حساب کاربری &gt; درخواست های من</b> وضعیت درخواست هایی که داده اید را پیگیری نمایید.</p>

                <div class="offer-field text-start">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label for="offerInput" class="offer-label">مبلغ پیشنهادی + مبلغ فروش</label>
                        <span class="offer-range" dir="ltr">{{ number_format($ad['price']) }} تا {{ number_format($ad['max_offer']) }}</span>
                    </div>
                    <div class="offer-input" dir="rtl">
                        <input type="text" id="offerInput" inputmode="numeric" autocomplete="off" placeholder="مثلا: ۳۰۰,۰۰۰,۰۰۰">
                        <span class="addon">تومان</span>
                    </div>
                    <p class="offer-hint" id="offerHint">در صورت خالی بودن این بخش مبلغ پایه برای پیشنهاد شما ثبت میشود.</p>
                    <p class="offer-hint d-none" id="offerErr" role="alert">مبلغ پیشنهادی باید بین {{ number_format($ad['price']) }} تا {{ number_format($ad['max_offer']) }} تومان باشد.</p>
                </div>

                <div class="m-actions">
                    <button type="button" class="btn btn-brand btn-lg flex-grow-1" id="requestSubmit"><span class="btn-label">ثبت درخواست</span><span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
                    <button type="button" class="btn btn-outline-danger-soft btn-lg" data-bs-dismiss="modal">لغو و بستن</button>
                </div>
            </div>
        </div>
    </div>
</div>
