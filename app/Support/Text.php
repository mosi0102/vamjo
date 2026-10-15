<?php

namespace App\Support;

/**
 * نرمال‌سازی متن فارسی برای مقایسه ورودی کاربر با مقادیر مجاز.
 * (ي/ك عربی، نیم‌فاصله، فاصله‌های غیرمعمول و فاصله اضافه)
 */
class Text
{
    public static function normalize(?string $value): string
    {
        $value = str_replace(
            ['ي', 'ك', 'ە', "\u{200c}", "\u{00a0}", "\u{200f}", "\u{200e}"],
            ['ی', 'ک', 'ه', '', ' ', '', ''],
            (string) $value
        );

        return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    }

    /** نزدیک‌ترین گزینه معتبر به ورودی کاربر (یا null) */
    public static function canonical(?string $value, array $options): ?string
    {
        $needle = self::normalize($value);

        foreach ($options as $option) {
            if (self::normalize((string) $option) === $needle) {
                return (string) $option;
            }
        }

        return null;
    }
}
