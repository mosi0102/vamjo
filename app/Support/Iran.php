<?php

namespace App\Support;

/**
 * اعتبارسنجی داده‌های ایرانی (کد ملی، شبا، موبایل).
 */
class Iran
{
    /** کد ملی ۱۰ رقمی با رقم کنترلی */
    public static function validNationalId(?string $code): bool
    {
        $code = preg_replace('/\D/', '', (string) $code);

        if (strlen($code) !== 10 || preg_match('/^(\d)\1{9}$/', $code)) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += ((int) $code[$i]) * (10 - $i);
        }

        $remainder = $sum % 11;
        $check = (int) $code[9];

        return $remainder < 2 ? $check === $remainder : $check === 11 - $remainder;
    }

    /** شبا: IR + ۲۴ رقم با صحت رقم کنترلی (mod 97) */
    public static function validSheba(?string $sheba): bool
    {
        $sheba = strtoupper(preg_replace('/\s/', '', (string) $sheba));

        if (! preg_match('/^IR\d{24}$/', $sheba)) {
            return false;
        }

        $rearranged = substr($sheba, 4).'1827' . substr($sheba, 2, 2);

        $remainder = 0;
        foreach (str_split($rearranged) as $digit) {
            $remainder = ($remainder * 10 + (int) $digit) % 97;
        }

        return $remainder === 1;
    }

    public static function validMobile(?string $mobile): bool
    {
        return (bool) preg_match('/^09\d{9}$/', (string) $mobile);
    }

    /** نرمال‌سازی ارقام فارسی/عربی به لاتین */
    public static function toLatin(?string $value): string
    {
        return strtr((string) $value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '،' => ',', '٫' => '.',
        ]);
    }

    /** فقط رقم؛ ارقام فارسی هم پذیرفته می‌شوند («۳۰۰,۰۰۰,۰۰۰» → 300000000) */
    public static function digits(?string $value): string
    {
        return preg_replace('/\D/', '', self::toLatin($value)) ?? '';
    }
}
