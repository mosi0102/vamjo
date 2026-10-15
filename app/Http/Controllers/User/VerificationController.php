<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\KycVerification;
use App\Models\User;
use App\Support\Iran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VerificationController extends Controller
{
    /** نمایش فرم / خلاصه احراز هویت */
    public function index(Request $request): View
    {
        $user = $request->user();

        $state = match ($user->kyc_status) {
            User::KYC_VERIFIED => 'verified',
            User::KYC_REJECTED => 'rejected',
            User::KYC_PENDING => 'pending',
            default => 'unverified',
        };

        $latest = $user->latestKyc;
        $reason = $latest?->reject_reason ?? session('kyc_reason');

        $profile = [
            'first' => (string) ($user->first_name ?? ''),
            'last' => (string) ($user->last_name ?? ''),
            'mobile' => (string) ($user->mobile ?? ''),
            'nid' => (string) ($user->national_id ?? ''),
            'birth' => (string) ($user->birth_date ?? ''),
            'birth_fa' => $user->birth_date ? $this->toPersianDigits(str_replace('-', ' – ', $user->birth_date)) : '',
            'sheba' => (string) ($user->sheba ?? ''),
            'bank' => (string) ($user->bank_name ?? ($latest?->bank_name ?? '')),
        ];

        return view('userPanel.profile.index', compact('state', 'profile', 'reason'));
    }

    /** ثبت / ارسال مدارک احراز هویت */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        /* ---------- ۱) بررسی وضعیت فعلی ---------- */
        if ($user->kyc_status === User::KYC_VERIFIED) {
            return redirect()->route('user.verify')
                ->withErrors(['form' => 'هویت شما قبلاً تایید شده است و امکان ارسال مجدد وجود ندارد.']);
        }

        if ($user->kyc_status === User::KYC_PENDING) {
            return redirect()->route('user.verify')
                ->withErrors(['form' => 'درخواست احراز هویت شما در حال بررسی است. لطفاً منتظر نتیجه بمانید.']);
        }

        // اگر کاربر rejected است ولی ردیف pending در KYC دارد (ناهماهنگی داده)، هم بلاک کن
        $hasPendingKyc = $user->kycVerifications()
            ->where('status', 'pending')
            ->exists();

        if ($hasPendingKyc) {
            return redirect()->route('user.verify')
                ->withErrors(['form' => 'درخواست احراز هویت شما در حال بررسی است.']);
        }

        /* ---------- ۲) نرمال‌سازی شبا ---------- */
        $request->merge([
            'sheba' => $this->normalizeSheba($request->input('sheba')),
        ]);

        /* ---------- ۳) اعتبارسنجی ---------- */
        $data = $request->validate([
            'first_name' => ['required', 'string', 'min:2', 'max:40', 'regex:/^[\x{0600}-\x{06FF}\x{200c}\s]+$/u'],
            'last_name' => ['required', 'string', 'min:2', 'max:40', 'regex:/^[\x{0600}-\x{06FF}\x{200c}\s]+$/u'],
            'national_code' => ['required', 'digits:10'],
            'birth_date' => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            'sheba' => ['required'],
            'selfie' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'id_card' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ], [], [
            'first_name' => 'نام',
            'last_name' => 'نام خانوادگی',
            'national_code' => 'کد ملی',
            'birth_date' => 'تاریخ تولد',
            'sheba' => 'شماره شبا',
            'selfie' => 'تصویر سلفی با کارت ملی',
            'id_card' => 'تصویر کارت ملی',
        ]);

        /* ---------- ۴) اعتبار کد ملی ---------- */
        if (!Iran::validNationalId($data['national_code'])) {
            throw ValidationException::withMessages([
                'national_code' => 'کد ملی وارد‌شده معتبر نیست.',
            ]);
        }

        /* ---------- ۵) چک تکراری در جدول کاربران (مهم – همین کامنت شده بود) ---------- */
        $dupUser = User::where('national_id', $data['national_code'])
            ->where('id', '!=', $user->id)
            ->exists();

        if ($dupUser) {
            throw ValidationException::withMessages([
                'national_code' => 'این کد ملی قبلاً توسط کاربر دیگری ثبت شده است.',
            ]);
        }

        /* ---------- ۶) چک تکراری در KYC سایر کاربران ---------- */
        $dupKyc = KycVerification::where('national_id', $data['national_code'])
            ->where('user_id', '!=', $user->id)
            ->exists();

        if ($dupKyc) {
            throw ValidationException::withMessages([
                'national_code' => 'برای این کد ملی قبلاً درخواست احراز هویت ثبت شده است.',
            ]);
        }

        /* ---------- ۷) ذخیره مدارک ---------- */
        $selfiePath = $request->file('selfie')->store("kyc/{$user->id}/selfies", 'local');
        $cardPath = $request->file('id_card')->store("kyc/{$user->id}/cards", 'local');

        $bankName = $this->bankNameFromSheba($data['sheba']);

        /* ---------- ۸) به‌روزرسانی اطلاعات کاربر (کاربر جدید ساخته نمی‌شود) ---------- */
        $user->forceFill([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'name' => trim($data['first_name'] . ' ' . $data['last_name']),
            'national_id' => $data['national_code'],
            'birth_date' => $data['birth_date'],
            'sheba' => $data['sheba'],
            'bank_name' => $bankName,
            'kyc_status' => User::KYC_PENDING,
        ])->save();

        /* ---------- ۹) ثبت ردیف جدید KYC (طبق طراحی مایگریشن: هر ارسال یک ردیف) ---------- */
        $kyc = $user->kycVerifications()->create([
            'national_id' => $data['national_code'],
            'birth_date' => $data['birth_date'],
            'sheba' => $data['sheba'],
            'bank_name' => $bankName,
            'selfie_path' => $selfiePath,
            'national_card_path' => $cardPath,
            'status' => 'pending',
        ]);

        ActivityLog::record($user, 'kyc.submitted', 'ارسال مدارک احراز هویت', [
            'kyc_id' => $kyc->id,
        ]);

        return redirect()->route('user.verify')
            ->with('success', 'مدارک شما دریافت شد و در حال بررسی است. نتیجه تا ۲۴ ساعت آینده اعلام می‌شود.');
    }

    /* ------------------------------ کمکی‌ها ------------------------------ */

    private function toPersianDigits(string $s): string
    {
        return strtr($s, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
    }

    /** استخراج نام بانک از کد بانک در شبا (ارقام ۳ تا ۵؛ دو رقم اول ارقام کنترلی IBAN هستند) */
    private function bankNameFromSheba(string $sheba): ?string
    {
        if (!preg_match('/^IR\d{2}(\d{3})/i', $sheba, $m)) {
            return null;
        }

        return [
            '010' => 'بانک مرکزی',
            '011' => 'بانک صنعت و معدن',
            '012' => 'بانک ملت',
            '013' => 'بانک رفاه',
            '014' => 'بانک مسکن',
            '015' => 'بانک سپه',
            '016' => 'بانک کشاورزی',
            '017' => 'بانک ملی',
            '018' => 'بانک تجارت',
            '019' => 'بانک صادرات',
            '020' => 'بانک توسعه صادرات',
            '021' => 'پست بانک',
            '022' => 'بانک توسعه تعاون',
            '051' => 'موسسه اعتباری توسعه',
            '053' => 'بانک پارسیان',
            '054' => 'بانک پاسارگاد',
            '055' => 'بانک اقتصاد نوین',
            '056' => 'بانک سامان',
            '057' => 'بانک سینا',
            '058' => 'بانک سرمایه',
            '059' => 'بانک شهر',
            '060' => 'بانک دی',
            '061' => 'بانک کارآفرین',
            '062' => 'بانک آینده',
            '063' => 'بانک انصار',
            '064' => 'بانک گردشگری',
            '065' => 'بانک حکمت ایرانیان',
            '066' => 'بانک ایران زمین',
            '069' => 'بانک خاورمیانه',
            '070' => 'بانک قرض‌الحسنه رسالت',
            '073' => 'بانک قرض‌الحسنه مهر ایران',
            '075' => 'بانک ایران‌زمین',
            '078' => 'بانک مهر اقتصاد',
            '079' => 'بانک قرض‌الحسنه کوثر',
        ][$m[1]] ?? null;
    }

    private function normalizeSheba(?string $sheba): string
    {
        if (!is_string($sheba) || $sheba === '') {
            return '';
        }

        // ارقام فارسی/عربی → لاتین
        $s = strtr($sheba, [
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
        ]);

        // هر چیزی غیر از عدد و IR را حذف کن
        $s = preg_replace('/[^\dIRir]/u', '', $s);

        // IR ابتدایی را حذف کن (اگر بود)
        $s = preg_replace('/^[Ii][Rr]/', '', $s);

        // فقط ۲۴ رقم اول
        $s = substr(preg_replace('/\D/', '', $s), 0, 24);

        return 'IR' . $s;
    }
}