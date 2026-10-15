<?php

namespace App\Support;

/**
 * نقش فعلی پنل (user | admin | support) – یک منبع واحد برای منوی کناری و ویوها.
 *
 * ترتیب تشخیص:
 *  1) کاربر لاگین‌شده با ستون role معتبر
 *  2) (فقط محیط local یا APP_DEBUG=true) ?role=support یا ?role=admin یا ?role=user که در سشن می‌ماند،
 *     پس لازم نیست پارامتر را در هر صفحه تکرار کنید
 *  3) بر اساس آدرس: هر چیزی زیر /adminPanel = admin، بقیه = user
 *
 * ⚠️ این کلاس فقط «ظاهر منو» را تعیین می‌کند؛ دسترسی واقعی را حتماً با middleware (auth + role) محافظت کنید.
 */
class PanelRole
{
    public const ROLES = ['user', 'admin', 'support'];

    public static function current(): string
    {
        $user = auth()->user();
        $role = $user ? strtolower(trim((string) ($user->role ?? ''))) : '';

        if (in_array($role, self::ROLES, true)) {
            return $role;
        }

        $inAdminArea = request()->is('adminPanel*');

        if (app()->environment('local', 'testing') || config('app.debug')) {
            $param = request('role');

            if (in_array($param, ['admin', 'support'], true)) {
                session(['demo_role' => $param]);
            } elseif ($param === 'user') {
                session()->forget('demo_role');
            }

            $demo = session('demo_role');
            if ($inAdminArea && in_array($demo, ['admin', 'support'], true)) {
                return $demo;
            }
        }

        return $inAdminArea ? 'admin' : 'user';
    }

    public static function isAdmin(): bool
    {
        return self::current() === 'admin';
    }
}
