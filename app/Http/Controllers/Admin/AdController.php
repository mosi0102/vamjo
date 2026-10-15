<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Ad;
use App\Models\LoanRadar;
use App\Models\RadarMatch;
use App\Support\Presenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdController extends Controller
{
    public function index(Request $request): View
    {
        $ads = Ad::query()
            ->with(['bank', 'user', 'documents'])
            ->when($request->filled('state'), function ($query) use ($request) {
                match ($request->string('state')->toString()) {
                    'pending' => $query->pendingReview(),
                    'published' => $query->published(),
                    'featured' => $query->featured(),
                    'rejected' => $query->where('status', Ad::STATUS_REJECTED),
                    default => $query,
                };
            })
            ->when($request->filled('q'), fn ($query) => $query->search($request->string('q')->toString()))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('adminPanel.ads.index', [
            'ads' => collect($ads->items())->map(fn (Ad $ad) => Presenter::adminAdRow($ad))->all(),
            'paginator' => $ads,
        ]);
    }

    public function approve(Request $request, Ad $ad): RedirectResponse
    {
        $data = $request->validate([
            'featured' => ['nullable', 'boolean'],
            'featured_days' => ['nullable', 'integer', 'in:7,14,30'],
        ]);

        $ad->update([
            'status' => Ad::STATUS_PUBLISHED,
            'reject_reason' => null,
            'featured' => $request->boolean('featured'),
            'published_at' => $ad->published_at ?? now(),
        ]);

        $this->notifyRadars($ad);

        ActivityLog::record($ad->user, 'ad.approved', "تایید و انتشار آگهی {$ad->code}", [
            'ad_id' => $ad->id, 'admin' => $request->user()->id,
        ]);

        return back()->with('success', 'آگهی منتشر شد.');
    }

    public function reject(Request $request, Ad $ad): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:200']], [], ['reason' => 'دلیل رد']);

        $ad->update([
            'status' => Ad::STATUS_REJECTED,
            'reject_reason' => $data['reason'],
        ]);

        ActivityLog::record($ad->user, 'ad.rejected', "رد آگهی {$ad->code}: {$data['reason']}", ['ad_id' => $ad->id]);

        return back()->with('success', 'آگهی رد شد و دلیل آن برای فروشنده ثبت گردید.');
    }

    /** ویژه/عادی کردن آگهی (panel.js با _method=PATCH) */
    public function feature(Request $request, Ad $ad): JsonResponse
    {
        $featured = $request->boolean('featured', ! $ad->featured);

        $ad->update(['featured' => $featured]);

        return response()->json(['ok' => true, 'featured' => $ad->featured]);
    }

    /** ساخت اعلان رادار برای آگهی منتشرشده */
    private function notifyRadars(Ad $ad): void
    {
        $radars = LoanRadar::query()->active()
            ->where(fn ($query) => $query->whereNull('bank_id')->orWhere('bank_id', $ad->bank_id))
            ->where(fn ($query) => $query->whereNull('type')->orWhere('type', $ad->type))
            ->where(fn ($query) => $query->whereNull('city')->orWhere('city', $ad->city))
            ->where(fn ($query) => $query->whereNull('max_amount')->orWhere('max_amount', '>=', $ad->amount))
            ->where(fn ($query) => $query->whereNull('min_amount')->orWhere('min_amount', '<=', $ad->amount))
            ->where(fn ($query) => $query->whereNull('months')->orWhere('months', '>=', $ad->months))
            ->where(fn ($query) => $query->whereNull('max_rate')->orWhere('max_rate', '>=', $ad->rate))
            ->get();

        foreach ($radars as $radar) {
            RadarMatch::firstOrCreate(['radar_id' => $radar->id, 'ad_id' => $ad->id], ['is_new' => true]);
        }
    }
}
