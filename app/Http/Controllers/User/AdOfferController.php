<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Ad;
use App\Models\AdOffer;
use App\Models\LoanRequest;
use App\Support\Code;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdOfferController extends Controller
{
    /** تایید درخواست خریدار → ساخت معامله هفت‌مرحله‌ای */
    public function accept(Request $request, AdOffer $offer)
    {
        $this->authorizeSeller($request, $offer);

        if ($offer->status !== AdOffer::STATUS_PENDING) {
            throw ValidationException::withMessages(['offer' => 'این درخواست قابل تایید نیست.']);
        }

        $ad = $offer->ad;
        $deal = LoanRequest::create([
            'code' => Code::make('loan_requests'),
            'ad_id' => $ad->id,
            'offer_id' => $offer->id,
            'buyer_id' => $offer->user_id,
            'seller_id' => $ad->user_id,
            'amount' => $offer->amount,
            'price' => $offer->amount, // مبلغ معامله = پیشنهادِ پذیرفته‌شده خریدار (نه مقدار وام)
            'step' => 3, // در انتظار پرداخت خریدار
            'status' => LoanRequest::STATUS_ACTIVE,
            'pay_due_at' => now()->addDays((int) config('vamjou.deadlines.pay')),
        ]);

        $offer->update(['status' => AdOffer::STATUS_ACCEPTED, 'responded_at' => now()]);

        // سایر درخواست‌های همان آگهی رد می‌شوند
        $ad->offers()->whereKeyNot($offer->id)->where('status', AdOffer::STATUS_PENDING)
            ->update(['status' => AdOffer::STATUS_REJECTED, 'responded_at' => now()]);

        $ad->update(['status' => Ad::STATUS_TRANSFER]);

        ActivityLog::record($request->user(), 'offer.accepted', "تایید درخواست خریدار برای آگهی {$ad->code}", [
            'ad_id' => $ad->id, 'offer_id' => $offer->id, 'request_id' => $deal->id,
        ]);

        return back()->with('success', 'درخواست تایید شد؛ خریدار ۷ روز برای پرداخت فرصت دارد.');
    }

    public function reject(Request $request, AdOffer $offer)
    {
        $this->authorizeSeller($request, $offer);

        if ($offer->status !== AdOffer::STATUS_PENDING) {
            throw ValidationException::withMessages(['offer' => 'این درخواست قابل رد شدن نیست.']);
        }

        $offer->update(['status' => AdOffer::STATUS_REJECTED, 'responded_at' => now()]);

        ActivityLog::record($request->user(), 'offer.rejected', 'رد درخواست خریدار', [
            'ad_id' => $offer->ad_id, 'offer_id' => $offer->id,
        ]);

        return back()->with('success', 'درخواست رد شد.');
    }

    private function authorizeSeller(Request $request, AdOffer $offer): void
    {
        $offer->loadMissing('ad');

        abort_unless($offer->ad?->user_id === $request->user()->id, 403, 'این درخواست متعلق به آگهی شما نیست.');
    }
}
