<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Site;
use App\Http\Controllers\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| وام‌جو – مسیرهای وب
|--------------------------------------------------------------------------
| ساختار: سایت عمومی ← پنل کاربر (/userPanel) ← پنل مدیریت و پشتیبان (/adminPanel)
| نام‌گذاری: site.* | user.* | admin.*  → مثال: route('user.ads.edit', $ad)
|
| میان‌افزارها:
|   auth   → کاربر باید وارد شده باشد
|   active → حساب مسدود نباشد (app/Http/Middleware/EnsureAccountIsActive.php)
|   kyc    → احراز هویت انجام شده باشد (EnsureKycVerified)
|   role   → نقش کاربر در لیست باشد (EnsureUserHasRole) : role:admin,support
*/

/* ===================== ورود و ثبت‌نام ===================== */

Route::middleware('guest')->group(function () {
    Route::get('/login', [OtpController::class, 'showLogin'])->name('login');
    Route::get('/register', fn () => redirect()->route('login'))->name('register');
    Route::post('/login', [OtpController::class, 'attempt'])->middleware('throttle:10,1')->name('login.attempt');
    Route::post('/login/verify', [OtpController::class, 'verifyForm'])->middleware('throttle:10,1')->name('login.verify');
    
    Route::post('/auth/otp/send', [OtpController::class, 'send'])->middleware('throttle:6,1')->name('auth.otp.send');
    Route::post('/auth/otp/verify', [OtpController::class, 'verify'])->middleware('throttle:10,1')->name('auth.otp.verify');
    Route::post('/auth/register', [OtpController::class, 'register'])->middleware('throttle:10,1')->name('auth.register');
});

Route::post('/logout', [OtpController::class, 'logout'])->middleware('auth')->name('logout');

/* ===================== سایت عمومی ===================== */

Route::name('site.')->group(function () {
    Route::get('/', [Site\HomeController::class, 'index'])->name('home');
    Route::get('/ads', [Site\AdController::class, 'index'])->name('ads');
    Route::get('/ads/create', fn () => redirect()->route('site.ads.create'));   // لینک نویگیشن موبایل
    Route::get('/ads/{ad}', [Site\AdController::class, 'show'])->name('ads.show');

    Route::get('/rules', [Site\PageController::class, 'rules'])->name('rules');
    Route::get('/faq', [Site\PageController::class, 'faq'])->name('faq');

    // ثبت آگهی (ورود + احراز هویت لازم است)
    Route::middleware(['auth', 'active', 'kyc'])->group(function () {
        Route::get('/add', [Site\AdController::class, 'create'])->name('ads.create');
        Route::post('/ads', [Site\AdController::class, 'store'])->name('ads.store');
        Route::post('/ads/{ad}/request', [Site\AdRequestController::class, 'store'])->name('ads.request');
    });

    // پرداخت
    Route::get('/payment/result', [Site\PaymentController::class, 'result'])->name('payment.result');
    Route::get('/payment/{loanRequest}', [Site\PaymentController::class, 'initiate'])
        ->middleware(['auth', 'active'])->name('payment.start');
});

/* ===================== پنل کاربر ===================== */

