<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * تراکنش‌های مالی سامانه.
 * type: payment (خریدار → امانت) | payout (امانت → فروشنده) | refund (برگشت وجه) | fee (کمیسیون)
 * status: pending | escrow | released | refunded | failed
 */
class Transaction extends Model
{
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_PAYOUT = 'payout';
    public const TYPE_REFUND = 'refund';
    public const TYPE_FEE = 'fee';

    public const STATUS_PENDING = 'pending';
    public const STATUS_ESCROW = 'escrow';
    public const STATUS_RELEASED = 'released';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'code', 'request_id', 'ad_id', 'user_id', 'counterparty_id', 'type', 'status',
        'amount', 'fee', 'net', 'gateway', 'authority', 'ref_id', 'sheba',
        'failure_reason', 'paid_at', 'released_at', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'fee' => 'integer',
            'net' => 'integer',
            'paid_at' => 'datetime',
            'released_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(LoanRequest::class, 'request_id');
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function counterparty(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counterparty_id');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeOfStatus(Builder $query, string ...$statuses): Builder
    {
        return $query->whereIn('status', $statuses);
    }

    /** مبالغ حقیقتاً در امانت: فقط تراکنشهای پرداخت خریدار با وضعیت امانت */
    public function scopeEscrow(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_PAYMENT)->where('status', self::STATUS_ESCROW);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_PAYMENT => 'پرداخت خریدار',
            self::TYPE_PAYOUT => 'واریز به فروشنده',
            self::TYPE_REFUND => 'برگشت وجه',
            self::TYPE_FEE => 'کمیسیون',
            default => $this->type,
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'در انتظار',
            self::STATUS_ESCROW => 'در امانت',
            self::STATUS_RELEASED => 'تسویه شده',
            self::STATUS_REFUNDED => 'برگشت داده شد',
            self::STATUS_FAILED => 'ناموفق',
            default => $this->status,
        };
    }
}
