{{-- مودال تایید عمومی (حذف، تایید/رد درخواست و ...) | با data-confirm-* روی دکمه باز‌کننده پر می‌شود
     data-bs-toggle="modal" data-bs-target="#confirmModal"
     data-confirm-title / -text / -action / -method (POST|DELETE|PATCH) / -label / -tone (danger|brand) / -reason (1 = فیلد دلیل) --}}
<div class="modal fade app-modal" id="confirmModal" tabindex="-1" aria-labelledby="cfTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sheet">
        <div class="modal-content">
            <div class="sheet-handle d-sm-none" aria-hidden="true"></div>
            <button type="button" class="btn-close m-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            <div class="modal-body text-center">
                <div class="m-illus round" id="cfIcon" aria-hidden="true">
                    <span class="cf-ic cf-danger">@include('userPanel.partials.icon', ['name' => 'trash'])</span>
                    <span class="cf-ic cf-brand">@include('userPanel.partials.icon', ['name' => 'check'])</span>
                </div>
                <h2 class="m-title" id="cfTitle"></h2>
                <p class="m-text" id="cfText"></p>
                <form method="POST" action="#" id="cfForm">
                    @csrf
                    <input type="hidden" name="_method" id="cfMethod" value="POST">
                    <div class="f-field text-start d-none" id="cfReasonBox">
                        <label for="cfReason">دلیل (اختیاری)</label>
                        <textarea id="cfReason" name="reason" class="f-input" rows="2" maxlength="200" placeholder="مثلا: مبلغ پیشنهادی مناسب نیست"></textarea>
                    </div>
                    <div class="m-actions">
                        <button type="submit" class="btn btn-lg flex-grow-1" id="cfOk"><span class="btn-label" id="cfOkLabel">تایید</span><span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
                        <button type="button" class="btn btn-ghost btn-lg" data-bs-dismiss="modal">انصراف</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