Route::prefix('userPanel')->name('user.')->middleware(['auth', 'active'])->group(function () {
    Route::get('/', [User\DashboardController::class, 'index'])->name('dashboard');

    // درخواست های من (خریدار)
    Route::get('/requests', [User\RequestController::class, 'index'])->name('requests');
    Route::post('/requests/{loanRequest}/pay', [User\RequestController::class, 'pay'])->name('requests.pay');
    Route::post('/requests/{loanRequest}/confirm-transfer', [User\RequestController::class, 'confirmTransfer'])
        ->name('requests.confirm');

    // آگهی های من (فروشنده)
    Route::prefix('ads')->name('ads.')->group(function () {
        Route::get('/', [User\AdController::class, 'index'])->name('index');

        Route::middleware('kyc')->group(function () {
            Route::get('/create', [User\AdController::class, 'create'])->name('create');
            Route::post('/', [User\AdController::class, 'store'])->name('store');
        });

        Route::get('/{ad}/offers', [User\AdController::class, 'offers'])->name('offers');
        Route::get('/{ad}/edit', [User\AdController::class, 'edit'])->name('edit');
        Route::put('/{ad}', [User\AdController::class, 'update'])->name('update');
        Route::delete('/{ad}', [User\AdController::class, 'destroy'])->name('destroy');
        Route::post('/{ad}/transfer', [User\AdController::class, 'transfer'])->name('transfer');
    });

    // تایید / رد درخواست خریداران
    Route::post('/ads/{ad}/offers/{offer}/accept', [User\AdOfferController::class, 'accept'])->name('offers.accept');
    Route::post('/ads/{ad}/offers/{offer}/reject', [User\AdOfferController::class, 'reject'])->name('offers.reject');
    Route::post('/offers/{offer}/accept', [User\AdOfferController::class, 'accept'])->name('offers.accept.flat');
    Route::post('/offers/{offer}/reject', [User\AdOfferController::class, 'reject'])->name('offers.reject.flat');

    // رادار وام
    Route::get('/radar', [User\RadarController::class, 'index'])->name('radar');
    Route::post('/radar', [User\RadarController::class, 'store'])->name('radar.store');
    Route::put('/radar/{radar}', [User\RadarController::class, 'update'])->name('radar.update');
    Route::delete('/radar/{radar}', [User\RadarController::class, 'destroy'])->name('radar.destroy');
    Route::patch('/radar/{radar}/toggle', [User\RadarController::class, 'toggle'])->name('radar.toggle');
    Route::delete('/radar/matches/{match}', [User\RadarController::class, 'destroyMatch'])->name('radar.matches.destroy');

    // تیکت های پشتیبانی
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [User\TicketController::class, 'index'])->name('index');
        Route::get('/create', [User\TicketController::class, 'create'])->name('create');
        Route::post('/', [User\TicketController::class, 'store'])->name('store');
        Route::get('/{ticket}', [User\TicketController::class, 'show'])->name('show');
        Route::post('/{ticket}/reply', [User\TicketController::class, 'reply'])->name('reply');
        Route::post('/{ticket}/close', [User\TicketController::class, 'close'])->name('close');
    });

    // اطلاعات هویتی / احراز هویت
    Route::get('/verify', [User\VerificationController::class, 'index'])->name('verify');
    Route::post('/verify', [User\VerificationController::class, 'store'])->name('verify.store');

    // پرداخت های من
    Route::get('/payments', [User\PaymentController::class, 'index'])->name('payments');
    Route::get('/payments/export', [User\PaymentController::class, 'export'])->name('payments.export');

    // آگهی های برگزیده
    Route::get('/favorites', [User\FavoriteController::class, 'index'])->name('favorites');
    Route::post('/favorites/{ad}', [User\FavoriteController::class, 'store'])->name('favorites.store');
    Route::post('/favorites/{favorite}/remove', [User\FavoriteController::class, 'destroy'])->name('favorites.remove');
    Route::delete('/favorites/{favorite}', [User\FavoriteController::class, 'destroy'])->name('favorites.destroy');
});

/* ===================== پنل مدیریت و پشتیبان ===================== */

Route::prefix('adminPanel')->name('admin.')->middleware(['auth', 'active', 'role:admin,support'])->group(function () {
    Route::redirect('/', '/adminPanel/dashboard');
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [Admin\UserController::class, 'index'])->name('index');
        Route::get('/{user}', [Admin\UserController::class, 'show'])->name('show');
        Route::post('/{user}/block', [Admin\UserController::class, 'block'])->middleware('role:admin')->name('block');
        Route::post('/{user}/unblock', [Admin\UserController::class, 'unblock'])->middleware('role:admin')->name('unblock');
        Route::post('/{user}/note', [Admin\UserController::class, 'note'])->name('note');
    });

    // احراز هویت کاربران
    Route::get('/verifications', [Admin\VerificationController::class, 'index'])->name('verifications');
    Route::get('/verifications/{kyc}/{kind}', [Admin\VerificationController::class, 'document'])
        ->whereIn('kind', ['card', 'selfie'])->name('verifications.document');
    Route::post('/verifications/{kyc}/approve', [Admin\VerificationController::class, 'approve'])->name('verifications.approve');
    Route::post('/verifications/{kyc}/reject', [Admin\VerificationController::class, 'reject'])->name('verifications.reject');

    // آگهی ها
    Route::get('/ads', [Admin\AdController::class, 'index'])->name('ads');
    Route::post('/ads/{ad}/approve', [Admin\AdController::class, 'approve'])->name('ads.approve');
    Route::post('/ads/{ad}/reject', [Admin\AdController::class, 'reject'])->name('ads.reject');
    Route::patch('/ads/{ad}/feature', [Admin\AdController::class, 'feature'])->name('ads.feature');

    // امور مالی (فقط مدیر)
    Route::middleware('role:admin')->group(function () {
        Route::get('/finance', [Admin\FinanceController::class, 'index'])->name('finance');
        Route::post('/finance/payouts/{transaction}', [Admin\FinanceController::class, 'payout'])->name('finance.payout');
        Route::post('/finance/refunds/{transaction}', [Admin\FinanceController::class, 'refund'])->name('finance.refund');
    });

    // تیکت های پشتیبانی
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [Admin\TicketController::class, 'index'])->name('index');
        Route::get('/{ticket}', [Admin\TicketController::class, 'show'])->name('show');
        Route::post('/{ticket}/reply', [Admin\TicketController::class, 'reply'])->name('reply');
        Route::post('/{ticket}/update', [Admin\TicketController::class, 'update'])->name('update');
        Route::post('/{ticket}/close', [Admin\TicketController::class, 'close'])->name('close');
    });
});

/* آدرس‌های قدیمی /panel/* (نسخه فرانت‌اند) به پنل کاربر هدایت می‌شوند */
Route::redirect('/panel', '/userPanel');
Route::redirect('/panel/{any}', '/userPanel/{any}')->where('any', '.*');
