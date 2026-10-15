<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\LoanRequest;
use App\Models\Transaction;
use App\Support\Code;
use App\Support\Fa;
use App\Support\Gateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * شروع پرداخت مبلغ معامله توسط خریدار.
     * در حالت شبیه‌ساز (بدون کلید درگاه) پرداخت بلافاصله تایید و مبلغ در امانت ثبت می‌شود.
     */
    public function initiate(Request $request, LoanRequest $loanRequest): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->id === $loanRequest->buyer_id, 403, 'این معامله متعلق به شما نیست.');
        abort_unless(
            $loanRequest->status === LoanRequest::STATUS_ACTIVE && $loanRequest->step === 3,
            403,
            'این معامله در وضعیت پرداخت نیست.'
        );

        $transaction = $loanRequest->transactions()
            ->where('type', Transaction::TYPE_PAYMENT)
            ->whereIn('status', [Transaction::STATUS_PENDING, Transaction::STATUS_FAILED])
            ->latest()
            ->first();

        $transaction ??= $loanRequest->transactions()->create([
            'code' => Code::make('transactions'),
            'ad_id' => $loanRequest->ad_id,
            'user_id' => $user->id,
            'counterparty_id' => $loanRequest->seller_id,
            'type' => Transaction::TYPE_PAYMENT,
            'status' => Transaction::STATUS_PENDING,
            'amount' => $loanRequest->amount,
            'fee' => (int) round($loanRequest->amount * config('vamjou.commission_rate') / 100),
            'gateway' => Gateway::label(),
        ]);

        $authority = Gateway::request($transaction);
        [$ok, $ref] = Gateway::verify($transaction);

        if (! $ok) {
            $transaction->update([
                'status' => Transaction::STATUS_FAILED,
                'authority' => $authority,
                'failure_reason' => 'پرداخت در درگاه تایید نشد.',
            ]);

            return redirect()->route('site.payment.result', ['status' => 'failed', 'tx' => $transaction->code]);
        }

        $transaction->update([
            'status' => Transaction::STATUS_ESCROW,
            'authority' => $authority,
            'ref_id' => $ref,
            'paid_at' => now(),
            'net' => $transaction->amount - $transaction->fee,
        ]);

        $loanRequest->update(['step' => 4, 'paid_at' => now()]);

        ActivityLog::record($user, 'payment.paid', "پرداخت مبلغ معامله {$loanRequest->code}", [
            'transaction_id' => $transaction->id, 'amount' => $transaction->amount,
        ]);

        return redirect()->route('site.payment.result', ['status' => 'success', 'tx' => $transaction->code]);
    }

    /** بازگشت از درگاه و نمایش رسید */
    public function result(Request $request): View
    {
        $status = $request->string('status')->toString() === 'failed' ? 'failed' : 'success';
        $transaction = Transaction::query()->where('code', $request->input('tx'))->with('ad')->first();

        $payment = [
            'amount' => $transaction?->amount ?? 0,
            'paid_at' => Fa::dateTime($transaction?->paid_at ?? $transaction?->created_at, 'j F Y – H:i'),
            'ref' => $transaction?->ref_id ?? '—',
            'tx' => $transaction?->code ?? '—',
            'request_id' => $transaction?->ad?->code ?? '—',
            'reason' => $transaction?->failure_reason
                ?? ($status === 'failed' ? 'انصراف از پرداخت در درگاه' : null),
        ];

        return view('userPanel.Result', compact('status', 'payment'));
    }
}
