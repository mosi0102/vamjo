<?php

namespace App\Support;

use App\Models\Ad;
use App\Models\KycVerification;
use App\Models\LoanRequest;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * آمار و نمودارهای داشبورد کاربر و مدیر.
 */
class Dashboard
{
    /* ============================ داشبورد کاربر ============================ */

    public static function userStats(User $user): array
    {
        $ads = $user->ads()->count();
        $requests = $user->purchases()->count() + $user->sales()->count();
        $paid = (int) $user->transactions()->ofType(Transaction::TYPE_PAYMENT)
            ->ofStatus(Transaction::STATUS_ESCROW, Transaction::STATUS_RELEASED)->sum('amount');
        $received = (int) $user->transactions()->ofType(Transaction::TYPE_PAYOUT)
            ->where('status', Transaction::STATUS_RELEASED)->sum('amount');

        $spark = fn (callable $query) => collect(range(5, 0))
            ->map(fn (int $back) => $query(now()->subMonths($back)->startOfMonth(), now()->subMonths($back)->endOfMonth()))
            ->all();

        return [
            [
                'label' => 'تعداد آگهی های من', 'icon' => 'doc', 'value' => $ads, 'prefix' => '+',
                'tone' => '', 'trend' => Fa::digits($user->ads()->where('created_at', '>=', now()->startOfMonth())->count()).' این ماه',
                'up' => true, 'spark' => $spark(fn ($from, $to) => $user->ads()->whereBetween('created_at', [$from, $to])->count()),
            ],
            [
                'label' => 'تعداد درخواست های من', 'icon' => 'clipboard', 'value' => $requests, 'prefix' => '+',
                'tone' => '', 'trend' => Fa::digits($user->purchases()->where('created_at', '>=', now()->startOfMonth())->count()).' این ماه',
                'up' => true, 'spark' => $spark(fn ($from, $to) => $user->purchases()->whereBetween('created_at', [$from, $to])->count()),
            ],
            [
                'label' => 'کل مبلغ پرداختی من', 'icon' => 'wallet', 'value' => $paid, 'unit' => 'تومان',
                'tone' => 'red', 'trend' => 'در امانت و تسویه', 'up' => false,
                'spark' => $spark(fn ($from, $to) => (int) round($user->transactions()->ofType(Transaction::TYPE_PAYMENT)
                    ->whereBetween('created_at', [$from, $to])->sum('amount') / 1_000_000)),
            ],
            [
                'label' => 'کل مبلغ دریافتی من', 'icon' => 'bag', 'value' => $received, 'unit' => 'تومان',
                'tone' => 'green', 'trend' => 'واریز شده', 'up' => true,
                'spark' => $spark(fn ($from, $to) => (int) round($user->transactions()->ofType(Transaction::TYPE_PAYOUT)
                    ->whereBetween('created_at', [$from, $to])->sum('amount') / 1_000_000)),
            ],
        ];
    }

    public static function userQuickActions(User $user): array
    {
        $verified = $user->is_verified;

        return [
            ['label' => 'ثبت آگهی جدید', 'sub' => 'وام خود را بفروشید', 'icon' => 'plus', 'url' => $verified ? '/add' : '/userPanel/verify', 'locked' => ! $verified],
            ['label' => 'مشاهده آگهی ها', 'sub' => 'وام مورد نیاز را پیدا کنید', 'icon' => 'book', 'url' => '/ads', 'locked' => false],
            ['label' => 'رادار وام', 'sub' => 'اعلان آگهی‌های مرتبط', 'icon' => 'radar', 'url' => '/userPanel/radar', 'locked' => false],
            ['label' => 'تیکت پشتیبانی', 'sub' => 'پاسخ‌گویی ۲۴ ساعته', 'icon' => 'chat', 'url' => '/userPanel/tickets', 'locked' => false],
        ];
    }

    /** مراحل تکمیل احراز هویت: [برچسب, انجام‌شده؟] */
    public static function verifySteps(User $user): array
    {
        $kyc = $user->latestKyc;

        return [
            ['سلفی با کارت ملی', (bool) $kyc?->selfie_path],
            ['تصویر کارت ملی', (bool) $kyc?->national_card_path],
            ['شماره شبا', (bool) ($kyc?->sheba ?: $user->sheba)],
            ['تاریخ تولد', (bool) ($kyc?->birth_date ?: $user->birth_date)],
        ];
    }

