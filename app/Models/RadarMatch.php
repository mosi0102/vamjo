<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadarMatch extends Model
{
    protected $fillable = ['radar_id', 'ad_id', 'is_new', 'seen_at'];

    protected function casts(): array
    {
        return ['is_new' => 'boolean', 'seen_at' => 'datetime'];
    }

    public function radar(): BelongsTo
    {
        return $this->belongsTo(LoanRadar::class, 'radar_id');
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function markSeen(): void
    {
        if ($this->is_new) {
            $this->update(['is_new' => false, 'seen_at' => now()]);
        }
    }
}
