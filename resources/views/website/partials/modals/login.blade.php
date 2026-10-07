{{-- مودال ورود | ثبت نام با کد یکبار مصرف (OTP) – در layout برای همه صفحات include می‌شود --}}
<div class="modal fade app-modal" id="loginModal" tabindex="-1" aria-labelledby="loginModalTitle" aria-hidden="true"
     data-send-url="{{ url('/auth/otp/send') }}" data-verify-url="{{ url('/auth/otp/verify') }}">
    <div class="modal-dialog modal-dialog-centered modal-sheet">
        <div class="modal-content">
            <div class="sheet-handle d-sm-none" aria-hidden="true"></div>
            <button type="button" class="btn-close m-close" data-bs-dismiss="modal" aria-label="بستن"></button>

            <div class="modal-body text-center">

                {{-- مرحله ۱: شماره موبایل --}}
                <div class="l-step" data-step="phone">
                    <div class="m-illus round" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="6" y="2.5" width="12" height="19" rx="3"/><path d="M10.5 18.5h3"/></svg>
                    </div>
                    <h2 class="m-title" id="loginModalTitle">ورود | ثبت نام</h2>
                    <p class="m-text">شماره موبایل خود را وارد کنید تا کد تایید برایتان ارسال شود.</p>

                    <div class="offer-field text-start">
                        <label for="phoneInput" class="offer-label mb-2 d-block">شماره موبایل</label>
                        <div class="offer-input" dir="ltr">
                            <input type="tel" id="phoneInput" inputmode="numeric" maxlength="11" autocomplete="tel" placeholder="09123456789">
                        </div>
                        <p class="offer-hint d-none" id="phoneErr" role="alert">شماره موبایل معتبر نیست؛ مثال: ۰۹۱۲۳۴۵۶۷۸۹</p>
                    </div>

                    <button type="button" class="btn btn-brand btn-lg w-100 mt-3" id="sendOtp"><span class="btn-label">دریافت کد تایید</span><span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
                    <p class="m-note">ورود شما به معنی پذیرش <a href="{{ url('/rules') }}">قوانین و مقررات</a> وام‌جو است.</p>
                </div>

                {{-- مرحله ۲: کد تایید --}}
                <div class="l-step d-none" data-step="otp">
                    <div class="m-illus round" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M12 3l8 3v6c0 4.5-3.3 8.3-8 9-4.7-.7-8-4.5-8-9V6z"/><path d="M9 12l2 2 4-4"/></svg>
                    </div>
                    <h2 class="m-title">کد تایید را وارد کنید</h2>
                    <p class="m-text">کد ۵ رقمی ارسال‌شده به شماره <b dir="ltr" id="otpPhone"></b> را وارد کنید.</p>

                    <div class="otp-boxes" dir="ltr" id="otpBoxes">
                        @for ($i = 0; $i < 5; $i++)
                            <input type="text" inputmode="numeric" maxlength="1" autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}" aria-label="رقم {{ $i + 1 }}">
                        @endfor
                    </div>
                    <p class="offer-hint d-none text-center" id="otpErr" role="alert">کد وارد شده صحیح نیست.</p>

                    <button type="button" class="btn btn-brand btn-lg w-100 mt-3" id="verifyOtp"><span class="btn-label">تایید و ورود</span><span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
                    <div class="otp-foot">
                        <span id="otpTimer">ارسال مجدد کد تا <b>۰۲:۰۰</b></span>
                        <button type="button" class="link-btn d-none" id="resendOtp">ارسال مجدد کد</button>
                        <button type="button" class="link-btn" id="editPhone">ویرایش شماره</button>
                    </div>
                </div>

                {{-- مرحله ۳: موفقیت --}}
                <div class="l-step d-none" data-step="done">
                    <div class="m-illus round ok" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                    </div>
                    <h2 class="m-title">ورود با موفقیت انجام شد</h2>
                    <p class="m-text mb-0">لحظه‌ای صبر کنید...</p>
                </div>

            </div>
        </div>
    </div>
</div>
