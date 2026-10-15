<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Bank;
use App\Models\LoanRadar;
use App\Models\RadarMatch;
use App\Support\Iran;
use App\Support\Presenter;
use App\Support\Text;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RadarController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $radars = $user->radars()->with('bank')->latest()->get()
            ->map(fn (LoanRadar $radar) => Presenter::radarRow($radar))
            ->all();

        $matches = RadarMatch::query()
            ->whereIn('radar_id', $user->radars()->pluck('id'))
            ->with('ad.bank')
            ->latest()
            ->get()
            ->map(fn (RadarMatch $match) => Presenter::radarMatchRow($match))
            ->all();

        return view('userPanel.radar.index', [
            'radars' => $radars,
            'matches' => $matches,
            'banks' => Presenter::banks(Bank::query()->active()->ordered()->get()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $radar = $request->user()->radars()->create($data);

        $this->syncMatches($radar);

        return back()->with('success', 'رادار «'.$radar->name.'» ساخته شد.');
    }

    public function update(Request $request, LoanRadar $radar): RedirectResponse
    {
        $this->authorizeOwner($request, $radar);

        $radar->update($this->validated($request));
        $this->syncMatches($radar);

        return back()->with('success', 'رادار به‌روزرسانی شد.');
    }

    public function destroy(Request $request, LoanRadar $radar): RedirectResponse
    {
        $this->authorizeOwner($request, $radar);

        $radar->delete();

        return back()->with('success', 'رادار حذف شد.');
    }

    /** فعال/غیرفعال کردن رادار (panel.js با _method=PATCH صدا می‌زند) */
    public function toggle(Request $request, LoanRadar $radar): JsonResponse
    {
        $this->authorizeOwner($request, $radar);

        $active = $request->boolean('active', ! $radar->is_active);
        $radar->update(['is_active' => $active]);

        if ($active) {
            $this->syncMatches($radar);
        }

        return response()->json(['ok' => true, 'active' => $radar->is_active]);
    }

    /** حذف یک نتیجه پیدا‌شده از لیست رادار (فرم confirm modal یا ajax) */
    public function destroyMatch(Request $request, RadarMatch $match): JsonResponse|RedirectResponse
    {
        $match->loadMissing('radar');

        abort_unless($match->radar?->user_id === $request->user()->id, 403);

        $match->delete();

        return $request->expectsJson()
            ? response()->json(['ok' => true])
            : back()->with('success', 'آگهی از نتایج رادار حذف شد.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:40'],
            'min_amount' => ['nullable', 'string'],
            'max_amount' => ['nullable', 'string'],
            'min_months' => ['nullable', 'string'],
            'max_rate' => ['nullable', 'numeric', 'between:0,100'],
            'bank' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:50'],
            'notify_panel' => ['nullable', 'boolean'],
            'notify_sms' => ['nullable', 'boolean'],
        ], [], ['name' => 'نام رادار', 'min_amount' => 'حداقل مبلغ', 'max_amount' => 'حداکثر مبلغ']);

        $bankName = Text::canonical($data['bank'] ?? null, Bank::query()->active()->pluck('name')->all());
        $bank = $bankName ? Bank::query()->where('name', $bankName)->first() : null;

        // همان قید فرانت (rdFormErr): حداقل یکی از شرط‌ها اجباری است
        if (! array_filter([
            Iran::digits($data['min_amount'] ?? null) ?: null,
            Iran::digits($data['max_amount'] ?? null) ?: null,
            Iran::digits($data['min_months'] ?? null) ?: null,
            ($data['max_rate'] ?? null) !== null && ($data['max_rate'] ?? null) !== '' ? $data['max_rate'] : null,
            $bank?->id,
            $data['city'] ?? null,
        ])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'name' => 'حداقل یکی از شرایط (مبلغ، اقساط، سود، بانک یا شهر) را وارد کنید.',
            ]);
        }

        return [
            'name' => $data['name'],
            'bank_id' => $bank?->id,
            'city' => $data['city'] ?? null,
            'min_amount' => Iran::digits($data['min_amount'] ?? null) ?: null,
            'max_amount' => Iran::digits($data['max_amount'] ?? null) ?: null,
            'months' => Iran::digits($data['min_months'] ?? null) ?: null,
            'max_rate' => $data['max_rate'] ?? null,
            'notify_panel' => $request->boolean('notify_panel'),
            'notify_sms' => $request->boolean('notify_sms'),
        ];
    }

    /** ساخت ردیف‌های نتیجه برای آگهی‌های منطبق */
    private function syncMatches(LoanRadar $radar): void
    {
        if (! $radar->is_active) {
            return;
        }

        $ads = $radar->matchingAds()->pluck('id');

        foreach ($ads as $adId) {
            RadarMatch::firstOrCreate(['radar_id' => $radar->id, 'ad_id' => $adId], ['is_new' => true]);
        }
    }

    private function authorizeOwner(Request $request, LoanRadar $radar): void
    {
        abort_unless($radar->user_id === $request->user()->id, 403, 'این رادار متعلق به شما نیست.');
    }
}
