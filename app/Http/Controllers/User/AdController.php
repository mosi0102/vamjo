<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\AdController as SiteAdController;
use App\Models\ActivityLog;
use App\Models\Ad;
use App\Models\AdDocument;
use App\Models\AdOffer;
use App\Models\Bank;
use App\Support\Iran;
use App\Support\Presenter;
use App\Support\Text;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdController extends Controller
{
    /** آگهی های من */
    public function index(Request $request): View
    {
        $user = $request->user();

        $ads = $user->ads()
            ->with('bank')
            ->withCount('offers')
            ->latest()
            ->get()
            ->map(function (Ad $ad) use ($user) {
                $deal = $user->sales()->where('ad_id', $ad->id)->active()->latest()->first();

                return Presenter::userAd($ad, $deal);
            })
            ->all();

      

        return view('userPanel.ads.index', compact('ads'));
    }

    /** فرم ثبت آگهی جدید (داخل پنل کاربر؛ نیازمند احراز هویت – middleware kyc) */
    public function create(): View
    {
        return view('userPanel.ads.create', [
            'banks' => Presenter::banks(Bank::query()->active()->ordered()->get()),
        ]);
    }

    /** ذخیره آگهی جدید – منطق مشترک ثبت فرم سایت (اعتبارسنجی، ساخت آگهی و مدارک) */
    public function store(Request $request): RedirectResponse
    {
        return app(SiteAdController::class)->store($request);
    }

    /** درخواست‌های ثبت‌شده روی یک آگهی */
    public function offers(Request $request, Ad $ad): View
    {
        $this->authorizeOwner($request, $ad);

        $ad->load('bank');

        $offers = $ad->offers()
            ->with('buyer')
            ->latest()
            ->get()
            ->map(fn (AdOffer $offer) => Presenter::offerRow($offer));

        $pending = $ad->offers()
            ->pending()
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->get();

        $activeOffers = $pending;

        $best = (int) ($activeOffers->max('amount') ?? 0);
        $avg = (int) round($activeOffers->avg('amount') ?? 0);

        $statusMap = [
            'pending' => ['pending', 'در انتظار پاسخ'],
            'accepted' => ['success', 'تأیید شده'],
            'rejected' => ['danger', 'رد شده'],
            'expired' => ['muted', 'منقضی شده'],
        ];

        $tabs = [
            'all' => 'همه',
            'pending' => 'در انتظار',
            'accepted' => 'تأیید شده',
            'rejected' => 'رد شده',
            'expired' => 'منقضی شده',
        ];

        $counts = [
            'all' => $offers->count(),
            'pending' => $offers->where('status', 'pending')->count(),
            'accepted' => $offers->where('status', 'accepted')->count(),
            'rejected' => $offers->where('status', 'rejected')->count(),
            'expired' => $offers->where('status', 'expired')->count(),
        ];

        $groupOf = fn ($offer) => $offer['status'] ?? 'pending';

        $fa = fn ($number) => number_format((int) $number);

        $faId = fn ($number) => strtr((string) $number, [
            '0' => '۰', '1' => '۱', '2' => '۲',
            '3' => '۳', '4' => '۴', '5' => '۵',
            '6' => '۶', '7' => '۷', '8' => '۸',
            '9' => '۹',
        ]);

        $avatarColors = [
            '#dcece5', '#f5e4cf', '#dce6f5',
            '#f4dfe5', '#e7e0f5', '#e8edcf',
        ];

        return view('userPanel.ads.offers', [
            'ad' => [
                'id' => $ad->id,
                'code' => $ad->code,
                'bank' => $ad->bank?->name ?? '',
                'image' => $ad->bank?->image ?? '1',
                'amount' => $ad->amount,
                'price' => $ad->price,
                'best' => $best,
                'avg' => $avg,
                'city' => $ad->city ?? '',
                'state' => $ad->statusLabel(),
            ],
            'offers' => $offers,
            'pending' => $pending,
            'best' => $best,
            'statusMap' => $statusMap,
            'tabs' => $tabs,
            'counts' => $counts,
            'groupOf' => $groupOf,
            'fa' => $fa,
            'faId' => $faId,
            'avatarColors' => $avatarColors,
        ]);
    }

    /** فرم ویرایش آگهی */
    public function edit(Request $request, Ad $ad): View
    {
        $this->authorizeOwner($request, $ad);

        $ad->load(['bank', 'documents']);

        return view('userPanel.ads.edit', [
            'ad' => Presenter::editAd($ad),
            'banks' => Presenter::banks(Bank::query()->active()->ordered()->get()),
        ]);
    }

    /** ذخیره ویرایش آگهی (آگهی دوباره به صف بررسی می‌رود) */
    public function update(Request $request, Ad $ad): RedirectResponse
    {
        $this->authorizeOwner($request, $ad);

        if (! $ad->isEditable()) {
            return back()->withErrors(['ad' => 'این آگهی در وضعیت فعلی قابل ویرایش نیست.']);
        }

        $data = $request->validate([
            'bank' => ['required', 'string'],
            'amount' => ['required', 'string'],
            'months' => ['required', 'string'],
            'rate' => ['required', 'string'],
            'price' => ['required', 'string'],
            'sheba' => ['required', 'string'],
            'note' => ['required', 'string', 'min:10', 'max:500'],
            'doc_balance' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:'.config('vamjou.uploads.max_size')],
            'doc_id' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:'.config('vamjou.uploads.max_size')],
        ], [], [
            'bank' => 'بانک', 'amount' => 'مبلغ وام', 'months' => 'تعداد اقساط',
            'rate' => 'نرخ سود', 'price' => 'قیمت پیشنهادی', 'sheba' => 'شماره شبا', 'note' => 'توضیحات',
        ]);

        if (! Iran::validSheba($data['sheba'])) {
            throw ValidationException::withMessages(['sheba' => 'شماره شبا وارد‌شده معتبر نیست.']);
        }

        $bankName = Text::canonical($data['bank'], Bank::query()->active()->pluck('name')->all());
        $bank = $bankName ? Bank::query()->where('name', $bankName)->first() : null;

        if (! $bank) {
            throw ValidationException::withMessages(['bank' => 'بانک انتخاب‌شده معتبر نیست.']);
        }

        $amount = (int) Iran::digits($data['amount']);
        $months = (int) Iran::digits($data['months']);
        $rate = (float) Iran::toLatin($data['rate']);

        $ad->update([
            'bank_id' => $bank->id,
            'amount' => $amount,
            'months' => $months,
            'rate' => $rate,
            'type'=>$request->type,
            'price' => (int) Iran::digits($data['price']),
            'max_price' => (int) round($amount * config('vamjou.price_recommendation.max') / 100),
            'total_repayment' => (int) round($amount * (1 + $rate / 100)),
            'installment' => $months ? (int) round(($amount * (1 + $rate / 100)) / $months) : null,
            'sheba' => strtoupper(Iran::toLatin($data['sheba'])),
            'description' => $data['note'],
            'status' => Ad::STATUS_PENDING,
            'reject_reason' => null,
        ]);

        foreach (['doc_balance' => AdDocument::KIND_BALANCE, 'doc_id' => AdDocument::KIND_NATIONAL_CARD] as $field => $kind) {
            if (! $request->hasFile($field)) {
                continue;
            }

            $file = $request->file($field);
            $path = $file->store("ads/{$ad->id}", config('vamjou.uploads.disk'));

            $ad->documents()->updateOrCreate(['kind' => $kind], [
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize() ?: 0,
                'must_replace' => false,
            ]);
        }

        ActivityLog::record($request->user(), 'ad.updated', "ویرایش آگهی {$ad->code}", ['ad_id' => $ad->id]);

        return redirect()->route('user.ads.index')
            ->with('success', 'آگهی شما به‌روزرسانی شد و برای بررسی مجدد ارسال گردید.');
    }

    public function destroy(Request $request, Ad $ad): RedirectResponse
    {
        $this->authorizeOwner($request, $ad);

        if ($ad->status === Ad::STATUS_TRANSFER) {
            return back()->withErrors(['ad' => 'آگهی در حال معامله است و حذف نمی‌شود.']);
        }

        $ad->delete();

        ActivityLog::record($request->user(), 'ad.deleted', "حذف آگهی {$ad->code}", ['ad_id' => $ad->id]);

        return redirect()->route('user.ads.index')->with('success', 'آگهی حذف شد.');
    }

    /** ثبت اطلاعات انتقال امتیاز توسط فروشنده */
    public function transfer(Request $request, Ad $ad): RedirectResponse
    {
        $this->authorizeOwner($request, $ad);

        $data = $request->validate([
            'transfer_time' => ['required', 'string', 'max:60'],
            'tracking_no' => ['required', 'string', 'min:6', 'max:20'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:'.config('vamjou.uploads.max_size')],
        ], [], ['transfer_time' => 'زمان انتقال', 'tracking_no' => 'شماره پیگیری']);

        $deal = $ad->requests()->active()->latest()->first();

        if (! $deal) {
            throw ValidationException::withMessages(['ad' => 'برای این آگهی معامله فعالی وجود ندارد.']);
        }

        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $ad->documents()->create([
                'kind' => 'transfer_receipt',
                'path' => $file->store("ads/{$ad->id}/transfer", config('vamjou.uploads.disk')),
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize() ?: 0,
            ]);
        }

        $deal->update(['step' => 6, 'transfer_due_at' => now()]);

        ActivityLog::record($request->user(), 'ad.transfer_submitted', "ثبت انتقال امتیاز برای معامله {$deal->code}", [
            'ad_id' => $ad->id, 'tracking_no' => $data['tracking_no'],
        ]);

        return redirect()->route('user.ads.index')->with('success', 'اطلاعات انتقال ثبت شد و در انتظار تایید خریدار است.');
    }

    private function authorizeOwner(Request $request, Ad $ad): void
    {
        abort_unless($ad->user_id === $request->user()->id, 403, 'این آگهی متعلق به شما نیست.');
    }
}
