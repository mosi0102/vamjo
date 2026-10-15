<?php

namespace App\Providers;

use App\Models\Ad;
use App\Models\KycVerification;
use App\Models\Ticket;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // بج‌های منوی پنل مدیریت: تعداد موارد منتظر اقدام (احراز هویت، آگهی، تیکت)
        View::composer('userPanel.partials.sidebar', function ($view) {
            $view->with('badges', $this->panelBadges());
        });
    }

    /** @return array<string, int> */
    private function panelBadges(): array
    {
        $user = auth()->user();

        if (! $user?->isStaff()) {
            return [];
        }

        try {
            return [
                '/adminPanel/verifications' => KycVerification::query()->pending()->count(),
                '/adminPanel/ads' => Ad::query()->pendingReview()->count(),
                '/adminPanel/tickets' => Ticket::query()
                    ->whereIn('status', [Ticket::STATUS_PENDING, Ticket::STATUS_ANSWERED])->count(),
            ];
        } catch (Throwable) {
            return [];   // پیش از اجرای مهاجرت‌ها جدول‌ها موجود نیستند
        }
    }
}
