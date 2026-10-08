/* =====================================================
   وام‌جو – اسکریپت پنل (کاربر / ادمین / پشتیبان)
   هر صفحه جدید پنل بخش خودش را در انتهای همین فایل اضافه می‌کند.
   ===================================================== */
$(function () {
    'use strict';

    var $html = $('html');
    var desktop = window.matchMedia('(min-width: 992px)');
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var fa = function (n) {
        return Number(n).toLocaleString('fa-IR').replace(/٬/g, ',');
    };

    /* ---------- Sidebar: دسکتاپ = جمع/باز شدن (ذخیره در مرورگر) | موبایل = کشوی کناری ---------- */
    var $toggle = $('#pnToggle');

    function syncAria() {
        var open = desktop.matches ? !$html.hasClass('pn-collapsed') : $html.hasClass('pn-drawer-open');
        $toggle.attr('aria-expanded', open);
        $('#pnMenuBtn').attr('aria-expanded', $html.hasClass('pn-drawer-open'));
    }

    function setDrawer(on) {
        $html.toggleClass('pn-drawer-open', on);
        syncAria();
    }

    $toggle.on('click', function () {
        if (desktop.matches) {
            var collapsed = $html.toggleClass('pn-collapsed').hasClass('pn-collapsed');
            try {
                localStorage.setItem('pn-collapsed', collapsed ? '1' : '0');
            } catch (e) {
            }
        } else {
            setDrawer(!$html.hasClass('pn-drawer-open'));
        }
        syncAria();
    });
    $('#pnMenuBtn').on('click', function () {
        setDrawer(true);
    });
    $('#pnOverlay, .pn-drawer-close').on('click', function () {
        setDrawer(false);
    });
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') setDrawer(false);
    });
    $('#pnSidebar .pn-link').on('click', function () {
        if (!desktop.matches) setDrawer(false);
    });

    // کشیدن کشو به سمت راست = بستن (لمسی)
    var tx = null;
    $('#pnSidebar').on('touchstart', function (e) {
        tx = e.originalEvent.touches[0].clientX;
    })
        .on('touchend', function (e) {
            if (tx !== null && e.originalEvent.changedTouches[0].clientX - tx > 70) setDrawer(false);
            tx = null;
        });

    var onBreakpoint = function () {
        if (desktop.matches) $html.removeClass('pn-drawer-open');
        syncAria();
    };
    desktop.addEventListener ? desktop.addEventListener('change', onBreakpoint) : desktop.addListener(onBreakpoint);
    syncAria();

    /* ---------- Reveal با تاخیر پلکانی (--d در CSS) ---------- */
    var io = ('IntersectionObserver' in window) ? new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) {
                var $t = $(e.target).addClass('in');
                setTimeout(function () {
                    $t.addClass('done');
                }, 1200);
                io.unobserve(e.target);
            }
        });
    }, {threshold: 0.08}) : null;
    $('.pn-reveal').each(function () {
        io ? io.observe(this) : $(this).addClass('in');
    });

    /* ---------- Count-up ---------- */
    var counted = false;

    function runCounters() {
        if (counted) return;
        counted = true;
        $('.pn-count').each(function () {
            var $el = $(this), target = +$el.data('count'), prefix = $el.data('prefix') || '';
            if (reduceMotion) {
                $el.text(prefix + fa(target));
                return;
            }
            $({v: 0}).animate({v: target}, {
                duration: 1500,
                step: function (now) {
                    $el.text(prefix + fa(Math.round(now)));
                },
                complete: function () {
                    $el.text(prefix + fa(target));
                }
            });
        });
    }

    if ($('.pn-count').length) setTimeout(runCounters, 350);

    /* ---------- Countdown مزایده ---------- */
    $('.pn-cd').each(function () {
        var $cd = $(this), left = Math.max(0, +$cd.data('remaining') || 0), prev = {};

        function set(unit, val) {
            var $n = $cd.find('[data-unit="' + unit + '"] .pn-cd-num');
            var txt = (unit === 'd') ? String(val) : ('0' + val).slice(-2);
            if (prev[unit] !== txt) {
                $n.text(txt);
                if (prev[unit] !== undefined && !reduceMotion) {
                    $n.removeClass('tick');
                    void $n[0].offsetWidth;
                    $n.addClass('tick');
                }
                prev[unit] = txt;
            }
        }

        function render() {
            set('d', Math.floor(left / 86400));
            set('h', Math.floor(left % 86400 / 3600));
            set('m', Math.floor(left % 3600 / 60));
            set('s', left % 60);
        }

        render();
        var t = setInterval(function () {
            if (left <= 0) {
                clearInterval(t);
                $cd.addClass('ended');
                return;
            }
            left--;
            render();
        }, 1000);
    });
});
