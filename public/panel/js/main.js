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
    }, { threshold: 0.08 }) : null;
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
            $({ v: 0 }).animate({ v: target }, {
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
        var labels = $chart.data('labels'), sets = { pay: $chart.data('pay'), recv: $chart.data('recv') };
        var names = $chart.data('names') || { pay: 'پرداختی', recv: 'دریافتی' }, units = $chart.data('units') || null,
            $bars = $('#pnBars'), built = false, shownTotal = 0;

        function countTo($el, to) {
            if (reduceMotion) {
                $el.text(fa(to));
                shownTotal = to;
                return;
            }
            $({ v: shownTotal }).stop(true).animate({ v: to }, {
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
                var $b = $(this).attr('data-v', fa(vals[i]) + ' ' + (units ? String(units[key]).split(' ')[0] : 'میلیون'));
                setTimeout(function () {
                    $b.css('height', (vals[i] / max * 100) + '%');
                }, reduceMotion ? 0 : 40 + i * 80);
            });
            $('#pnChartTitle').text(names[key]);
            if (units) $('#pnChartUnit').text(units[key]);
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
            }, { threshold: 0.25 });
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
    window.pnToast = function (msg, opt) {
        opt = opt || {};
        var closed = false, acted = false, timer;
        var $t = $('<div class="pn-toast" role="status"><svg viewBox="0 0 24 24" class="pn-ic" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg><span class="tt"></span></div>');
        $t.find('.tt').text(msg);
        if (opt.action) {
            $t.addClass('has-act').append($('<button type="button" class="tt-act">').text(opt.action));
        }
        $t.appendTo('body');
        requestAnimationFrame(function () {
            $t.addClass('show');
        });

        function close() {
            if (closed) return;
            closed = true;
            clearTimeout(timer);
            $t.removeClass('show');
            setTimeout(function () {
                $t.remove();
            }, 400);
            if (opt.onClose) opt.onClose(acted);
        }

        $t.on('click', '.tt-act', function () {
            acted = true;
            if (opt.onAction) opt.onAction();
            close();
        });
        timer = setTimeout(close, opt.duration || 2200);
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
            var text = String($(this).data('copy')), label = $(this).data('copy-label') || 'شناسه وام کپی شد';
            var done = function () {
                window.pnToast(label);
            };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(done);
            } else {
                var $ta = $('<textarea>').val(text).css({ position: 'fixed', opacity: 0 }).appendTo('body');
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
            $('#payId').text('#' + Number($b.data('id')).toLocaleString('fa-IR', { useGrouping: false }));
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
                var $ta = $('<textarea>').val(text).css({ position: 'fixed', opacity: 0 }).appendTo('body');
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
            $('#trId').text('#' + Number($b.data('id')).toLocaleString('fa-IR', { useGrouping: false }));
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

    /* =====================================================
       ابزار مشترک: Dropzone (کلیک + درگ‌ودراپ + پیش‌نمایش + اعتبارسنجی)
       ===================================================== */
    window.pnDropzone = function ($dz) {
        var input = $dz.find('input[type=file]')[0], $field = $dz.closest('.f-field'),
            title = $dz.find('.dz-title').text();

        function set(file) {
            if (file && !(/^image\/(png|jpeg)$/.test(file.type) && file.size <= 5 * 1024 * 1024)) {
                $field.addClass('invalid').find('.f-err').text('فقط JPG یا PNG و حداکثر ۵ مگابایت مجاز است.');
                input.value = '';
                return;
            }
            $field.removeClass('invalid');
            if (!file) {
                $dz.removeClass('has-file').find('.dz-thumb').prop('hidden', true).attr('src', '');
                $dz.find('.dz-remove').prop('hidden', true);
                $dz.find('.dz-title').text(title);
                return;
            }
            $dz.addClass('has-file').find('.dz-thumb').attr('src', URL.createObjectURL(file)).prop('hidden', false);
            $dz.find('.dz-remove').prop('hidden', false);
            $dz.find('.dz-title').text(file.name);
        }

        $dz.on('change', 'input[type=file]', function () {
            set(this.files[0]);
        })
            .on('dragover dragenter', function (e) {
                e.preventDefault();
                $dz.addClass('drag');
            })
            .on('dragleave drop', function () {
                $dz.removeClass('drag');
            })
            .on('drop', function (e) {
                e.preventDefault();
                var f = e.originalEvent.dataTransfer.files;
                if (f.length) {
                    input.files = f;
                    set(f[0]);
                }
            })
            .on('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    $(input).trigger('click');
                }
            })
            .on('click', '.dz-remove', function (e) {
                e.preventDefault();
                e.stopPropagation();
                input.value = '';
                set(null);
            });
        $dz.data('reset', function () {
            input.value = '';
            set(null);
        });
    };
    $('.dz-scope .dz').each(function () {
        window.pnDropzone($(this));
    });

    /* =====================================================
       مودال تایید عمومی (حذف / تایید / رد)
       ===================================================== */
    var $cf = $('#confirmModal');
    if ($cf.length) {
        $cf.on('show.bs.modal', function (e) {
            var $b = $(e.relatedTarget);
            if (!$b.length) return;
            var tone = $b.data('confirm-tone') || 'brand';
            $cf.removeClass('tone-danger tone-brand').addClass('tone-' + tone);
            $('#cfTitle').text($b.data('confirm-title'));
            $('#cfText').text($b.data('confirm-text'));
            $('#cfForm').attr('action', $b.data('confirm-action'));
            $('#cfMethod').val($b.data('confirm-method') || 'POST');
            $('#cfOkLabel').text($b.data('confirm-label') || 'تایید');
            $('#cfOk').attr('class', 'btn btn-lg flex-grow-1 ' + (tone === 'danger' ? 'btn-danger-solid' : 'btn-brand'));
            $('#cfReasonBox').toggleClass('d-none', !$b.data('confirm-reason'));
            $('#cfReason').val('');
        });
        $('#cfForm').on('submit', function () {
            $('#cfOk').addClass('loading').prop('disabled', true).find('.spinner-border').removeClass('d-none');
        });
        $cf.on('hidden.bs.modal', function () {
            $('#cfOk').removeClass('loading').prop('disabled', false).find('.spinner-border').addClass('d-none');
        });
    }

    /* =====================================================
       مودال ثبت اطلاعات انتقال امتیاز (فروشنده)
       ===================================================== */
    var $st = $('#sellerTransferModal');
    if ($st.length) {
        var stTimer = null;

        function leftText(sec) {
            var d = Math.floor(sec / 86400), h = Math.floor(sec % 86400 / 3600), m = Math.floor(sec % 3600 / 60);
            var f = function (n) {
                return Number(n).toLocaleString('fa-IR');
            };
            return (d ? f(d) + ' روز و ' : '') + f(h) + ' ساعت' + (d ? '' : ' و ' + f(m) + ' دقیقه');
        }

        $st.on('show.bs.modal', function (e) {
            var $b = $(e.relatedTarget);
            if (!$b.length) return;
            $('#stLoan').text($b.data('title'));
            $('#stId').text('#' + Number($b.data('id')).toLocaleString('fa-IR', { useGrouping: false }));
            $('#stBank').text($b.data('bank-l')).css('background', $b.data('bank-c'));
            $('#stForm').attr('action', $b.data('action'));
            $('#stForm')[0].reset();
            $('#stForm .f-field').removeClass('invalid');
            $('#stForm .dz').each(function () {
                var r = $(this).data('reset');
                r && r();
            });
            var left = +$b.data('deadline') || 0;
            clearInterval(stTimer);
            if (left > 0) {
                $('#stDeadline').prop('hidden', false);
                $('#stLeft').text(leftText(left));
                stTimer = setInterval(function () {
                    left -= 30;
                    $('#stLeft').text(leftText(Math.max(left, 0)));
                }, 30000);
            } else {
                $('#stDeadline').prop('hidden', true);
            }
        });
        $st.on('hidden.bs.modal', function () {
            clearInterval(stTimer);
            $('#stForm button[type=submit]').removeClass('loading').prop('disabled', false).find('.spinner-border').addClass('d-none');
        });

        $('#stNow').on('click', function () {                                           // پر کردن زمان فعلی (تاریخ شمسی)
            try {
                $('#stTime').val(new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }).format(new Date())).trigger('input');
            } catch (er) {
            }
            $('#stTime').closest('.f-field').removeClass('invalid');
        });
        $('#stTrack').on('input', function () {
            this.value = String(this.value).replace(/[۰-۹]/g, function (d) {
                return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d);
            }).replace(/\D/g, '').slice(0, 20);
        });
        $('#stForm').on('input change', '.f-input', function () {
            $(this).closest('.f-field').removeClass('invalid');
        });
        $('#stForm').on('submit', function (e) {
            var bad = [], chk = function (name, ok) {
                var $f = $('#stForm [data-field="' + name + '"]').toggleClass('invalid', !ok);
                if (!ok) bad.push($f);
            };
            chk('time', $.trim($('#stTime').val()).length >= 4);
            chk('track', /^\d{6,20}$/.test($('#stTrack').val()));
            chk('receipt', !!$('#stReceipt')[0].files.length);
            if (bad.length) {
                e.preventDefault();
                bad[0].find('input:not([hidden])').first().trigger('focus');
                return;
            }
            $(this).find('button[type=submit]').addClass('loading').prop('disabled', true).find('.spinner-border').removeClass('d-none');
        });
    }

    /* =====================================================
       درخواست‌های آگهی: مرتب‌سازی
       ===================================================== */
    $('#ofSort').on('change', function () {
        var by = this.value, $list = $('#rqList'), $items = $list.children('.rq-card').get();
        $items.sort(function (a, b) {
            var da = $(a).data(), db = $(b).data();
            return by === 'amount' ? (db.amount - da.amount) : (da.age - db.age);
        });
        $list.append($items);
    });

    /* =====================================================
       رادار وام
       ⚠️ PN_DEMO = true یعنی فعال/غیرفعال کردن رادار بدون بک‌اند شبیه‌سازی می‌شود.
          برای اتصال واقعی false کنید: PATCH /panel/radar/{id}/toggle {active:0|1}
       ===================================================== */
    var PN_DEMO = false;
    var rdFaD = function (s) {
        return String(s).replace(/\d/g, function (d) {
            return '۰۱۲۳۴۵۶۷۸۹'.charAt(d);
        });
    };
    var rdLat = function (s) {
        return String(s || '').replace(/[۰-۹]/g, function (d) {
            return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d);
        });
    };

    /* =====================================================
       شماره شبا: یکدست‌سازی ورودی (تایپ، چسباندن و پر شدن خودکار)
       هر مقدار به شکل «IR + ۲۴ رقم» تبدیل می‌شود؛ فاصله، خط تیره و ارقام فارسی/عربی هم پذیرفته می‌شود.
       دلیل وجود: با maxlength و خواندن مقدار قبل از paste، شباهای قالّب‌دار (IR12 0120 ...) بریده می‌شدند.
       ===================================================== */
    function pnShebaDigits(v) {
        return String(v || '')
            .replace(/[۰-۹]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d); })
            .replace(/[٠-٩]/g, function (d) { return '٠١٢٣٤٥٦٧٨٩'.indexOf(d); })
            .replace(/\D/g, '')
            .slice(0, 24);
    }

    function pnShebaValue(v) {
        var d = pnShebaDigits(v);
        return d ? 'IR' + d : '';
    }

    function pnShebaField($input, onUpdate) {
        $input.on('input', function () {
            var v = pnShebaValue(this.value);
            if (v !== this.value) this.value = v;
            if (onUpdate) onUpdate.call(this, pnShebaDigits(this.value));
        }).on('paste drop', function (e) {
            var ev = e.originalEvent || e,
                dt = e.type === 'paste' ? ev.clipboardData : ev.dataTransfer,
                txt = dt && dt.getData ? dt.getData('text') : '';
            if (!txt) return;                       // متن قابل خواندن نبود → مسیر پیش‌فرض مرورگر
            e.preventDefault();
            this.value = pnShebaValue(txt);
            $(this).trigger('input');
        });
    }

    if ($('#rdRadars').length) {
        var rdSel = 'all', $rdList = $('#rdList');

        function rdApply() {
            var shown = 0, onlyNew = $('#rdOnlyNew').is(':checked');
            $rdList.children('.rd-loan').each(function () {
                var $c = $(this),
                    ok = (rdSel === 'all' || String($c.data('radar')) === String(rdSel)) && (!onlyNew || +$c.data('new') === 1);
                var was = $c.hasClass('d-none');
                $c.toggleClass('d-none', !ok);
                if (ok) {
                    if (was && !reduceMotion) {
                        $c.removeClass('rq-pop');
                        void this.offsetWidth;
                        $c.css('animation-delay', (shown * 70) + 'ms').addClass('rq-pop');
                    }
                    shown++;
                }
            });
            $('#rdCount').text(rdFaD(shown));
            $('#rdEmpty').prop('hidden', shown > 0);
            $('#rdEmptyTitle').text(onlyNew ? 'وام جدیدی وجود ندارد' : 'هنوز وامی پیدا نشده');
        }

        function rdSelect($el) {
            $('#rdRadars .rd-radar').removeClass('active').attr('aria-selected', 'false');
            $el.addClass('active').attr('aria-selected', 'true');
            rdSel = $el.data('radar');
            rdApply();
        }

        $('#rdRadars').on('click', '.rd-radar', function (e) {
            if ($(e.target).closest('.rd-switch, .rd-ic-btn').length) return;
            rdSelect($(this));
        })
            .on('keydown', '.rd-radar', function (e) {
                if ((e.key === 'Enter' || e.key === ' ') && e.target === this) {
                    e.preventDefault();
                    rdSelect($(this));
                }
            });
        $('#rdOnlyNew').on('change', rdApply);

        /* روشن / خاموش کردن رادار */
        $('#rdRadars').on('change', '.rd-switch input', function () {
            var $in = $(this), on = this.checked, $sw = $in.closest('.rd-switch'), $card = $in.closest('.rd-radar');
            $card.toggleClass('off', !on);
            var msg = '«' + $sw.data('name') + '» ' + (on ? 'فعال شد' : 'غیرفعال شد');
            if (PN_DEMO) {
                window.pnToast(msg);
                return;
            }
            $.ajax({
                url: $sw.data('url'),
                method: 'POST',
                data: { _method: 'PATCH', active: on ? 1 : 0 },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            })
                .done(function () {
                    window.pnToast(msg);
                })
                .fail(function () {
                    $in.prop('checked', !on);
                    $card.toggleClass('off', on);
                    window.pnToast('تغییر وضعیت انجام نشد');
                });
        });
    }

    /* مودال رادار جدید / ویرایش */
    var $rd = $('#radarModal');
    if ($rd.length) {
        var rdNum = function (v) {
            return +rdLat(v).replace(/\D/g, '') || 0;
        };
        var rdMil = function (n) {
            return rdFaD(String(Math.round(n / 1e5) / 10).replace('.', '٫'));
        };
        var $rdForm = $('#rdForm');

        function rdSummary() {
            var min = rdNum($('#rdMin').val()), max = rdNum($('#rdMax').val()), mo = rdNum($('#rdMonths').val()),
                rate = $('#rdRate').val(), bank = $('#rdBank').val(), city = $.trim($('#rdCity').val());
            var t = [];
            if (min || max) t.push('مبلغ ' + (min ? rdMil(min) : '۰') + (max ? ' تا ' + rdMil(max) : '+') + ' میلیون');
            if (mo) t.push('حداقل ' + rdFaD(mo) + ' ماه');
            if (rate) t.push('سود تا ' + rdFaD(rate) + '٪');
            if (bank) t.push(bank);
            if (city) t.push(city);
            $('#rdSummaryTags').html($.map(t, function (x) {
                return $('<span>').text(x)[0].outerHTML;
            }).join(''));
            return t.length;
        }

        $rdForm.on('input', '.rd-money', function () {
            var v = rdLat(this.value).replace(/\D/g, '');
            this.value = v ? rdFaD(Number(v).toLocaleString('en-US')) : '';
        })
            .on('input', '.rd-int', function () {
                this.value = rdFaD(rdLat(this.value).replace(/\D/g, '').slice(0, 3));
            })
            .on('input change', '.f-input', function () {
                $(this).closest('.f-field').removeClass('invalid');
                $('#rdFormErr').removeClass('show');
                rdSummary();
            });

        $rd.on('show.bs.modal', function (e) {
            var $b = $(e.relatedTarget), r = $b.length ? $b.data('radar') : null, form = $rdForm[0];
            form.reset();
            $rdForm.find('.f-field').removeClass('invalid');
            $('#rdFormErr').removeClass('show');
            if (r && typeof r === 'object') {                                           // حالت ویرایش
                $('#rdTitle').text('ویرایش رادار');
                $('#rdSubmitLabel').text('ذخیره تغییرات');
                $rdForm.attr('action', $rdForm.data('update-url') + '/' + r.id);
                $('#rdMethod').val('PUT');
                $('#rdNameInput').val(r.name);
                $('#rdMin').val(r.min ? rdFaD(Number(r.min).toLocaleString('en-US')) : '');
                $('#rdMax').val(r.max ? rdFaD(Number(r.max).toLocaleString('en-US')) : '');
                $('#rdMonths').val(r.months ? rdFaD(r.months) : '');
                $('#rdRate').val(r.rate || '');
                $('#rdBank').val(r.bank || '');
                $('#rdCity').val(r.city || '');
                $('#rdNotifySms').prop('checked', !!r.sms);
                $('#rdNotifyPanel').prop('checked', r.panel !== false);
            } else {                                                                    // حالت ساخت
                $('#rdTitle').text('رادار جدید');
                $('#rdSubmitLabel').text('ثبت رادار جدید');
                $rdForm.attr('action', $rdForm.data('create-url'));
                $('#rdMethod').val('POST');
            }
            rdSummary();
        });
        $rd.on('hidden.bs.modal', function () {
            $rdForm.find('button[type=submit]').removeClass('loading').prop('disabled', false).find('.spinner-border').addClass('d-none');
        });

        $rdForm.on('submit', function (e) {
            var bad = [], chk = function (n, ok) {
                var $f = $rdForm.find('[data-field="' + n + '"]').toggleClass('invalid', !ok);
                if (!ok) bad.push($f);
            };
            var min = rdNum($('#rdMin').val()), max = rdNum($('#rdMax').val()), mo = rdNum($('#rdMonths').val());
            chk('name', $.trim($('#rdNameInput').val()).length >= 2);
            chk('min', !(min && max && min > max));
            chk('max', !(min && max && min > max));
            chk('months', !$('#rdMonths').val() || (mo >= 1 && mo <= 120));
            var hasFilter = rdSummary() > 0;
            $('#rdFormErr').toggleClass('show', !hasFilter);
            if (bad.length || !hasFilter) {
                e.preventDefault();
                (bad[0] ? bad[0].find('input').first() : $('#rdMin')).trigger('focus');
                return;
            }
            $rdForm.find('.rd-money, .rd-int').each(function () {
                this.value = rdLat(this.value).replace(/\D/g, '');
            });     // ارسال عدد لاتین ساده به سرور
            $(this).find('button[type=submit]').addClass('loading').prop('disabled', true).find('.spinner-border').removeClass('d-none');
        });
    }

    /* =====================================================
       ویرایش آگهی
       ===================================================== */
    var $ed = $('#adEditForm');
    if ($ed.length) {
        var edDirty = false, edSaving = false, edLeaveHref = null;
        var edFa = function (s) {
            return String(s).replace(/\d/g, function (d) {
                return '۰۱۲۳۴۵۶۷۸۹'.charAt(d);
            });
        };
        var edLat = function (s) {
            return String(s || '').replace(/[۰-۹]/g, function (d) {
                return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d);
            });
        };
        var edNum = function (v) {
            return +edLat(v).replace(/[^\d.]/g, '') || 0;
        };

        /* فرمت ورودی‌ها */
        $ed.on('input', '[data-money]', function () {
            var v = edLat(this.value).replace(/\D/g, '');
            this.value = v ? edFa(Number(v).toLocaleString('en-US')) : '';
        })
            .on('input', '[data-int]', function () {
                this.value = edFa(edLat(this.value).replace(/\D/g, '').slice(0, 3));
            })
            .on('input', '[data-decimal]', function () {
                var v = edLat(this.value).replace(/[٫,]/g, '.').replace(/[^\d.]/g, '').replace(/(\..*)\./g, '$1').slice(0, 5);
                this.value = edFa(v).replace('.', '٫');
            });
        pnShebaField($('#edSheba'));
        $('#edNote').on('input', function () {
            $('#edNoteCount').text(edFa(this.value.length));
        }).trigger('input');

        /* توصیه سیستم: ۵٪ تا ۳۰٪ مبلغ وام */
        function edReco(anim) {
            var a = edNum($('#edAmount').val()), $box = $('#edReco');
            if (!a) {
                $('#edRecoText').text('** تا ** میلیون تومان');
                return;
            }
            var lo = a * 0.05, hi = a * 0.30, mil = hi >= 1e6 && lo >= 1e5;
            var f = mil ? function (v) {
                return edFa(String(Math.round(v / 1e5) / 10).replace('.', '٫'));
            } : function (v) {
                return edFa(Math.round(v).toLocaleString('en-US'));
            };
            $('#edRecoText').text(f(lo) + ' تا ' + f(hi) + (mil ? ' میلیون تومان' : ' تومان'));
            if (anim) {
                $box.removeClass('live');
                void $box[0].offsetWidth;
                $box.addClass('live');
            }
        }

        $('#edAmount').on('input', function () {
            edReco(true);
        });
        edReco(false);

        /* مدارک: تصویر فعلی + جایگزینی */
        function edDz($dz) {
            var input = $dz.find('input[type=file]')[0], $field = $dz.closest('.f-field'),
                src0 = $dz.data('existing-src'), must = +$dz.data('must') === 1;
            var title0 = $dz.data('title'), sub0 = $dz.find('.dz-sub').text();

            function restore() {
                input.value = '';
                $field.removeClass('changed invalid');
                $dz.find('.dz-remove').prop('hidden', true);
                if (src0) {
                    $dz.addClass('has-file').toggleClass('must-replace', must).find('.dz-thumb').attr('src', src0).prop('hidden', false);
                    $dz.find('.dz-title').text('تصویر فعلی');
                } else {
                    $dz.removeClass('has-file must-replace').find('.dz-thumb').prop('hidden', true).attr('src', '');
                    $dz.find('.dz-title').text(title0);
                }
                $dz.find('.dz-sub').text(sub0);
            }

            function set(file) {
                if (!file) {
                    restore();
                    edTrack();
                    return;
                }
                if (!(/^image\/(png|jpeg)$/.test(file.type) && file.size <= 5 * 1024 * 1024)) {
                    input.value = '';
                    $field.addClass('invalid').find('.f-err').text('فقط JPG یا PNG و حداکثر ۵ مگابایت مجاز است.');
                    return;
                }
                $field.removeClass('invalid').addClass('changed');
                $dz.addClass('has-file').removeClass('must-replace').find('.dz-thumb').attr('src', URL.createObjectURL(file)).prop('hidden', false);
                $dz.find('.dz-title').text(file.name);
                $dz.find('.dz-sub').text('تصویر جدید جایگزین تصویر قبلی می‌شود');
                $dz.find('.dz-remove').prop('hidden', false);
                edTrack();
            }

            $dz.on('change', 'input[type=file]', function () {
                set(this.files[0]);
            })
                .on('dragover dragenter', function (e) {
                    e.preventDefault();
                    $dz.addClass('drag');
                }).on('dragleave drop', function () {
                    $dz.removeClass('drag');
                })
                .on('drop', function (e) {
                    e.preventDefault();
                    var f = e.originalEvent.dataTransfer.files;
                    if (f.length) {
                        input.files = f;
                        set(f[0]);
                    }
                })
                .on('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        $(input).trigger('click');
                    }
                })
                .on('click', '.dz-remove', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    set(null);
                });
            $dz.data('restore', restore).data('hasNew', function () {
                return !!input.files.length;
            });
        }

        $('.ed-dz').each(function () {
            edDz($(this));
        });

        /* ردیابی تغییرات */
        $ed.find('.f-input').each(function () {
            $(this).data('init', this.value);
        });

        function edTrack() {
            $ed.find('.f-input').each(function () {
                $(this).closest('.f-field').toggleClass('changed', this.value !== $(this).data('init'));
            });
            $('.ed-dz').each(function () {
                if ($(this).data('hasNew')()) $(this).closest('.f-field').addClass('changed');
            });
            var n = $ed.find('.f-field.changed').length;
            edDirty = n > 0;
            $('#edBar').toggleClass('show', edDirty);
            $('body').toggleClass('ed-dirty', edDirty);
            $('#edChanged').text(edDirty ? edFa(n) + ' مورد تغییر کرده · ذخیره نشده' : 'تغییرات ذخیره نشده');
        }

        $ed.on('input change', '.f-input', function () {
            $(this).closest('.f-field').removeClass('invalid');
            edTrack();
        });

        $('#edReset').on('click', function () {
            $ed[0].reset();
            $ed.find('.f-field').removeClass('invalid changed');
            $('.ed-dz').each(function () {
                $(this).data('restore')();
            });
            $('#edNote').trigger('input');
            edReco(false);
            edTrack();
            window.pnToast('تغییرات بازگردانی شد');
        });

        /* هشدار خروج بدون ذخیره */
        var $leave = $('#leaveModal'), leaveM = $leave.length ? bootstrap.Modal.getOrCreateInstance($leave[0]) : null;
        document.addEventListener('click', function (e) {
            if (!edDirty || edSaving || !leaveM) return;
            var a = e.target.closest && e.target.closest('a[href]');
            if (!a || a.id === 'leaveGo') return;
            var h = a.getAttribute('href') || '';
            if (!h || h.charAt(0) === '#' || /^(javascript|mailto|tel):/i.test(h) || a.target === '_blank' || a.hasAttribute('data-bs-toggle')) return;
            e.preventDefault();
            e.stopPropagation();
            edLeaveHref = a.href;
            leaveM.show();
        }, true);
        $('#leaveGo').on('click', function (e) {
            e.preventDefault();
            edDirty = false;
            window.location.href = edLeaveHref;
        });
        window.addEventListener('beforeunload', function (e) {
            if (edDirty && !edSaving) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        /* اعتبارسنجی و ذخیره */
        $ed.on('submit', function (e) {
            e.preventDefault();
            var bad = [], chk = function (n, ok, msg) {
                var $f = $ed.find('[data-field="' + n + '"]').toggleClass('invalid', !ok),
                    $e = $f.find('.f-err');
                if ($e.length && !$e.data('def')) $e.data('def', $e.text());
                if (!ok) {
                    if (msg) $e.text(msg);
                    bad.push($f);
                } else if ($e.data('def')) {
                    $e.text($e.data('def'));
                }
            };
            var mo = edNum($('#edMonths').val()),
                rate = $('#edRate').val() ? parseFloat(edLat($('#edRate').val()).replace('٫', '.')) : NaN;
            chk('bank', !!$('#edBank').val());
            chk('amount', edNum($('#edAmount').val()) > 0);
            chk('months', mo >= 1 && mo <= 120);
            chk('rate', !isNaN(rate) && rate >= 0 && rate <= 100);
            chk('price', edNum($('#edPrice').val()) > 0);
            chk('sheba', /^IR\d{24}$/.test($('#edSheba').val()),
                'شماره شبا باید ۲۴ رقم بعد از IR باشد (اکنون ' + edFa(pnShebaDigits($('#edSheba').val()).length) + ' رقم).');
            chk('note', $.trim($('#edNote').val()).length >= 10);
            $('.ed-dz').each(function () {
                var $dz = $(this), $f = $dz.closest('.f-field'), has = $dz.data('hasNew')(),
                    existing = !!$dz.data('existing-src'), must = +$dz.data('must') === 1;
                var ok = has || (existing && !must);
                $f.toggleClass('invalid', !ok);
                if (!ok) {
                    $f.find('.f-err').text(must && existing ? 'این مدرک رد شده است؛ تصویر جدید بارگذاری کنید.' : 'مدرک را بارگذاری کنید.');
                    bad.push($f);
                }
            });
            if (bad.length) {
                $('html, body').animate({ scrollTop: bad[0].offset().top - 120 }, 400);
                bad[0].find('input:not([hidden]), select, textarea').first().trigger('focus');
                return;
            }

            var $b = $('#edSave').addClass('loading').prop('disabled', true);
            $b.find('.spinner-border').removeClass('d-none');
            if (PN_DEMO) {                                                                    // دمو: شبیه‌سازی ذخیره
                setTimeout(function () {
                    $b.removeClass('loading').prop('disabled', false).find('.spinner-border').addClass('d-none');
                    $ed.find('.f-input').each(function () {
                        $(this).data('init', this.value);
                    });
                    $('.ed-dz').each(function () {
                        $(this).data('existing-src', $(this).find('.dz-thumb').attr('src') || $(this).data('existing-src'));
                        $(this).attr('data-must', 0).data('must', 0).removeClass('must-replace');
                    });
                    $ed.find('.f-field').removeClass('changed');
                    edDirty = false;
                    edTrack();
                    window.pnToast('تغییرات ذخیره و آگهی برای تایید ارسال شد');
                }, 900);
                return;
            }
            $ed.find('[data-money], [data-int]').each(function () {
                this.value = edLat(this.value).replace(/\D/g, '');
            });          // ارسال عدد لاتین ساده به سرور
            $ed.find('[data-decimal]').each(function () {
                this.value = edLat(this.value).replace('٫', '.');
            });
            edSaving = true;
            this.submit();                                                   // PUT /panel/ads/{id} (multipart)
        });
    }

    /* =====================================================
       تیکت‌های پشتیبانی: ایجاد تیکت
       ===================================================== */
    var tkFa = function (s) {
        return String(s).replace(/\d/g, function (d) {
            return '۰۱۲۳۴۵۶۷۸۹'.charAt(d);
        });
    };
    var $tkf = $('#tkForm');
    if ($tkf.length) {
        $('#tkMessage').on('input', function () {
            $('#tkCount').text(tkFa(this.value.length));
        });
        $tkf.on('input change', '.f-input', function () {
            $(this).closest('.f-field').removeClass('invalid');
        });
        $tkf.on('submit', function (e) {
            e.preventDefault();
            var bad = [], chk = function (n, ok) {
                var $f = $tkf.find('[data-field="' + n + '"]').toggleClass('invalid', !ok);
                if (!ok) bad.push($f);
            };
            chk('subject', $.trim($('#tkSubject').val()).length >= 4);
            chk('message', $.trim($('#tkMessage').val()).length >= 10);
            if ($tkf.find('[data-field="attachment"]').hasClass('invalid')) bad.push($tkf.find('[data-field="attachment"]'));
            if (bad.length) {
                $('html, body').animate({ scrollTop: bad[0].offset().top - 110 }, 350);
                bad[0].find('input:not([hidden]), textarea').first().trigger('focus');
                return;
            }
            var $b = $('#tkSubmit').addClass('loading').prop('disabled', true);
            $b.find('.spinner-border').removeClass('d-none');
            if (PN_DEMO) {
                setTimeout(function () {
                    window.pnToast('تیکت شما ثبت شد');
                    window.location.href = $tkf.data('demo-redirect');
                }, 800);
                return;
            }
            this.submit();                                                         // POST /panel/tickets (multipart)
        });
    }

    /* =====================================================
       تیکت‌های پشتیبانی: گفت‌وگو
       ===================================================== */
    var $tkc = $('#tkMessages');
    if ($tkc.length) {
        var tkBottom = function (smooth) {
            $tkc.stop(true).animate({ scrollTop: $tkc[0].scrollHeight }, smooth && !reduceMotion ? 400 : 0);
        };
        tkBottom(false);

        var $tx = $('#tkText'), $tkr = $('#tkReply');
        var autosize = function () {
            $tx.css('height', 'auto').css('height', Math.min($tx[0].scrollHeight, 140) + 'px');
        };
        $tx.on('input', function () {
            autosize();
            $('#tkErr').removeClass('show');
        });
        $tx.on('keydown', function (e) {                                          // Enter = ارسال (دسکتاپ) | Shift+Enter = خط جدید
            if (e.key === 'Enter' && !e.shiftKey && window.matchMedia('(hover: hover)').matches) {
                e.preventDefault();
                $tkr.trigger('submit');
            }
        });

        /* ضمیمه */
        var tkErr = function (m) {
            $('#tkErr').text(m).addClass('show');
        };
        $('#tkFile').on('change', function () {
            var f = this.files[0];
            $('#tkErr').removeClass('show');
            if (!f) {
                $('#tkAttached').prop('hidden', true);
                return;
            }
            if (!(/^image\/(png|jpeg)$/.test(f.type) && f.size <= 5 * 1024 * 1024)) {
                this.value = '';
                $('#tkAttached').prop('hidden', true);
                tkErr('فقط JPG یا PNG و حداکثر ۵ مگابایت مجاز است.');
                return;
            }
            $('#tkAttachedName').text(f.name);
            $('#tkAttached').prop('hidden', false);
        });
        $('#tkAttachedRemove').on('click', function () {
            $('#tkFile').val('');
            $('#tkAttached').prop('hidden', true);
        });
        $('.tk-attach').on('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $('#tkFile').trigger('click');
            }
        });

        /* ارسال پاسخ */
        $tkr.on('submit', function (e) {
            e.preventDefault();
            var text = $.trim($tx.val()), file = $('#tkFile')[0].files[0];
            if (!text && !file) {
                tkErr('متن پاسخ یا یک فایل ضمیمه وارد کنید.');
                $tx.trigger('focus');
                return;
            }
            var $b = $('#tkSend').addClass('loading').prop('disabled', true);
            $b.find('.spinner-border').removeClass('d-none');
            if (!PN_DEMO) {
                this.submit();
                return;
            }                                // POST /panel/tickets/{id}/reply (multipart)

            setTimeout(function () {                                                // دمو: افزودن پیام به گفت‌وگو
                var now = new Date().toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' });
                var adm = $('body').hasClass('pn-admin'), note = adm && $('#tkInternal').is(':checked');
                var $m = $('<div class="tk-msg me' + (note ? ' note' : '') + '"><b class="tk-who"></b></div>');
                $m.find('.tk-who').text(note ? 'یادداشت داخلی' : (adm ? 'پشتیبانی (شما)' : 'پیام شما'));
                if (text) $('<div class="tk-bubble">').text(text).css('white-space', 'pre-wrap').appendTo($m);
                if (file) $('<div class="tk-file"><span class="tk-file-name"></span></div>').find('.tk-file-name').append($('<span dir="ltr">').text(file.name)).end().appendTo($m);
                $('<small class="tk-time">').text(now).appendTo($m);
                $m.appendTo($tkc);
                $tx.val('').trigger('input');
                $('#tkFile').val('');
                $('#tkAttached').prop('hidden', true);
                if (!note) $('#tkStatus').attr('class', 'pn-pill ' + (adm ? 'approved' : 'pending')).text(adm ? 'پاسخ داده شده' : 'در انتظار پاسخ');
                $b.removeClass('loading').prop('disabled', false).find('.spinner-border').addClass('d-none');
                tkBottom(true);
                $tx.trigger('focus');
            }, 500);
        });
    }

    /* =====================================================
       اطلاعات هویتی (احراز هویت)
       ===================================================== */
    var $vf = $('#vfForm');
    if ($vf.length) {
        var vfFa = function (s) {
            return String(s).replace(/\d/g, function (d) {
                return '۰۱۲۳۴۵۶۷۸۹'.charAt(d);
            });
        };
        var vfLat = function (s) {
            return String(s || '').replace(/[۰-۹]/g, function (d) {
                return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d);
            });
        };

        /* اعتبارسنجی کد ملی و شبا (استاندارد IBAN: mod 97) */
        function vfNid(c) {
            if (!/^\d{10}$/.test(c) || /^(\d)\1{9}$/.test(c)) return false;
            var s = 0;
            for (var i = 0; i < 9; i++) s += (+c.charAt(i)) * (10 - i);
            var r = s % 11, k = +c.charAt(9);
            return r < 2 ? k === r : k === 11 - r;
        }

        function vfIban(s) {
            if (!/^IR\d{24}$/.test(s)) return false;
            var r = s.slice(4) + '1827' + s.slice(2, 4), rem = 0;
            for (var i = 0; i < r.length; i++) rem = (rem * 10 + (+r.charAt(i))) % 97;
            return rem === 1;
        }


        function vfShebaMsg() {
            var s = String($('#vfSheba').val() || '')
                .toUpperCase()
                .replace(/\s+/g, '');

            var digits = pnShebaDigits(s);

            if (digits.length < 24) {
                return 'شماره شبا باید ۲۴ رقم بعد از IR باشد (اکنون ' +
                    vfFa(digits.length) + ' رقم).';
            }

            if (digits.length !== 24 || !/^IR\d{24}$/.test(s)) {
                return 'فرمت شماره شبا صحیح نیست.';
            }

            return 'شماره شبا معتبر نیست؛ لطفاً شماره را از روی اطلاعات بانکی بررسی کنید.';
        }

        var vfBanks = {
            '010': 'مرکزی',
            '011': 'صنعت و معدن',
            '012': 'ملت',
            '013': 'رفاه',
            '014': 'مسکن',
            '015': 'سپه',
            '016': 'کشاورزی',
            '017': 'ملی',
            '018': 'تجارت',
            '019': 'صادرات',
            '020': 'توسعه صادرات',
            '021': 'پست بانک',
            '051': 'موسسه اعتباری توسعه',
            '053': 'کارآفرین',
            '054': 'پارسیان',
            '055': 'اقتصاد نوین',
            '056': 'سامان',
            '057': 'پاسارگاد',
            '058': 'سرمایه',
            '059': 'سینا',
            '060': 'قرض‌الحسنه مهر ایران',
            '061': 'شهر',
            '062': 'آینده',
            '063': 'انصار',
            '064': 'گردشگری',
            '065': 'حکمت ایرانیان',
            '066': 'دی',
            '069': 'ایران زمین',
            '070': 'رسالت',
            '075': 'ایران ونزوئلا'
        };

        /* پیشرفت تکمیل */
        function vfProgress() {
            var done = {
                base: $.trim($('#vfFirst').val()).length >= 2 && $.trim($('#vfLast').val()).length >= 2 && vfNid(vfLat($('#vfNid').val())),
                birth: !!$('#vfBirthVal').val(), sheba: vfIban($('#vfSheba').val()),
                id: !!$('#vf_id_card')[0].files.length, selfie: !!$('#vf_selfie')[0].files.length
            }, n = 0;
            $('#vfProgress .pn-steps li').each(function () {
                var ok = !!done[$(this).data('step')];
                $(this).toggleClass('done', ok);
                if (ok) n++;
            });
            var pct = Math.round(n / 5 * 100);
            $('#vfBar').css('--w', pct + '%');
            $('#vfPct').text(vfFa(pct) + '٪');
            $('#vfProgress').attr('aria-valuenow', pct);
        }

        /* ورودی‌ها */
        $('#vfNid').on('input', function () {
            this.value = vfLat(this.value).replace(/\D/g, '').slice(0, 10);
        });
        var vfShebaInfo = function (d) {
            var bank = d.length >= 7 ? vfBanks[d.substr(2, 3)] : '';                      // رقم‌های ۵ تا ۷ شبا = کد بانک
            $('#vfBankChip').text(bank ? 'بانک ' + bank : '').prop('hidden', !bank);
            $('#vfShebaCount').text(vfFa(d.length) + ' از ' + vfFa(24)).toggleClass('ok', d.length === 24);
        };
        pnShebaField($('#vfSheba'), vfShebaInfo);
        vfShebaInfo(pnShebaDigits($('#vfSheba').val()));
        $vf.on('input change', '.f-input', function () {
            $(this).closest('.f-field').removeClass('invalid');
            vfProgress();
        });
        $vf.on('change', 'input[type=file]', vfProgress).on('click', '.dz-remove', function () {
            setTimeout(vfProgress, 0);
        });

        /* انتخاب تاریخ تولد شمسی (سال / ماه / روز) */
        var vfMonths = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
        var curY = 1405;
        try {
            curY = +vfLat(new Intl.DateTimeFormat('en-u-ca-persian', { year: 'numeric' }).formatToParts(new Date()).filter(function (p) {
                return p.type === 'year';
            })[0].value).replace(/\D/g, '');
        } catch (er) {
        }
        var $y = $('#vfY'), $m = $('#vfM'), $d = $('#vfD'), $pop = $('#vfPop');
        for (var yy = curY - 18; yy >= curY - 100; yy--) $y.append($('<option>').val(yy).text(vfFa(yy)));
        $.each(vfMonths, function (i, n) {
            $m.append($('<option>').val(i + 1).text(n));
        });

        function vfDays() {
            var mo = +$m.val(), max = mo <= 6 ? 31 : (mo <= 11 ? 30 : 29), cur = +$d.val() || 1;
            $d.empty();
            for (var i = 1; i <= max; i++) $d.append($('<option>').val(i).text(vfFa(i)));
            $d.val(Math.min(cur, max));
        }

        $m.on('change', vfDays);
        vfDays();

        function vfPop(open) {
            $pop.prop('hidden', !open);
            $('#vfBirth').attr('aria-expanded', open);
        }

        $('#vfBirth, #vfBirthBtn').on('click', function (e) {
            e.stopPropagation();
            vfPop($pop.prop('hidden'));
        });
        $pop.on('click', function (e) {
            e.stopPropagation();
        });
        $(document).on('click', function () {
            vfPop(false);
        }).on('keydown', function (e) {
            if (e.key === 'Escape') vfPop(false);
        });
        $('#vfPopCancel').on('click', function () {
            vfPop(false);
        });
        $('#vfPopOk').on('click', function () {
            var mm = ('0' + $m.val()).slice(-2), dd = ('0' + $d.val()).slice(-2);
            $('#vfBirthVal').val($y.val() + '-' + mm + '-' + dd);
            $('#vfBirth').val(vfFa($y.val() + ' – ' + mm + ' – ' + dd));
            $('[data-field="birth"]').removeClass('invalid');
            vfPop(false);
            vfProgress();
        });

        /* ثبت */
        $vf.on('submit', function (e) {
            e.preventDefault();
            var bad = [], chk = function (n, ok, msg) {
                var $f = $vf.find('[data-field="' + n + '"]').toggleClass('invalid', !ok),
                    $e = $f.find('.f-err');
                if ($e.length && !$e.data('def')) $e.data('def', $e.text());
                if (!ok) {
                    if (msg) $e.text(msg);
                    bad.push($f);
                } else if ($e.data('def')) {
                    $e.text($e.data('def'));
                }
            };
            var nameOk = function (v) {
                return /^[\u0600-\u06FF\u200c\s]{2,40}$/.test($.trim(v));
            };
            chk('first', nameOk($('#vfFirst').val()));
            chk('last', nameOk($('#vfLast').val()));
            chk('nid', vfNid(vfLat($('#vfNid').val())));
            chk('birth', !!$('#vfBirthVal').val());
            chk('sheba', vfIban($('#vfSheba').val()), vfShebaMsg());
            chk('selfie', !!$('#vf_selfie')[0].files.length);
            chk('id_card', !!$('#vf_id_card')[0].files.length);
            $vf.find('.dz').each(function () {
                var $f = $(this).closest('.f-field');
                if ($f.hasClass('invalid') && !$(this).find('input')[0].files.length) $f.find('.f-err').text($(this).find('.dz-title').text() + ' را بارگذاری کنید.');
            });
            if (bad.length) {
                $('html, body').animate({ scrollTop: bad[0].offset().top - 110 }, 350);
                bad[0].find('input:not([hidden]), select').first().trigger('focus');
                return;
            }
            var $b = $('#vfSubmit').addClass('loading').prop('disabled', true);
            $b.find('.spinner-border').removeClass('d-none');
            if (PN_DEMO) {                                                                // دمو: شبیه‌سازی ارسال
                setTimeout(function () {
                    $b.removeClass('loading').prop('disabled', false).find('.spinner-border').addClass('d-none');
                    bootstrap.Modal.getOrCreateInstance($('#vfDone')[0]).show();
                }, 900);
                return;
            }
            this.submit();                                                                // POST /panel/verify (multipart)
        });
        vfProgress();
    }

    /* =====================================================
       پرداخت های من
       ===================================================== */
    var $pmExport = $('#pmExport');
    if ($pmExport.length) {
        $pmExport.on('click', function () {                                           // خروجی CSV از ردیف‌های قابل مشاهده
            var rows = [['شناسه تراکنش', 'شناسه وام', 'بانک', 'مبلغ (تومان)', 'زمان پرداخت', 'وضعیت']];
            $('#rqList .rq-card:not(.d-none)').each(function () {
                rows.push(String($(this).data('csv')).split('|'));
            });
            if (rows.length === 1) {
                window.pnToast('موردی برای خروجی وجود ندارد');
                return;
            }
            var csv = '\ufeff' + rows.map(function (r) {
                return r.map(function (c) {
                    return '"' + String(c).replace(/"/g, '""') + '"';
                }).join(',');
            }).join('\r\n');
            var a = document.createElement('a');
            a.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
            a.download = 'payments.csv';
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.pnToast('گزارش دانلود شد');
        });
    }
    var $rc = $('#receiptModal');
    if ($rc.length) {
        $rc.on('show.bs.modal', function (e) {
            var $b = $(e.relatedTarget);
            if (!$b.length) return;
            $('#rcAmount').text($b.data('amount'));
            $('#rcLoan').text($b.data('title'));
            $('#rcLoanId').text($b.data('loan'));
            $('#rcDate').text($b.data('date'));
            $('#rcTx').text($b.data('tx')).attr('dir', 'ltr');
            $('#rcGateway').text($b.data('gateway'));
            $('#rcStatus').attr('class', 'pn-pill ' + $b.data('status-class')).text($b.data('status'));
        });
        $('#rcTx').on('click keydown', function (e) {
            if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') return;
            e.preventDefault();
            var t = $(this).text();
            if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(t).then(function () {
                window.pnToast('شماره تراکنش کپی شد');
            });
        });
        $('#rcPrint').on('click', function () {
            $('body').addClass('pn-print-receipt');
            window.print();
        });
        window.addEventListener('afterprint', function () {
            $('body').removeClass('pn-print-receipt');
        });
    }

    /* =====================================================
       آگهی های برگزیده
       ===================================================== */
    var $fvGrid = $('#fvGrid');
    if ($fvGrid.length) {
        var fvGroup = 'all', fvQ = '';
        var fvNorm = function (s) {
            return String(s || '').replace(/[۰-۹]/g, function (d) {
                return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d);
            }).replace(/[,،٬\s]+/g, '').toLowerCase();
        };
        var fvFa = function (n) {
            return String(n).replace(/\d/g, function (d) {
                return '۰۱۲۳۴۵۶۷۸۹'.charAt(d);
            });
        };

        function fvRender() {
            var cnt = { all: 0, active: 0, off: 0 }, shown = 0;
            $fvGrid.children('.fv-item').each(function () {
                var $i = $(this), removed = $i.attr('data-removed') === '1', grp = $i.data('group');
                if (!removed) {
                    cnt.all++;
                    cnt[grp]++;
                }
                var ok = !removed && (fvGroup === 'all' || grp === fvGroup) && (!fvQ || fvNorm($i.data('search')).indexOf(fvQ) > -1);
                var was = $i.hasClass('d-none');
                $i.toggleClass('d-none', !ok);
                if (ok) {
                    if (was && !reduceMotion) {
                        $i.removeClass('rq-pop');
                        void this.offsetWidth;
                        $i.css('animation-delay', (shown * 60) + 'ms').addClass('rq-pop');
                    }
                    shown++;
                }
            });
            $('[data-count]').each(function () {
                $(this).text(fvFa(cnt[$(this).data('count')] || 0));
            });
            var none = cnt.all === 0;
            $('#fvEmpty').prop('hidden', shown > 0);
            $('#fvEmptyTitle').text(none ? 'هنوز آگهی‌ای ذخیره نکرده‌اید' : 'آگهی‌ای پیدا نشد');
            $('#fvEmptyText').text(none ? 'با زدن دکمه نشان‌گذاری روی هر آگهی، آن را اینجا پیدا می‌کنید.' : 'فیلتر یا عبارت جستجو را تغییر دهید.');
        }

        $('.rq-tab[data-fgroup]').on('click', function () {
            $('.rq-tab[data-fgroup]').removeClass('active').attr('aria-selected', 'false');
            $(this).addClass('active').attr('aria-selected', 'true');
            fvGroup = $(this).data('fgroup');
            fvRender();
        });
        var fvT;
        $('#fvSearch').on('input', function () {
            var v = this.value;
            clearTimeout(fvT);
            fvT = setTimeout(function () {
                fvQ = fvNorm(v);
                fvRender();
            }, 180);
        });
        $('#fvSort').on('change', function () {
            var by = this.value, items = $fvGrid.children('.fv-item').get();
            items.sort(function (a, b) {
                var da = $(a).data(), db = $(b).data();
                return by === 'amount-asc' ? da.amount - db.amount : by === 'amount-desc' ? db.amount - da.amount : by === 'rate' ? da.rate - db.rate : da.age - db.age;
            });
            $fvGrid.append(items);
        });

        /* حذف از برگزیده‌ها با امکان بازگردانی (Undo) */
        $fvGrid.on('click', '.fv-remove', function () {
            var $i = $(this).closest('.fv-item');
            $i.addClass('removing');
            setTimeout(function () {
                $i.attr('data-removed', '1').removeClass('removing');
                fvRender();
            }, reduceMotion ? 0 : 320);
            window.pnToast('از برگزیده‌ها حذف شد', {
                action: 'بازگردانی', duration: 5000,
                onAction: function () {
                    $i.removeAttr('data-removed');
                    fvRender();
                },
                onClose: function (undone) {
                    if (undone || PN_DEMO) return;                                          // حذف واقعی: DELETE /panel/favorites/{id}
                    $.ajax({
                        url: $i.data('remove-url'),
                        method: 'POST',
                        data: { _method: 'DELETE' },
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                    });
                }
            });
        });
        fvRender();
    }

    /* =====================================================
       پنل مدیریت / پشتیبان
       ===================================================== */
    var admFa = function (s) {
        return String(s).replace(/\d/g, function (d) {
            return '۰۱۲۳۴۵۶۷۸۹'.charAt(d);
        });
    };
    var admNorm = function (s) {
        return String(s || '').replace(/[۰-۹]/g, function (d) {
            return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d);
        }).replace(/[,،٬\s]+/g, '').toLowerCase();
    };

    /* ---------- جدول عمومی: تب، جستجو، فیلتر، مرتب‌سازی، صفحه‌بندی ---------- */
    function pnTable($root) {
        var size = +$root.data('page-size') || 8, $rows = $root.find('tbody tr'),
            tab = $root.find('.rq-tab.active').data('tgroup') || 'all';
        var q = '', filters = {}, sortKey = null, sortDir = 1, page = 1, matched = [];
        var grp = function ($r) {
            return String($r.attr('data-groups') || $r.attr('data-group') || '').split(' ');
        };

        function counts() {
            $root.find('.rq-tab[data-tgroup]').each(function () {
                var g = $(this).data('tgroup');
                var n = g === 'all' ? $rows.length : $rows.filter(function () {
                    return grp($(this)).indexOf(g) > -1;
                }).length;
                $(this).find('.rq-count').text(admFa(n));
            });
        }

        function render(animate) {
            matched = $rows.filter(function () {
                var $r = $(this);
                if (tab !== 'all' && grp($r).indexOf(tab) < 0) return false;
                if (q && admNorm($r.attr('data-search')).indexOf(q) < 0) return false;
                for (var k in filters) if (filters[k] && String($r.attr('data-' + k)) !== filters[k]) return false;
                return true;
            }).get();
            if (sortKey) matched.sort(function (a, b) {
                return (parseFloat($(a).attr('data-' + sortKey)) - parseFloat($(b).attr('data-' + sortKey))) * sortDir;
            });
            var pages = Math.max(1, Math.ceil(matched.length / size));
            page = Math.min(page, pages);
            var from = (page - 1) * size, show = matched.slice(from, from + size);
            $rows.addClass('d-none').removeClass('pop');
            $.each(show, function (i, r) {
                var $r = $(r).removeClass('d-none');
                $r.closest('tbody').append(r);
                if (animate && !reduceMotion) {
                    $r.addClass('pop').find('td').css('animation-delay', (i * 40) + 'ms');
                }
            });
            $root.find('[data-tempty]').prop('hidden', matched.length > 0);
            $root.find('.at-scroll').toggle(matched.length > 0);
            $root.find('[data-tinfo]').text(matched.length ? 'نمایش ' + admFa(from + 1) + ' تا ' + admFa(from + show.length) + ' از ' + admFa(matched.length) + ' مورد' : '');
            var $p = $root.find('[data-tpager]').empty();
            if (pages > 1) {
                var b = function (label, p, dis, act, aria) {
                    return $('<button type="button">').text(label).prop('disabled', dis).toggleClass('active', !!act).attr('aria-label', aria || null).data('p', p).appendTo($p);
                };
                b('›', Math.max(1, page - 1), page <= 1, false, 'قبلی');                         // در RTL: قبلی سمت راست
                for (var i = 1; i <= pages; i++) b(admFa(i), i, false, i === page);
                b('‹', Math.min(pages, page + 1), page >= pages, false, 'بعدی');
            }
        }

        $root.on('click', '.rq-tab[data-tgroup]', function () {
            $root.find('.rq-tab[data-tgroup]').removeClass('active').attr('aria-selected', 'false');
            $(this).addClass('active').attr('aria-selected', 'true');
            tab = $(this).data('tgroup');
            page = 1;
            render(true);
        });
        var t;
        $root.on('input', '[data-tsearch]', function () {
            var v = this.value;
            clearTimeout(t);
            t = setTimeout(function () {
                q = admNorm(v);
                page = 1;
                render(true);
            }, 180);
        });
        $root.on('change', '[data-tfilter]', function () {
            filters[$(this).data('tfilter')] = this.value;
            page = 1;
            render(true);
        });
        $root.on('click', 'th[data-sort]', function () {
            var k = $(this).data('sort');
            sortDir = sortKey === k ? -sortDir : 1;
            sortKey = k;
            $root.find('th[data-sort]').removeClass('asc desc');
            $(this).addClass(sortDir === 1 ? 'asc' : 'desc');
            render(true);
        });
        $root.on('click', '[data-tpager] button', function () {
            page = +$(this).data('p');
            render(true);
            $('html, body').animate({ scrollTop: $root.offset().top - 100 }, 250);
        });
        $root.on('table:refresh', function () {
            counts();
            render(false);
        });
        $root.data('matched', function () {
            return matched;
        });
        counts();
        render(false);
    }

    $('[data-table]').each(function () {
        pnTable($(this));
    });

    /* خروجی CSV از نتیجه فیلترشده */
    var csvHeads = {
        users: ['شناسه', 'نام', 'موبایل', 'کد ملی', 'احراز هویت', 'وضعیت حساب', 'تاریخ عضویت'],
        finance: ['شماره تراکنش', 'نوع', 'کاربر', 'شناسه وام', 'مبلغ', 'کمیسیون', 'وضعیت', 'زمان']
    };
    $('[data-texport]').on('click', function () {
        var rows = [csvHeads[$(this).data('texport')] || []], m = $('[data-table]').first().data('matched')();
        $.each(m, function (_, r) {
            rows.push(String($(r).attr('data-csv')).split('|'));
        });
        if (rows.length === 1) {
            window.pnToast('موردی برای خروجی وجود ندارد');
            return;
        }
        var csv = '\ufeff' + rows.map(function (r) {
            return r.map(function (c) {
                return '"' + String(c).replace(/"/g, '""') + '"';
            }).join(',');
        }).join('\r\n');
        var a = document.createElement('a');
        a.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
        a.download = $(this).data('texport') + '.csv';
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.pnToast('فایل خروجی دانلود شد');
    });

    /* تب‌های داخل صفحه (جزئیات کاربر) */
    $(document).on('click', '[data-tabs] [data-tab-target]', function () {
        var $w = $(this).closest('[data-tabs]'), k = $(this).data('tab-target');
        $w.find('[data-tab-target]').removeClass('active').attr('aria-selected', 'false');
        $(this).addClass('active').attr('aria-selected', 'true');
        $w.find('[data-tab-pane]').prop('hidden', true).filter('[data-tab-pane="' + k + '"]').prop('hidden', false);
    });

    /* Lightbox مدارک */
    window.pnLightbox = function (src) {
        var $lb = $('<div class="pn-lightbox" role="dialog" aria-label="مشاهده مدرک"><button type="button" class="lb-close" aria-label="بستن"><svg viewBox="0 0 24 24" class="pn-ic"><path d="M6 6l12 12M18 6L6 18"/></svg></button><img alt=""></div>');
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
            $(document).off('keydown.lb2');
        };
        $lb.on('click', function (e) {
            if ($(e.target).closest('.lb-close').length || e.target === this) close();
        });
        $lb.find('img').on('click', function () {
            $lb.toggleClass('zoomed');
        });
        $(document).on('keydown.lb2', function (e) {
            if (e.key === 'Escape') {
                e.stopPropagation();
                close();
            }
        });
    };
    $(document).on('click', '.at-doc img, img.zoomable', function () {
        var s = $(this).attr('data-full') || $(this).attr('src');
        if (s) window.pnLightbox(s);
    });

    /* به‌روزرسانی ردیف جدول بعد از اقدام مدیر (دمو: سمت کاربر؛ واقعی: بعد از ریلود) */
    function admSetRow($tr, groups, pillClass, pillText) {
        $tr.attr('data-groups', groups).removeAttr('data-group').find('.pn-pill').first().attr('class', 'pn-pill ' + pillClass).text(pillText);
        $tr.closest('[data-table]').trigger('table:refresh');
    }

    var admBusy = function ($b, on) {
        $b.toggleClass('loading', on).prop('disabled', on).find('.spinner-border').toggleClass('d-none', !on);
    };

    /* سوئیچ «آگهی ویژه» در جدول */
    $(document).on('change', '.rd-switch[data-switch-group] input', function () {
        var $in = $(this), on = this.checked, $sw = $in.closest('.rd-switch'), $tr = $in.closest('tr'),
            g = $sw.data('switch-group');
        var gs = ($tr.attr('data-groups') || '').split(' ').filter(function (x) {
            return x && x !== g;
        });
        if (on) gs.push(g);
        $tr.attr('data-groups', gs.join(' ')).closest('[data-table]').trigger('table:refresh');
        var msg = $sw.data('name') + (on ? ' به‌عنوان آگهی ویژه ثبت شد' : ' از آگهی‌های ویژه خارج شد');
        if (PN_DEMO) {
            window.pnToast(msg);
            return;
        }
        $.ajax({
            url: $sw.data('url'),
            method: 'POST',
            data: { _method: 'PATCH', featured: on ? 1 : 0 },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        })
            .done(function () {
                window.pnToast(msg);
            }).fail(function () {
                $in.prop('checked', !on);
                window.pnToast('تغییر انجام نشد');
            });
    });

    /* ---------- بررسی مدارک احراز هویت ---------- */
    var $kyc = $('#kycModal');
    if ($kyc.length) {
        var $kycRow = null, kycMode = 'approve';

        function kycMode_(m) {
            kycMode = m;
            var rej = m === 'reject';
            $('#kycRejectBox, #kycRejectActions').toggleClass('d-none', !rej);
            $('#kycActions, #kycChecks').toggleClass('d-none', rej);
        }

        function kycUpdate() {
            $('#kycApprove').prop('disabled', $('#kycChecks input:checked').length < 3);
        }

        var kycStatusTxt = { pending: 'در انتظار بررسی', approved: 'تایید شده', rejected: 'رد شده' };

        $kyc.on('show.bs.modal', function (e) {
            var $b = $(e.relatedTarget);
            if (!$b.length) return;
            $kycRow = $b.closest('tr');
            $('#kycName').text($b.data('name'));
            $('#kycSent').text('ارسال شده ' + $b.data('sent'));
            $('#kycNid').text($b.data('nid'));
            $('#kycBirth').text($b.data('birth'));
            $('#kycMobile').text($b.data('mobile'));
            $('#kycSheba').text($b.data('sheba'));
            $('#kycBank').text($b.data('bank'));
            $('#kycSelfie').attr('src', $b.data('selfie'));
            $('#kycCard').attr('src', $b.data('idcard'));
            $('#kycForm').data('id', $b.data('id'));
            $('#kycProfile').attr('href', $b.data('profile'));
            $('#kycChecks input').prop('checked', false);
            $('#kycReasonSel').val('');
            $('#kycReason').val('');
            $('#kycRejectBox .f-field').removeClass('invalid');
            kycMode_('approve');
            kycUpdate();

            var hasDocs = String($b.data('has-docs')) === '1';
            $('#kycNoDocs').prop('hidden', hasDocs);
            $('.kyc-docs').toggle(hasDocs);

            var st = String($b.data('status')), done = st !== 'pending';
            $('#kycActions, #kycChecks').toggleClass('d-none', done);
            $('#kycReviewed').prop('hidden', !done);
            $('#kycStatusPill').attr('class', 'pn-pill ' + st).text(kycStatusTxt[st] || st);
            $('#kycReviewer').text($b.data('reviewer') || '—');
            $('#kycReviewedAt').text($b.data('reviewed') || '—');
            var reason = String($b.data('reason') || '');
            $('#kycReasonWrap').prop('hidden', !reason);
            $('#kycReasonShown').text(reason);
            $('.kyc-result').prop('hidden', !done);
        });
        $('#kycChecks').on('change', 'input', kycUpdate);
        $('#kycRejectBtn').on('click', function () {
            kycMode_('reject');
        });
        $('#kycBack').on('click', function () {
            kycMode_('approve');
        });
        $('#kycApprove').on('click', function () {
            $('#kycForm').attr('action', '/adminPanel/verifications/' + $('#kycForm').data('id') + '/approve');
            kycMode = 'approve';
        });
        $('#kycRejectGo').on('click', function () {
            $('#kycForm').attr('action', '/adminPanel/verifications/' + $('#kycForm').data('id') + '/reject');
            kycMode = 'reject';
        });
        $('#kycForm').on('submit', function (e) {
            var rej = !$('#kycRejectBox').hasClass('d-none');
            if (rej) {
                var sel = $('#kycReasonSel').val(), txt = $.trim($('#kycReason').val());
                if (!sel && !txt) {
                    e.preventDefault();
                    $('#kycRejectBox .f-field').addClass('invalid');
                    return;
                }
                $('#kycReason').val((sel ? sel + (txt ? ' — ' : '') : '') + txt);
            } else if ($('#kycChecks input:checked').length < 3) {
                e.preventDefault();
                return;
            }
            /* تایید/رد روی سرور ثبت می‌شود و کاربر بلافاصله از وضعیت جدیدش مطلع می‌شود */
            admBusy($(rej ? '#kycRejectGo' : '#kycApprove'), true);
        });
    }

    /* ---------- بررسی آگهی: تایید و انتشار / رد / ویژه ---------- */
    var $ar = $('#adReviewModal');
    if ($ar.length) {
        var $arRow = null, $arBtn = null;
        var arStatusTxt = {
            published: 'منتشر شده',
            transfer: 'در حال معامله',
            completed: 'تکمیل شده',
            rejected: 'رد شده'
        };

        function arMode(rej) {
            $('#arRejectBox, #arRejectActions').toggleClass('d-none', !rej);
            $('#arActions, #arFeatureBox').toggleClass('d-none', rej);
        }

        $ar.on('show.bs.modal', function (e) {
            var $b = $(e.relatedTarget);
            if (!$b.length) return;
            $arBtn = $b;
            $arRow = $b.closest('tr');
            $('#arTitle').text($b.data('title'));
            $('#arId').text('#' + admFa($b.data('id')));
            $('#arSeller').text($b.data('seller'));
            $('#arCity').text($b.data('city'));
            $('#arBank').text('بانک ' + $b.data('bank'));
            $('#arAmount').text($b.data('amount'));
            $('#arPrice').text($b.data('price'));
            $('#arMonths').text($b.data('months'));
            $('#arRate').text($b.data('rate'));
            $('#arSheba').text($b.data('sheba'));
            $('#arNote').text($b.data('note'));
            /* مدرک بارگذاری‌شده یا پیام «ثبت نشده» */
            $.each([[$('#arDoc1'), 'تصویر موجودی/امتیاز وام'], [$('#arDoc2'), 'کارت ملی فروشنده']], function (_, it) {
                var $img = it[0], src = String($b.data($img.attr('id') === 'arDoc1' ? 'doc1' : 'doc2') || '');
                $img.prop('hidden', !src).attr('src', src);
                $img.closest('.at-doc').toggleClass('doc-empty', !src).find('figcaption').text(src ? it[1] : 'مدرک ثبت نشده');
            });
            var ratio = (+$b.data('price-raw')) / (+$b.data('amount-raw')) * 100, ok = ratio >= 5 && ratio <= 30;
            $('#arCheck').attr('class', 'ar-price-check ' + (ok ? 'ok' : 'warn')).html('').append($('<span>').text(ok ? '✓ قیمت فروش (' + admFa(Math.round(ratio)) + '٪ مبلغ وام) در بازه توصیه سیستم (۵٪ تا ۳۰٪) است.' : '⚠ قیمت فروش ' + admFa(Math.round(ratio)) + '٪ مبلغ وام است و خارج از بازه توصیه (۵٪ تا ۳۰٪) قرار دارد.'));
            $('#arFeatured').prop('checked', +$b.data('featured') === 1);
            $('#arDays').toggleClass('d-none', +$b.data('featured') !== 1);
            $('#arReasonSel').val('');
            $('#arReason').val('');
            $('#arRejectBox .f-field').removeClass('invalid');
            var st = $b.data('status'), pending = st === 'pending', isAdm = +$b.data('adminPanel') === 1;
            $('#arForm').data('id', $b.data('id'));
            arMode(false);
            $('#arActions').toggleClass('d-none', !pending);
            $('#arFeatureBox').toggleClass('d-none', !(pending && isAdm));
            $('#arReadonly').prop('hidden', pending);
            $('#arStatusTxt').text(arStatusTxt[st] || '');
        });
        $('#arFeatured').on('change', function () {
            $('#arDays').toggleClass('d-none', !this.checked);
        });
        $('#arRejectBtn').on('click', function () {
            arMode(true);
        });
        $('#arBack').on('click', function () {
            arMode(false);
        });
        $('#arApprove').on('click', function () {
            $('#arForm').attr('action', '/adminPanel/ads/' + $('#arForm').data('id') + '/approve');
        });
        $('#arRejectGo').on('click', function () {
            $('#arForm').attr('action', '/adminPanel/ads/' + $('#arForm').data('id') + '/reject');
        });
        $('#arForm').on('submit', function (e) {
            var rej = !$('#arRejectBox').hasClass('d-none');
            if (rej) {
                var sel = $('#arReasonSel').val(), txt = $.trim($('#arReason').val());
                if (!sel && !txt) {
                    e.preventDefault();
                    $('#arRejectBox .f-field').addClass('invalid');
                    return;
                }
                $('#arReason').val((sel ? sel + (txt ? ' — ' : '') : '') + txt);
            }
            var $b = $(rej ? '#arRejectGo' : '#arApprove');
            if (!PN_DEMO) {
                admBusy($b, true);
                return;
            }
            e.preventDefault();
            admBusy($b, true);
            setTimeout(function () {
                admBusy($b, false);
                bootstrap.Modal.getInstance($ar[0]).hide();
                var feat = !rej && $('#arFeatured').is(':checked');
                if ($arRow) {
                    admSetRow($arRow, rej ? 'rejected' : 'published' + (feat ? ' featured' : ''), rej ? 'rejected' : 'approved', rej ? 'رد شده' : 'منتشر شده');
                    $arRow.find('.at-btn').removeClass('btn-brand').addClass('btn-ghost').text('مشاهده').attr('data-status', rej ? 'rejected' : 'published').data('status', rej ? 'rejected' : 'published');
                    $arRow.find('.rd-switch input').prop('checked', feat);
                }
                window.pnToast(rej ? 'آگهی رد شد و به کاربر اطلاع داده شد' : (feat ? 'آگهی منتشر و به‌عنوان ویژه ثبت شد' : 'آگهی منتشر شد'));
            }, 800);
        });
    }

    /* ---------- ثبت واریز به فروشنده ---------- */
    var $po = $('#payoutModal');
    if ($po.length) {
        var $poRow = null;
        $po.on('show.bs.modal', function (e) {
            var $b = $(e.relatedTarget);
            if (!$b.length) return;
            $poRow = $b.closest('tr');
            $('#poSeller').text($b.data('seller'));
            $('#poLoan').text($b.data('loan'));
            $('#poSheba').text($b.data('sheba'));
            $('#poGross').text($b.data('gross'));
            $('#poFee').text($b.data('fee'));
            $('#poNet').text($b.data('net'));
            $('#poForm').attr('action', $b.data('action'));
            $('#poRef').val('').closest('.f-field').removeClass('invalid');
        });
        $('#poSheba').on('click keydown', function (e) {
            if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') return;
            e.preventDefault();
            if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText($(this).text()).then(function () {
                window.pnToast('شماره شبا کپی شد');
            });
        });
        $('#poRef').on('input', function () {
            this.value = String(this.value).replace(/[۰-۹]/g, function (d) {
                return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d);
            }).replace(/\D/g, '');
            $(this).closest('.f-field').removeClass('invalid');
        });
        $('#poForm').on('submit', function (e) {
            if ($('#poRef').val().length < 6) {
                e.preventDefault();
                $('#poRef').closest('.f-field').addClass('invalid');
                return;
            }
            var $b = $(this).find('button[type=submit]');
            if (!PN_DEMO) {
                admBusy($b, true);
                return;
            }
            e.preventDefault();
            admBusy($b, true);
            setTimeout(function () {
                admBusy($b, false);
                bootstrap.Modal.getInstance($po[0]).hide();
                if ($poRow) {
                    admSetRow($poRow, 'paid', 'approved', 'واریز شده');
                    $poRow.find('.at-actions').html('<span class="text-muted small">ثبت شد ✓</span>');
                }
                window.pnToast('واریز به فروشنده ثبت شد');
            }, 800);
        });
    }

    /* ---------- تیکت (پشتیبان): پاسخ‌های آماده ---------- */
    $(document).on('click', '.at-chip[data-canned]', function () {
        $('#tkText').val($(this).data('canned')).trigger('input').trigger('focus');
    });

    /* =====================================================
       ثبت آگهی جدید (پنل کاربر) – همان فرم سایت، درون لایه پنل
       ارسال عادی فرم (POST user.ads.store)؛ سرور اعتبارسنجی نهایی را انجام می‌دهد.
       ===================================================== */
    var $adForm = $('#adForm');
    if ($adForm.length) {
        var adFa = function (s) {
            return String(s).replace(/\d/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'.charAt(d); });
        };
        var adLat = function (s) {
            return String(s || '').replace(/[۰-۹]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d); });
        };
        var adNum = function (v) { return +adLat(v).replace(/[^\d.]/g, '') || 0; };

        /* فرمت ورودی‌ها (نمایش ارقام فارسی، ارسال لاتین به سرور) */
        var fmMoney = function (el) {
            var v = adLat(el.value).replace(/\D/g, '');
            el.value = v ? adFa(Number(v).toLocaleString('en-US')) : '';
        };
        var fmInt = function (el) {
            el.value = adFa(adLat(el.value).replace(/\D/g, '').slice(0, 3));
        };
        var fmDec = function (el) {
            var v = adLat(el.value).replace(/[٫,]/g, '.').replace(/[^\d.]/g, '').replace(/(\..*)\./g, '$1').slice(0, 5);
            el.value = adFa(v).replace('.', '٫');
        };

        $adForm.on('input', '[data-money]', function () {
            fmMoney(this);
            adReco(true);
        })
            .on('input', '[data-int]', function () { fmInt(this); })
            .on('input', '[data-decimal]', function () { fmDec(this); });

        /* شبا: هر مقدار (تایپ/پیست) به IR + ۲۴ رقم تبدیل می‌شود */
        pnShebaField($('#fSheba'));

        /* شماره توضیحات */
        $('#fNote').on('input', function () {
            $('#fNoteCount').text(adFa(this.value.length));
        }).trigger('input');

        /* توصیه سیستم: ۵٪ تا ۳۰٪ مبلغ وام */
        function adReco(anim) {
            var a = adNum($('#fAmount').val()), $box = $('#recoBox');
            if (!a) {
                $('#recoText').text('** تا ** میلیون تومان');
                if ($box.length) $box.removeClass('live');
                return;
            }
            var lo = a * 0.05, hi = a * 0.30, mil = hi >= 1e6 && lo >= 1e5;
            var f = mil ? function (v) { return adFa(String(Math.round(v / 1e5) / 10).replace('.', '٫')); }
                : function (v) { return adFa(Math.round(v).toLocaleString('en-US')); };
            $('#recoText').text(f(lo) + ' تا ' + f(hi) + (mil ? ' میلیون تومان' : ' تومان'));
            if (anim && $box.length) {
                $box.removeClass('live');
                void $box[0].offsetWidth;
                $box.addClass('live');
            }
        }

        /* بازسازی نمایش بعد از خطاهای سمت سرور (مقادیر old لاتین‌اند) */
        $adForm.find('[data-money]').each(function () {
            fmMoney(this);
            adReco(false);
        });
        $adForm.find('[data-int], [data-decimal]').each(function () {
            fmInt(this), fmDec(this);
        });
        (function () {
            var v = pnShebaValue($('#fSheba').val());
            if (v && v !== $('#fSheba').val()) $('#fSheba').val(v);
        })();

        $adForm.on('input change', '.f-input, #fAgree', function () {
            $(this).closest('.f-field').removeClass('invalid');
        });

        /* اعتبارسنجی و ارسال */
        $adForm.on('submit', function (e) {
            var bad = [], chk = function (name, ok, msg) {
                var $f = $adForm.find('[data-field="' + name + '"]'), $e = $f.find('.f-err');
                if ($e.length && !$e.data('def')) $e.data('def', $e.text());
                $f.toggleClass('invalid', !ok);
                if (!ok) {
                    if (msg) $e.text(msg);
                    bad.push($f);
                } else if ($e.data('def')) {
                    $e.text($e.data('def'));
                }
            };
            var months = adNum($('#fMonths').val()),
                rate = $('#fRate').val() ? parseFloat(adLat($('#fRate').val()).replace('٫', '.')) : NaN;
            chk('bank', !!$('#fBank').val());
            chk('amount', adNum($('#fAmount').val()) > 0);
            chk('months', months >= 1 && months <= 120);
            chk('rate', !isNaN(rate) && rate >= 0 && rate <= 100);
            chk('price', adNum($('#fPrice').val()) > 0);
            chk('sheba', /^IR\d{24}$/.test($('#fSheba').val()),
                'شماره شبا باید ۲۴ رقم بعد از IR باشد (اکنون ' + adFa(pnShebaDigits($('#fSheba').val()).length) + ' رقم).');
            chk('note', $.trim($('#fNote').val()).length >= 10);
            chk('doc_balance', !!$('#fDocBalance')[0] && $('#fDocBalance')[0].files.length > 0);
            chk('doc_id', !!$('#fDocId')[0] && $('#fDocId')[0].files.length > 0);
            chk('agree', $('#fAgree').is(':checked'));

            if (bad.length) {
                e.preventDefault();
                $('html, body').animate({ scrollTop: bad[0].offset().top - 120 }, 400);
                bad[0].find('input:not([hidden]), select, textarea').first().trigger('focus');
                return;
            }
            /* ارسال عدد لاتین ساده به سرور */
            $adForm.find('[data-money], [data-int]').each(function () {
                this.value = adLat(this.value).replace(/\D/g, '');
            });
            $adForm.find('[data-decimal]').each(function () {
                this.value = adLat(this.value).replace('٫', '.');
            });
            $(this).find('button[type=submit]').addClass('loading').prop('disabled', true).find('.spinner-border').removeClass('d-none');
        });
    }
});