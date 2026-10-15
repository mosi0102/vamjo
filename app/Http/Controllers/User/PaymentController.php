<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Support\Fa;
use App\Support\Presenter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $payments = Transaction::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('type', [Transaction::TYPE_PAYMENT, Transaction::TYPE_REFUND])
            ->with('ad.bank')
            ->latest()
            ->get()
            ->map(fn (Transaction $transaction) => Presenter::paymentRow($transaction))
            ->all();

        return view('userPanel.payments.index', compact('payments'));
    }

    /** خروجی CSV از تراکنش‌های کاربر */
    public function export(Request $request): StreamedResponse
    {
        $transactions = Transaction::query()
            ->where('user_id', $request->user()->id)
            ->with('ad')
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($transactions) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM برای نمایش صحیح فارسی در اکسل
            fputcsv($out, ['شماره تراکنش', 'نوع', 'وضعیت', 'مبلغ (تومان)', 'کمیسیون', 'شناسه وام', 'تاریخ']);

            foreach ($transactions as $transaction) {
                fputcsv($out, [
                    $transaction->code,
                    $transaction->typeLabel(),
                    $transaction->statusLabel(),
                    $transaction->amount,
                    $transaction->fee,
                    $transaction->ad?->code,
                    Fa::dateTime($transaction->paid_at ?? $transaction->created_at, 'Y/m/d H:i'),
                ]);
            }

            fclose($out);
        }, 'payments-'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
