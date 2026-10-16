<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Ad;
use App\Models\AdDocument;
use App\Models\Bank;
use App\Models\Favorite;
use App\Support\Code;
use App\Support\Iran;
use App\Support\Presenter;
use App\Support\Text;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdController extends Controller
{
    public function index(Request $request): View
    {
        $banks = Bank::query()->active()->ordered()->get();
        $sort = $request->string('sort')->toString();

        $filters = fn ($query) => $query
            ->ofBank($request->input('bank') ?: $request->input('bank.0'))
            ->ofType($request->input('type'))
            ->city($request->string('city')->toString() ?: null)
            ->amountFrom(Iran::digits($request->input('amount_min')) ?: null)
            ->amountTo(Iran::digits($request->input('amount_max')) ?: null)
            ->monthsTo(Iran::digits($request->input('months')) ?: null)
            ->rateTo($request->filled('rate') ? (float) Iran::toLatin($request->input('rate')) : null)
            ->when($request->boolean('urgent'), fn ($q) => $q->urgent())
            ->when($request->boolean('pay'), fn ($q) => $q->payable())
            ->search($request->string('q')->toString() ?: null)
            ->when($sort === 'rate-asc', fn ($q) => $q
                ->orderBy('rate')->orderByDesc('published_at'))
            ->when($sort === 'amount-asc', fn ($q) => $q
                ->orderBy('amount'))
            ->when($sort === 'cheap', fn ($q) => $q->orderBy('price'))
            ->when($sort === 'expensive' || $sort === 'amount-desc', fn ($q) => $q
                ->orderByDesc('amount'))
            ->when(in_array($sort, ['', 'new'], true), fn ($q) => $q
                ->orderByDesc('featured')->orderByDesc('published_at'));

        $ads = $filters(Ad::query()->published()->with('bank')->withCount('offers'))
            ->paginate(12)
            ->withQueryString();

        // dd($ads->items());

        return view('website.list', [
            'banks' => $banks->mapWithKeys(fn (Bank $bank) => [
                $bank->slug => $bank->toFilterArray(),
            ])->all(),
            'types' => config('vamjou.loan_types'),
            'ads' => collect($ads->items())->map(fn (Ad $ad) => Presenter::siteAd($ad))->all(),
            'paginator' => $ads,
            'total' => $ads->total(),
            'current' => ['sort' => $sort ?: 'new'],
        ]);
    }

    public function show(Request $request, Ad $ad): View
    {
        abort_unless(
            in_array($ad->status, [Ad::STATUS_PUBLISHED, Ad::STATUS_TRANSFER], true) || $request->user()?->isStaff(),
            404
        );

        $ad->load('bank')->loadCount('offers');
        $ad->increment('views');

        $similar = Ad::query()
            ->published()
            ->with('bank')
            ->withCount('offers')
            ->where('id', '!=', $ad->id)
            ->where(function ($query) use ($ad) {
                // بانک یکسان باشد
                $query->where('bank_id', $ad->bank_id)

                    // یا مبلغ وام در محدوده ۲۰ درصدی باشد
                    ->orWhereBetween('amount', [
                        (int) round($ad->amount * 0.8),
                        (int) round($ad->amount * 1.2),
                    ])

                    // یا قیمت در محدوده ۳۰ درصدی باشد
                    ->orWhereBetween('price', [
                        (int) round($ad->price * 0.7),
                        (int) round($ad->price * 1.3),
                    ]);
            })
            ->orderByRaw(
                'ABS(CAST(amount AS SIGNED) - CAST(? AS SIGNED)) / GREATEST(?, 1)
     + ABS(CAST(price AS SIGNED) - CAST(? AS SIGNED)) / GREATEST(?, 1) ASC',
                [
                    $ad->amount,
                    max((int) $ad->amount, 1),
                    $ad->price,
                    max((int) $ad->price, 1),
                ]
            )
            ->limit(3)
            ->get()
            ->map(fn (Ad $item) => Presenter::adCard($item))
            ->all();

        $isFavorite = $request->user()
            ? Favorite::query()->where('user_id', $request->user()->id)->where('ad_id', $ad->id)->exists()
            : false;

        return view('website.details', [
            'ad' => Presenter::adDetail($ad),
            'similar' => $similar,
            'isFavorite' => $isFavorite,
        ]);
    }

    /** فرم ثبت آگهی (نیازمند ورود + احراز هویت) */
    public function create(): View
    {
        return view('website.addNew', [
            'banks' => Presenter::banks(Bank::query()->active()->ordered()->get()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $bankName = Text::canonical($data['bank'], Bank::query()->active()->pluck('name')->all());
        $bank = $bankName ? Bank::query()->where('name', $bankName)->first() : null;

        if (! $bank) {
            return back()->withInput()->withErrors(['bank' => 'بانک انتخاب‌شده معتبر نیست.']);
        }

        $amount = (int) Iran::digits($data['amount']);
        $months = (int) Iran::digits($data['months']);
        $price = (int) Iran::digits($data['price']);
        $rate = (float) Iran::toLatin($data['rate']);

        $ad = Ad::create([
            'code' => Code::make('ads'),
            'user_id' => $request->user()->id,
            'bank_id' => $bank->id,
            'type' => $request->string('type')->toString() ?: 'qarz',
            'amount' => $amount,
            'months' => $months,
            'rate' => $rate,
            'price' => $price,
            'max_price' => (int) round($amount * config('vamjou.price_recommendation.max') / 100),
            'installment' => $months ? (int) round(($amount * (1 + $rate / 100)) / $months) : null,
            'total_repayment' => (int) round($amount * (1 + $rate / 100)),
            'guarantor' => $data['note'] ?? null,
            'city' => $data['city'] ?? $request->user()->city,
            'description' => $data['note'] ?? null,
            'sheba' => strtoupper(Iran::toLatin($data['sheba'])),
            'status' => Ad::STATUS_PENDING,
        ]);

        $this->storeDocuments($ad, $request);

        ActivityLog::record($request->user(), 'ad.created', "ثبت آگهی {$ad->code}", ['ad_id' => $ad->id]);

        return redirect()
            ->route('user.ads.index')
            ->with('success', 'آگهی شما ثبت شد و پس از بررسی اپراتور منتشر می‌شود.');
    }

    /** اعتبارسنجی مشترک فرم ثبت و ویرایش آگهی */
    private function validated(Request $request, bool $withDocuments = true): array
    {
        $rules = [
            'bank' => ['required', 'string'],
            'amount' => ['required', 'string'],
            'months' => ['required', 'string'],
            'rate' => ['required', 'string'],
            'price' => ['required', 'string'],
            'sheba' => ['required', 'string'],
            'note' => ['required', 'string', 'min:10', 'max:500'],
            'city' => ['nullable', 'string', 'max:50'],
            'guarantor' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', Rule::in(array_keys(config('vamjou.loan_types')))],
        ];

        if ($withDocuments) {
            $rules['doc_balance'] = ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:'.config('vamjou.uploads.max_size')];
            $rules['doc_id'] = ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:'.config('vamjou.uploads.max_size')];
        }

        $data = $request->validate($rules, [], [
            'bank' => 'بانک', 'amount' => 'مبلغ وام', 'months' => 'تعداد اقساط',
            'rate' => 'نرخ سود', 'price' => 'قیمت پیشنهادی', 'sheba' => 'شماره شبا',
            'note' => 'توضیحات آگهی',
        ]);

        $amount = (int) Iran::digits($data['amount']);
        $months = (int) Iran::digits($data['months']);
        $rate = (float) Iran::toLatin($data['rate']);

        if ($amount < 1_000_000) {
            throw \Illuminate\Validation\ValidationException::withMessages(['amount' => 'مبلغ وام باید حداقل ۱ میلیون تومان باشد.']);
        }

        if ($months < 1 || $months > 120) {
            throw \Illuminate\Validation\ValidationException::withMessages(['months' => 'تعداد اقساط باید بین ۱ تا ۱۲۰ ماه باشد.']);
        }

        if ($rate < 0 || $rate > 100) {
            throw \Illuminate\Validation\ValidationException::withMessages(['rate' => 'نرخ سود باید بین ۰ تا ۱۰۰ درصد باشد.']);
        }

        if (! Iran::validSheba($data['sheba'])) {
            throw \Illuminate\Validation\ValidationException::withMessages(['sheba' => 'شماره شبا وارد‌شده معتبر نیست.']);
        }

        if ((int) Iran::digits($data['price']) < 1_000_000) {
            throw \Illuminate\Validation\ValidationException::withMessages(['price' => 'قیمت پیشنهادی را وارد کنید.']);
        }

        return $data;
    }

    /** ذخیره مدارک بارگذاری‌شده آگهی */
    private function storeDocuments(Ad $ad, Request $request): void
    {
        foreach (['doc_balance' => AdDocument::KIND_BALANCE, 'doc_id' => AdDocument::KIND_NATIONAL_CARD] as $field => $kind) {
            if (! $request->hasFile($field)) {
                continue;
            }

            $file = $request->file($field);
            $path = $file->store("ads/{$ad->id}", config('vamjou.uploads.disk'));

            $ad->documents()->create([
                'kind' => $kind,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize() ?: 0,
            ]);
        }
    }
}
