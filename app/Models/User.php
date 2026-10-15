<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SUPPORT = 'support';

    public const KYC_UNVERIFIED = 'unverified';
    public const KYC_PENDING = 'pending';
    public const KYC_VERIFIED = 'verified';
    public const KYC_REJECTED = 'rejected';

    protected $fillable = [
        'name', 'first_name', 'last_name', 'mobile', 'email', 'password',
        'national_id', 'birth_date', 'sheba', 'bank_name', 'city', 'avatar',
        'role', 'kyc_status', 'blocked_at', 'block_reason', 'last_login_at', 'last_login_ip',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'blocked_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    /* ------------------------------ روابط ------------------------------ */

    /** آگهی‌هایی که کاربر فروشنده آن‌ها است */
    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class);
    }

    /** درخواست‌های ثبت‌شده روی آگهی‌ها (به‌عنوان خریدار) */
    public function offers(): HasMany
    {
        return $this->hasMany(AdOffer::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(LoanRequest::class, 'buyer_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(LoanRequest::class, 'seller_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assignee_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function radars(): HasMany
    {
        return $this->hasMany(LoanRadar::class);
    }

    public function kycVerifications(): HasMany
    {
        return $this->hasMany(KycVerification::class);
    }

    public function latestKyc(): HasOne
    {
        return $this->hasOne(KycVerification::class)->latestOfMany();
    }

    public function notes(): HasMany
    {
        return $this->hasMany(UserNote::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /* ------------------------------ دسترسی‌ها ------------------------------ */

    public function getIsVerifiedAttribute(): bool
    {
        return $this->kyc_status === self::KYC_VERIFIED;
    }

    public function getFullNameAttribute(): string
    {
        $full = trim(($this->first_name ?? '').' '.($this->last_name ?? ''));

        return $full !== '' ? $full : (string) $this->name;
    }

    /** نام نمایشی در پنل (نام کامل، وگرنه همان name) */
    public function getDisplayNameAttribute(): string
    {
        return $this->full_name;
    }

    public function isRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isSupport(): bool
    {
        return $this->role === self::ROLE_SUPPORT;
    }

    /** مدیر یا پشتیبان (دسترسی به /adminPanel) */
    public function isStaff(): bool
    {
        return $this->isRole(self::ROLE_ADMIN, self::ROLE_SUPPORT);
    }

    public function isBlocked(): bool
    {
        return $this->blocked_at !== null;
    }

    public function roleLabel(): string
    {
        return config('panel.roles')[$this->role] ?? $this->role;
    }

    public function kycLabel(): string
    {
        return match ($this->kyc_status) {
            self::KYC_PENDING => 'در انتظار بررسی',
            self::KYC_VERIFIED => 'احراز شده',
            self::KYC_REJECTED => 'رد شده',
            default => 'احراز نشده',
        };
    }

    /* ------------------------------ اسکوپ‌ها ------------------------------ */

    public function scopeRole(Builder $query, string ...$roles): Builder
    {
        return $query->whereIn('role', $roles);
    }

    public function scopeStaff(Builder $query): Builder
    {
        return $query->role(self::ROLE_ADMIN, self::ROLE_SUPPORT);
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('kyc_status', self::KYC_VERIFIED);
    }

    public function scopePendingKyc(Builder $query): Builder
    {
        return $query->where('kyc_status', self::KYC_PENDING);
    }

    public function scopeBlocked(Builder $query): Builder
    {
        return $query->whereNotNull('blocked_at');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        return $query->when($term !== '', fn (Builder $q) => $q->where(
            fn (Builder $w) => $w->where('name', 'like', "%{$term}%")
                ->orWhere('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('mobile', 'like', "%{$term}%")
                ->orWhere('national_id', 'like', "%{$term}%")
        ));
    }
}
