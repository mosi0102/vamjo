<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Ad;
use App\Models\AdDocument;
use App\Models\AdOffer;
use App\Models\Bank;
use App\Models\Faq;
use App\Models\Favorite;
use App\Models\KycVerification;
use App\Models\LoanRadar;
use App\Models\LoanRequest;
use App\Models\RadarMatch;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * داده نمونه واقع‌گرا برای همه صفحات (قابل تکرار: با migrate:fresh --seed اجرا کنید).
 */
class DemoDataSeeder extends Seeder
{
    private array $banks = [];

    private const SHEBAS = [
        'IR062960000000100324200001',
        'IR610610000000700140150007',
        'IR540540000000800160170008',
        'IR180120000000300600700003',
        'IR150150000000500100110005',
        'IR012170000000200400500002',
        'IR190190000000400800900004',
        'IR560560000000600120130006',
    ];

    public function run(): void
    {
        Model::unguard();

        $this->banks = Bank::query()->pluck('id', 'slug')->all();

        $staff = $this->seedStaff();
        $users = $this->seedUsers();
        $this->seedKyc($users);
        $ads = $this->seedAds($users);
        $deals = $this->seedDeals($ads, $users);
        $this->seedTransactions($deals, $users);
        $this->seedTickets($users, $staff['support']);
        $this->seedRadars($users, $ads);
        $this->seedFavorites($users, $ads);
        $this->seedLogs($users, $staff['admin']);

        Model::reguard();
    }

    /* ------------------------------ کاربران ------------------------------ */

    private function seedStaff(): array
    {
        $admin = User::updateOrCreate(['mobile' => '09120000001'], [
            'name' => 'مدیر وامجو',
            'first_name' => 'مدیر',
            'last_name' => 'وامجو',
            'email' => 'admin@vamjou.test',
            'password' => Hash::make('password'),
            'national_id' => $this->nationalId(1),
            'role' => User::ROLE_ADMIN,
            'kyc_status' => User::KYC_VERIFIED,
            'city' => 'تهران',
            'sheba' => self::SHEBAS[0],
            'bank_name' => 'بانک ملت',
        ]);

        $support = User::updateOrCreate(['mobile' => '09120000002'], [
            'name' => 'کارشناس پشتیبانی',
            'first_name' => 'کارشناس',
            'last_name' => 'پشتیبانی',
            'email' => 'support@vamjou.test',
            'password' => Hash::make('password'),
            'national_id' => $this->nationalId(2),
            'role' => User::ROLE_SUPPORT,
            'kyc_status' => User::KYC_VERIFIED,
            'city' => 'مشهد',
            'sheba' => self::SHEBAS[1],
            'bank_name' => 'بانک شهر',
        ]);

        return ['admin' => $admin, 'support' => $support];
    }

    /** @return array<int, User> */
    private function seedUsers(): array
    {
        $names = [
            ['علی', 'رضایی', '09301021000', 'سبزوار', 'verified'],
            ['مریم', 'احمدی', '09121234567', 'تهران', 'verified'],
            ['حسین', 'کریمی', '09153334455', 'مشهد', 'pending'],
            ['زهرا', 'محمدی', '09351112233', 'نیشابور', 'unverified'],
            ['رضا', 'نوری', '09171230001', 'سبزوار', 'verified'],
            ['سارا', 'حسینی', '09302223344', 'مشهد', 'rejected'],
            ['محمد', 'طاهری', '09154443322', 'اصفهان', 'verified'],
            ['نرگس', 'صادقی', '09367778899', 'شیراز', 'pending'],
            ['امیر', 'جعفری', '09125556677', 'تهران', 'verified'],
            ['فاطمه', 'کاظمی', '09391110022', 'سبزوار', 'unverified'],
            ['مهدی', 'رحیمی', '09151119988', 'مشهد', 'verified'],
            ['لیلا', 'موسوی', '09381234567', 'سبزوار', 'pending'],
        ];

        $users = [];

        foreach ($names as $index => [$first, $last, $mobile, $city, $kyc]) {
            $users[] = User::updateOrCreate(['mobile' => $mobile], [
                'name' => $first.' '.$last,
                'first_name' => $first,
                'last_name' => $last,
                'national_id' => $this->nationalId($index + 10),
                'city' => $city,
                'sheba' => self::SHEBAS[$index % count(self::SHEBAS)],
                'bank_name' => 'بانک ملت',
                'birth_date' => '13'.(70 + $index % 12).'/0'.(1 + $index % 9).'/1'.($index % 9),
                'role' => User::ROLE_USER,
                'kyc_status' => $kyc,
                'blocked_at' => $index === 5 ? now()->subDays(3) : null,
                'block_reason' => $index === 5 ? 'مدارک جعلی احراز هویت' : null,
                'last_login_at' => now()->subHours($index * 3),
                'last_login_ip' => '5.112.40.'.(10 + $index),
                'created_at' => now()->subDays(30 - $index),
            ]);
        }

        return $users;
    }

