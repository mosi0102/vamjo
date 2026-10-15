<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * برای ثبت آگهی و ارسال درخواست خرید، کاربر باید احراز هویت شده باشد.
 */
class EnsureKycVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_verified) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'برای این کار ابتدا احراز هویت خود را تکمیل کنید.',
                ], 403);
            }

            return redirect()
                ->route('user.verify')
                ->with('warning', 'برای این کار ابتدا احراز هویت خود را تکمیل کنید.');
        }

        return $next($request);
    }
}
