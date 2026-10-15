<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * تیکت پشتیبانی.
 * status: pending | answered | closed   |   priority: low | normal | high | urgent
 */
class Ticket extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ANSWERED = 'answered';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'code', 'user_id', 'assignee_id', 'subject', 'department',
        'priority', 'status', 'last_reply_at', 'closed_at',
    ];

    protected function casts(): array
    {
        return ['last_reply_at' => 'datetime', 'closed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class)->orderBy('created_at');
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_ANSWERED]);
    }

    public function scopePendingAnswer(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeAssignedTo(Builder $query, User $user): Builder
    {
        return $query->where('assignee_id', $user->id);
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function priorityLabel(): string
    {
        return config('vamjou.ticket_priorities')[$this->priority] ?? $this->priority;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'در انتظار پاسخ',
            self::STATUS_ANSWERED => 'پاسخ داده شده',
            self::STATUS_CLOSED => 'بسته شده',
            default => $this->status,
        };
    }
}
