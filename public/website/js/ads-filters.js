/*
 * ads-filters.js — وام‌جو
 * تعاملات فیلترهای صفحه آگهی‌ها:
 *  ۱) ارسال خودکار فرم با تغییر چک‌باکس‌ها (دسکتاپ) — موبایل با دکمه «مشاهده آگهی‌ها»
 *  ۲) جستجوی آگهی با تاخیر (debounce) و کلید Enter
 *  ۳) مرتب‌سازی از طریق دراپ‌داون (مقدار hidden#sortInput + submit)
 *  ۴) «حذف همه فیلترها» + دکمه ریست حالت خالی
 *  ۵) جستجوی زنده بین بانک‌ها (با نرمال‌سازی ی/ك و نیم‌فاصله)
 *  ۶) شمارنده فیلترهای فعال روی دکمه موبایل
 *  ۷) دکمه «نمایش بیشتر» مقاله SEO
 * مستقل از کتابخانه خارجی؛ سازگار با Bootstrap 5 (offcanvas/dropdown).
 */
(function () {
    'use strict';

    var form = document.getElementById('adsFilterForm');
    if (!form || form.dataset.adsFiltersBound === '1') {
        return; // جلوگیری از بایند تکراری (مثلاً اگر اسکریپت دوبار لود شود)
    }
    form.dataset.adsFiltersBound = '1';

    var searchInput = document.getElementById('adsSearch');       // بیرون از فرم (form=attr)
    var bankSearch = document.getElementById('bankSearch');
    var bankSearchClear = document.getElementById('bankSearchClear');
    var bankList = document.getElementById('bankList');
    var bankEmpty = document.getElementById('bankEmpty');
    var clearAllBtn = document.getElementById('clearAll');
    var applyBtn = document.getElementById('applyFilters');       // دکمه موبایل (بیرون از فرم)
    var emptyReset = document.getElementById('emptyReset');
    var sortInput = document.getElementById('sortInput');
    var filterBadge = document.getElementById('filterBadge');
    var seoToggle = document.getElementById('seoToggle');
    var seoText = document.getElementById('seoText');

    var submitTimer = null;
    var FA_DIGITS = '۰۱۲۳۴۵۶۷۸۹';

    function faDigits(value) {
        return String(value).replace(/[0-9]/g, function (d) {
            return FA_DIGITS[+d];
        });
    }

    /* ارسال فرم (با پشتیبانی از کنترل‌های form-associated مثل #adsSearch) */
    function submitForm(delay) {
        clearTimeout(submitTimer);
        submitTimer = setTimeout(function () {
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        }, delay || 0);
    }

    function updateBadge() {
        if (!filterBadge) return;
        var count = form.querySelectorAll('input[type="checkbox"]:checked').length;
        filterBadge.textContent = faDigits(count);
        if (count > 0) {
            filterBadge.removeAttribute('hidden');
        } else {
            filterBadge.setAttribute('hidden', 'hidden');
        }
    }

    /* ۱) ارسال خودکار با تغییر چک‌باکس‌ها — دسکتاپ: فوری | موبایل: بعد از «مشاهده آگهی‌ها» */
    Array.prototype.forEach.call(form.querySelectorAll('input[type="checkbox"]'), function (cb) {
        cb.addEventListener('change', function () {
            updateBadge();
            if (window.matchMedia('(min-width: 992px)').matches) {
                submitForm(0);
            }
        });
    });

    /* تغییر فیلدهای عددی/لیست (فیلترهای اختیاری مبلغ، اقساط، نرخ و ...) */
    Array.prototype.forEach.call(form.querySelectorAll('input[type="number"], select'), function (el) {
        el.addEventListener('change', function () {
            submitForm(0);
        });
    });

    /* ۲) جستجوی آگهی‌ها با debounce و کلید Enter */
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            submitForm(450);
        });
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitForm(0);
            }
        });
    }

    /* ۵) جستجوی زنده لیست بانک‌ها */
    function normalize(text) {
        return String(text || '')
            .replace(/\u064A/g, '\u06CC')   /* ي → ی */
            .replace(/\u0643/g, '\u06A9')   /* ك → ک */
            .replace(/\u200C/g, ' ')        /* نیم‌فاصله → فاصله */
            .toLowerCase()
            .trim();
    }

    function filterBanks() {
        if (!bankList) return;
        var term = normalize(bankSearch ? bankSearch.value : '');
        var visible = 0;

        Array.prototype.forEach.call(bankList.querySelectorAll('.bank-opt'), function (item) {
            var match = !term || normalize(item.getAttribute('data-name')).indexOf(term) !== -1;
            item.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        if (bankSearchClear) bankSearchClear.hidden = !term;
        if (bankEmpty) bankEmpty.hidden = visible > 0;
    }

    if (bankSearch) {
        bankSearch.addEventListener('input', filterBanks);
    }

    if (bankSearchClear && bankSearch) {
        bankSearchClear.addEventListener('click', function () {
            bankSearch.value = '';
            filterBanks();
            bankSearch.focus();
        });
    }

    /* ۳) مرتب‌سازی */
    Array.prototype.forEach.call(document.querySelectorAll('.sort-opt'), function (btn) {
        btn.addEventListener('click', function () {
            if (!sortInput) return;
            sortInput.value = btn.getAttribute('data-sort');
            Array.prototype.forEach.call(document.querySelectorAll('.sort-opt'), function (b) {
                b.classList.toggle('active', b === btn);
            });
            submitForm(0);
        });
    });

    /* ۴) حذف همه فیلترها (دکمه بالای پنل + دکمه حالت خالی) */
    function resetFilters() {
        Array.prototype.forEach.call(form.querySelectorAll('input[type="checkbox"]'), function (cb) {
            cb.checked = false;
        });
        if (searchInput) searchInput.value = '';
        if (bankSearch) bankSearch.value = '';
        if (sortInput) sortInput.value = 'new';
        filterBanks();
        updateBadge();
        submitForm(0);
    }

    if (clearAllBtn) clearAllBtn.addEventListener('click', resetFilters);
    if (emptyReset) emptyReset.addEventListener('click', resetFilters);

    /* ۶) موبایل: دکمه «مشاهده آگهی‌ها» داخل شیت فیلترها */
    if (applyBtn) {
        applyBtn.addEventListener('click', function () {
            submitForm(0);
        });
    }

    /* ۷) نمایش بیشتر/کمتر مقاله پایین صفحه */
    if (seoToggle && seoText) {
        seoToggle.addEventListener('click', function () {
            var clamped = seoText.classList.toggle('clamped');
            seoToggle.textContent = clamped ? 'نمایش بیشتر' : 'نمایش کمتر';
        });
    }

    updateBadge();
})();