    /** درخواست‌های اخیر کاربر برای کارت‌های داشبورد */
    public static function recentRequests(User $user, int $limit = 3): array
    {
        return $user->purchases()->with('ad.bank')->latest()->limit($limit)->get()->map(function (LoanRequest $request) {
            $ad = $request->ad;
            $bank = $ad?->bank;

            return [
                'id' => $ad?->id,
                'bank' => $bank?->name ?? '',
                'l' => $bank?->initial ?? '',
                'image' => $bank?->image ?? '1',
                'c' => $bank?->color ?? '#1e5a45',
                'amount' => $request->amount,
                'price' => $request->price,
                'status' => match ($request->status) {
                    LoanRequest::STATUS_COMPLETED => 'approved',
                    LoanRequest::STATUS_REJECTED, LoanRequest::STATUS_CANCELLED, LoanRequest::STATUS_EXPIRED => 'rejected',
                    default => 'pending',
                },
            ];
        })->all();
    }

    /** آخرین آگهی ویژه منتشرشده به‌عنوان «حراجی» داشبورد */
    public static function auction(): array
    {
        $ad = Ad::query()->published()->with('bank')->orderByDesc('featured')->latest()->first();

        if (! $ad) {
            return [];
        }

        return [
            'bank' => $ad->bank?->name ?? '',
            'image' => $ad->bank?->image ?? '1',
            'l' => $ad->bank?->initial ?? '',
            'c' => $ad->bank?->color ?? '#1e5a45',
            'amount' => $ad->amount,
            'months' => $ad->months,
            'rate' => $ad->rate,
            'min_buy' => $ad->price,
            'offers' => $ad->offers()->count(),
            'remaining' => max(0, now()->diffInSeconds($ad->created_at->copy()->addDays(3), false)),
        ];
    }

    /** نمودار شش‌ماهه پرداخت/دریافت کاربر (به میلیون تومان) */
    public static function userChart(User $user): array
    {
        $labels = [];
        $pay = [];
        $recv = [];

        foreach (range(5, 0) as $back) {
            $from = now()->subMonths($back)->startOfMonth();
            $to = now()->subMonths($back)->endOfMonth();
            $labels[] = Jalali::monthLabel($from);
            $pay[] = (int) round($user->transactions()->ofType(Transaction::TYPE_PAYMENT)
                ->whereBetween('created_at', [$from, $to])->sum('amount') / 1_000_000);
            $recv[] = (int) round($user->transactions()->ofType(Transaction::TYPE_PAYOUT)
                ->whereBetween('created_at', [$from, $to])->sum('amount') / 1_000_000);
        }

        return ['labels' => $labels, 'pay' => $pay, 'recv' => $recv];
    }

    /** فعالیت‌های اخیر کاربر */
    public static function userActivities(User $user, int $limit = 4): array
    {
        return $user->activityLogs()->latest()->limit($limit)->get()->map(fn ($log) => [
            'icon' => $log->icon(),
            'text' => $log->description ?? $log->action,
            'time' => Fa::ago($log->created_at),
        ])->all();
    }

    /* ============================ داشبورد مدیریت ============================ */

    public static function adminKpis(User $user): array
    {
        $kpis = [
            ['کل کاربران', Fa::number(User::query()->role(User::ROLE_USER)->count()), 'users', '', null],
            ['ثبت نام امروز', Fa::number(User::query()->whereDate('created_at', today())->count()), 'plus', 'green', null],
            ['احراز هویت منتظر بررسی', Fa::number(KycVerification::pending()->count()), 'shield', 'amber', '/adminPanel/verifications'],
            ['آگهی منتظر تایید', Fa::number(Ad::query()->pendingReview()->count()), 'book', 'amber', '/adminPanel/ads'],
            ['آگهی های فعال', Fa::number(Ad::query()->published()->count()), 'check', '', null],
            ['تیکت های بی پاسخ', Fa::number(Ticket::query()->pendingAnswer()->count()), 'chat', 'amber', '/adminPanel/tickets'],
        ];

        if ($user->isAdmin()) {
            $escrow = (int) Transaction::query()->escrow()->sum('amount');
            $commission = (int) Transaction::query()
                ->where('created_at', '>=', now()->startOfMonth())
                ->sum('fee');

            $kpis[] = ['موجودی امانت', Fa::digits(number_format($escrow)).' <small>تومان</small>', 'wallet', 'green', '/adminPanel/finance'];
            $kpis[] = ['کمیسیون این ماه', Fa::digits(number_format($commission)).' <small>تومان</small>', 'trend', 'green', '/adminPanel/finance'];
        }

        return $kpis;
    }

