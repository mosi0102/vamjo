<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * آگهی فروش امتیاز وام.
 *
 * status: pending | published | transfer | completed | rejected
 * state (کلید مورد استفاده در ویوها): review | published | transfer | completed | rejected
 */
class Ad extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_TRANSFER = 'transfer';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'code', 'user_id', 'bank_id', 'type', 'amount', 'months', 'rate', 'price',
        'max_price', 'installment', 'total_repayment', 'guarantor', 'city', 'description',
        'sheba', 'status', 'reject_reason', 'featured', 'urgent', 'views',
        'published_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'price' => 'integer',
            'max_price' => 'integer',
            'installment' => 'integer',
            'total_repayment' => 'integer',
            'months' => 'integer',
            'rate' => 'float',
            'featured' => 'boolean',
            'urgent' => 'boolean',
            'views' => 'integer',
            'published_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /* ------------------------------ روابط ------------------------------ */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AdDocument::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(AdOffer::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(LoanRequest::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /* ------------------------------ وضعیت‌ها ------------------------------ */

    /** کلید وضعیت برای ویوها (stateMap) */
    public function getStateAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'review',
            self::STATUS_TRANSFER => 'transfer',
            self::STATUS_COMPLETED => 'completed',
            self::STATUS_REJECTED => 'rejected',
            default => 'published',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'منتظر تایید',
            self::STATUS_PUBLISHED => 'منتشر شده',
            self::STATUS_TRANSFER => 'در حال معامله',
            self::STATUS_COMPLETED => 'تکمیل شده',
            self::STATUS_REJECTED => 'رد شده',
            default => $this->status,
        };
    }

    /** آگهی فقط در وضعیت «منتظر تایید» یا «رد شده» قابل ویرایش است */
    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_REJECTED], true);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function typeLabel(): string
    {
        return config('vamjou.loan_types')[$this->type] ?? $this->type;
    }

    public function title(): string
    {
        return 'وام '.number_format($this->amount).' تومان بانک '.($this->bank->name ?? '');
    }

    /** مبلغ هر قسط (تخمینی؛ در صورت نبود مقدار ذخیره‌شده محاسبه می‌شود) */
    public function installmentAmount(): int
    {
        if ($this->installment) {
            return (int) $this->installment;
        }

        if (! $this->months) {
            return 0;
        }

        return (int) round($this->totalRepaymentAmount() / $this->months);
    }

    /** مبلغ بازپرداخت نهایی (اصل + سود) */
    public function totalRepaymentAmount(): int
    {
        if ($this->total_repayment) {
            return (int) $this->total_repayment;
        }

        $months = max((int) $this->months, 1);

        return (int) round($this->amount * (1 + ($this->rate / 100) * $months / 12));
    }

    /* ------------------------------ اسکوپ‌ها ------------------------------ */

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    /** فیلتر چند‌گانه بانک‌ها (slug یا id) در صفحه لیست آگهی‌ها */
    public function scopeOfBank(Builder $query, $bankIds): Builder
    {
        $ids = array_values(array_filter((array) $bankIds));

        if (! $ids) {
            return $query;
        }

        $numeric = array_values(array_filter($ids, 'is_numeric'));
        $strings = array_values(array_filter($ids, 'is_string'));

        return $query->where(function (Builder $w) use ($numeric, $strings) {
            $w->whereIn('bank_id', $numeric)
                ->orWhereHas('bank', fn (Builder $b) => $b->whereIn('slug', $strings));
        });
    }

    /** فیلتر چندگانه نوع وام */
    public function scopeOfType(Builder $query, $types): Builder
    {
        $valid = array_values(array_filter((array) $types));

        return $valid ? $query->whereIn('type', $valid) : $query;
    }

    /** وام فوری (فیلتر گزینه‌های بیشتر) */
    public function scopeUrgent(Builder $query): Builder
    {
        return $query->where('urgent', true);
    }

    /** آگهی قابل خرید (پرداخت از مبلغ وام) – در حال معامله نیست */
    public function scopePayable(Builder $query): Builder
    {
        return $query->where('status', '!=', self::STATUS_TRANSFER);
    }

    public function scopeCity(Builder $query, ?string $city): Builder
    {
        return $city ? $query->where('city', $city) : $query;
    }

    public function scopeAmountFrom(Builder $query, $min): Builder
    {
        return $min ? $query->where('amount', '>=', (int) $min) : $query;
    }

    public function scopeAmountTo(Builder $query, $max): Builder
    {
        return $max ? $query->where('amount', '<=', (int) $max) : $query;
    }

    public function scopeMonthsTo(Builder $query, $months): Builder
    {
        return $months ? $query->where('months', '<=', (int) $months) : $query;
    }

    public function scopeRateTo(Builder $query, $rate): Builder
    {
        return $rate !== null && $rate !== '' ? $query->where('rate', '<=', $rate) : $query;
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        return $query->when($term !== '', fn (Builder $q) => $q->where(
            fn (Builder $w) => $w->where('code', 'like', "%{$term}%")
                ->orWhere('city', 'like', "%{$term}%")
                ->orWhere('amount', 'like', "%{$term}%")
                ->orWhereHas('bank', fn (Builder $b) => $b->where(function (Builder $bw) use ($term) {
                    $bw->where('name', 'like', "%{$term}%")
                        ->orWhere('short_name', 'like', "%{$term}%");
                }))
        ));
    }
}
