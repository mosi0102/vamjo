<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    public const CATEGORIES = [
        'general' => 'عمومی',
        'sell' => 'ثبت آگهی',
        'buy' => 'خرید امتیاز',
        'pay' => 'پرداخت و امانت',
        'kyc' => 'احراز هویت',
        'account' => 'حساب کاربری',
    ];

    protected $fillable = ['category', 'question', 'answer', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOfCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
