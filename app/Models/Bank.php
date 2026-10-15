<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Bank extends Model
{
    protected $fillable = ['name', 'short_name', 'slug', 'initial', 'color', 'image', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class);
    }

    public function radars(): HasMany
    {
        return $this->hasMany(LoanRadar::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /** نام کوتاه برای کارت‌ها و نوار بانک‌ها (پیش‌فرض: بدون کلمه «بانک») */
    public function getShortNameAttribute(?string $value): string
    {
        return $value ?: (string) Str::of($this->attributes['name'] ?? '')->after('بانک ')->trim();
    }

    /** نام نمایشی کوتاه (مثل «رسالت») */
    public function getDisplayNameAttribute(): string
    {
        return $this->short_name;
    }

    /** آدرس تصویر بانک در public/website/img/bank */
    public function getLogoAttribute(): string
    {
        return asset('website/img/bank/'.$this->image.'.png');
    }

    /** ساختار کارت بانک برای نوار بانک‌ها و فیلترها */
    public function toCardArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'bank_name' => $this->display_name,
            'name' => $this->name,
            'c' => $this->color,
            'l' => $this->initial,
            'image' => $this->image,
        ];
    }

    /** ساختار فیلترهای صفحه لیست آگهی‌ها (کلید = slug) */
    public function toFilterArray(): array
    {
        return [
            'n' => $this->display_name,
            'full' => $this->name,
            'c' => $this->color,
            'l' => $this->initial,
            'image' => $this->image,
        ];
    }
}
