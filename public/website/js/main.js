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
});
