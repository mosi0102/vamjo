<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * رادار وام: شرایط وام مورد نظر خریدار؛ با انتشار آگهی منطبق، اعلان ساخته می‌شود.
 */
class LoanRadar extends Model
{
    protected $fillable = [
        'user_id', 'name', 'bank_id', 'type', 'city', 'min_amount', 'max_amount',
        'months', 'max_rate', 'notify_sms', 'notify_panel', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_amount' => 'integer',
            'max_amount' => 'integer',
            'months' => 'integer',
            'max_rate' => 'float',
            'notify_sms' => 'boolean',
            'notify_panel' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(RadarMatch::class, 'radar_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /** آگهی‌های منطبق با این رادار */
    public function matchingAds(): Builder
    {
        return Ad::query()
            ->published()
            ->ofBank($this->bank_id)
            ->ofType($this->type)
            ->city($this->city)
            ->amountFrom($this->min_amount)
            ->amountTo($this->max_amount)
            ->monthsTo($this->months)
            ->rateTo($this->max_rate);
    }
}
