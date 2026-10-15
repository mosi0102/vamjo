<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * محدود کردن مسیر به نقش‌های مشخص. نمونه: ->middleware('role:admin,support')
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'برای ادامه باید وارد حساب خود شوید.'], 401)
                : redirect()->guest(route('login'));
        }

        if (! $user->isRole(...$roles)) {
            abort($request->expectsJson() ? 403 : 403, 'شما دسترسی لازم برای این بخش را ندارید.');
        }

        return $next($request);
    }
}
