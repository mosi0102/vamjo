<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * درخواست/پیشنهاد خریدار برای یک آگهی.
 * status: pending | accepted | rejected | expired
 */
class AdOffer extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = ['ad_id', 'user_id', 'amount', 'note', 'status', 'expires_at', 'responded_at', 'ip'];

    protected function casts(): array
    {
        return ['amount' => 'integer', 'expires_at' => 'datetime', 'responded_at' => 'datetime'];
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function loanRequest(): HasOne
    {
        return $this->hasOne(LoanRequest::class, 'offer_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_PENDING
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'در انتظار پاسخ',
            self::STATUS_ACCEPTED => 'تایید شده',
            self::STATUS_REJECTED => 'رد شده',
            self::STATUS_EXPIRED => 'منقضی شده',
            default => $this->status,
        };
    }
}
