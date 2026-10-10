{{-- مودال ثبت اطلاعات انتقال امتیاز (فروشنده) | داده‌ها از data-* دکمه پر می‌شود (panel.js) --}}
<div class="modal fade app-modal pay-modal" id="sellerTransferModal" tabindex="-1" aria-labelledby="stTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sheet">
        <div class="modal-content">
            <div class="sheet-handle d-sm-none" aria-hidden="true"></div>
            <button type="button" class="btn-close m-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            <div class="modal-body">

                <div class="m-illus big text-center" aria-hidden="true">
                    <svg viewBox="0 0 120 100">
                        <ellipse cx="60" cy="93" rx="46" ry="6" fill="#1e5a45" opacity=".1"/>
                        <path d="M60 14a34 34 0 0 1 30 18M60 86a34 34 0 0 1-30-18" stroke="#2a7a5f" stroke-width="5" fill="none" stroke-linecap="round"/>
                        <path d="M92 22l-2 12-12-3M28 78l2-12 12 3" stroke="#2a7a5f" stroke-width="5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="60" cy="50" r="17" fill="#2a7a5f" stroke="#e9efe6" stroke-width="3"/><path d="M60 40l3.2 6.5 7.2 1-5.2 5 1.2 7.1-6.4-3.4-6.4 3.4 1.2-7.1-5.2-5 7.2-1z" fill="#fff"/>
                        <circle cx="30" cy="30" r="12" fill="#e9efe6" stroke="#2a7a5f" stroke-width="3"/><circle cx="30" cy="27" r="4" fill="#2a7a5f"/><path d="M22 37c2-6 14-6 16 0" fill="#2a7a5f"/>
                        <circle cx="90" cy="72" r="12" fill="#e9efe6" stroke="#2a7a5f" stroke-width="3"/><circle cx="90" cy="69" r="4" fill="#2a7a5f"/><path d="M82 79c2-6 14-6 16 0" fill="#2a7a5f"/>
                    </svg>
                </div>

                <h2 class="m-title text-center" id="stTitle">انتقال امتیاز</h2>
                <p class="m-text text-center">کاربر عزیز از زمان پرداخت مبلغ توسط خریدار، شما فقط <b>۷ روز</b> زمان دارید تا انتقال امتیاز وام را انجام دهید. در صورت <b>عدم انتقال</b> در این بازه به <b class="neg">مدت ۳۰ روز امکان ثبت آگهی جدید نخواهید داشت.</b></p>

                <div class="tr-deadline" id="stDeadline" hidden>@include('userPanel.partials.icon', ['name' => 'clock'])<span>مهلت باقی‌مانده: <b id="stLeft"></b></span></div>

                <div class="pay-loan">
                    <span class="pn-bank lg" id="stBank" aria-hidden="true"></span>
                    <div><h3 id="stLoan"></h3><span class="pn-id">شناسه وام <b id="stId"></b></span></div>
                </div>

                <form method="POST" action="#" id="stForm" enctype="multipart/form-data" novalidate class="dz-scope">
                    @csrf
                    <div class="st-grid">
                        <div class="f-field" data-field="time">
                            <label for="stTime">زمان انتقال امتیاز <span class="req">*</span></label>
                            <div class="chip-wrap">
                                <input type="text" id="stTime" name="transfer_time" class="f-input" placeholder="مثال: ۱۰ مهر ۱۴۰۵ ساعت ۱۴" autocomplete="off">
                                <button type="button" class="chip-now" id="stNow">اکنون</button>
                            </div>
                            <p class="f-err">زمان انتقال را وارد کنید.</p>
                        </div>
                        <div class="f-field" data-field="track">
                            <label for="stTrack">شماره پیگیری <span class="req">*</span></label>
                            <input type="text" id="stTrack" name="tracking_no" class="f-input ltr" dir="ltr" inputmode="numeric" maxlength="20" placeholder="مثال: 12345679" autocomplete="off">
                            <p class="f-err">شماره پیگیری را به‌صورت عدد (۶ تا ۲۰ رقم) وارد کنید.</p>
                        </div>
                    </div>

                    <div class="f-field mt-3" data-field="receipt">
                        <label class="dz" for="stReceipt" tabindex="0">
                            <input type="file" id="stReceipt" name="receipt" accept="image/png,image/jpeg" hidden>
                            <span class="dz-ic">@include('userPanel.partials.icon', ['name' => 'doc'])</span>
                            <img class="dz-thumb" alt="" hidden>
                            <b class="dz-title">تصویر رسید انتقال امتیاز</b>
                            <small class="dz-sub">JPG, PNG (حداکثر ۵MB)</small>
                            <button type="button" class="dz-remove" aria-label="حذف فایل" hidden>×</button>
                        </label>
                        <p class="f-err">تصویر رسید انتقال را بارگذاری کنید.</p>
                    </div>

                    <div class="m-actions">
                        <button type="submit" class="btn btn-brand btn-lg flex-grow-1"><span class="btn-label">ثبت و ارسال مدارک</span><span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
                        <button type="button" class="btn btn-outline-danger-soft btn-lg" data-bs-dismiss="modal">لغو و بستن</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