    private function seedKyc(array $users): void
    {
        foreach ($users as $index => $user) {
            if ($user->kyc_status === User::KYC_UNVERIFIED) {
                continue;
            }

            KycVerification::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'national_id' => $user->national_id,
                    'birth_date' => $user->birth_date,
                    'sheba' => $user->sheba,
                    'bank_name' => $user->bank_name,
                    'national_card_path' => null,
                    'selfie_path' => null,
                    'status' => match ($user->kyc_status) {
                        User::KYC_VERIFIED => KycVerification::STATUS_APPROVED,
                        User::KYC_REJECTED => KycVerification::STATUS_REJECTED,
                        default => KycVerification::STATUS_PENDING,
                    },
                    'reject_reason' => $user->kyc_status === User::KYC_REJECTED
                        ? 'تصویر سلفی همراه با کارت ملی واضح نیست؛ لطفاً با نور مناسب دوباره عکس بگیرید.'
                        : null,
                    'reviewed_at' => in_array($user->kyc_status, [User::KYC_VERIFIED, User::KYC_REJECTED], true)
                        ? now()->subDays(2) : null,
                    'created_at' => now()->subHours($index * 2 + 1),
                ]
            );
        }
    }

    /* ------------------------------ آگهی‌ها ------------------------------ */

    /** @return array<int, Ad> */
    private function seedAds(array $users): array
    {
        $rows = [
            // [مالک, بانک, مبلغ, ماه, سود, قیمت, وضعیت, ویژه, شهر, نوع]
            [0, 'resalat', 300_000_000, 60, 4, 30_000_000, Ad::STATUS_PUBLISHED, true, 'سبزوار', 'qarz'],
            [1, 'shahr', 100_000_000, 24, 23, 20_000_000, Ad::STATUS_PUBLISHED, false, 'تهران', 'marriage'],
            [4, 'javidan', 250_000_000, 48, 18, 45_000_000, Ad::STATUS_PUBLISHED, true, 'سبزوار', 'housing'],
            [6, 'tejarat', 120_000_000, 24, 20, 25_000_000, Ad::STATUS_PUBLISHED, false, 'اصفهان', 'child'],
            [8, 'mehr-iran', 100_000_000, 24, 23, 20_000_000, Ad::STATUS_PUBLISHED, false, 'تهران', 'mehrbani'],
            [10, 'keshavarzi', 250_000_000, 36, 18, 45_000_000, Ad::STATUS_PUBLISHED, false, 'مشهد', 'commercial'],
            [3, 'saderat', 150_000_000, 36, 18, 30_000_000, Ad::STATUS_PENDING, false, 'نیشابور', 'medical'],
            [7, 'melli', 200_000_000, 48, 18, 40_000_000, Ad::STATUS_PENDING, false, 'شیراز', 'qarz'],
            [9, 'mellat', 300_000_000, 60, 4, 55_000_000, Ad::STATUS_REJECTED, false, 'سبزوار', 'qarz'],
            [5, 'javidan', 250_000_000, 36, 18, 45_000_000, Ad::STATUS_TRANSFER, false, 'مشهد', 'housing'],
            [2, 'sepah', 100_000_000, 24, 23, 20_000_000, Ad::STATUS_COMPLETED, false, 'مشهد', 'qarz'],
            [11, 'shahr', 120_000_000, 24, 23, 25_000_000, Ad::STATUS_TRANSFER, false, 'سبزوار', 'marriage'],
        ];

        $ads = [];

        foreach ($rows as $index => [$ownerIndex, $bankSlug, $amount, $months, $rate, $price, $status, $featured, $city, $type]) {
            $createdAt = now()->subDays(20 - $index);
            $total = (int) round($amount * (1 + $rate / 100 * $months / 12));

            $ad = Ad::updateOrCreate(
                ['code' => (string) (1235890 + $index)],
                [
                    'user_id' => $users[$ownerIndex]->id,
                    'bank_id' => $this->banks[$bankSlug],
                    'type' => $type,
                    'amount' => $amount,
                    'months' => $months,
                    'rate' => $rate,
                    'price' => $price,
                    'max_price' => (int) round($amount * 0.45),
                    'installment' => (int) round($total / $months),
                    'total_repayment' => $total,
                    'guarantor' => 'یک ضامن کارمند رسمی یا کاسب دارای جواز کسب معتبر',
                    'city' => $city,
                    'description' => 'ضامن کارمند رسمی است و انتقال امتیاز در همان شهر انجام می‌شود.',
                    'sheba' => self::SHEBAS[$index % count(self::SHEBAS)],
                    'status' => $status,
                    'featured' => $featured,
                    'urgent' => $index % 4 === 0,
                    'views' => 40 + $index * 57,
                    'reject_reason' => $status === Ad::STATUS_REJECTED
                        ? 'تصویر مدرک خوانا نیست؛ آگهی را ویرایش و دوباره ارسال کنید.'
                        : null,
                    'published_at' => in_array($status, [Ad::STATUS_PUBLISHED, Ad::STATUS_TRANSFER, Ad::STATUS_COMPLETED], true)
                        ? $createdAt->copy()->addDay() : null,
                    'completed_at' => $status === Ad::STATUS_COMPLETED ? now()->subDays(4) : null,
                    'created_at' => $createdAt,
                ]
            );

            if ($status === Ad::STATUS_REJECTED) {
                AdDocument::updateOrCreate(
                    ['ad_id' => $ad->id, 'kind' => AdDocument::KIND_BALANCE],
                    ['path' => 'ads/demo/balance.jpg', 'original_name' => 'balance.jpg', 'size' => 182_000, 'must_replace' => true]
                );
            }

            $ads[] = $ad;
        }

        return $ads;
    }

    /* --------------------- درخواست‌ها، معاملات و تراکنش‌ها --------------------- */

    private function seedDeals(array $ads, array $users): array
    {
        $deals = [];

        // ۱) آگهی در حال معامله (بانک جاویدان – مالک: سارا حسینی) با ۵ درخواست
        $ad = $ads[9];
        $offerRows = [
            [0, 45_000_000, AdOffer::STATUS_PENDING, 2],
            [1, 42_000_000, AdOffer::STATUS_PENDING, 5],
            [2, 38_500_000, AdOffer::STATUS_PENDING, 26],
            [3, 35_000_000, AdOffer::STATUS_REJECTED, 50],
            [4, 30_000_000, AdOffer::STATUS_EXPIRED, 74],
        ];

        foreach ($offerRows as [$userIndex, $amount, $status, $hoursAgo]) {
            AdOffer::updateOrCreate(
                ['ad_id' => $ad->id, 'user_id' => $users[$userIndex]->id],
                [
                    'amount' => $amount,
                    'status' => $status,
                    'expires_at' => now()->addHours(24),
                    'responded_at' => $status === AdOffer::STATUS_PENDING ? null : now()->subHours($hoursAgo - 1),
                    'ip' => '5.112.40.'.(20 + $userIndex),
                    'created_at' => now()->subHours($hoursAgo),
                ]
            );
        }

        $accepted = AdOffer::query()->where('ad_id', $ad->id)->where('user_id', $users[0]->id)->first();

        $deals[] = LoanRequest::updateOrCreate(['code' => '1235897'], [
            'ad_id' => $ad->id,
            'offer_id' => $accepted?->id,
            'buyer_id' => $users[0]->id,
            'seller_id' => $ad->user_id,
            'amount' => $accepted?->amount ?? 45_000_000,
            'price' => $ad->amount,
            'step' => 6,
            'status' => LoanRequest::STATUS_ACTIVE,
            'pay_due_at' => now()->addDays(5),
            'paid_at' => now()->subDay(),
            'transfer_due_at' => now()->addDays(6),
        ]);

        // ۲) معامله در انتظار پرداخت (آگهی بانک شهر – خریدار: حسین کریمی)
        $waiting = $ads[11];
        $offer = AdOffer::updateOrCreate(
            ['ad_id' => $waiting->id, 'user_id' => $users[2]->id],
            ['amount' => 26_000_000, 'status' => AdOffer::STATUS_ACCEPTED, 'responded_at' => now()->subHours(6), 'expires_at' => now()->addDay(), 'created_at' => now()->subDay()]
        );

        $deals[] = LoanRequest::updateOrCreate(['code' => '1235896'], [
            'ad_id' => $waiting->id,
            'offer_id' => $offer->id,
            'buyer_id' => $users[2]->id,
            'seller_id' => $waiting->user_id,
            'amount' => $offer->amount,
            'price' => $waiting->amount,
            'step' => 3,
            'status' => LoanRequest::STATUS_ACTIVE,
            'pay_due_at' => now()->addDays(7),
        ]);

        // ۳) معامله تکمیل‌شده (بانک سپه – خریدار: امیر جعفری)
        $done = $ads[10];
        $deals[] = LoanRequest::updateOrCreate(['code' => '1235898'], [
            'ad_id' => $done->id,
            'buyer_id' => $users[8]->id,
            'seller_id' => $done->user_id,
            'amount' => 22_000_000,
            'price' => $done->amount,
            'step' => 7,
            'status' => LoanRequest::STATUS_COMPLETED,
            'paid_at' => now()->subDays(12),
            'transfer_confirmed_at' => now()->subDays(6),
            'completed_at' => now()->subDays(4),
        ]);

        // ۴) معامله رد‌شده (برای نمایش وضعیت رد در درخواست های من)
        $rejected = $ads[8];
        $deals[] = LoanRequest::updateOrCreate(['code' => '1235899'], [
            'ad_id' => $rejected->id,
            'buyer_id' => $users[0]->id,
            'seller_id' => $rejected->user_id,
            'amount' => 30_000_000,
            'price' => $rejected->amount,
            'step' => 2,
            'status' => LoanRequest::STATUS_REJECTED,
            'reject_reason' => 'متاسفانه درخواست شما رد شد.',
        ]);

        return $deals;
    }

    private function seedTransactions(array $deals, array $users): void
    {
        foreach ($deals as $index => $deal) {
            if ($deal->status === LoanRequest::STATUS_REJECTED) {
                continue;
            }
            $paid = $deal->paid_at;
            $fee = (int) round($deal->amount * config('vamjou.commission_rate') / 100);

            Transaction::updateOrCreate(['code' => (string) (58963000 + $index * 11)], [
                'request_id' => $deal->id,
                'ad_id' => $deal->ad_id,
                'user_id' => $deal->buyer_id,
                'counterparty_id' => $deal->seller_id,
                'type' => Transaction::TYPE_PAYMENT,
                'status' => $paid ? Transaction::STATUS_ESCROW : Transaction::STATUS_PENDING,
                'amount' => $deal->amount,
                'fee' => $fee,
                'net' => $deal->amount - $fee,
                'gateway' => 'زرین پال',
                'ref_id' => $paid ? (string) (58963200 + $index) : null,
                'paid_at' => $paid,
                'created_at' => $paid ?? $deal->created_at,
            ]);

            if ($deal->status === LoanRequest::STATUS_COMPLETED) {
                Transaction::updateOrCreate(['code' => (string) (58963300 + $index * 7)], [
                    'request_id' => $deal->id,
                    'ad_id' => $deal->ad_id,
                    'user_id' => $deal->seller_id,
                    'counterparty_id' => $deal->buyer_id,
                    'type' => Transaction::TYPE_PAYOUT,
                    'status' => Transaction::STATUS_RELEASED,
                    'amount' => $deal->amount - $fee,
                    'fee' => $fee,
                    'net' => $deal->amount - $fee,
                    'sheba' => $deal->seller?->sheba,
                    'ref_id' => '123456789012',
                    'paid_at' => $deal->paid_at,
                    'released_at' => $deal->completed_at,
                    'created_at' => $deal->completed_at ?? now(),
                ]);
            }
        }

        // یک واریز در انتظار + برگشت وجه + پرداخت ناموفق
        $deal = $deals[0];
        $fee = (int) round($deal->amount * config('vamjou.commission_rate') / 100);

        Transaction::updateOrCreate(['code' => '58963301'], [
            'request_id' => $deal->id,
            'ad_id' => $deal->ad_id,
            'user_id' => $deal->seller_id,
            'counterparty_id' => $deal->buyer_id,
            'type' => Transaction::TYPE_PAYOUT,
            'status' => Transaction::STATUS_PENDING,
            'amount' => $deal->amount - $fee,
            'fee' => $fee,
            'net' => $deal->amount - $fee,
            'sheba' => $deal->seller?->sheba,
            'paid_at' => now()->subHour(),
        ]);

        Transaction::updateOrCreate(['code' => '58963100'], [
            'ad_id' => $deals[3]->ad_id,
            'user_id' => $users[6]->id,
            'type' => Transaction::TYPE_REFUND,
            'status' => Transaction::STATUS_REFUNDED,
            'amount' => 15_000_000,
            'net' => 15_000_000,
            'paid_at' => now()->subDays(5),
            'released_at' => now()->subDays(4),
        ]);

        Transaction::updateOrCreate(['code' => '58963044'], [
            'ad_id' => $deals[3]->ad_id,
            'user_id' => $users[4]->id,
            'type' => Transaction::TYPE_PAYMENT,
            'status' => Transaction::STATUS_FAILED,
            'amount' => 25_000_000,
            'gateway' => 'زرین پال',
            'failure_reason' => 'انصراف از پرداخت در درگاه',
            'created_at' => now()->subDays(8),
        ]);
    }

    /* ------------------------------ تیکت‌ها ------------------------------ */

    private function seedTickets(array $users, User $support): void
    {
        $rows = [
            [4, 'عدم ثبت آگهی انتقال وام', 'آگهی ها', 'urgent', Ticket::STATUS_PENDING, null, [
                'سلام، من انتقال امتیاز وام را انجام دادم ولی در پنل هنوز گزینه ثبت اطلاعات انتقال فعال نشده. لطفاً بررسی کنید.',
                'تصویر رسید انتقال را هم ضمیمه کردم.',
            ]],
            [7, 'پیگیری واریزی فروش امتیاز وام', 'پرداخت ها', 'high', Ticket::STATUS_PENDING, null, [
                'مبلغ واریزی من به حساب نرسیده است؛ لطفاً شماره پیگیری را ارسال کنید.',
            ]],
            [10, 'مشکل در بارگذاری مدارک', 'احراز هویت', 'normal', Ticket::STATUS_PENDING, 'me', [
                'هنگام بارگذاری تصویر کارت ملی خطا می‌دهد.',
            ]],
            [11, 'تغییر شماره همراه', 'حساب کاربری', 'normal', Ticket::STATUS_PENDING, null, [
                'می‌خواهم شماره همراه حسابم را تغییر دهم.',
            ]],
            [0, 'پیگیری پرداخت ناموفق', 'پرداخت ها', 'high', Ticket::STATUS_ANSWERED, 'me', [
                'پرداخت من ناموفق بود ولی مبلغ از حسابم کسر شد.',
                'پرداخت مورد بررسی قرار گرفت و مبلغ تا ۷۲ ساعت بازمی‌گردد.',
            ]],
            [1, 'سوال درباره کارمزد', 'سایر', 'low', Ticket::STATUS_ANSWERED, 'support', [
                'کارمزد معامله چند درصد است؟',
                'کارمزد ۵ درصد از مبلغ معامله است و پس از انجام معامله کسر می‌شود.',
            ]],
            [2, 'محدود شدن حساب کاربری', 'حساب کاربری', 'urgent', Ticket::STATUS_CLOSED, 'me', [
                'حساب من محدود شده است؛ دلیل آن را اعلام کنید.',
                'بررسی انجام شد؛ حساب شما فعال است.',
            ]],
        ];

        foreach ($rows as $index => [$userIndex, $subject, $dept, $priority, $status, $assignee, $messages]) {
            $ticket = Ticket::updateOrCreate(['code' => (string) (136548 + $index)], [
                'user_id' => $users[$userIndex]->id,
                'assignee_id' => $assignee === 'me' ? $support->id : null,
                'subject' => $subject,
                'department' => $dept,
                'priority' => $priority,
                'status' => $status,
                'last_reply_at' => now()->subHours(2 + $index * 4),
                'closed_at' => $status === Ticket::STATUS_CLOSED ? now()->subDay() : null,
                'created_at' => now()->subHours(6 + $index * 5),
            ]);

            foreach ($messages as $messageIndex => $body) {
                $isSupport = $messageIndex > 0;

                $ticket->messages()->updateOrCreate(
                    ['body' => $body],
                    [
                        'user_id' => $isSupport ? $support->id : $users[$userIndex]->id,
                        'author_role' => $isSupport ? 'support' : 'user',
                        'is_read' => $isSupport,
                        'created_at' => now()->subHours(5 + $index + $messageIndex),
                    ]
                );
            }
        }
    }

    /* ------------------------------ رادار و برگزیده‌ها ------------------------------ */

    private function seedRadars(array $users, array $ads): void
    {
        $buyer = $users[0];

        $radars = [
            ['رادار رسالت', 'resalat', 'سبزوار', 200_000_000, 300_000_000, 24, 23, true],
            ['وام های جاویدان', 'javidan', null, null, 500_000_000, null, null, true],
            ['وام ارزان مشهد', null, 'مشهد', null, null, null, 20, false],
        ];

        foreach ($radars as $index => [$name, $bankSlug, $city, $min, $max, $months, $rate, $active]) {
            $radar = LoanRadar::updateOrCreate(
                ['user_id' => $buyer->id, 'name' => $name],
                [
                    'bank_id' => $bankSlug ? $this->banks[$bankSlug] : null,
                    'city' => $city,
                    'min_amount' => $min,
                    'max_amount' => $max,
                    'months' => $months,
                    'max_rate' => $rate,
                    'notify_sms' => true,
                    'notify_panel' => true,
                    'is_active' => $active,
                    'created_at' => now()->subDays(6 - $index),
                ]
            );

            $matches = $ads[0]->bank_id === $radar->bank_id || $index === 1
                ? Ad::query()->published()->where('bank_id', $radar->bank_id ?? $ads[0]->bank_id)->limit(2)->pluck('id')
                : collect();

            foreach ($matches as $matchIndex => $adId) {
                RadarMatch::updateOrCreate(
                    ['radar_id' => $radar->id, 'ad_id' => $adId],
                    ['is_new' => $matchIndex === 0, 'seen_at' => $matchIndex === 0 ? null : now()->subDay()]
                );
            }
        }
    }

    private function seedFavorites(array $users, array $ads): void
    {
        foreach ([0, 1, 4, 7] as $index) {
            Favorite::updateOrCreate(
                ['user_id' => $users[0]->id, 'ad_id' => $ads[$index]->id],
                ['created_at' => now()->subDays($index + 1)]
            );
        }
    }

    private function seedLogs(array $users, User $admin): void
    {
        $samples = [
            ['login', 'ورود به حساب کاربری'],
            ['kyc.submitted', 'ارسال مدارک احراز هویت'],
            ['ad.created', 'ثبت آگهی جدید'],
            ['payment.paid', 'پرداخت مبلغ معامله'],
            ['ticket.created', 'ثبت تیکت پشتیبانی'],
            ['register', 'ثبت نام در سامانه'],
        ];

        foreach ($users as $index => $user) {
            foreach ($samples as $sampleIndex => [$action, $description]) {
                if (($index + $sampleIndex) % 3 !== 0) {
                    continue;
                }

                ActivityLog::updateOrCreate(
                    ['user_id' => $user->id, 'action' => $action],
                    [
                        'description' => $description,
                        'ip' => '5.112.40.'.(10 + $index),
                        'created_at' => now()->subHours($index * 12 + $sampleIndex * 5),
                    ]
                );
            }
        }

        ActivityLog::updateOrCreate(
            ['user_id' => $users[0]->id, 'action' => 'admin.note'],
            ['actor_id' => $admin->id, 'description' => 'بررسی حساب توسط مدیر سیستم', 'created_at' => now()->subHours(2)]
        );
    }

    /* ------------------------------ کمکی‌ها ------------------------------ */

    /** ساخت کد ملی معتبر (با رقم کنترلی صحیح) */
    private function nationalId(int $seed): string
    {
        $base = str_pad((string) abs((1_000_000_000 + $seed * 79_193) % 999_999_999), 9, '0', STR_PAD_LEFT);
        $sum = 0;

        for ($i = 0; $i < 9; $i++) {
            $sum += ((int) $base[$i]) * (10 - $i);
        }

        $remainder = $sum % 11;
        $check = $remainder < 2 ? $remainder : 11 - $remainder;

        return $base.$check;
    }
}
