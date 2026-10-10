{{-- لودر صفحه + نوار پیشرفت بالای صفحه | درست بعد از <body> قرار می‌گیرد (سایت و پنل) --}}
<div class="v-loader" id="vLoader" role="status" aria-live="polite" aria-label="در حال بارگذاری">
    <div class="v-loader-box">
        <img class="v-logo" src="{{ asset('website/img/logo.png') }}" alt="وام‌جو" height="54">
        <div class="v-dots" aria-hidden="true"><i></i><i></i><i></i></div>
    </div>
</div>
<div class="v-bar" id="vBar" aria-hidden="true"></div>
<script>
    /* لودر فقط اگر لود بیش از ۲۲۰ms طول بکشد نمایش داده می‌شود (جلوگیری از چشمک زدن) */
    (function () {
        var l = document.getElementById('vLoader'), shown = false, done = false;
        var t = setTimeout(function () { if (!done) { shown = true; l.classList.add('show'); } }, 220);
        function fin() {
            if (done) return; done = true; clearTimeout(t);
            if (!shown) { l.style.display = 'none'; return; }
            l.classList.add('out'); setTimeout(function () { l.style.display = 'none'; }, 480);
        }
        if (document.readyState === 'complete') fin(); else window.addEventListener('load', fin);
        setTimeout(fin, 8000);                                           // اطمینان: هیچ‌وقت گیر نمی‌کند
        window.addEventListener('pageshow', function (e) { if (e.persisted) l.style.display = 'none'; });
    })();
</script>
