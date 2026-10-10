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

    /* =====================================================
       داشبورد
       ===================================================== */

    /* سلام بر اساس ساعت + تاریخ شمسی */
    var $greet = $('#pnGreet');
    if ($greet.length) {
        var hr = new Date().getHours();
        var hi = hr < 5 ? 'شب بخیر' : hr < 12 ? 'صبح بخیر' : hr < 17 ? 'ظهر بخیر' : hr < 20 ? 'عصر بخیر' : 'شب بخیر';
        $greet.text(hi + '، ' + $greet.data('name'));
        try {
            $('#pnDate').text(new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }).format(new Date()));
        } catch (e) {
            $('#pnDate').hide();
        }
    }

    /* نمودار ستونی (پرداختی / دریافتی) */
    var $chart = $('#pnChart');
    if ($chart.length) {
        var labels = $chart.data('labels'), sets = {pay: $chart.data('pay'), recv: $chart.data('recv')};
        var names = {pay: 'پرداختی', recv: 'دریافتی'}, $bars = $('#pnBars'), built = false, shownTotal = 0;

        function countTo($el, to) {
            if (reduceMotion) {
                $el.text(fa(to));
                shownTotal = to;
                return;
            }
            $({v: shownTotal}).stop(true).animate({v: to}, {
                duration: 900, step: function (n) {
                    $el.text(fa(Math.round(n)));
                }, complete: function () {
                    $el.text(fa(to));
                    shownTotal = to;
                }
            });
        }

        function drawChart(key) {
            var vals = sets[key], max = Math.max.apply(null, vals) * 1.1;
            if (!built) {
                $bars.html($.map(labels, function (l) {
                    return '<div class="pn-bar-col"><div class="pn-bar-track"><div class="pn-bar"></div></div><span class="pn-bar-label">' + l + '</span></div>';
                }).join(''));
                built = true;
            }
            $bars.toggleClass('recv', key === 'recv');
            $bars.find('.pn-bar').each(function (i) {
                var $b = $(this).attr('data-v', fa(vals[i]) + ' میلیون');
                setTimeout(function () {
                    $b.css('height', (vals[i] / max * 100) + '%');
                }, reduceMotion ? 0 : 40 + i * 80);
            });
            $('#pnChartTitle').text(names[key]);
            countTo($('#pnChartTotal'), vals.reduce(function (a, b) {
                return a + b;
            }, 0));
        }

        $('.pn-tab').on('click', function () {
            $('.pn-tab').removeClass('active').attr('aria-selected', 'false');
            $(this).addClass('active').attr('aria-selected', 'true');
            drawChart($(this).data('set'));
        });
        if ('IntersectionObserver' in window) {
            var cio = new IntersectionObserver(function (en) {
                if (en[0].isIntersecting) {
                    drawChart('pay');
                    cio.disconnect();
                }
            }, {threshold: 0.25});
            cio.observe($chart[0]);
        } else {
            drawChart('pay');
        }
    }

    /* اعلان‌ها */
    var $notif = $('#pnNotif');
    $('#pnBell').on('click', function (e) {
        e.stopPropagation();
        var open = $notif.toggleClass('open').hasClass('open');
        $(this).attr('aria-expanded', open);
    });
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#pnNotif').length) {
            $notif.removeClass('open');
            $('#pnBell').attr('aria-expanded', 'false');
        }
    });
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') $notif.removeClass('open');
    });
    $('#pnReadAll').on('click', function () {
        $('.pn-notif-item').removeClass('new');
        $('.pn-bell-dot').fadeOut(200);
    });

    /* خروج: نمایش لودینگ روی دکمه تایید */
    $('#logoutForm').on('submit', function () {
        $(this).find('button[type=submit]').addClass('loading').prop('disabled', true).find('.spinner-border').removeClass('d-none');
    });

    /* =====================================================
       ابزار مشترک: Toast
       ===================================================== */
    window.pnToast = function (msg) {
        var $t = $('<div class="pn-toast" role="status"><svg viewBox="0 0 24 24" class="pn-ic" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg><span></span></div>');
        $t.find('span').text(msg).end().appendTo('body');
        requestAnimationFrame(function () {
            $t.addClass('show');
        });
        setTimeout(function () {
            $t.removeClass('show');
            setTimeout(function () {
                $t.remove();
            }, 400);
        }, 2200);
    };

    /* =====================================================
       درخواست های من
       ===================================================== */
    var $rqList = $('#rqList');
    if ($rqList.length) {
        var $cards = $rqList.children('.rq-card'), curGroup = 'all', curQ = '';
        var digitsFa = '۰۱۲۳۴۵۶۷۸۹';
        var norm = function (s) {
            return String(s || '').replace(/[۰-۹]/g, function (d) {
                return digitsFa.indexOf(d);
            }).replace(/[,،٬\s]+/g, '').toLowerCase();
        };

        function applyRq() {
            var shown = 0;
            $cards.each(function () {
                var $c = $(this);
                var ok = (curGroup === 'all' || $c.data('group') === curGroup) && (!curQ || norm($c.data('search')).indexOf(curQ) > -1);
                var wasHidden = $c.hasClass('d-none');
                $c.toggleClass('d-none', !ok);
                if (ok) {
                    if (wasHidden && !reduceMotion) {
                        $c.removeClass('rq-pop');
                        void this.offsetWidth;
                        $c.css('animation-delay', (shown * 70) + 'ms').addClass('rq-pop');
                    }
                    shown++;
                }
            });
            $('#rqEmpty').prop('hidden', shown > 0);
        }

        $('.rq-tab').on('click', function () {
            $('.rq-tab').removeClass('active').attr('aria-selected', 'false');
            $(this).addClass('active').attr('aria-selected', 'true');
            curGroup = $(this).data('group');
            applyRq();
        });
        var rqT;
        $('#rqSearch').on('input', function () {
            var v = this.value;
            clearTimeout(rqT);
            rqT = setTimeout(function () {
                curQ = norm(v);
                applyRq();
            }, 180);
        });

        /* کپی شناسه وام */
        $rqList.on('click', '.rq-id', function (e) {
            e.preventDefault();
            var text = String($(this).data('copy'));
            var done = function () {
                window.pnToast('شناسه وام کپی شد');
            };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(done);
            } else {
                var $ta = $('<textarea>').val(text).css({position: 'fixed', opacity: 0}).appendTo('body');
                $ta[0].select();
                try {
                    document.execCommand('copy');
                    done();
                } catch (er) {
                }
                $ta.remove();
            }
        });
    }

    /* مودال پرداخت مبلغ خرید وام (داده از دکمه‌ی باز‌کننده) */
    var $pay = $('#payModal');
    if ($pay.length) {
        $pay.on('show.bs.modal', function (e) {
            var $b = $(e.relatedTarget);
            if (!$b.length) return;
            $('#payTitle').text($b.data('title'));
            $('#payId').text('#' + Number($b.data('id')).toLocaleString('fa-IR', {useGrouping: false}));
            $('#payAmount').text(fa($b.data('price')));
            $('#payBank').text($b.data('bank-l')).css('background', $b.data('bank-c'));
            $('#payForm').attr('action', $b.data('action'));          // POST /panel/requests/{id}/pay → ریدایرکت به زرین‌پال
        });
        $('#payForm').on('submit', function () {
            $(this).find('button[type=submit]').addClass('loading').prop('disabled', true).find('.spinner-border').removeClass('d-none');
        });
        $pay.on('hidden.bs.modal', function () {
            $('#payForm button[type=submit]').removeClass('loading').prop('disabled', false).find('.spinner-border').addClass('d-none');
        });
    }

    /* =====================================================
       نتیجه پرداخت
       ===================================================== */
    var $pr = $('#prCard');
    if ($pr.length) {
        if ($pr.data('status') === 'success' && !reduceMotion) {                       // کانفتی ملایم
            var colors = ['#2e9a4f', '#1e5a45', '#d9a93f', '#6fd49b', '#f0c95a'];
            for (var k = 0; k < 28; k++) {
                $('<i class="cf"></i>').css({
                    left: (Math.random() * 100) + '%',
                    background: colors[k % colors.length],
                    animationDuration: (2.2 + Math.random() * 1.8) + 's',
                    animationDelay: (0.5 + Math.random() * 0.9) + 's',
                    transform: 'rotate(' + (Math.random() * 90) + 'deg)'
                }).appendTo($pr);
            }
            setTimeout(function () {
                $pr.find('.cf').remove();
            }, 5500);
        }
        $('#prPrint').on('click', function () {
            window.print();
        });
        $pr.on('click keydown', '.pr-copy', function (e) {
            if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') return;
            e.preventDefault();
            var text = String($(this).data('copy'));
            var done = function () {
                window.pnToast('کپی شد');
            };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(done);
            } else {
                var $ta = $('<textarea>').val(text).css({position: 'fixed', opacity: 0}).appendTo('body');
                $ta[0].select();
                try {
                    document.execCommand('copy');
                    done();
                } catch (er) {
                }
                $ta.remove();
            }
        });
    }

    /* =====================================================
       مودال تایید انتقال امتیاز (مشاهده مدرک + تایید)
       ===================================================== */
    var $tr = $('#transferModal');
    if ($tr.length) {
        var $viewer = $('#docViewer'), $img = $('#docImg');

        function resetDoc() {
            $viewer.removeClass('loading loaded has-img');
            $img.attr('src', '').prop('hidden', true);
            $('#docZoom').prop('hidden', true);
        }

        $tr.on('show.bs.modal', function (e) {
            var $b = $(e.relatedTarget);
            if (!$b.length) return;
            $('#trTitle').text($b.data('title'));
            $('#trId').text('#' + Number($b.data('id')).toLocaleString('fa-IR', {useGrouping: false}));
            $('#trBank').text($b.data('bank-l')).css('background', $b.data('bank-c'));
            $('#transferForm').attr('action', $b.data('action'));       // POST /panel/requests/{id}/confirm-transfer
            $('#trAgree').prop('checked', false);
            $('#trSubmit').prop('disabled', true);
            resetDoc();
            var src = $b.data('doc');
            if (src) {                                                  // لودینگ اسکلتی تا آماده شدن تصویر
                $viewer.addClass('loading has-img');
                $img.prop('hidden', false)
                    .off('load error').on('load', function () {
                    $viewer.removeClass('loading').addClass('loaded');
                    $('#docZoom').prop('hidden', false);
                })
                    .on('error', function () {
                        resetDoc();
                        $('#docEmpty').text('بارگذاری مدرک ممکن نشد');
                    }).attr('src', src);
            } else {
                $('#docEmpty').text('محل تصویر مدرک انتقال امتیاز');
            }
        });
        $('#trAgree').on('change', function () {
            $('#trSubmit').prop('disabled', !this.checked);
        });       // تایید فقط بعد از تیک بررسی مدرک
        $('#transferForm').on('submit', function (e) {
            if (!$('#trAgree').is(':checked')) {
                e.preventDefault();
                return;
            }
            $('#trSubmit').addClass('loading').prop('disabled', true).find('.spinner-border').removeClass('d-none');
        });
        $tr.on('hidden.bs.modal', function () {
            $('#trSubmit').removeClass('loading').find('.spinner-border').addClass('d-none');
            resetDoc();
        });

        /* Lightbox بزرگنمایی مدرک */
        function openLightbox(src) {
            var $lb = $('<div class="pn-lightbox" role="dialog" aria-label="مشاهده مدرک"><button type="button" class="lb-close" aria-label="بستن"><svg viewBox="0 0 24 24" class="pn-ic"><path d="M6 6l12 12M18 6L6 18"/></svg></button><img alt="مدرک انتقال امتیاز"></div>');
            $lb.find('img').attr('src', src);
            $lb.appendTo('body');
            requestAnimationFrame(function () {
                $lb.addClass('show');
            });
            var close = function () {
                $lb.removeClass('show');
                setTimeout(function () {
                    $lb.remove();
                }, 300);
                $(document).off('keydown.lb');
            };
            $lb.on('click', function (e) {
                if ($(e.target).closest('.lb-close').length || e.target === this) close();
            });
            $lb.find('img').on('click', function () {
                $lb.toggleClass('zoomed');
            });
            $(document).on('keydown.lb', function (e) {
                if (e.key === 'Escape') {
                    e.stopPropagation();
                    close();
                }
            });
        }

        $('#docZoom, #docImg').on('click', function () {
            var s = $img.attr('src');
            if (s) openLightbox(s);
        });
    }
});

