<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * کاربران مسدودشده نباید به پنل دسترسی داشته باشند.
 */
class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isBlocked()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = 'حساب کاربری شما مسدود شده است.'.($user->block_reason ? ' دلیل: '.$user->block_reason : '');

            return $request->expectsJson()
                ? response()->json(['message' => $message], 403)
                : redirect()->route('login')->withErrors(['mobile' => $message]);
        }

        return $next($request);
    }
}
