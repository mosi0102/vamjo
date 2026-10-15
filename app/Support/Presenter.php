<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Ad;
use App\Models\AdDocument;
use App\Models\AdOffer;
use App\Models\Bank;
use App\Models\Favorite;
use App\Models\KycVerification;
use App\Models\LoanRadar;
use App\Models\LoanRequest;
use App\Models\RadarMatch;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;

/**
 * تبدیل مدل‌ها به ساختار آرایه‌ای که قالب‌های Blade انتظار دارند.
 * (هر متد دقیقاً کلیدهای همان صفحه را برمی‌گرداند تا ویوها فقط داده بگیرند.)
 */
class Presenter
{
    /* ============================ سایت عمومی ============================ */

    /** کارت آگهی در صفحه اصلی و لیست آگهی‌ها */
    public static function siteAd(Ad $ad): array
    {
        $bank = $ad->bank;

        return [
            'id' => $ad->id,
            'bank' => $bank?->slug,
            'bank_name' => $bank?->display_name ?? '',
            'bank_full_name' => $bank?->name ?? '',
            'c' => $bank?->color ?? '#1e5a45',
            'l' => $bank?->initial ?? '',
            'type' => $ad->type,
            'type_name' => $ad->typeLabel(),
            'amount' => $ad->amount,
            'buy' => $ad->price,
            'months' => $ad->months,
            'rate' => $ad->rate,
            'offers' => $ad->offers_count ?? $ad->offers()->count(),
            'urgent' => $ad->urgent ? 1 : 0,
            'pay' => $ad->status === Ad::STATUS_TRANSFER ? 0 : 1,
            'image' => $bank?->image ?? '1',
        ];
    }

    /** کارت آگهی ساده (صفحه اصلی) */
    public static function adCard(Ad $ad): array
    {
        $card = self::siteAd($ad);
        unset($card['bank'], $card['type'], $card['type_name'], $card['urgent'], $card['pay']);

        return $card;
    }

    /** جزئیات آگهی در صفحه /ads/{id} */
    public static function adDetail(Ad $ad): array
    {
        $bank = $ad->bank;

        return [
            'id' => $ad->id,
            'code' => $ad->code,
            'bank' => $bank?->slug,
            'bank_name' => $bank?->display_name ?? '',
            'bank_full_name' => $bank?->name ?? '',
            'city' => $ad->city ?? '',
            'image' => $bank?->image ?? '1',
            'ago' => Fa::ago($ad->published_at ?? $ad->created_at),
            'amount' => $ad->amount,
            'rate' => $ad->rate,
            'months' => $ad->months,
            'installment' => $ad->installmentAmount(),
            'total' => $ad->totalRepaymentAmount(),
            'guarantor' => $ad->guarantor ?: 'توافقی',
            'price' => $ad->price,
            'max_offer' => $ad->max_price ?: (int) round($ad->price * 1.5),
            'offers' => $ad->offers_count ?? $ad->offers()->count(),
            'phone' => config('vamjou.support_phone', '05144444400'),
        ];
    }

    /** فهرست بانک‌ها برای فرم‌های ثبت/ویرایش آگهی و رادار */
    public static function banks($banks): array
    {
        return collect($banks)->map(fn (Bank $b) => $b->name)->values()->all();
    }

    /* ============================ پنل کاربر ============================ */

    /** ردیف «آگهی های من» */
    public static function userAd(Ad $ad, ?LoanRequest $deal = null): array
    {
        $bank = $ad->bank;

        return [
            'id' => $ad->id,
            'bank' => $bank?->display_name ?? '',
            'bank_full' => $bank?->name ?? '',
            'image' => $bank?->image ?? '1',
            'l' => $bank?->initial ?? '',
            'c' => $bank?->color ?? '#1e5a45',
            'amount' => $ad->amount,
            'price' => $ad->price,
            'ago' => Fa::ago($ad->created_at),
            'city' => $ad->city ?? '',
            'views' => $ad->views,
            'offers' => $ad->offers_count ?? $ad->offers()->count(),
            'new_offers' => $ad->new_offers_count ?? 0,
            'state' => $ad->state,
            'note' => $ad->reject_reason,
            'deadline' => $deal?->transfer_due_at ? max(0, now()->diffInSeconds($deal->transfer_due_at, false)) : null,
        ];
    }

