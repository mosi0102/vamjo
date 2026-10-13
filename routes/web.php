<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| وام‌جو – مسیرهای وب
|--------------------------------------------------------------------------
| ساختار: سایت عمومی ← پنل کاربر (/userPanel) ← پنل مدیریت و پشتیبان (/adminPanel)
| فعلاً همه Route::view هستند (فرانت‌اند)؛ هر گروه را بعداً به کنترلر منتقل کنید.
| نام‌گذاری: site.* | user.* | admin.*  → مثال: route('user.ads.edit', 12)
*/

/* ===================== سایت عمومی ===================== */
Route::name('site.')->group(function () {
    Route::view('/', 'website.index')->name('home');
    Route::view('/ads', 'website.list')->name('ads');
    Route::view('/ads/{id}', 'website.details')->whereNumber('id')->name('ads.show');   // بعداً: کنترلر + مدل
    Route::view('/add', 'website.addNew')->name('ads.create');                          // ثبت آگهی (نیازمند ورود + احراز هویت)
    Route::view('/rules', 'website.rules')->name('rules');                              // قوانین و مقررات
    Route::view('/faq', 'website.faq')->name('faq');                                    // سوالات متداول
});

/* ===================== پنل کاربر ===================== */
Route::prefix('userPanel')->name('user.')->group(function () {   // ->middleware('auth')
    Route::view('/', 'userPanel.index')->name('dashboard');
    Route::view('/requests', 'userPanel.requests.index')->name('requests');

    Route::prefix('ads')->name('ads.')->group(function () {
        Route::view('/', 'userPanel.ads.index')->name('index');
        Route::view('/{id}/offers', 'userPanel.ads.offers')->whereNumber('id')->name('offers');
        Route::view('/{id}/edit', 'userPanel.ads.edit')->whereNumber('id')->name('edit');
    });

    Route::view('/radar', 'userPanel.radar.index')->name('radar');

    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::view('/', 'userPanel.ticket.index')->name('index');
        Route::view('/create', 'userPanel.ticket.create')->name('create');
        Route::view('/{id}', 'userPanel.ticket.show')->whereNumber('id')->name('show');
    });

    Route::view('/verify', 'userPanel.profile.index')->name('verify');   // اطلاعات هویتی / احراز هویت
    Route::view('/payments', 'userPanel.payments.index')->name('payments');
    Route::view('/favorites', 'userPanel.favorite.index')->name('favorites');
});

/* ===================== پنل مدیریت و پشتیبان ===================== */
Route::prefix('adminPanel')->name('admin.')->group(function () {   // ->middleware(['auth', 'role:admin,support'])
    Route::redirect('/', '/adminPanel/dashboard');
    Route::view('/dashboard', 'adminPanel.index')->name('dashboard');

    Route::prefix('users')->name('users.')->group(function () {
        Route::view('/', 'adminPanel.users.index')->name('index');
        Route::view('/{id}', 'adminPanel.users.details')->whereNumber('id')->name('show');
    });

    Route::view('/verifications', 'adminPanel.users.verify')->name('verifications');   // احراز هویت کاربران جدید
    Route::view('/ads', 'adminPanel.ads.index')->name('ads');
    Route::view('/finance', 'adminPanel.payments.index')->name('finance');             // ->middleware('role:admin') فقط مدیر

    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::view('/', 'adminPanel.ticket.index')->name('index');
        Route::view('/{id}', 'adminPanel.ticket.show')->whereNumber('id')->name('show');   // صفحه پاسخ تیکت (در لیست شما نبود؛ اضافه شد)
    });
});

/*
|--------------------------------------------------------------------------
| مسیرهای POST/PUT/DELETE که فرانت‌اند به آن‌ها ارسال می‌کند (با ساخت کنترلرها اضافه کنید)
|--------------------------------------------------------------------------
| احراز ورود : POST /auth/otp/send | /auth/otp/verify | /auth/register | /logout
| سایت       : POST /ads | POST /ads/{id}/request | GET /payment/{id}  و  /payment/result
| پنل کاربر  : PUT|DELETE /userPanel/ads/{id} | POST /userPanel/ads/{id}/transfer
|              POST /userPanel/ads/{id}/offers/{offer}/accept|reject
|              POST /userPanel/requests/{id}/pay | /confirm-transfer
|              POST|PUT|DELETE /userPanel/radar[/{id}] | PATCH /userPanel/radar/{id}/toggle | DELETE /userPanel/radar/matches/{id}
|              POST /userPanel/tickets | /{id}/reply | /{id}/close | POST /userPanel/verify | DELETE /userPanel/favorites/{id}
| پنل مدیریت : POST /adminPanel/verifications/{id}/approve|reject | /adminPanel/ads/{id}/approve|reject | PATCH /adminPanel/ads/{id}/feature
|              POST /adminPanel/users/{id}/block|unblock|note | /adminPanel/finance/payouts/{tx} | /adminPanel/finance/refunds/{tx}
|              POST /adminPanel/tickets/{id}/reply|update|close
*/