    /** نمودار هفت روز اخیر: حجم تراکنش‌ها و ثبت‌نام‌ها */
    public static function adminChart(): array
    {
        $labels = [];
        $pay = [];
        $recv = [];

        foreach (range(6, 0) as $back) {
            $day = Carbon::today()->subDays($back);
            $labels[] = Jalali::weekday($day);
            $pay[] = (int) round(Transaction::query()->whereDate('created_at', $day)->sum('amount') / 1_000_000);
            $recv[] = User::query()->whereDate('created_at', $day)->count();
        }

        return ['labels' => $labels, 'pay' => $pay, 'recv' => $recv];
    }

    public static function adminKycQueue(int $limit = 4): array
    {
        return KycVerification::query()->pending()->with('user')->latest()
            ->limit($limit)->get()
            ->map(fn (KycVerification $kyc) => [$kyc->user?->display_name ?? '', Fa::ago($kyc->created_at)])
            ->all();
    }

    public static function adminAdQueue(int $limit = 3): array
    {
        return Ad::query()->pendingReview()->with(['user', 'bank'])->latest()
            ->limit($limit)->get()
            ->map(fn (Ad $ad) => [
                'وام '.Fa::digits(number_format($ad->amount)).' بانک '.($ad->bank?->name ?? ''),
                $ad->user?->display_name ?? '',
                Fa::ago($ad->created_at),
                $ad->bank?->image ?? '1',
            ])->all();
    }

    public static function adminTicketQueue(int $limit = 3): array
    {
        return Ticket::query()->pendingAnswer()->with('user')->oldest('created_at')
            ->limit($limit)->get()
            ->map(fn (Ticket $ticket) => [$ticket->subject, $ticket->user?->display_name ?? '', $ticket->priority, $ticket->id])
            ->all();
    }

    public static function adDistribution(): array
    {
        return [
            ['منتشر شده', Ad::query()->published()->count(), '#2e9a4f'],
            ['منتظر تایید', Ad::query()->pendingReview()->count(), '#d9a93f'],
            ['در حال معامله', Ad::query()->where('status', Ad::STATUS_TRANSFER)->count(), '#3e4ea0'],
            ['تکمیل شده', Ad::query()->where('status', Ad::STATUS_COMPLETED)->count(), '#1e5a45'],
            ['رد شده', Ad::query()->where('status', Ad::STATUS_REJECTED)->count(), '#d33'],
        ];
    }

    public static function adminActivities(int $limit = 4): array
    {
        return \App\Models\ActivityLog::query()->latest()->limit($limit)->get()->map(fn ($log) => [
            $log->icon(),
            $log->description ?? $log->action,
            Fa::ago($log->created_at),
        ])->all();
    }

    /** خلاصه مالی صفحه امور مالی */
    public static function financeSummary(): array
    {
        $monthPaid = (int) Transaction::query()->ofType(Transaction::TYPE_PAYMENT)
            ->where('created_at', '>=', now()->startOfMonth())->sum('amount');

        return [
            'month_volume' => $monthPaid,
            'escrow' => (int) Transaction::query()->escrow()->sum('amount'),
            'commission' => (int) Transaction::query()->where('created_at', '>=', now()->startOfMonth())->sum('fee'),
            'due_payout' => (int) Transaction::query()->ofType(Transaction::TYPE_PAYOUT)
                ->ofStatus(Transaction::STATUS_PENDING)->sum('amount'),
        ];
    }
}
