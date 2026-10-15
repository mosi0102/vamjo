<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Favorite;
use App\Support\Presenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $favorites = $request->user()->favorites()
            ->with(['ad.bank', 'ad' => fn ($query) => $query->withCount('offers')])
            ->latest()
            ->get()
            ->filter(fn (Favorite $favorite) => $favorite->ad !== null)
            ->map(fn (Favorite $favorite) => Presenter::favoriteRow($favorite))
            ->values()
            ->all();

        return view('userPanel.favorite.index', compact('favorites'));
    }

    /** افزودن/حذف نشان‌کردن آگهی */
    public function store(Request $request, Ad $ad): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $favorite = Favorite::firstOrCreate(['user_id' => $user->id, 'ad_id' => $ad->id]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'saved' => true, 'id' => $favorite->id]);
        }

        return back()->with('success', 'آگهی به برگزیده‌ها اضافه شد.');
    }

    /**
     * حذف برگزیده؛ پارامتر می‌تواند شناسه ردیف برگزیده یا شناسه آگهی باشد
     * (panel.js شناسه ردیف را از data-id می‌فرستد).
     */
    public function destroy(Request $request, int $favorite): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $deleted = Favorite::query()->where('user_id', $user->id)->whereKey($favorite)->delete();

        if (! $deleted) {
            Favorite::query()->where('user_id', $user->id)->where('ad_id', $favorite)->delete();
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'saved' => false]);
        }

        return back()->with('success', 'آگهی از برگزیده‌ها حذف شد.');
    }
}
