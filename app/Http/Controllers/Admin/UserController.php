<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\LoanRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Support\Fa;
use App\Support\Presenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->role(User::ROLE_USER)
            ->withCount(['ads', 'purchases', 'sales'])
            ->addSelect(['paid_total' => Transaction::query()
                ->selectRaw('coalesce(sum(amount), 0)')
                ->whereColumn('user_id', 'users.id')
                ->whereIn('status', [Transaction::STATUS_ESCROW, Transaction::STATUS_RELEASED]),
            ])
            ->when($request->filled('q'), fn ($query) => $query->search($request->string('q')->toString()))
            ->when($request->filled('state'), function ($query) use ($request) {
                match ($request->string('state')->toString()) {
                    'verified' => $query->verified(),
                    'pending' => $query->pendingKyc(),
                    'unverified' => $query->whereIn('kyc_status', [User::KYC_UNVERIFIED, User::KYC_REJECTED]),
                    'blocked' => $query->blocked(),
                    default => $query,
                };
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('adminPanel.users.index', [
            'users' => collect($users->items())->map(fn (User $user) => Presenter::adminUserRow($user))->all(),
            'summary' => [
                'total' => User::query()->role(User::ROLE_USER)->count(),
                'verified' => User::query()->verified()->count(),
                'pending' => User::query()->pendingKyc()->count(),
                'blocked' => User::query()->blocked()->count(),
            ],
            'paginator' => $users,
        ]);
    }

    public function show(Request $request, User $user): View
    {
        $user->load('latestKyc.reviewer');

        $ads = $user->ads()->with('bank')->latest()->limit(10)->get()
            ->map(fn ($ad) => [
                $ad->title(),
                $ad->code,
                $ad->statusLabel(),
                match ($ad->status) {
                    'published', 'completed' => 'approved',
                    'rejected' => 'rejected',
                    default => 'pending',
                },
                Fa::ago($ad->created_at),
            ])->all();

        $requests = LoanRequest::query()
            ->where(fn ($query) => $query->where('buyer_id', $user->id)->orWhere('seller_id', $user->id))
            ->with('ad')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (LoanRequest $item) => [
                ($item->ad?->title() ?? 'معامله'),
                $item->code,
                $item->stepLabel(),
                $item->isCompleted() ? 'approved' : ($item->status === LoanRequest::STATUS_ACTIVE ? 'pending' : 'rejected'),
                Fa::digits(number_format($item->amount)),
            ])->all();

        return view('adminPanel.users.details', [
            'u' => [
                'id' => $user->id,
                'name' => $user->display_name,
                'mobile' => $user->mobile,
                'nid' => $user->national_id,
                'birth' => $user->birth_date ? Fa::digits(str_replace('-', '/', $user->birth_date)) : '—',
                'sheba' => $user->sheba,
                'bank' => $user->bank_name,
                'city' => $user->city,
                'joined' => Fa::dateTime($user->created_at),
                'last' => Fa::ago($user->last_login_at ?? $user->created_at),
                'ver' => $user->kyc_status,
                'blocked' => $user->isBlocked(),
                'ip' => $user->last_login_ip ?? '—',
            ],
            'stats' => [
                ['آگهی ها', Fa::digits($user->ads()->count()), 'book'],
                ['درخواست ها', Fa::digits($user->purchases()->count() + $user->sales()->count()), 'clipboard'],
                ['کل پرداخت', Fa::digits(number_format((int) $user->transactions()->sum('amount'))).' <small>تومان</small>', 'wallet'],
                ['معاملات موفق', Fa::digits($user->purchases()->where('status', LoanRequest::STATUS_COMPLETED)->count()), 'check'],
            ],
            'myAds' => $ads,
            'myReqs' => $requests,
            'logs' => Presenter::userLogs($user, 8),
            'notes' => $user->notes()->with('author')->latest()->get(),
            'kyc' => $user->latestKyc,
            'user' => $user,
        ]);
    }

    public function block(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:200']]);

        $user->forceFill([
            'blocked_at' => now(),
            'block_reason' => $data['reason'] ?? null,
        ])->save();

        ActivityLog::record($user, 'user.blocked', 'مسدود شدن حساب کاربری توسط مدیر', [
            'reason' => $data['reason'] ?? null,
        ]);

        return back()->with('success', 'کاربر مسدود شد.');
    }

    public function unblock(Request $request, User $user): RedirectResponse
    {
        $user->forceFill(['blocked_at' => null, 'block_reason' => null])->save();

        ActivityLog::record($user, 'user.unblocked', 'رفع مسدودی حساب کاربری');

        return back()->with('success', 'مسدودی کاربر برداشته شد.');
    }

    public function note(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'min:3', 'max:500']], [], ['body' => 'متن یادداشت']);

        $user->notes()->create([
            'author_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        ActivityLog::record($user, 'user.note', 'ثبت یادداشت داخلی برای کاربر');

        return back()->with('success', 'یادداشت ثبت شد.');
    }
}
