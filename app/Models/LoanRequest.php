<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * معامله خرید امتیاز وام (بعد از تایید درخواست خریدار ساخته می‌شود).
 * step: 1..7  |  status: active | completed | rejected | cancelled | expired
 */
class LoanRequest extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';

    public const STEPS = [
        1 => 'ثبت درخواست',
        2 => 'تایید درخواست',
        3 => 'پرداخت',
        4 => 'تایید پرداخت',
        5 => 'انتقال امتیاز',
        6 => 'تایید انتقال',
        7 => 'تکمیل',
    ];

    public const WAITING = [
        1 => 'در انتظار ثبت',
        2 => 'در انتظار تایید',
        3 => 'در انتظار پرداخت',
        4 => 'در انتظار تایید پرداخت',
        5 => 'در انتظار انتقال امتیاز',
        6 => 'در انتظار انتقال',
        7 => 'در حال تکمیل',
    ];

    protected $fillable = [
        'code', 'ad_id', 'offer_id', 'buyer_id', 'seller_id', 'amount', 'price', 'step',
        'status', 'reject_reason', 'pay_due_at', 'paid_at', 'transfer_due_at',
        'transfer_confirmed_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'price' => 'integer',
            'step' => 'integer',
            'pay_due_at' => 'datetime',
            'paid_at' => 'datetime',
            'transfer_due_at' => 'datetime',
            'transfer_confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(AdOffer::class, 'offer_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'request_id');
    }

    public function stepLabel(): string
    {
        return self::WAITING[$this->step] ?? '';
    }

    public function isWaitingPayment(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->step === 3;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
