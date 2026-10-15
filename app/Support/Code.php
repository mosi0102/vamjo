<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * ساخت شناسه‌های عددی نمایشی (شناسه وام، شماره تیکت، شماره تراکنش) که در کل جدول یکتا باشند.
 */
class Code
{
    public static function make(string $table, string $column = 'code', int $digits = 7, int $prefix = 0): string
    {
        $min = (int) str_pad('1', $digits, '0');
        $max = (int) str_repeat('9', $digits);

        do {
            $code = (string) random_int($min, $max);
            if ($prefix > 0) {
                $code = $prefix.$code;
            }
        } while (DB::table($table)->where($column, $code)->exists());

        return $code;
    }
}