    /** داده صفحه ویرایش آگهی */
    public static function editAd(Ad $ad): array
    {
        $docs = $ad->documents->keyBy('kind');

        return [
            'id' => $ad->id,
            'state' => $ad->state,
            'note' => $ad->reject_reason,
            'bank' => $ad->bank?->name ?? '',
            'amount' => $ad->amount,
            'months' => $ad->months,
            'rate' => rtrim(rtrim(number_format($ad->rate, 2, '.', ''), '0'), '.'),
            'price' => $ad->price,
            'sheba' => $ad->sheba ?? '',
            'description' => $ad->description ?? '',
            'doc_balance' => $docs->get('balance')?->toViewArray(),
            'doc_id' => $docs->get('national_card')?->toViewArray(),
        ];
    }

    /** ردیف درخواست‌های ثبت‌شده روی آگهی (صفحه فروشنده) */
    public static function offerRow(AdOffer $offer): array
    {
        $buyer = $offer->buyer;
        $deals = $buyer
            ? $buyer->purchases()->where('status', LoanRequest::STATUS_COMPLETED)->count()
                + $buyer->sales()->where('status', LoanRequest::STATUS_COMPLETED)->count()
            : 0;

        return [
            'id' => $offer->id,
            'name' => $buyer?->display_name ?? 'کاربر',
            'amount' => $offer->amount,
            'status' => $offer->status,
            'ago' => Fa::ago($offer->created_at),
            'age' => (int) $offer->created_at->diffInMinutes(now()),
            'deals' => $deals,
            'rating' => null,
            'verified' => (bool) $buyer?->is_verified,
            'expires' => $offer->expires_at && $offer->expires_at->isFuture()
                ? Fa::digits($offer->expires_at->diffInHours(now())).' ساعت'
                : null,
        ];
    }

    /** ردیف «درخواست های من» */
    public static function requestRow(LoanRequest $request): array
    {
        $ad = $request->ad;
        $bank = $ad?->bank;

        return [
            'id' => $request->id,
            'code' => $request->code,
            'bank' => $bank?->display_name ?? '',
            'image' => $bank?->image ?? '1',
            'l' => $bank?->initial ?? '',
            'c' => $bank?->color ?? '#1e5a45',
            'amount' => $request->amount,
            'price' => $request->price,
            'ago' => Fa::ago($request->created_at),
            'city' => $ad?->city ?? '',
            'step' => $request->step,
            'state' => match ($request->status) {
                LoanRequest::STATUS_COMPLETED => 'completed',
                LoanRequest::STATUS_REJECTED, LoanRequest::STATUS_CANCELLED, LoanRequest::STATUS_EXPIRED => 'rejected',
                default => 'active',
            },
            'doc' => null,
            'deadline' => Fa::remainingDays($request->pay_due_at ?? $request->transfer_due_at),
            'note' => $request->reject_reason,
        ];
    }

    /** ردیف رادار */
    public static function radarRow(LoanRadar $radar): array
    {
        return [
            'id' => $radar->id,
            'name' => $radar->name,
            'active' => (bool) $radar->is_active,
            'min' => $radar->min_amount,
            'max' => $radar->max_amount,
            'months' => $radar->months,
            'rate' => $radar->max_rate,
            'bank' => $radar->bank?->name ?? '',
            'city' => $radar->city ?? '',
            'sms' => (bool) $radar->notify_sms,
            'panel' => (bool) $radar->notify_panel,
        ];
    }

    /** ردیف آگهی پیدا‌شده توسط رادار */
    public static function radarMatchRow(RadarMatch $match): array
    {
        $ad = $match->ad;
        $bank = $ad?->bank;

        return [
            'id' => $ad?->id,
            'match_id' => $match->id,
            'radar' => $match->radar_id,
            'bank' => $bank?->display_name ?? '',
            'image' => $bank?->image ?? '1',
            'l' => $bank?->initial ?? '',
            'c' => $bank?->color ?? '#1e5a45',
            'amount' => $ad?->amount ?? 0,
            'price' => $ad?->price ?? 0,
            'ago' => $ad ? Fa::ago($ad->published_at ?? $ad->created_at) : '—',
            'city' => $ad?->city ?? '',
            'months' => $ad?->months ?? 0,
            'rate' => $ad?->rate ?? 0,
            'found' => Fa::ago($match->created_at),
            'is_new' => (bool) $match->is_new,
        ];
    }

