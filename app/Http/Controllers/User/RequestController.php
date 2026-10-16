<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Ad;
use App\Models\LoanRequest;
use App\Models\Transaction;
use App\Support\Code;
use App\Support\Presenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RequestController extends Controller
{
    /** درخواست های من (خریدار) */
    public function index(Request $request): View
    {
        $user = $request->user();

        $requests = $user->purchases()
            ->with('ad.bank', 'ad.documents')
            ->latest()
            ->get()
            ->map(fn (LoanRequest $item) => Presenter::requestRow($item))
            ->all();

        return view('userPanel.requests.index', compact('requests'));
    }

    /** رفتن به درگاه پرداخت */
    public function pay(Request $request, LoanRequest $loanRequest): RedirectResponse
    {
        abort_unless($loanRequest->buyer_id === $request->user()->id, 403);

        return redirect()->route('site.payment.start', $loanRequest);
    }

    /** تایید انتقال امتیاز توسط خریدار → تسویه با فروشنده */
    public function confirmTransfer(Request $request, LoanRequest $loanRequest): RedirectResponse
    {
        $user = $request->user();

        abort_unless($loanRequest->buyer_id === $user->id, 403, 'این معامله متعلق به شما نیست.');

        if ($loanRequest->step !== 6) {
            return back()->withErrors(['request' => 'معامله در مرحله تایید انتقال نیست.']);
        }

        $amount = $loanRequest->amount;
        $fee = (int) round($amount * config('vamjou.commission_rate') / 100);
        $net = $amount - $fee;

        $payout = $loanRequest->transactions()->create([
            'code' => Code::make('transactions'),
            'ad_id' => $loanRequest->ad_id,
            'user_id' => $loanRequest->seller_id,
            'counterparty_id' => $user->id,
            'type' => Transaction::TYPE_PAYOUT,
            'status' => Transaction::STATUS_PENDING,
            'amount' => $net,
            'fee' => $fee,
            'net' => $net,
            'sheba' => $loanRequest->seller?->sheba,
        ]);

        $loanRequest->transactions()->create([
            'code' => Code::make('transactions'),
            'ad_id' => $loanRequest->ad_id,
            'user_id' => $user->id,
            'type' => Transaction::TYPE_FEE,
            'status' => Transaction::STATUS_RELEASED,
            'amount' => $fee,
            'net' => $fee,
        ]);

        $loanRequest->update([
            'step' => 7,
            'status' => LoanRequest::STATUS_COMPLETED,
            'transfer_confirmed_at' => now(),
            'completed_at' => now(),
        ]);

        $loanRequest->ad?->update(['status' => Ad::STATUS_COMPLETED, 'completed_at' => now()]);

        ActivityLog::record($user, 'request.transfer_confirmed', "تایید انتقال امتیاز معامله {$loanRequest->code}", [
            'request_id' => $loanRequest->id, 'payout_id' => $payout->id,
        ]);

        return redirect()->route('user.requests')
            ->with('success', 'انتقال امتیاز تایید شد؛ مبلغ پس از کسر کمیسیون به فروشنده واریز می‌شود.');
    }
}
