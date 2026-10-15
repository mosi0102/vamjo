<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1e5a45">
    <title>ورود | ثبت نام – وام‌جو</title>
    <link rel="stylesheet" href="{{ asset('website/css/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('website/css/bootstrap.min.css') }}">

    <style>
        :root { --brand: #1e5a45; --brand-soft: #e4efe9; --ink: #1f2a26; --muted: #6b7d76; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px;
            font-family: "IRANSans", "Vazirmatn", Tahoma, sans-serif;
            background: linear-gradient(160deg, #f4f8f6 0%, #e8f1ec 100%); color: var(--ink);
        }
        .card { width: 100%; max-width: 430px; background: #fff; border-radius: 20px; padding: 28px; box-shadow: 0 18px 45px rgba(20, 60, 45, .12); }
        .brand { display: flex; align-items: center; gap: 10px; margin-bottom: 18px; font-weight: 700; color: var(--brand); }
        .brand span { display: grid; place-items: center; width: 38px; height: 38px; border-radius: 12px; background: var(--brand-soft); }
        h1 { font-size: 1.25rem; margin: 0 0 6px; }
        p.hint { color: var(--muted); font-size: .9rem; margin: 0 0 20px; line-height: 1.9; }
        label { display: block; font-size: .88rem; margin-bottom: 6px; color: var(--muted); }
        input {
            width: 100%; padding: 12px 14px; border: 1px solid #d7e2dc; border-radius: 12px;
            font-size: 1rem; text-align: left; direction: ltr; background: #fbfdfc;
        }
        input:focus { outline: 2px solid var(--brand-soft); border-color: var(--brand); }
        .field { margin-bottom: 14px; }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        button {
            width: 100%; margin-top: 8px; padding: 13px 18px; border: 0; border-radius: 12px;
            background: var(--brand); color: #fff; font-size: 1rem; font-weight: 600; cursor: pointer;
        }
        button.ghost { background: #fff; color: var(--brand); border: 1px solid var(--brand); }
        .alert { border-radius: 12px; padding: 11px 14px; font-size: .88rem; margin-bottom: 16px; line-height: 1.9; }
        .alert-ok { background: var(--brand-soft); color: #16483a; }
        .alert-err { background: #fdeaea; color: #9b1c1c; }
        .err { color: #b42318; font-size: .82rem; margin-top: 6px; }
        .sep { margin: 18px 0; border: 0; border-top: 1px dashed #d7e2dc; }
        .foot { text-align: center; margin-top: 16px; font-size: .85rem; }
        a { color: var(--brand); text-decoration: none; }
        .summary { background: #f6faf8; border-radius: 12px; padding: 12px 14px; font-size: .86rem; color: var(--muted); margin-bottom: 16px; }
    </style>
</head>
<body>
<div class="card">
    <div class="brand"><span>و</span> وام‌جو</div>

    <h1>{{ old('phone') ? 'کد تایید را وارد کنید' : 'ورود | ثبت نام' }}</h1>
    <p class="hint">
        @if (old('phone'))
            کد ۵ رقمی ارسال‌شده به <b dir="ltr">{{ old('phone') }}</b> را وارد کنید. اگر کاربر جدید هستید، نام و کد ملی خود را هم تکمیل کنید.
        @else
            با شماره موبایل و کد یکبار مصرف وارد شوید؛ ثبت‌نام نیازی به رمز عبور ندارد.
        @endif
    </p>

    @if (session('status'))
        <div class="alert alert-ok">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-err">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if (old('phone'))
        {{-- مرحله ۲: کد تایید + تکمیل اطلاعات کاربر جدید --}}
        <form method="POST" action="{{ route('login.verify') }}">
            @csrf
            <input type="hidden" name="phone" value="{{ old('phone') }}">

            <div class="field">
                <label for="code">کد تایید (۵ رقمی)</label>
                <input type="text" id="code" name="code" inputmode="numeric" maxlength="5"
                       autocomplete="one-time-code" value="{{ old('code') }}" required autofocus>
            </div>

            <div class="summary">
                اگر کاربر جدید هستید، این بخش را تکمیل کنید:
            </div>

            <div class="row">
                <div class="field">
                    <label for="first_name">نام</label>
                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" dir="rtl">
                </div>
                <div class="field">
                    <label for="last_name">نام خانوادگی</label>
                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" dir="rtl">
                </div>
            </div>

            <div class="field">
                <label for="national_code">کد ملی</label>
                <input type="text" id="national_code" name="national_code" inputmode="numeric" maxlength="10"
                       value="{{ old('national_code') }}">
            </div>

            <button type="submit">ورود به حساب</button>
        </form>

        <hr class="sep">

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf
            <input type="hidden" name="phone" value="{{ old('phone') }}">
            <button type="submit" class="ghost">ارسال مجدد کد</button>
        </form>
    @else
        {{-- مرحله ۱: شماره موبایل --}}
        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf
            <div class="field">
                <label for="phone">شماره موبایل</label>
                <input type="text" id="phone" name="phone" inputmode="numeric" maxlength="11"
                       placeholder="09123456789" value="{{ old('phone') }}" required autofocus>
            </div>
            <button type="submit">ارسال کد تایید</button>
        </form>
    @endif

    <p class="foot"><a href="{{ route('site.home') }}">بازگشت به صفحه اصلی سایت</a></p>
</div>
</body>
</html>
