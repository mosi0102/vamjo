{{-- مودال پرداخت مبلغ خرید وام | داده‌ها از دکمه‌ی data-* پر می‌شود (panel.js) --}}
<div class="modal fade app-modal pay-modal" id="payModal" tabindex="-1" aria-labelledby="payModalTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sheet">
        <div class="modal-content">
            <div class="sheet-handle d-sm-none" aria-hidden="true"></div>
            <button type="button" class="btn-close m-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            <div class="modal-body">

                <div class="m-illus big text-center" aria-hidden="true">
                    <svg viewBox="0 0 120 100">
                        <ellipse cx="60" cy="92" rx="44" ry="6" fill="#1e5a45" opacity=".1"/>
                        <rect x="18" y="8" width="52" height="68" rx="7" fill="#f6f3ec" stroke="#cdd6d0"
                              stroke-width="2"/>
                        <rect x="18" y="8" width="52" height="12" rx="6" fill="#3f8f72"/>
                        <path d="M28 30h30M28 38h22" stroke="#c4ccc7" stroke-width="3" stroke-linecap="round"/>
                        <rect x="26" y="46" width="36" height="28" rx="6" fill="#1e5a45"/>
                        <path d="M36 58l8-7 8 7v8H36z" fill="#fff"/>
                        <rect x="68" y="58" width="44" height="22" rx="4" fill="#bfe0c4" stroke="#2e9a4f"
                              stroke-width="2" transform="rotate(-8 90 69)"/>
                        <rect x="64" y="64" width="44" height="22" rx="4" fill="#d8efdc" stroke="#2e9a4f"
                              stroke-width="2"/>
                        <circle cx="86" cy="75" r="6" fill="#2e9a4f"/>
                        <circle cx="30" cy="70" r="12" fill="#2a7a5f" stroke="#fff" stroke-width="3"/>
                        <path d="M25 70l4 4 7-8" stroke="#fff" stroke-width="2.4" fill="none" stroke-linecap="round"
                              stroke-linejoin="round"/>
                    </svg>
                </div>

                <h2 class="m-title text-center" id="payModalTitle">پرداخت مبلغ خرید وام</h2>
                <p class="m-text text-center">
                    مبلغ پرداخت شده شما در دست ما تا زمان انجام قطعی معامله به امانت می ماند و در صورتی که معامله انجام
                    نشود، مبلغ به حساب شما برگشت داده می‌شود.

                </p>

                <div class="pay-note" role="note">
                    @include('userPanel.partials.icon', ['name' => 'shield'])
                    <span>
                        کاربر عزیز، شما تنها <b>۷ روز</b> مهلت پرداخت دارید و پس از گذشت زمان مشخص شده معامله <b
                            class="neg">فسخ</b> شده و تا <b class="neg">۳۰ روز</b> امکان ثبت درخواست جدید را نخواهید داشت.
                    </span>
                </div>

                <div class="pay-loan">
                    <span class="pn-bank lg" id="payBank" aria-hidden="true"></span>
                    <div>
                        <h3 id="payTitle"></h3>
                        <span class="pn-id">شناسه وام <b id="payId"></b></span>
                    </div>
                </div>

                <div class="pay-total">
                    <span>مبلغ قابل پرداخت طبق پیشنهاد شما</span>
                    <div><b id="payAmount">۰</b> <small>تومان</small></div>
                </div>

                <form method="POST" action="#" id="payForm" class="m-actions">
                    @csrf
                    <button type="submit" class="btn btn-brand btn-lg flex-grow-1">
                        <span class="btn-label">

                            پرداخت از طریق
                              <img src="{{asset('website/img/zarin.png')}}" height="20">
                        </span>
                        <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span>
                    </button>
                    <button type="button" class="btn btn-outline-danger-soft btn-lg" data-bs-dismiss="modal">لغو و
                        بستن
                    </button>
                </form>
                <p class="pay-secure">@include('userPanel.partials.icon', ['name' => 'lock']) اتصال امن؛ پرداخت در درگاه
                    رسمی زرین‌پال انجام می‌شود.</p>
            </div>
        </div>
    </div>
</div>
