<?php

namespace App\Support;

/**
 * تبدیل تاریخ میلادی به شمسی (الگوریتم jalaali) برای نمایش تاریخ‌ها در پنل و سایت.
 * توکن‌های پشتیبانی‌شده در format: Y، y، m، n، d، j، F، H، i، s
 */
class Jalali
{
    private const BREAKS = [
        -61, 9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210,
        1635, 2060, 2097, 2192, 2262, 2324, 2394, 2456, 3178,
    ];

    public const MONTHS = [
        'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
        'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند',
    ];

    /** روزهای هفته به ترتیب تقویم شمسی (شنبه تا جمعه) */
    public const WEEKDAYS = ['شنبه', 'یکشنبه', 'دوشنبه', 'سه شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه'];

    /** نام روز هفته برای یک تاریخ (۰ = شنبه) */
    public static function weekday(\DateTimeInterface $date): string
    {
        return self::WEEKDAYS[((int) $date->format('w') + 1) % 7];
    }

    /** @return array{0:int,1:int,2:int} [سال، ماه، روز] شمسی */
    public static function fromDate(\DateTimeInterface $date): array
    {
        return self::d2j(self::g2d((int) $date->format('Y'), (int) $date->format('n'), (int) $date->format('j')));
    }

    public static function format(\DateTimeInterface $date, string $format = 'Y/m/d'): string
    {
        [$jy, $jm, $jd] = self::fromDate($date);

        $tokens = [
            'Y' => (string) $jy,
            'y' => substr((string) $jy, -2),
            'm' => str_pad((string) $jm, 2, '0', STR_PAD_LEFT),
            'n' => (string) $jm,
            'd' => str_pad((string) $jd, 2, '0', STR_PAD_LEFT),
            'j' => (string) $jd,
            'F' => self::monthName($jm),
            'H' => $date->format('H'),
            'i' => $date->format('i'),
            's' => $date->format('s'),
        ];

        return strtr($format, $tokens);
    }

    public static function monthName(int $jm): string
    {
        return self::MONTHS[max(0, min(11, $jm - 1))];
    }

    /** ماه و سال شمسی یک تاریخ، مثل «مهر ۱۴۰۵» */
    public static function monthLabel(\DateTimeInterface $date): string
    {
        return self::format($date, 'F Y');
    }

    /* ------------------------- توابع کمکی الگوریتم ------------------------- */

    private static function div(int $a, int $b): int
    {
        return intdiv($a, $b);
    }

    private static function mod(int $a, int $b): int
    {
        return $a - $b * self::div($a, $b);
    }

    /** ترتیب روز ژولینی از تاریخ میلادی */
    private static function g2d(int $gy, int $gm, int $gd): int
    {
        $d = self::div(($gy + self::div($gm - 8, 6) + 100100) * 1461, 4)
            + self::div(153 * self::mod($gm + 9, 12) + 2, 5)
            + $gd - 34840408;

        return $d - self::div(self::div($gy + 100100 + self::div($gm - 8, 6), 100) * 3, 4) + 752;
    }

    /** @return array{0:int,1:int,2:int} */
    private static function d2j(int $jdn): array
    {
        $gy = self::d2g($jdn)[0];
        $jy = $gy - 621;
        $r = self::jalCal($jy);
        $jdn1f = self::g2d($gy, 3, $r['march']);
        $k = $jdn - $jdn1f;

        if ($k >= 0) {
            if ($k <= 185) {
                return [$jy, 1 + self::div($k, 31), self::mod($k, 31) + 1];
            }
            $k -= 186;
        } else {
            $jy -= 1;
            $k += 179;
            if ($r['leap'] === 1) {
                $k += 1;
            }
        }

        return [$jy, 7 + self::div($k, 30), self::mod($k, 30) + 1];
    }

    /** @return array{leap:int,gy:int,march:int} */
    private static function jalCal(int $jy): array
    {
        $breaks = self::BREAKS;
        $bl = count($breaks);
        $gy = $jy + 621;
        $leapJ = -14;
        $jp = $breaks[0];
        $jump = 0;

        for ($i = 1; $i < $bl; $i++) {
            $jm = $breaks[$i];
            $jump = $jm - $jp;
            if ($jy < $jm) {
                break;
            }
            $leapJ += self::div($jump, 33) * 8 + self::div(self::mod($jump, 33), 4);
            $jp = $jm;
        }

        $n = $jy - $jp;
        $leapJ += self::div($n, 33) * 8 + self::div(self::mod($n, 33) + 3, 4);

        if (self::mod($jump, 33) === 4 && $jump - $n === 4) {
            $leapJ += 1;
        }

        $leapG = self::div($gy, 4) - self::div((self::div($gy, 100) + 1) * 3, 4) - 150;
        $march = 20 + $leapJ - $leapG;

        if ($jump - $n < 6) {
            $n = $n - $jump + self::div($jump + 4, 33) * 33;
        }

        $leap = self::mod(self::mod($n + 1, 33) - 1, 4);
        if ($leap === -1) {
            $leap = 4;
        }

        return ['leap' => $leap, 'gy' => $gy, 'march' => $march];
    }

    /** @return array{0:int,1:int,2:int} */
    private static function d2g(int $jdn): array
    {
        $j = 4 * $jdn + 139361631;
        $j += self::div(self::div(4 * $jdn + 183187720, 146097) * 3, 4) * 4 - 3908;
        $i = self::div(self::mod($j, 1461), 4) * 5 + 308;
        $gd = self::div(self::mod($i, 153), 5) + 1;
        $gm = self::mod(self::div($i, 153), 12) + 1;
        $gy = self::div($j, 1461) - 100100 + self::div(8 - $gm, 6);

        return [$gy, $gm, $gd];
    }
}