    /** ردیف «پرداخت های من» */
    public static function paymentRow(Transaction $transaction): array
    {
        $ad = $transaction->ad;
        $bank = $ad?->bank;

        return [
            'tx' => $transaction->code,
            'loan' => $ad?->code,
            'loan_id' => $ad?->id,
            'bank' => $bank?->display_name ?? '',
            'image' => $bank?->image ?? '1',
            'l' => $bank?->initial ?? '',
            'c' => $bank?->color ?? '#1e5a45',
            'amount' => $ad?->amount ?? $transaction->amount,
            'paid' => $transaction->amount,
            'date' => Fa::dateTime($transaction->paid_at ?? $transaction->created_at, 'j F Y – H:i'),
            'age' => (int) ($transaction->paid_at ?? $transaction->created_at)->diffInMinutes(now()),
            'status' => match ($transaction->status) {
                Transaction::STATUS_RELEASED => 'released',
                Transaction::STATUS_REFUNDED => 'refunded',
                Transaction::STATUS_FAILED => 'failed',
                default => 'escrow',
            },
        ];
    }

    /** ردیف برگزیده‌ها */
    public static function favoriteRow(Favorite $favorite): array
    {
        $ad = $favorite->ad;
        $bank = $ad?->bank;

        return [
            'id' => $ad?->id,
            'bank' => $bank?->slug,
            'bank_name' => $bank?->display_name ?? '',
            'image' => $bank?->image ?? '1',
            'c' => $bank?->color ?? '#1e5a45',
            'l' => $bank?->initial ?? '',
            'amount' => $ad?->amount ?? 0,
            'buy' => $ad?->price ?? 0,
            'months' => $ad?->months ?? 0,
            'rate' => $ad?->rate ?? 0,
            'offers' => $ad?->offers_count ?? 0,
            'saved' => Fa::ago($favorite->created_at),
            'age' => (int) $favorite->created_at->diffInMinutes(now()),
            'active' => (bool) $ad?->isPublished(),
            'drop' => null,
        ];
    }

