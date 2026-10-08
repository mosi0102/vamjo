{{-- مودال ورود | ثبت نام با کد یکبار مصرف (OTP) – در layout برای همه صفحات include می‌شود --}}
<div class="modal fade app-modal" id="loginModal" tabindex="-1" aria-labelledby="loginModalTitle" aria-hidden="true"
     data-send-url="{{ url('/auth/otp/send') }}" data-verify-url="{{ url('/auth/otp/verify') }}" data-profile-url="{{ url('/auth/register') }}">
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

                {{-- مرحله ۳: تکمیل ثبت نام (فقط کاربر جدید) --}}
                <div class="l-step d-none" data-step="profile">
                    <div class="m-illus round" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                    </div>
                    <h2 class="m-title">تکمیل ثبت نام</h2>
                    <p class="m-text">به وام‌جو خوش آمدید! برای ساخت حساب کاربری، اطلاعات زیر را وارد کنید.</p>
                    <div class="row g-3 text-start">
                        <div class="col-6"><label for="pfFirst" class="offer-label mb-2 d-block">نام <span class="req">*</span></label><div class="offer-input" data-pf="first"><input type="text" id="pfFirst" autocomplete="given-name"></div></div>
                        <div class="col-6"><label for="pfLast" class="offer-label mb-2 d-block">نام خانوادگی <span class="req">*</span></label><div class="offer-input" data-pf="last"><input type="text" id="pfLast" autocomplete="family-name"></div></div>
                        <div class="col-12"><label for="pfNid" class="offer-label mb-2 d-block">کد ملی <span class="req">*</span></label><div class="offer-input" dir="ltr" data-pf="nid"><input type="text" id="pfNid" inputmode="numeric" maxlength="10" autocomplete="off" placeholder="۱۰ رقم"></div></div>
                    </div>
                    <p class="offer-hint d-none" id="profileErr" role="alert"></p>
                    <button type="button" class="btn btn-brand btn-lg w-100 mt-3" id="saveProfile"><span class="btn-label">ثبت نام و ورود</span><span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
                </div>

                {{-- مرحله ۴: اطلاع‌رسانی احراز هویت --}}
                <div class="l-step d-none" data-step="verify">
                    <div class="m-illus round warn" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M6 16c.6-1.8 5.4-1.8 6 0M14 10h4M14 14h3"/></svg>
                    </div>
                    <h2 class="m-title">احراز هویت لازم است</h2>
                    <p class="m-text mb-3">برای ثبت آگهی یا ثبت درخواست وام، ابتدا باید احراز هویت خود را در پنل کاربری تکمیل کنید. این مرحله شامل موارد زیر است:</p>
                    <ul class="verify-list text-start">
                        <li>تصویر سلفی همراه با کارت ملی</li>
                        <li>تصویر کارت ملی</li>
                        <li>شماره شبای حساب بانکی شما</li>
                        <li>تاریخ تولد</li>
                    </ul>
                    <div class="m-actions">
                        <a href="{{ url('/userPanel/verify') }}" class="btn btn-brand btn-lg flex-grow-1">شروع احراز هویت</a>
                        <button type="button" class="btn btn-ghost btn-lg" data-bs-dismiss="modal">بعداً</button>
                    </div>
                </div>

                {{-- مرحله ۵: موفقیت --}}
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
