/* =====================================================
   وام‌جو – لودینگ (مشترک سایت و پنل)
   API:  VLoader.start() / done()          نوار بالای صفحه
         VLoader.wrap(promise)             نوار تا پایان درخواست
         VLoader.busy(el, true|false)      لودینگ روی یک بخش
         VLoader.skeleton(el, 'ad'|'row'|'stat', n) → تابع حذف
   خودکار: کلیک روی لینک‌های داخلی، ارسال فرم، همه‌ی درخواست‌های jQuery.ajax
   ===================================================== */
(function (w, d) {
    'use strict';
    var bar = d.getElementById('vBar'), timer = null, val = 0, active = 0;

    function set(v) {
        val = v;
        if (bar) bar.style.transform = 'scaleX(' + v + ')';
    }

    function trickle() {
        timer = setTimeout(function () {
            if (val < 0.85) {
                set(val + (0.9 - val) * 0.15);
                trickle();
            }
        }, 260);
    }

    function start() {
        if (!bar) return;
        if (++active > 1) return;
        clearTimeout(timer);
        bar.style.opacity = 1;
        set(0.08);
        trickle();
    }

    function done() {
        if (!bar || !active) return;
        if (--active) return;
        clearTimeout(timer);
        set(1);
        setTimeout(function () {
            bar.style.opacity = 0;
            setTimeout(function () {
                set(0);
            }, 300);
        }, 220);
    }

    /* لینک‌های داخلی (فقط اگر واقعاً ناوبری انجام شود) */
    d.addEventListener('click', function (e) {
        var a = e.target.closest && e.target.closest('a[href]');
        if (!a || e.button || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        var href = a.getAttribute('href') || '';
        if (!href || href.charAt(0) === '#' || /^(javascript|mailto|tel):/i.test(href)) return;
        if ((a.target && a.target !== '_self') || a.hasAttribute('download') || a.hasAttribute('data-bs-toggle') || a.hasAttribute('data-no-loader')) return;
        if (a.origin !== w.location.origin || (a.pathname === w.location.pathname && a.search === w.location.search)) return;
        setTimeout(function () {
            if (!e.defaultPrevented) start();
        }, 0);        // بعد از اجرای بقیه‌ی هندلرها
    });
    /* فرم‌هایی که به‌صورت عادی ارسال می‌شوند */
    d.addEventListener('submit', function (e) {
        setTimeout(function () {
            if (!e.defaultPrevented) start();
        }, 0);
    });
    /* برگشت با دکمه Back (bfcache) */
    w.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            active = 1;
            done();
        }
    });
    /* همه‌ی درخواست‌های jQuery.ajax */
    if (w.jQuery) {
        w.jQuery(d).on('ajaxSend', start).on('ajaxComplete', done);
    }

    /* لودینگ روی یک بخش */
    function busy(el, on) {
        el = el && el.jquery ? el[0] : el;
        if (!el) return;
        el.classList.toggle('v-busy', on !== false);
        el.setAttribute('aria-busy', on !== false);
    }

    /* اسکلت‌ها */
    var T = {
        ad: '<div class="sk-row"><div class="sk sk-circle"></div><div class="sk-col"><div class="sk sk-line w60"></div><div class="sk sk-line w80"></div></div></div><div class="sk sk-bar"></div><div class="sk sk-line w50"></div>',
        row: '<div class="sk-row"><div class="sk sk-circle"></div><div class="sk-col"><div class="sk sk-line w60"></div><div class="sk sk-line w40"></div></div><div class="sk sk-line w40" style="max-width:110px"></div></div><div class="sk sk-bar" style="height:10px"></div>',
        stat: '<div class="sk-row"><div class="sk sk-circle" style="width:48px;height:48px;border-radius:14px"></div><div class="sk-col"><div class="sk sk-line w60"></div></div></div><div class="sk sk-line w80"></div>'
    };

    function skeleton(el, type, n) {
        el = el && el.jquery ? el[0] : el;
        if (!el) return function () {
        };
        var nodes = [], html = T[type] || T.ad;
        for (var i = 0; i < (n || 3); i++) {
            var s = d.createElement('div');
            s.className = 'sk-wrap';
            s.setAttribute('aria-hidden', 'true');
            s.innerHTML = html;
            el.appendChild(s);
            nodes.push(s);
        }
        el.setAttribute('aria-busy', 'true');
        return function remove() {
            nodes.forEach(function (x) {
                if (x.parentNode) x.parentNode.removeChild(x);
            });
            el.removeAttribute('aria-busy');
        };
    }

    /* تصاویر تنبل: fade-in بعد از لود */
    [].forEach.call(d.querySelectorAll('img.lazy-img'), function (img) {
        var ok = function () {
            img.classList.add('loaded');
        };
        img.complete && img.naturalWidth ? ok() : img.addEventListener('load', ok);
    });

    w.VLoader = {
        start: start, done: done, busy: busy, skeleton: skeleton,
        wrap: function (p) {
            start();
            var f = function () {
                done();
            };
            return p && p.then ? p.then(function (r) {
                f();
                return r;
            }, function (er) {
                f();
                throw er;
            }) : (f(), p);
        }
    };
})(window, document);
