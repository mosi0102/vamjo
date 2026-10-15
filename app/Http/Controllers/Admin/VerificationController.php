<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\KycVerification;
use App\Models\User;
use App\Support\Presenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VerificationController extends Controller
{
    /** درخواست‌های احراز هویت؛ پیش‌فرض: در انتظار بررسی‌ها اول فهرست می‌شوند */
    public function index(Request $request): View
    {
        $state = $request->string('state')->toString() ?: 'all';

        $rows = KycVerification::query()
            ->with(['user', 'reviewer'])
            ->ofStatus($state !== 'all' ? $state : null)
            ->orderByRaw('case when status = ? then 0 else 1 end', [KycVerification::STATUS_PENDING])
            ->latest('id')
            ->limit(300)
            ->get();

        return view('adminPanel.users.verify', [
            'rows' => $rows->map(fn (KycVerification $kyc) => Presenter::kycRow($kyc))->all(),
            'counts' => [
                'all' => KycVerification::query()->count(),
                'pending' => KycVerification::query()->pending()->count(),
                'approved' => KycVerification::query()->ofStatus(KycVerification::STATUS_APPROVED)->count(),
                'rejected' => KycVerification::query()->ofStatus(KycVerification::STATUS_REJECTED)->count(),
            ],
            'state' => $state,
        ]);
    }

    public function approve(Request $request, KycVerification $kyc): RedirectResponse
    {
        $kyc->update([
            'status' => KycVerification::STATUS_APPROVED,
            'reject_reason' => null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $kyc->user?->forceFill(['kyc_status' => User::KYC_VERIFIED])->save();

        ActivityLog::record($kyc->user, 'kyc.approved', 'تایید احراز هویت توسط کارشناس', [
            'kyc_id' => $kyc->id, 'reviewer' => $request->user()->id,
        ]);

        return back()->with('success', 'احراز هویت '.($kyc->user?->display_name ?? 'کاربر').' تایید شد.');
    }

    public function reject(Request $request, KycVerification $kyc): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:200']], [], ['reason' => 'دلیل رد']);

        $kyc->update([
            'status' => KycVerification::STATUS_REJECTED,
            'reject_reason' => $data['reason'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $kyc->user?->forceFill(['kyc_status' => User::KYC_REJECTED])->save();

        ActivityLog::record($kyc->user, 'kyc.rejected', 'رد احراز هویت: '.$data['reason'], ['kyc_id' => $kyc->id]);

        return back()->with('success', 'احراز هویت رد شد و دلیل آن برای کاربر نمایش داده می‌شود.');
    }

    /**
     * نمایش مدرک ارسالی کاربر (سلفی / کارت ملی).
     * مدارک روی دیسک خصوصی ذخیره می‌شوند و فقط با دسترسی کارشناس قابل مشاهده‌اند.
     */
    public function document(KycVerification $kyc, string $kind): StreamedResponse
    {
        abort_unless(in_array($kind, [KycVerification::DOC_SELFIE, KycVerification::DOC_CARD], true), 404);

        $path = $kyc->documentPath($kind);

        abort_if(! $path, 404);

        foreach (['local', 'uploads', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->response($path, null, [
                    'Cache-Control' => 'private, no-store, max-age=0',
                    'X-Content-Type-Options' => 'nosniff',
                ]);
            }
        }

        abort(404);
    }
}
