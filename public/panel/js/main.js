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
            $('#stId').text('#' + Number($b.data('id')).toLocaleString('fa-IR', {useGrouping: false}));
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
    var PN_DEMO = true;
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
                data: {_method: 'PATCH', active: on ? 1 : 0},
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
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
        $('#edSheba').on('input', function () {
            var d = edLat(this.value).replace(/^\s*ir/i, '').replace(/\D/g, '').slice(0, 24);
            this.value = d ? 'IR' + d : '';
        });
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
                var $f = $ed.find('[data-field="' + n + '"]').toggleClass('invalid', !ok);
                if (!ok) {
                    if (msg) $f.find('.f-err').text(msg);
                    bad.push($f);
                }
            };
            var mo = edNum($('#edMonths').val()),
                rate = $('#edRate').val() ? parseFloat(edLat($('#edRate').val()).replace('٫', '.')) : NaN;
            chk('bank', !!$('#edBank').val());
            chk('amount', edNum($('#edAmount').val()) > 0);
            chk('months', mo >= 1 && mo <= 120);
            chk('rate', !isNaN(rate) && rate >= 0 && rate <= 100);
            chk('price', edNum($('#edPrice').val()) > 0);
            chk('sheba', /^IR\d{24}$/.test($('#edSheba').val()));
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
                $('html, body').animate({scrollTop: bad[0].offset().top - 120}, 400);
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
});
