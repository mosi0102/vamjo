/* =====================================================
   وام‌جو – اسکریپت مشترک سایت
   (هر صفحه جدید در انتهای همین فایل بخش خودش را اضافه می‌کند)
   ===================================================== */
$(function () {
    'use strict';

    var fa = function (n) { return Number(n).toLocaleString('fa-IR'); };
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var $win = $(window);

    /* ---------- Header shadow on scroll ---------- */
    var $header = $('#header');
    $win.on('scroll.header', function () {
        $header.toggleClass('scrolled', $win.scrollTop() > 10);
    }).trigger('scroll.header');

    /* ---------- FAQ accordion ---------- */
    $('#faqList').on('click', '.faq-q', function () {
        var $item = $(this).closest('.faq-item');
        var opening = !$item.hasClass('open');
        $item.siblings('.open').removeClass('open').find('.faq-a').slideUp(300).end().find('.faq-q').attr('aria-expanded', 'false');
        $item.toggleClass('open', opening).find('.faq-a').stop(true, true)[opening ? 'slideDown' : 'slideUp'](300);
        $(this).attr('aria-expanded', opening);
    });
    $('.faq-item.open .faq-a').show();

    /* ---------- Scroll reveal ---------- */
    $('.stats .col-6, .step, .contact-box, .faq-item, .section-head').addClass('reveal');
    var io = ('IntersectionObserver' in window) ? new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (!e.isIntersecting) return;
            var $el = $(e.target), idx = $el.parent().children('.reveal').index($el);
            setTimeout(function () { $el.addClass('in'); }, reduceMotion ? 0 : (idx % 4) * 90);
            io.unobserve(e.target);
        });
    }, { threshold: 0.12 }) : null;
    $('.reveal').each(function () { io ? io.observe(this) : $(this).addClass('in'); });

    /* ---------- Count-up numbers ---------- */
    var counted = false;
    function runCounters() {
        if (counted) return; counted = true;
        $('.count').each(function () {
            var $el = $(this), target = +$el.data('count');
            if (reduceMotion) { $el.text(fa(target)); return; }
            $({ v: 0 }).animate({ v: target }, {
                duration: 1600,
                step: function (now) { $el.text(fa(Math.round(now))); },
                complete: function () { $el.text(fa(target)); }
            });
        });
    }
    var $stats = $('.stats');
    if ($stats.length) {
        $win.on('scroll.counters', function () {
            if ($win.scrollTop() + $win.height() > $stats.offset().top + 80) { runCounters(); $win.off('scroll.counters'); }
        }).trigger('scroll.counters');
    }

    /* ---------- Hero parallax (ماوس + اسکرول، با نرم‌سازی) ---------- */
    var $hero = $('.hero'), $layers = $('.parallax .layer');
    if ($hero.length && $layers.length && !reduceMotion) {
        var mx = 0, my = 0, cx = 0, cy = 0, rafId = null;
        var canHover = window.matchMedia('(hover: hover)').matches;

        function frame() {
            cx += (mx - cx) * 0.08;           // lerp برای حرکت نرم
            cy += (my - cy) * 0.08;
            var sy = $win.scrollTop();
            $layers.each(function () {
                var $l = $(this), move = +$l.data('move'), speed = +$l.data('speed');
                var x = cx * move, y = cy * move * 0.6 + sy * speed;
                this.style.transform = 'translate3d(' + x.toFixed(2) + 'px,' + y.toFixed(2) + 'px,0)';
            });
            rafId = requestAnimationFrame(frame);
        }

        if (canHover) {
            $hero.on('mousemove', function (e) {
                var o = $hero.offset(), w = $hero.outerWidth(), h = $hero.outerHeight();
                mx = ((e.pageX - o.left) / w - 0.5) * 2;
                my = ((e.pageY - o.top) / h - 0.5) * 2;
            }).on('mouseleave', function () { mx = my = 0; });
        }

        // فقط وقتی هیرو دیده می‌شود انیمیشن اجرا شود
        if ('IntersectionObserver' in window) {
            new IntersectionObserver(function (en) {
                if (en[0].isIntersecting) { if (!rafId) rafId = requestAnimationFrame(frame); }
                else if (rafId) { cancelAnimationFrame(rafId); rafId = null; }
            }).observe($hero[0]);
        } else { rafId = requestAnimationFrame(frame); }
    }

    /* =====================================================
       صفحه لیست آگهی‌ها (فیلتر، جستجو، مرتب‌سازی – سمت کاربر)
       در پروژه واقعی می‌توانید این منطق را به کوئری‌استرینگ/Ajax سمت سرور وصل کنید.
       ===================================================== */
    var $page = $('#adsPage');
    if ($page.length) {
        var $grid = $('#adsGrid'), $items = $grid.children('.ad-item'), $empty = $('#adsEmpty');
        var digits = '۰۱۲۳۴۵۶۷۸۹';
        var norm = function (s) {
            return String(s || '').replace(/ي/g, 'ی').replace(/ك/g, 'ک')
                .replace(/[۰-۹]/g, function (d) { return digits.indexOf(d); })
                .replace(/[,،٬\s]+/g, ' ').toLowerCase().trim();
        };
        var checked = function (name) { return $page.find('input[name="' + name + '"]:checked').map(function () { return this.value; }).get(); };

        function apply(animate) {
            var banks = checked('bank'), types = checked('type');
            var urgent = $('#fUrgent').is(':checked'), pay = $('#fPay').is(':checked');
            var q = norm($('#adsSearch').val()).replace(/ /g, '');
            var sort = $('.sort-opt.active').data('sort') || 'new';
            var shown = 0;

            $items.sort(function (a, b) {
                var da = $(a).data(), db = $(b).data();
                if (sort === 'amount-desc') return db.amount - da.amount || da.order - db.order;
                if (sort === 'amount-asc') return da.amount - db.amount || da.order - db.order;
                if (sort === 'rate-asc') return da.rate - db.rate || da.order - db.order;
                return da.order - db.order;
            }).appendTo($grid);

            $items.each(function () {
                var $i = $(this), d = $i.data();
                var ok = (!banks.length || banks.indexOf(d.bank) > -1) &&
                    (!types.length || types.indexOf(d.type) > -1) &&
                    (!urgent || +d.urgent === 1) && (!pay || +d.pay === 1) &&
                    (!q || norm(d.search).replace(/ /g, '').indexOf(q) > -1);
                $i.toggleClass('d-none', !ok);
                if (ok) {
                    if (animate && !reduceMotion) { $i.removeClass('pop'); void this.offsetWidth; $i.css('animation-delay', (shown * 60) + 'ms').addClass('pop'); }
                    shown++;
                }
            });

            $('#resultCount, #sheetCount').text(fa(shown));
            $empty.prop('hidden', shown > 0);
            var active = banks.length + types.length + (urgent ? 1 : 0) + (pay ? 1 : 0);
            $('#filterBadge').text(fa(active)).prop('hidden', active === 0);
        }

        $page.on('change', 'input[type=checkbox]', function () { apply(true); });
        var t; $('#adsSearch').on('input', function () { clearTimeout(t); t = setTimeout(function () { apply(true); }, 200); });

        /* Sort dropdown */
        $page.on('click', '.sort-opt', function () {
            $('.sort-opt').removeClass('active'); $(this).addClass('active');
            $('#sortLabel').text($(this).text());
            apply(true);
        });

        /* Bank list search */
        $('#bankSearch').on('input', function () {
            var q = norm(this.value), n = 0;
            $('#bankSearchClear').prop('hidden', !q);
            $('.bank-opt').each(function () {
                var hit = !q || norm($(this).data('name')).indexOf(q) > -1;
                $(this).toggle(hit); if (hit) n++;
            });
            $('#bankEmpty').prop('hidden', n > 0);
        });
        $('#bankSearchClear').on('click', function () { $('#bankSearch').val('').trigger('input').focus(); });

        /* Reset */
        $('#clearAll, #emptyReset').on('click', function () {
            $page.find('input[type=checkbox]').prop('checked', false);
            $('#adsSearch').val(''); $('#bankSearch').val('').trigger('input');
            apply(true);
        });

        /* SEO text: نمایش بیشتر (موبایل) */
        $('#seoToggle').on('click', function () {
            var open = $('#seoText').toggleClass('clamped').hasClass('clamped') === false;
            $(this).text(open ? 'نمایش کمتر' : 'نمایش بیشتر');
        });
    }

    /* =====================================================
       ورود با OTP + مودال ثبت درخواست
       ⚠️ API.demo = true یعنی بدون بک‌اند شبیه‌سازی می‌شود (هر کد ۵ رقمی پذیرفته می‌شود).
          برای اتصال به لاراول مقدار را false کنید؛ سه آدرس زیر باید JSON برگردانند:
          POST /auth/otp/send    {phone}         → {ok:true}
          POST /auth/otp/verify  {phone, code}   → {ok:true}   (در خطا: HTTP 422)
          POST /ads/{id}/request {offer}         → {ok:true, redirect:"/payment/.."}
       ===================================================== */
    var API = { demo: true };
    var csrf = $('meta[name="csrf-token"]').attr('content');
    var isAuth = String($('body').attr('data-auth')) === '1';
    var intent = null;                          // بعد از ورود چه کاری انجام شود: {type:'request'} یا {type:'redirect', url}

    var latin = function (s) {
        return String(s || '').replace(/[۰-۹]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d); })
            .replace(/[٠-٩]/g, function (d) { return '٠١٢٣٤٥٦٧٨٩'.indexOf(d); });
    };
    function post(url, data, demoResponse) {
        if (API.demo) {
            var d = $.Deferred(); setTimeout(function () { d.resolve(demoResponse || { ok: true }); }, 700); return d.promise();
        }
        return $.ajax({ url: url, method: 'POST', data: data, dataType: 'json', headers: { 'X-CSRF-TOKEN': csrf } });
    }
    function busy($btn, on) { $btn.toggleClass('loading', on).find('.spinner-border').toggleClass('d-none', !on); }

    /* ---------- Request modal ---------- */
    var $rm = $('#requestModal'), rm = $rm.length ? bootstrap.Modal.getOrCreateInstance($rm[0]) : null;
    if ($rm.length) {
        var minOffer = +$rm.data('min'), maxOffer = +$rm.data('max');
        var $offer = $('#offerInput');

        $offer.on('input', function () {
            var v = latin(this.value).replace(/\D/g, '');
            this.value = v ? Number(v).toLocaleString('en-US') : '';
            $offer.parent().removeClass('invalid'); $('#offerErr').addClass('d-none'); $('#offerHint').removeClass('d-none');
        });

        $('#requestSubmit').on('click', function () {
            var raw = +latin($offer.val()).replace(/\D/g, '');
            if (raw && (raw < minOffer || raw > maxOffer)) {
                $offer.parent().addClass('invalid'); $('#offerHint').addClass('d-none'); $('#offerErr').removeClass('d-none'); $offer.trigger('focus');
                return;
            }
            var $b = $(this); busy($b, true);
            post($rm.data('url'), { offer: raw || '' }, { ok: true, redirect: $rm.data('pay') })
                .done(function (res) { window.location.href = (res && res.redirect) || $rm.data('pay'); })
                .fail(function () { busy($b, false); $('#offerErr').text('ثبت درخواست انجام نشد؛ دوباره تلاش کنید.').removeClass('d-none'); });
        });
        $rm.on('hidden.bs.modal', function () { $offer.val('').parent().removeClass('invalid'); $('#offerErr').addClass('d-none'); $('#offerHint').removeClass('d-none'); busy($('#requestSubmit'), false); });
    }

    /* ---------- Login (OTP) modal ---------- */
    var $lm = $('#loginModal'), lm = $lm.length ? bootstrap.Modal.getOrCreateInstance($lm[0]) : null;
    var timerId = null, phone = '';

    function showStep(n) { $lm.find('.l-step').addClass('d-none').filter('[data-step="' + n + '"]').removeClass('d-none'); }
    function resetLogin() {
        clearInterval(timerId); showStep('phone');
        $('#phoneInput').val(''); $('#phoneErr, #otpErr').addClass('d-none');
        $('#otpBoxes').removeClass('invalid').find('input').val('').removeClass('filled');
        busy($('#sendOtp'), false); busy($('#verifyOtp'), false);
    }
    function openLogin(i) { if (!lm) return; intent = i || null; resetLogin(); lm.show(); }

    function startTimer(sec) {
        clearInterval(timerId); $('#resendOtp').addClass('d-none'); $('#otpTimer').removeClass('d-none');
        var left = sec;
        function tick() {
            var m = Math.floor(left / 60), s = left % 60;
            var pad = function (n) { return ('0' + n).slice(-2).replace(/\d/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'.charAt(d); }); };
            $('#otpTimer b').text(pad(m) + ':' + pad(s));
            if (left-- <= 0) { clearInterval(timerId); $('#otpTimer').addClass('d-none'); $('#resendOtp').removeClass('d-none'); }
        }
        tick(); timerId = setInterval(tick, 1000);
    }
    function sendCode() {
        var $b = $('#sendOtp'); busy($b, true);
        post($lm.data('send-url'), { phone: phone })
            .done(function () {
                busy($b, false); $('#otpPhone').text(phone); showStep('otp'); startTimer(120);
                setTimeout(function () { $('#otpBoxes input').first().trigger('focus'); }, 150);
            })
            .fail(function () { busy($b, false); $('#phoneErr').text('ارسال کد انجام نشد؛ دوباره تلاش کنید.').removeClass('d-none'); });
    }

    /* باز کردن مودال ورود */
    $(document).on('click', '[data-login-required]', function (e) {
        if (isAuth) return;                       // کاربر لاگین است → لینک عادی کار می‌کند
        e.preventDefault();
        var href = $(this).attr('href');
        openLogin(href && href !== '#' ? { type: 'redirect', url: href } : null);
    });
    $('#requestBtn, #requestBtnBar').on('click', function () {
        if (isAuth) { rm && rm.show(); } else { openLogin({ type: 'request' }); }
    });

    /* مرحله ۱: شماره موبایل */
    $('#phoneInput').on('input', function () {
        this.value = latin(this.value).replace(/\D/g, '').slice(0, 11); $('#phoneErr').addClass('d-none');
    }).on('keydown', function (e) { if (e.key === 'Enter') $('#sendOtp').trigger('click'); });
    $('#sendOtp').on('click', function () {
        phone = $('#phoneInput').val();
        if (!/^09\d{9}$/.test(phone)) { $('#phoneErr').text('شماره موبایل معتبر نیست؛ مثال: ۰۹۱۲۳۴۵۶۷۸۹').removeClass('d-none'); return; }
        sendCode();
    });

    /* مرحله ۲: کد تایید */
    var $otp = $('#otpBoxes input');
    function otpValue() { return $otp.map(function () { return this.value; }).get().join(''); }
    $otp.on('input', function () {
        this.value = latin(this.value).replace(/\D/g, '').slice(-1);
        $(this).toggleClass('filled', !!this.value);
        $('#otpBoxes').removeClass('invalid'); $('#otpErr').addClass('d-none');
        if (this.value) { var $n = $otp.eq($otp.index(this) + 1); $n.length ? $n.trigger('focus') : $('#verifyOtp').trigger('click'); }
    }).on('keydown', function (e) {
        if (e.key === 'Backspace' && !this.value) $otp.eq($otp.index(this) - 1).trigger('focus').val('').removeClass('filled');
    }).on('paste', function (e) {
        var t = latin((e.originalEvent.clipboardData || window.clipboardData).getData('text')).replace(/\D/g, '').slice(0, 5);
        if (!t) return; e.preventDefault();
        $otp.each(function (i) { this.value = t[i] || ''; $(this).toggleClass('filled', !!t[i]); });
        if (t.length === 5) $('#verifyOtp').trigger('click');
    });
    $('#verifyOtp').on('click', function () {
        var code = otpValue();
        if (code.length < 5) { $('#otpBoxes').addClass('invalid'); $('#otpErr').text('کد ۵ رقمی را کامل وارد کنید.').removeClass('d-none'); return; }
        var $b = $(this); if ($b.hasClass('loading')) return; busy($b, true);
        post($lm.data('verify-url'), { phone: phone, code: code })
            .done(function () {
                clearInterval(timerId); isAuth = true; $('body').attr('data-auth', 1); showStep('done');
                setTimeout(function () {
                    var go = intent; intent = null;
                    if (go && go.type === 'request' && rm) { $lm.one('hidden.bs.modal', function () { rm.show(); }); lm.hide(); }
                    else if (go && go.type === 'redirect') { window.location.href = go.url; }
                    else { window.location.reload(); }
                }, 1100);
            })
            .fail(function () {
                busy($b, false); $('#otpBoxes').addClass('invalid'); $('#otpErr').text('کد وارد شده صحیح نیست.').removeClass('d-none');
                $otp.val('').removeClass('filled').first().trigger('focus');
            });
    });
    $('#resendOtp').on('click', function () { $otp.val('').removeClass('filled'); sendCode(); });
    $('#editPhone').on('click', function () { clearInterval(timerId); showStep('phone'); $('#phoneInput').trigger('focus'); });
    $lm.on('hidden.bs.modal', function () { clearInterval(timerId); });

    /* ---------- Bookmark ---------- */
    $('#bookmarkBtn').on('click', function () {
        if (!isAuth) return;                      // برای مهمان، data-login-required مودال ورود را باز می‌کند
        var on = !$(this).hasClass('on'); $(this).toggleClass('on', on).attr('aria-pressed', on);
    });
});
