{{-- مودال تایید خروج از حساب (برای دکمه‌های خروج هدر و سایدبار) --}}
<div class="modal fade app-modal" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sheet">
        <div class="modal-content">
            <div class="sheet-handle d-sm-none" aria-hidden="true"></div>
            <div class="modal-body text-center">
                <div class="m-illus round danger" aria-hidden="true">@include('userPanel.partials.icon', ['name' => 'power'])</div>
                <h2 class="m-title" id="logoutModalTitle">خروج از حساب کاربری</h2>
                <p class="m-text">آیا مطمئن هستید که می‌خواهید از حساب کاربری خود خارج شوید؟</p>
                <form method="POST" action="{{ url('/logout') }}" id="logoutForm" class="m-actions">
                    @csrf
                    <button type="submit" class="btn btn-danger-solid btn-lg flex-grow-1"><span class="btn-label">بله، خارج شو</span><span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
                    <button type="button" class="btn btn-ghost btn-lg" data-bs-dismiss="modal">انصراف</button>
                </form>
            </div>
        </div>
    </div>
</div>
