<?php

namespace App\Support;

/**
 * تبدیل ارقام و اعداد لاتین به فارسی برای نمایش در ویوها و خروجی CSV.
 */
class Fa
{
    public static function digits($value): string
    {
        return strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
    }

    public static function number($value): string
    {
        return self::digits(number_format((float) $value));
    }

    /** مبلغ به تومان با جداکننده فارسی */
    public static function money($value): string
    {
        return self::digits(number_format((float) $value)).' تومان';
    }

    /** مبلغ به میلیون تومان (مثل «۳۰۰» یا «۲.۵») */
    public static function million($value): string
    {
        $mil = rtrim(rtrim(number_format(((float) $value) / 1_000_000, 1), '0'), '.');

        return self::digits($mil);
    }

    /** تاریخ و ساعت شمسی با ارقام فارسی */
    public static function dateTime(?\DateTimeInterface $at, string $format = 'Y/m/d – H:i'): string
    {
        return $at ? self::digits(Jalali::format($at, $format)) : '—';
    }

    /** تاریخ شمسی بدون ساعت */
    public static function date(?\DateTimeInterface $at, string $format = 'Y/m/d'): string
    {
        return self::dateTime($at, $format);
    }

    /** فاصله زمانی خوانا (مثلاً «۲ ساعت پیش») */
    public static function ago(?\DateTimeInterface $at): string
    {
        if (! $at) {
            return '—';
        }

        $diff = $at->diff(now());
        $map = [
            'y' => 'سال', 'm' => 'ماه', 'd' => 'روز',
            'h' => 'ساعت', 'i' => 'دقیقه', 's' => 'ثانیه',
        ];

        foreach ($map as $key => $label) {
            if ($diff->{$key} > 0) {
                return self::digits($diff->{$key}).' '.$label.' پیش';
            }
        }

        return 'همین حالا';
    }

    /** مهلت باقی‌مانده بر حسب روز (برای مهلت ۷ روزه معامله) */
    public static function remainingDays(?\DateTimeInterface $deadline): ?int
    {
        if (! $deadline) {
            return null;
        }

        return max(0, (int) now()->diffInDays($deadline, false));
    }
}
