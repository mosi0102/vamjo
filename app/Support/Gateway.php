<?php

namespace App\Support;

use App\Models\Transaction;
use Illuminate\Support\Str;

/**
 * درگاه پرداخت (زرین‌پال).
 *
 * تا زمانی که ZARINPAL_MERCHANT_ID تنظیم نشده باشد، درگاه در حالت «شبیه‌ساز محلی»
 * کار می‌کند: هر پرداخت موفق فرض می‌شود و تراکنش بلافاصله در وضعیت امانت ثبت می‌شود.
 * برای اتصال واقعی، merchant id را در .env بگذارید و متدهای request()/verify() را
 * به API زرین‌پال وصل کنید.
 */
class Gateway
{
    public static function label(): string
    {
        return config('vamjou.payment.gateway_label', 'زرین پال');
    }

    public static function isSandbox(): bool
    {
        return (bool) config('vamjou.payment.sandbox') || blank(config('vamjou.payment.merchant_id'));
    }

    /** شروع پرداخت؛ در حالت شبیه‌ساز یک شناسه ساختگی برمی‌گرداند */
    public static function request(Transaction $transaction): string
    {
        if (self::isSandbox()) {
            return 'SANDBOX-'.strtoupper(Str::random(10));
        }

        // TODO: اتصال به زرین‌پال (PaymentRequest) و دریافت authority
        return 'MOCK-'.strtoupper(Str::random(10));
    }

    /** تایید پرداخت؛ خروجی: [موفق؟، کد مرجع] */
    public static function verify(Transaction $transaction): array
    {
        if (self::isSandbox()) {
            return [true, (string) random_int(10_000_000, 99_999_999)];
        }

        // TODO: اتصال به زرین‌پال (PaymentVerification) و دریافت ref_id
        return [true, (string) random_int(10_000_000, 99_999_999)];
    }
}
