<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\OtpCode;
use App\Models\User;
use App\Support\Iran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * ورود و ثبت‌نام با شماره موبایل و کد یکبار مصرف.
 *
 * قرارداد فرانت‌اند (public/website/js/main.js):
 *   POST /auth/otp/send   {phone}                → {ok:true}
 *   POST /auth/otp/verify {phone, code}          → {ok:true, is_new, verified}
 *   POST /auth/register   {phone, first_name, last_name, national_code} → {ok:true, verified}
 * صفحه /login هم نسخه سرور-محور همین جریان را ارائه می‌دهد.
 */
class OtpController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if ($user = Auth::user()) {
            return redirect()->to($this->homeFor($user));
        }

        return view('auth.login');
    }

    /* ------------------------- نسخه JSON (مودال سایت) ------------------------- */

    public function send(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'regex:/^09\d{9}$/'],
        ], [], ['phone' => 'شماره موبایل']);

        $code = $this->issue($data['phone'], $request->ip());

        return response()->json([
            'ok' => true,
            'message' => 'کد تایید ارسال شد.',
            'code' => config('app.debug') ? $code : null, // فقط برای توسعه محلی
        ]);
    }


    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'regex:/^09\d{9}$/'],
            'code' => ['required', 'digits:5'],
        ], [], [
            'phone' => 'شماره موبایل',
            'code' => 'کد تایید',
        ]);

        // بررسی کد تایید
        $otp = OtpCode::latestFor($data['phone']);

        if (
            !$otp ||
            !$otp->isValid() ||
            !hash_equals((string) $otp->code, (string) $data['code'])
        ) {
            $otp?->increment('attempts');

            throw ValidationException::withMessages([
                'code' => 'کد وارد شده صحیح نیست یا منقضی شده است.',
            ]);
        }

        // بررسی وجود کاربر با شماره موبایل
        $user = User::where('mobile', $data['phone'])->first();

        // جلوگیری از ورود کاربران مسدود
        if ($user && $user->isBlocked()) {
            return response()->json([
                'ok' => false,
                'message' => 'حساب کاربری شما مسدود شده است.',
            ], 403);
        }

        // مصرف کد پس از اعتبارسنجی و بررسی وضعیت کاربر
        $otp->update(['consumed_at' => now()]);

        $isNew = false;

        // اگر کاربر وجود ندارد، حساب جدید بساز
        if (!$user) {
            $user = User::create([
                'name' => 'کاربر جدید',
                'first_name' => 'کاربر',
                'last_name' => 'جدید',
                'mobile' => $data['phone'],
                'role' => User::ROLE_USER,
                'kyc_status' => User::KYC_UNVERIFIED,
            ]);

            ActivityLog::record(
                $user,
                'register',
                'ثبت نام خودکار با شماره موبایل'
            );

            $isNew = true;
        }

        // ورود کاربر به سیستم
        $this->login($user, $request);

        return response()->json([
            'ok' => true,
            'is_new' => $isNew,
            'verified' => (bool) $user->is_verified,
            'redirect' => $this->homeFor($user),
            'message' => $isNew
                ? 'حساب کاربری شما ساخته شد و با موفقیت وارد شدید.'
                : 'با موفقیت وارد حساب کاربری شدید.',
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'regex:/^09\d{9}$/'],
            'first_name' => ['required', 'string', 'min:2', 'max:40'],
            'last_name' => ['required', 'string', 'min:2', 'max:40'],
            'national_code' => ['required', 'digits:10'],
        ], [], [
            'first_name' => 'نام',
            'last_name' => 'نام خانوادگی',
            'national_code' => 'کد ملی',
        ]);

        if (!Iran::validNationalId($data['national_code'])) {
            throw ValidationException::withMessages(['national_code' => 'کد ملی وارد‌شده معتبر نیست.']);
        }

        if (User::where('national_id', $data['national_code'])->where('mobile', '!=', $data['phone'])->exists()) {
            throw ValidationException::withMessages(['national_code' => 'این کد ملی قبلاً در سامانه ثبت شده است.']);
        }

        $user = User::where('mobile', $data['phone'])->first();

        if ($user) {
            $user->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'national_id' => $data['national_code'],
                'name' => $data['first_name'] . ' ' . $data['last_name'],
            ]);
        } else {
            $user = User::create([
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'mobile' => $data['phone'],
                'national_id' => $data['national_code'],
                'role' => User::ROLE_USER,
                'kyc_status' => User::KYC_UNVERIFIED,
            ]);

            ActivityLog::record($user, 'register', 'ثبت نام در سامانه');
        }

        $this->login($user, $request);

        return response()->json(['ok' => true, 'is_new' => false, 'verified' => $user->is_verified]);
    }

    /* ------------------------- نسخه سرور-محور صفحه /login ------------------------- */

    public function attempt(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'regex:/^09\d{9}$/'],
        ], [], ['phone' => 'شماره موبایل']);

        $code = $this->issue($data['phone'], $request->ip());

        return back()
            ->withInput(['phone' => $data['phone']])
            ->with('status', config('app.debug')
                ? "کد تایید برای {$data['phone']} ارسال شد. (کد تست: {$code})"
                : 'کد تایید ارسال شد.');
    }

    public function verifyForm(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'regex:/^09\d{9}$/'],
            'code' => ['required', 'digits:5'],
            'first_name' => ['nullable', 'string', 'max:40'],
            'last_name' => ['nullable', 'string', 'max:40'],
            'national_code' => ['nullable', 'digits:10'],
        ]);

        $this->consume($data['phone'], $data['code']);

        $user = User::where('mobile', $data['phone'])->first();

        if (!$user) {
            if (blank($data['first_name'] ?? null) || blank($data['national_code'] ?? null)) {
                return back()->withInput()
                    ->withErrors(['first_name' => 'برای ثبت‌نام، نام و کد ملی خود را وارد کنید.']);
            }

            if (!Iran::validNationalId($data['national_code'])) {
                return back()->withInput()->withErrors(['national_code' => 'کد ملی وارد‌شده معتبر نیست.']);
            }

            $user = User::create([
                'name' => trim($data['first_name'] . ' ' . ($data['last_name'] ?? '')),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? null,
                'mobile' => $data['phone'],
                'national_id' => $data['national_code'],
                'role' => User::ROLE_USER,
                'kyc_status' => User::KYC_UNVERIFIED,
            ]);

            ActivityLog::record($user, 'register', 'ثبت نام در سامانه');
        }

        if ($user->isBlocked()) {
            return back()->withErrors(['phone' => 'حساب کاربری شما مسدود شده است.']);
        }

        $this->login($user, $request);

        return redirect()->intended($this->homeFor($user));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('site.home');
    }

    /* ------------------------------ کمکی‌ها ------------------------------ */

    private function issue(string $mobile, ?string $ip): string
    {
        $code = (string) random_int(10000, 99999);

        OtpCode::create([
            'mobile' => $mobile,
            'code' => $code,
            'purpose' => OtpCode::PURPOSE_LOGIN,
            'expires_at' => now()->addMinutes(5),
            'ip' => $ip,
        ]);

        Log::info("کد ورود {$mobile}: {$code}");   // در محیط واقعی این‌جا پیامک ارسال می‌شود

        return $code;
    }

    private function consume(string $mobile, string $code): void
    {
        $otp = OtpCode::latestFor($mobile);

        if (!$otp || !$otp->isValid() || !hash_equals($otp->code, $code)) {
            $otp?->increment('attempts');

            throw ValidationException::withMessages(['code' => 'کد وارد شده صحیح نیست یا منقضی شده است.']);
        }

        $otp->update(['consumed_at' => now()]);
    }

    private function login(User $user, Request $request): void
    {
        Auth::login($user, true);
        $request->session()->regenerate();

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        ActivityLog::record($user, 'login', 'ورود به حساب کاربری');
    }

    private function homeFor(User $user): string
    {
        return $user->isStaff() ? route('admin.dashboard') : route('user.dashboard');
    }
}
