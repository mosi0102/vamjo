<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Transaction;
use App\Support\Dashboard;
use App\Support\Presenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function index(Request $request): View
    {
        $rows = Transaction::query()
            ->with(['user', 'ad.bank'])
            ->when($request->filled('state'), function ($query) use ($request) {
                match ($request->string('state')->toString()) {
                    'payout_due' => $query->ofType(Transaction::TYPE_PAYOUT)->ofStatus(Transaction::STATUS_PENDING),
                    'escrow' => $query->escrow(),
                    'paid' => $query->ofType(Transaction::TYPE_PAYOUT)->ofStatus(Transaction::STATUS_RELEASED),
                    'refund' => $query->ofStatus(Transaction::STATUS_REFUNDED),
                    'failed' => $query->ofStatus(Transaction::STATUS_FAILED),
                    default => $query,
                };
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('adminPanel.payments.index', [
            'rows' => collect($rows->items())->map(fn (Transaction $transaction) => Presenter::adminPaymentRow($transaction))->all(),
            'summary' => Dashboard::financeSummary(),
            'rate' => (int) config('vamjou.commission_rate'),
            'paginator' => $rows,
        ]);
    }

    /** ثبت واریز مبلغ معامله به فروشنده */
    public function payout(Request $request, Transaction $transaction): RedirectResponse
    {
        abort_unless($transaction->type === Transaction::TYPE_PAYOUT, 404);

        $data = $request->validate([
            'reference' => ['required', 'string', 'min:6', 'max:24'],
        ], [], ['reference' => 'کد پیگیری واریز']);

        $transaction->update([
            'status' => Transaction::STATUS_RELEASED,
            'ref_id' => $data['reference'],
            'released_at' => now(),
        ]);

        ActivityLog::record($transaction->user, 'payout.released', "واریز مبلغ معامله با کد پیگیری {$data['reference']}", [
            'transaction_id' => $transaction->id, 'amount' => $transaction->amount,
        ]);

        return back()->with('success', 'واریز فروشنده ثبت شد.');
    }

    /** برگشت وجه به خریدار */
    public function refund(Request $request, Transaction $transaction): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:200'],
        ]);

        $transaction->update([
            'status' => Transaction::STATUS_REFUNDED,
            'failure_reason' => $data['reason'] ?? $transaction->failure_reason,
            'released_at' => now(),
        ]);

        ActivityLog::record($transaction->user, 'payment.refunded', 'برگشت وجه به خریدار', [
            'transaction_id' => $transaction->id, 'amount' => $transaction->amount,
        ]);

        return back()->with('success', 'برگشت وجه ثبت شد.');
    }
}
