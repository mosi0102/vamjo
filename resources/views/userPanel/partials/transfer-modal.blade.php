{{-- مودال مشاهده مدرک و تایید انتقال امتیاز | داده‌ها از دکمه‌ی data-* پر می‌شود (panel.js) --}}
<div class="modal fade app-modal pay-modal" id="transferModal" tabindex="-1" aria-labelledby="transferModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sheet">
        <div class="modal-content">
            <div class="sheet-handle d-sm-none" aria-hidden="true"></div>
            <button type="button" class="btn-close m-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            <div class="modal-body">

                <div class="m-illus big text-center" aria-hidden="true">
                    <svg viewBox="0 0 120 100">
                        <ellipse cx="60" cy="93" rx="46" ry="6" fill="#1e5a45" opacity=".1"/>
                        <path d="M52 8h22l10 10v32H52z" fill="#e9efe6" stroke="#c8d4cb" stroke-width="2"/><path d="M74 8v10h10" fill="#cfdccf"/>
                        <path d="M58 22l10-7 10 7M60 22v14M68 22v14M76 22v14M57 37h22" stroke="#2a7a5f" stroke-width="2.4" fill="none" stroke-linecap="round"/><circle cx="68" cy="44" r="4" fill="#d9a93f"/>
                        <circle cx="28" cy="52" r="9" fill="#2a7a5f"/><path d="M13 78c1-13 9-18 15-18s14 5 15 18z" fill="#2a7a5f"/><ellipse cx="28" cy="80" rx="18" ry="5" fill="#e9efe6"/>
                        <circle cx="98" cy="58" r="9" fill="#efe7d3"/><path d="M84 82c1-12 8-17 14-17s13 5 14 17z" fill="#efe7d3"/><ellipse cx="98" cy="84" rx="16" ry="5" fill="#2a7a5f"/>
                        <path d="M40 66h30" stroke="#2a7a5f" stroke-width="3" stroke-linecap="round"/><path d="M66 61l6 5-6 5" stroke="#2a7a5f" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="60" cy="76" r="14" fill="#f6f3ec" stroke="#1e5a45" stroke-width="3"/><path d="M53 76l5 5 9-10" stroke="#1e5a45" stroke-width="3.2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>

                <h2 class="m-title text-center" id="transferModalTitle">تایید انتقال امتیاز</h2>
                <p class="m-text text-center">کاربر عزیز، طبق مدارک ثبت شده توسط فروشنده امتیاز انتقال داده شده، پس از بررسی صحت اطلاعات ثبت شده تایید انتقال امتیاز را انجام دهید.</p>

                {{-- مدرک انتقال --}}
                <div class="doc-viewer" id="docViewer">
                    <div class="doc-skeleton" aria-hidden="true"></div>
                    <img id="docImg" alt="مدرک انتقال امتیاز" hidden>
                    <p class="doc-empty" id="docEmpty">محل تصویر مدرک انتقال امتیاز</p>
                    <button type="button" class="doc-zoom" id="docZoom" aria-label="بزرگنمایی مدرک" hidden>@include('userPanel.partials.icon', ['name' => 'zoom'])<span>بزرگنمایی</span></button>
                </div>

                <div class="pay-loan">
                    <span class="pn-bank lg" id="trBank" aria-hidden="true"></span>
                    <div>
                        <h3 id="trTitle"></h3>
                        <span class="pn-id">شناسه وام <b id="trId"></b></span>
                    </div>
                </div>

                <form method="POST" action="#" id="transferForm">
                    @csrf
                    <label class="chk tr-agree">
                        <input type="checkbox" id="trAgree">
                        <span class="box"></span>
                        <span class="chk-text">اطلاعات مدرک را بررسی کرده‌ام و انتقال امتیاز به حساب من انجام شده است. می‌دانم این تایید قابل بازگشت نیست.</span>
                    </label>
                    <div class="m-actions">
                        <button type="submit" class="btn btn-brand btn-lg flex-grow-1" id="trSubmit" disabled>
                            <span class="btn-label">تایید انتقال و دریافت امتیاز</span><span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span>
                        </button>
                        <button type="button" class="btn btn-outline-danger-soft btn-lg" data-bs-dismiss="modal">لغو و بستن</button>
                    </div>
                </form>
                <p class="pay-secure tr-help">مشکلی در مدرک دیده می‌شود؟ <a href="{{ url('/panel/tickets') }}">ثبت تیکت پشتیبانی</a></p>
            </div>
        </div>
    </div>
</div>
