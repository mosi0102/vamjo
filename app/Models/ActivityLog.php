<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * سابقه فعالیت کاربران؛ در «سابقه فعالیت» جزئیات کاربر و داشبورد مدیریت استفاده می‌شود.
 */
class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'actor_id', 'action', 'description',
        'subject_type', 'subject_id', 'ip', 'meta',
    ];

    protected function casts(): array
    {
        return ['meta' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function scopeForUser(Builder $query, $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }

    /** آیکون نمایشی برای هر نوع رویداد */
    public function icon(): string
    {
        return match (true) {
            str_contains($this->action, 'login') => 'check',
            str_contains($this->action, 'kyc') => 'shield',
            str_contains($this->action, 'ad') => 'book',
            str_contains($this->action, 'payment') => 'card',
            str_contains($this->action, 'ticket') => 'chat',
            str_contains($this->action, 'register') => 'user',
            default => 'doc',
        };
    }

    public static function record(User|int|null $user, string $action, ?string $description = null, array $meta = []): self
    {
        $userId = $user instanceof User ? $user->id : $user;

        return static::create([
            'user_id' => $userId,
            'actor_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'ip' => request()->ip(),
            'meta' => $meta ?: null,
        ]);
    }
}
