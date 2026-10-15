<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AdDocument extends Model
{
    public const KIND_BALANCE = 'balance';
    public const KIND_NATIONAL_CARD = 'national_card';
    public const KIND_SELFIE = 'selfie';
    public const KIND_OTHER = 'other';

    protected $fillable = ['ad_id', 'kind', 'path', 'original_name', 'size', 'must_replace'];

    protected function casts(): array
    {
        return ['must_replace' => 'boolean', 'size' => 'integer'];
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk(config('vamjou.uploads.disk'))->url($this->path);
    }

    /** ساختار مورد استفاده در ویو ویرایش آگهی */
    public function toViewArray(): array
    {
        return [
            'url' => $this->url,
            'name' => $this->original_name ?: basename($this->path),
            'must_replace' => $this->must_replace,
        ];
    }
}
