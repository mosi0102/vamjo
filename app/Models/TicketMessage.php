<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TicketMessage extends Model
{
    protected $fillable = [
        'ticket_id', 'user_id', 'author_role', 'body',
        'attachment_path', 'attachment_name', 'attachment_size', 'is_read',
    ];

    protected function casts(): array
    {
        return ['is_read' => 'boolean'];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isFromSupport(): bool
    {
        return $this->author_role === 'support';
    }

    public function attachmentUrl(): ?string
    {
        if (! $this->attachment_path) {
            return null;
        }

        return Storage::disk(config('vamjou.uploads.disk'))->url($this->attachment_path);
    }
}
