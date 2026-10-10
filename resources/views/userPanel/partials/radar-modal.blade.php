{{-- مودال رادار جدید / ویرایش رادار | ورودی: $banks --}}
<div class="modal fade app-modal pay-modal" id="radarModal" tabindex="-1" aria-labelledby="rdTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sheet">
        <div class="modal-content">
            <div class="sheet-handle d-sm-none" aria-hidden="true"></div>
            <button type="button" class="btn-close m-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            <div class="modal-body">

                <div class="m-illus big text-center" aria-hidden="true">
                    <svg viewBox="0 0 120 100">
                        <ellipse cx="60" cy="94" rx="40" ry="5" fill="#1e5a45" opacity=".1"/>
                        <rect x="14" y="8" width="88" height="82" rx="16" fill="#1e5a45"/>
                        <circle cx="52" cy="48" r="30" fill="#e3efe8"/><circle cx="52" cy="48" r="21" fill="none" stroke="#2a7a5f" stroke-width="2"/><circle cx="52" cy="48" r="11" fill="none" stroke="#2a7a5f" stroke-width="2"/>
                        <path d="M52 48L72 30" stroke="#1e5a45" stroke-width="3" stroke-linecap="round"/><circle cx="52" cy="48" r="3" fill="#1e5a45"/><circle cx="64" cy="58" r="3" fill="#d9a93f"/><circle cx="40" cy="36" r="2.5" fill="#2e9a4f"/>
                        <rect x="76" y="20" width="22" height="30" rx="3" fill="#f6f3ec"/><path d="M80 28h14M80 34h14M80 40h9" stroke="#9fb8ab" stroke-width="2.4" stroke-linecap="round"/>
                        <circle cx="30" cy="68" r="12" fill="none" stroke="#f6f3ec" stroke-width="5"/><path d="M22 76l-9 9" stroke="#f6f3ec" stroke-width="6" stroke-linecap="round"/>
                    </svg>
                </div>

                <h2 class="m-title text-center" id="rdTitle">رادار جدید</h2>

                <form method="POST" action="{{ url('/panel/radar') }}" id="rdForm" novalidate data-create-url="{{ url('/panel/radar') }}" data-update-url="{{ url('/panel/radar') }}">
                    @csrf
                    <input type="hidden" name="_method" id="rdMethod" value="POST">

                    <div class="f-field" data-field="name">
                        <label for="rdNameInput">نام رادار (برای شناسایی راحت تر) <span class="req">*</span></label>
                        <input type="text" id="rdNameInput" name="name" class="f-input" maxlength="40" placeholder="مثال : رادار رسالت" autocomplete="off">
                        <p class="f-err">نام رادار را وارد کنید.</p>
                    </div>

                    <div class="st-grid">
                        <div class="f-field" data-field="min">
                            <label for="rdMin">حداقل مبلغ (تومان)</label>
                            <input type="text" id="rdMin" name="min_amount" class="f-input rd-money" inputmode="numeric" autocomplete="off" placeholder="مثال: ۳۰۰,۰۰۰,۰۰۰">
                            <p class="f-err">حداقل مبلغ نباید از حداکثر بیشتر باشد.</p>
                        </div>
                        <div class="f-field" data-field="max">
                            <label for="rdMax">حداکثر مبلغ (تومان)</label>
                            <input type="text" id="rdMax" name="max_amount" class="f-input rd-money" inputmode="numeric" autocomplete="off" placeholder="مثال: ۳۰۰,۰۰۰,۰۰۰">
                            <p class="f-err">حداکثر مبلغ نباید از حداقل کمتر باشد.</p>
                        </div>
                        <div class="f-field" data-field="months">
                            <label for="rdMonths">حداقل اقساط (ماه)</label>
                            <input type="text" id="rdMonths" name="min_months" class="f-input rd-int" inputmode="numeric" autocomplete="off" placeholder="مثال : ۶۰">
                            <p class="f-err">تعداد اقساط را بین ۱ تا ۱۲۰ وارد کنید.</p>
                        </div>
                        <div class="f-field">
                            <label for="rdRate">حداکثر نرخ سود</label>
                            <select id="rdRate" name="max_rate" class="f-input f-select">
                                <option value="">مهم نیست</option>
                                @foreach ([10, 15, 20, 23, 25, 30] as $p)<option value="{{ $p }}">تا {{ strtr((string) $p, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']) }} درصد</option>@endforeach
                            </select>
                        </div>
                        <div class="f-field">
                            <label for="rdBank">بانک یا صندوق</label>
                            <select id="rdBank" name="bank" class="f-input f-select">
                                <option value="">همه بانک ها</option>
                                @foreach ($banks as $b)<option value="{{ $b }}">{{ $b }}</option>@endforeach
                            </select>
                        </div>
                        <div class="f-field">
                            <label for="rdCity">شهر</label>
                            <input type="text" id="rdCity" name="city" class="f-input" list="rdCities" autocomplete="off" placeholder="مثال : سبزوار">
                            <datalist id="rdCities"><option value="سبزوار"><option value="مشهد"><option value="تهران"><option value="نیشابور"><option value="اصفهان"><option value="شیراز"></datalist>
                        </div>
                    </div>

                    {{-- خلاصه شرایط (زنده) --}}
                    <div class="rd-summary" id="rdSummary" aria-live="polite"><small>شرایط رادار شما:</small><div class="rd-tags" id="rdSummaryTags"></div></div>
                    <p class="f-err rd-form-err" id="rdFormErr" role="alert">حداقل یکی از شرایط (مبلغ، اقساط، سود، بانک یا شهر) را وارد کنید.</p>

                    {{-- روش اطلاع‌رسانی --}}
                    <div class="rd-notify">
                        <label class="chk"><input type="checkbox" name="notify_panel" id="rdNotifyPanel" value="1" checked><span class="box"></span><span class="chk-text">اعلان در پنل</span></label>
                        <label class="chk"><input type="checkbox" name="notify_sms" id="rdNotifySms" value="1" checked><span class="box"></span><span class="chk-text">اطلاع از طریق پیامک</span></label>
                    </div>

                    <div class="m-actions">
                        <button type="submit" class="btn btn-brand btn-lg flex-grow-1"><span class="btn-label" id="rdSubmitLabel">ثبت رادار جدید</span><span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
                        <button type="button" class="btn btn-outline-danger-soft btn-lg" data-bs-dismiss="modal">لغو و بستن</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
