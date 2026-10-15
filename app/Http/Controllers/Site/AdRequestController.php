<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Ad;
use App\Models\AdOffer;
use App\Support\Iran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdRequestController extends Controller
{
    /**
     * ثبت درخواست خرید برای یک آگهی.
     * قرارداد فرانت‌اند: {ok:true, redirect:"..."} (public/website/js/main.js)
     */
    public function store(Request $request, Ad $ad): JsonResponse
    {
        $user = $request->user();

        abort_unless($ad->isPublished(), 404, 'این آگهی در حال حاضر فعال نیست.');

        if ($ad->user_id === $user->id) {
            return response()->json(['ok' => false, 'message' => 'برای آگهی خودتان نمی‌توانید درخواست ثبت کنید.'], 422);
        }

        $existing = AdOffer::query()
            ->where('ad_id', $ad->id)
            ->where('user_id', $user->id)
            ->whereIn('status', [AdOffer::STATUS_PENDING, AdOffer::STATUS_ACCEPTED])
            ->exists();

        if ($existing) {
            return response()->json([
                'ok' => true,
                'redirect' => route('user.requests'),
                'message' => 'درخواست شما قبلاً ثبت شده است.',
            ]);
        }

        $min = $ad->price;
        $max = $ad->max_price ?: (int) round($ad->price * 1.5);
        $amount = (int) Iran::digits($request->input('offer'));

        if ($amount && ($amount < $min || $amount > $max)) {
            return response()->json([
                'ok' => false,
                'message' => 'مبلغ پیشنهادی باید بین '.number_format($min).' تا '.number_format($max).' تومان باشد.',
            ], 422);
        }

        $offer = AdOffer::create([
            'ad_id' => $ad->id,
            'user_id' => $user->id,
            'amount' => $amount ?: $min,
            'status' => AdOffer::STATUS_PENDING,
            'expires_at' => now()->addHours((int) config('vamjou.deadlines.offer')),
            'ip' => $request->ip(),
        ]);

        ActivityLog::record($user, 'offer.created', "ثبت درخواست برای آگهی {$ad->code}", [
            'ad_id' => $ad->id, 'offer_id' => $offer->id,
        ]);

        return response()->json([
            'ok' => true,
            'redirect' => route('user.requests'),
            'message' => 'درخواست شما ثبت شد و در انتظار تایید فروشنده است.',
        ]);
    }
}
