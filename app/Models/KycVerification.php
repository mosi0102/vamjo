<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * درخواست احراز هویت کاربر.
 * status: pending | approved | rejected
 */
class KycVerification extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /** انواع مدارک ارسالی */
    public const DOC_SELFIE = 'selfie';
    public const DOC_CARD = 'card';

    protected $fillable = [
        'user_id', 'national_id', 'birth_date', 'sheba', 'bank_name',
        'national_card_path', 'selfie_path', 'status', 'reject_reason',
        'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeOfStatus(Builder $query, ?string $status): Builder
    {
        return in_array($status, [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED], true)
            ? $query->where('status', $status)
            : $query;
    }

    /** مسیر ذخیره‌شده مدرک روی دیسک خصوصی */
    public function documentPath(string $kind): ?string
    {
        return $kind === self::DOC_SELFIE ? $this->selfie_path : $this->national_card_path;
    }

    /**
     * مدرک احراز هویت هرگز به‌صورت عمومی سرو نمی‌شود؛ فقط از طریق مسیر محافظت‌شده
     * /adminPanel/verifications/{id}/document/{kind} برای کارشناسان قابل مشاهده است.
     */
    public function documentUrl(string $kind): ?string
    {
        if (! $this->documentPath($kind)) {
            return null;
        }

        return route('admin.verifications.document', ['kyc' => $this->id, 'kind' => $kind]);
    }

    public function nationalCardUrl(): ?string
    {
        return $this->documentUrl(self::DOC_CARD);
    }

    public function selfieUrl(): ?string
    {
        return $this->documentUrl(self::DOC_SELFIE);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'در انتظار بررسی',
            self::STATUS_APPROVED => 'تایید شده',
            self::STATUS_REJECTED => 'رد شده',
            default => $this->status,
        };
    }
}
