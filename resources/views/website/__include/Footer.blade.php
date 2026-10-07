{{-- Footer --}}
<footer class="site-footer" id="footer">
    <div class="container">
        <div class="text-center">
            <img class="logo logo-light mb-3" src="{{ asset('website/img/logoWhite.png') }}" alt="وام‌جو" height="46">
            <p class="footer-text">لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده از طراحان گرافیک است. چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است و برای شرایط فعلی تکنولوژی مورد نیاز و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد. کتابهای زیادی در شصت و سه درصد گذشته حال و آینده.</p>
        </div>

        <ul class="footer-links list-unstyled">
            <li><a href="{{ url('/') }}">صفحه نخست</a></li>
            <li><a href="{{ url('/ads') }}">آگهی‌های وام</a></li>
            <li><a href="{{ url('/#banks') }}">بانک‌های تحت پوشش</a></li>
            <li><a href="{{ url('/rules') }}">قوانین و مقررات</a></li>
            <li><a href="{{ url('/#faq') }}">سوالات متداول</a></li>
        </ul>

        <div class="row g-4 footer-contact align-items-center">
            <div class="col-lg-6">
                <p class="mb-1 text-lg-start text-center"><b>آدرس شرکت</b> <span class="sep">|</span> ساعات مراجعه ۹ الی ۱۴ و عصرها ۱۶ الی ۲۰</p>
                <p class="mb-0 text-lg-start text-center">خراسان رضوی – سبزوار – خیابان بیهق – کوچه بیهق ۱۴ – شرکت وامینجا</p>
            </div>
            <div class="col-lg-6">
                <div class="d-flex flex-column justify-content-start">
                    <p class="mb-1 phones text-lg-end text-center" dir="ltr"><span>۰۵۱ ۳۳۷ ۶۷۳۰۰</span><span class="sep">|</span><span>۰۹۰۵۱۳۱۵۰۰۳</span><span class="sep">|</span><span>۰۹۳۳-۴۱۷۵۷۰۰</span></p>
                    <p class="mb-0 text-lg-end text-center">کارشناسان ما آماده پاسخ‌گویی هروز هفته در هر ۲۴ ساعت روز می باشد.</p>
                </div>

            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">
            <span>تمام حقوق مادی و معنوی متعلق به وامجو می باشد.</span>
            <div class="trust-badges" aria-label="نمادهای اعتماد">
                <span class="badge-box b1"></span><span class="badge-box b2"></span><span class="badge-box b3"></span>
            </div>
        </div>
    </div>
</footer>
