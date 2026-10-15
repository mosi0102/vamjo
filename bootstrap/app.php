<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // میان‌افزارهای اختصاصی پلتفرم وامجو
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'kyc' => \App\Http\Middleware\EnsureKycVerified::class,
            'active' => \App\Http\Middleware\EnsureAccountIsActive::class,
        ]);

        // کاربر مهمان به صفحه ورود هدایت می‌شود
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('site.home'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
