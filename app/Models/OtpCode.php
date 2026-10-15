<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * کد یکبار مصرف ورود/ثبت‌نام (پیامک).
 */
class OtpCode extends Model
{
    public const PURPOSE_LOGIN = 'login';
    public const PURPOSE_REGISTER = 'register';

    protected $fillable = ['mobile', 'code', 'purpose', 'attempts', 'expires_at', 'consumed_at', 'ip'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'consumed_at' => 'datetime', 'attempts' => 'integer'];
    }

    public function scopeUsable(Builder $query): Builder
    {
        return $query->whereNull('consumed_at')->where('expires_at', '>', now());
    }

    public function scopeForMobile(Builder $query, string $mobile): Builder
    {
        return $query->where('mobile', $mobile);
    }

    public function isValid(): bool
    {
        return $this->consumed_at === null && $this->expires_at->isFuture() && $this->attempts < 5;
    }

    public static function latestFor(string $mobile, string $purpose = self::PURPOSE_LOGIN): ?self
    {
        return static::query()
            ->forMobile($mobile)
            ->where('purpose', $purpose)
            ->latest('id')
            ->first();
    }
}