    /** ردیف تیکت کاربر */
    public static function ticketRow(Ticket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'code' => $ticket->code,
            'title' => $ticket->subject,
            'status' => $ticket->status,
            'date' => Fa::dateTime($ticket->last_reply_at ?? $ticket->created_at, 'j F Y – H:i'),
            'priority' => $ticket->priority,
            'dept' => $ticket->department,
            'msgs' => $ticket->messages_count ?? $ticket->messages()->count(),
            'unread' => ($ticket->unread_count ?? 0) > 0,
        ];
    }

    /** تیکت در پنل کاربر */
    public static function ticketDetail(Ticket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'code' => $ticket->code,
            'title' => $ticket->subject,
            'status' => $ticket->status,
            'priority' => $ticket->priority,
            'dept' => $ticket->department,
            'date' => Fa::dateTime($ticket->created_at, 'j F Y – H:i'),
        ];
    }

    /** پیام تیکت در پنل کاربر */
    public static function ticketMessage(\App\Models\TicketMessage $message): array
    {
        $file = null;
        if ($message->attachment_path) {
            $file = [
                'name' => $message->attachment_name ?: basename($message->attachment_path),
                'size' => $message->attachment_size ?: '',
                'url' => $message->attachmentUrl(),
            ];
        }

        return [
            'from' => $message->isFromSupport() ? 'support' : 'user',
            'time' => Fa::digits($message->created_at->format('H:i')),
            'text' => $message->body,
            'file' => $file,
        ];
    }

    /* ============================ پنل مدیریت ============================ */

    /** ردیف جدول کاربران */
    public static function adminUserRow(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->display_name,
            'mobile' => $user->mobile ?? '',
            'nid' => $user->national_id ?? '',
            'ver' => $user->kyc_status,
            'blocked' => $user->isBlocked(),
            'ads' => $user->ads_count ?? $user->ads()->count(),
            'reqs' => ($user->purchases_count ?? 0) + ($user->sales_count ?? 0),
            'paid' => (int) ($user->paid_total ?? 0),
            'joined' => Fa::dateTime($user->created_at),
            'age' => (int) $user->created_at->diffInMinutes(now()),
        ];
    }

    /** ردیف جدول آگهی‌ها در پنل مدیریت */
    public static function adminAdRow(Ad $ad): array
    {
        $bank = $ad->bank;
        $docs = $ad->documents->keyBy('kind');

        return [
            'id' => $ad->id,
            'code' => $ad->code,
            'bank' => $bank?->display_name ?? '',
            'bank_full' => $bank?->name ?? '',
            'l' => $bank?->initial ?? '',
            'c' => $bank?->color ?? '#1e5a45',
            'image' => $bank?->image ?? '1',
            'amount' => $ad->amount,
            'price' => $ad->price,
            'months' => $ad->months,
            'rate' => $ad->rate,
            'seller' => $ad->user?->display_name ?? '',
            'seller_id' => $ad->user_id,
            'city' => $ad->city ?? '',
            'status' => $ad->status,
            'featured' => (bool) $ad->featured,
            'sheba' => $ad->sheba ?? '',
            'note' => $ad->description ?? '',
            'doc1' => $docs->get(AdDocument::KIND_BALANCE)?->url,
            'doc2' => $docs->get(AdDocument::KIND_NATIONAL_CARD)?->url,
            'ago' => Fa::ago($ad->created_at),
            'age' => (int) $ad->created_at->diffInMinutes(now()),
        ];
    }

    /** ردیف جدول تراکنش‌ها (آرایه عددی مطابق ویو) */
    public static function adminPaymentRow(Transaction $transaction): array
    {
        $ad = $transaction->ad;
        $group = match (true) {
            $transaction->type === Transaction::TYPE_PAYOUT && $transaction->status === Transaction::STATUS_PENDING => 'payout_due',
            $transaction->type === Transaction::TYPE_PAYOUT => 'paid',
            $transaction->status === Transaction::STATUS_REFUNDED => 'refund',
            $transaction->status === Transaction::STATUS_FAILED => 'failed',
            default => 'escrow',
        };

        return [
            $transaction->code,
            $group,
            $transaction->user?->display_name ?? '',
            $transaction->type === Transaction::TYPE_PAYOUT ? 'فروشنده' : 'خریدار',
            $ad?->code,
            $ad?->bank?->display_name ?? '',
            $transaction->amount,
            Fa::dateTime($transaction->paid_at ?? $transaction->created_at, 'j F – H:i'),
            (int) ($transaction->paid_at ?? $transaction->created_at)->diffInMinutes(now()),
            $transaction->type === Transaction::TYPE_PAYOUT ? ($transaction->sheba ?? '') : '',
        ];
    }

    /** ردیف جدول احراز هویت (پنل مدیریت) – داده کامل برای جدول و مودال بررسی */
    public static function kycRow(KycVerification $kyc): array
    {
        $user = $kyc->user;

        return [
            'id' => $kyc->id,
            'user_id' => $kyc->user_id,
            'name' => $user?->display_name ?? 'کاربر حذف‌شده',
            'mobile' => $user?->mobile ?? '',
            'nid' => $kyc->national_id ?? '',
            'birth' => $kyc->birth_date ? Fa::digits(str_replace('-', '/', $kyc->birth_date)) : '',
            'sheba' => $kyc->sheba ?? '',
            'bank' => $kyc->bank_name ?? '',
            'status' => $kyc->status,
            'status_label' => $kyc->statusLabel(),
            'ago' => Fa::ago($kyc->created_at),
            'age' => (int) $kyc->created_at->diffInMinutes(now()),
            'sent' => Fa::dateTime($kyc->created_at, 'j F Y – H:i'),
            'selfie' => $kyc->selfieUrl(),
            'idcard' => $kyc->nationalCardUrl(),
            'reason' => $kyc->reject_reason,
            'reviewer' => $kyc->reviewer?->display_name,
            'reviewed' => $kyc->reviewed_at ? Fa::dateTime($kyc->reviewed_at, 'j F Y – H:i') : null,
            'blocked' => (bool) $user?->isBlocked(),
            'user_ads' => (int) ($user?->ads_count ?? $user?->ads()->count() ?? 0),
        ];
    }

    /** ردیف تیکت در پنل مدیریت (آرایه عددی مطابق ویو) */
    public static function adminTicketRow(Ticket $ticket, ?int $currentUserId = null): array
    {
        $assignee = $ticket->assignee_id === $currentUserId && $currentUserId
            ? 'me'
            : ($ticket->assignee?->display_name);

        return [
            $ticket->id,
            $ticket->subject,
            $ticket->user?->display_name ?? '',
            $ticket->department,
            $ticket->priority,
            $ticket->status,
            $assignee,
            Fa::ago($ticket->last_reply_at ?? $ticket->created_at),
            (int) ($ticket->last_reply_at ?? $ticket->created_at)->diffInMinutes(now()),
        ];
    }

    /** سابقه فعالیت (آرایه عددی مطابق ویو) */
    public static function activityRow(ActivityLog $log): array
    {
        return [
            $log->icon(),
            $log->description ?? $log->action,
            Fa::ago($log->created_at).($log->ip ? ' · '.$log->ip : ''),
        ];
    }

    /** عملکرد کاربر برای تب «سابقه فعالیت» */
    public static function userLogs(User $user, int $limit = 8): array
    {
        return $user->activityLogs()->latest()->limit($limit)->get()
            ->map(fn (ActivityLog $log) => self::activityRow($log))->all();
    }
}
