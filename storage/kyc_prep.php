<?php

/**
 * تولید داده معتبر برای تست جریان احراز هویت (کد ملی با رقم کنترلی، شبا معتبر) + دو تصویر نمونه.
 * خروجی: NID=... و SHEBA=... (برای استفاده در اسکریپت bash)
 */

require __DIR__.'/../vendor/autoload.php';

use App\Support\Iran;

// کد ملی با رقم کنترلی صحیح
do {
    $nine = '';
    for ($i = 0; $i < 9; $i++) {
        $nine .= (string) random_int(0, 9);
    }
    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $sum += ((int) $nine[$i]) * (10 - $i);
    }
    $remainder = $sum % 11;
    $nid = $nine.($remainder < 2 ? $remainder : 11 - $remainder);
} while (! Iran::validNationalId($nid));

// شبای معتبر با کد بانک ۰۱۲ (ملت): IR + ۳ رقم بانک + ۲۱ رقم
$bankCode = '012';
do {
    $body = $bankCode;
    for ($i = 0; $i < 21; $i++) {
        $body .= (string) random_int(0, 9);
    }
    $sheba = 'IR'.$body;
} while (! Iran::validSheba($sheba));

// دو تصویر PNG نمونه (۱×۱ پیکسل) برای آپلود مدارک
$png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFAAH/q842iQAAAABJRU5ErkJggg==');
file_put_contents(__DIR__.'/logs/kyc_selfie.png', $png);
file_put_contents(__DIR__.'/logs/kyc_card.png', $png);

// مقادیر فارسی به‌صورت فایل (تا مشکل انکدینگ آرگومان‌های خط فرمان در ویندوز پیش نیاید)
file_put_contents(__DIR__.'/logs/kyc_first.val', 'آزمون');
file_put_contents(__DIR__.'/logs/kyc_last.val', 'بررسی‌هویت');
file_put_contents(__DIR__.'/logs/kyc_reason.val', 'تصویر سلفی همراه با کارت ملی خوانا نیست');

echo "NID={$nid}\n";
echo "SHEBA={$sheba}\n";
