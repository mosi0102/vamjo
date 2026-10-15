#!/usr/bin/env bash
# تست سرتاسری جریان احراز هویت (فقط محلی):
#   ثبت درخواست کاربر → نمایش در پنل ادمین → مشاهده مدارک → تایید/رد → تغییر وضعیت کاربر
set -u
B=${B:-http://127.0.0.1:8123}
PHPBIN="php -d extension=mbstring -d extension=fileinfo"
ROOT=$(cd "$(dirname "$0")/.." && pwd)
ART="$ROOT/artisan"
# مسیر ویندوزی برای آپلود فایل با curl (curl ویندوزی مسیرهای /c/... را نمی‌فهمد)
ROOTW=$(cd "$ROOT" && pwd -W 2>/dev/null || echo "$ROOT")
ADMIN=${ADMIN:-09058375956}
UA=${UA:-09120009001}      # کاربر آزمایشی ۱ (تایید می‌شود)
UB=${UB:-09120009002}      # کاربر آزمایشی ۲ (رد می‌شود)
J1=/tmp/kc_user1.jar
J2=/tmp/kc_user2.jar
JA=/tmp/kc_admin.jar

tinker() { $PHPBIN "$ART" tinker --execute="$1" 2>&1 | tr -d '\r' | grep -v "already loaded\|^PHP Warning\|^Warning" | tail -1; }
tinker_id() { tinker "$1" | grep -E '^[0-9]+$' | tail -1; }
token() { sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' "$1" | head -1; }

mkuser() { # $1 mobile, $2 name, $3 nid
  tinker_id "echo \\App\\Models\\User::updateOrCreate(['mobile' => '$1'], ['name' => '$2', 'first_name' => '$2', 'last_name' => 'آزمون', 'national_id' => '$3', 'role' => \\App\\Models\\User::ROLE_USER, 'kyc_status' => \\App\\Models\\User::KYC_UNVERIFIED])->id;"
}

login() { # $1 mobile, $2 jar
  rm -f "$2"
  curl -s -c "$2" "$B/login" -o /tmp/kc_login.html
  local t; t=$(token /tmp/kc_login.html)
  curl -s -b "$2" -c "$2" -L "$B/login" --data-urlencode "_token=$t" --data-urlencode "phone=$1" -o /tmp/kc_flash.html
  local otp
  otp=$($PHPBIN "$ART" tinker --execute="echo optional(\\App\\Models\\OtpCode::latestFor('$1'))->code;" 2>/dev/null | tr -d '\r' | grep -E '^[0-9]{5}$' | tail -1)
  [ -n "$otp" ] || { echo "! OTP دریافت نشد برای $1"; return 1; }
  curl -s -b "$2" "$B/login" -o /tmp/kc_login2.html
  local t2; t2=$(token /tmp/kc_login2.html)
  local code; code=$(curl -s -o /dev/null -w '%{http_code}' -b "$2" -c "$2" -X POST "$B/login/verify" \
    --data-urlencode "_token=$t2" --data-urlencode "phone=$1" --data-urlencode "code=$otp")
  echo "→ ورود $1 : $code"
}

submit() { # $1 jar, $2 nid, $3 sheba, $4 label
  curl -s -b "$1" -c "$1" "$B/userPanel/verify" -o "/tmp/kc_form_$4.html"
  local t; t=$(token "/tmp/kc_form_$4.html")
  [ -n "$t" ] || { echo "! CSRF فرم احراز هویت پیدا نشد"; return 1; }
  local out; out=$(curl -s -b "$1" -c "$1" -o "/tmp/kc_after_$4.html" -w '%{http_code} %{url_effective}' -L "$B/userPanel/verify" \
    -F "_token=$t" \
    -F "first_name=<$ROOTW/storage/logs/kyc_first.val" \
    -F "last_name=<$ROOTW/storage/logs/kyc_last.val" \
    -F "national_code=$2" \
    -F "birth_date=1370-05-14" -F "sheba=$3" \
    -F "selfie=@$ROOTW/storage/logs/kyc_selfie.png" \
    -F "id_card=@$ROOTW/storage/logs/kyc_card.png")
  echo "→ ارسال مدارک ($4): $out"
  grep -o "این کد ملی قبلاً[^<]*\|کد ملی وارد‌شده معتبر نیست\|کد ملی معتبر نیست" "/tmp/kc_after_$4.html" | head -2
}

# ---------- ۱) کاربران آزمایشی ----------
eval "$($PHPBIN "$ROOT/storage/kyc_prep.php" 2>/dev/null | grep -E '^(NID|SHEBA)=')"
NID_A=$NID
SHEBA_A=${SHEBA_FIX:-$SHEBA}      # SHEBA_FIX=<شبا> برای تست یک شبای مشخص
eval "$($PHPBIN "$ROOT/storage/kyc_prep.php" 2>/dev/null | grep -E '^(NID|SHEBA)=')"
NID_B=$NID; SHEBA_B=$SHEBA
echo "کاربران آزمایشی: $UA (id $(mkuser "$UA" "آزمون الف" "$NID_A")) / $UB (id $(mkuser "$UB" "آزمون ب" "$NID_B"))"

# ---------- ۲) ثبت درخواست توسط کاربر ----------
login "$UA" "$J1"
login "$UB" "$J2"
echo "-- قبل از احراز هویت، /add برای کاربر A:"
curl -s -b "$J1" -o /dev/null -w "   /add -> %{http_code} %{redirect_url}\n" "$B/add"

submit "$J1" "$NID_A" "$SHEBA_A" A
submit "$J2" "$NID_B" "$SHEBA_B" B

KID_A=$(tinker_id "echo optional(\\App\\Models\\KycVerification::where('national_id', '$NID_A')->latest('id')->first())->id;")
KID_B=$(tinker_id "echo optional(\\App\\Models\\KycVerification::where('national_id', '$NID_B')->latest('id')->first())->id;")
echo "-- رکوردهای ثبت‌شده: A=$KID_A B=$KID_B"
tinker "foreach (\\App\\Models\\KycVerification::whereIn('id', ['$KID_A', '$KID_B'])->get() as \$k) { echo \$k->id.' | '.\$k->status.' | sheba='.(\$k->sheba ?: '-').' | bank='.(\$k->bank_name ?: '-').' | card='.(\$k->national_card_path ?: '-').' | وضعیت کاربر='.\$k->user->kyc_status.PHP_EOL; }"

# ---------- ۳) پنل ادمین: مشاهده درخواست‌ها ----------
login "$ADMIN" "$JA"
curl -s -b "$JA" -c "$JA" "$B/adminPanel/verifications" -o /tmp/kc_admin.html -w "-- صفحه احراز هویت ادمین: %{http_code} (حجم %{size_download})\n"
echo "-- رکورد A در جدول ادمین: $(grep -c "$NID_A" /tmp/kc_admin.html) بار | رکورد B: $(grep -c "$NID_B" /tmp/kc_admin.html) بار"
echo "-- لینک مدرک سلفی A: $(grep -o "/adminPanel/verifications/$KID_A/selfie" /tmp/kc_admin.html | head -1)"
echo "-- لینک مدرک کارت ملی A: $(grep -o "/adminPanel/verifications/$KID_A/card" /tmp/kc_admin.html | head -1)"
echo "-- شبا در ردیف A: $(grep -c "$SHEBA_A" /tmp/kc_admin.html) بار"

# صفحه جزئیات کاربر در پنل مدیریت (باید داده واقعی و مدارک همان کاربر را نشان دهد)
UID_A=$(tinker_id "echo optional(\\App\\Models\\User::where('mobile', '$UA')->first())->id;")
curl -s -b "$JA" "$B/adminPanel/users/$UID_A" -o /tmp/kc_details.html -w "-- صفحه جزئیات کاربر $UID_A: %{http_code}\n"
NID_A_FA=$($PHPBIN -r 'echo strtr($argv[1], ["0"=>"۰","1"=>"۱","2"=>"۲","3"=>"۳","4"=>"۴","5"=>"۵","6"=>"۶","7"=>"۷","8"=>"۸","9"=>"۹"]);' "$NID_A")
echo "   کد ملی واقعی (ارقام فارسی): $(grep -c "$NID_A_FA" /tmp/kc_details.html) بار | لینک مدرک سلفی رکورد A: $(grep -c "/adminPanel/verifications/$KID_A/selfie" /tmp/kc_details.html) بار | نام کاربر: $(grep -c 'آزمون' /tmp/kc_details.html) بار"

echo "-- دسترسی به مدارک:"
curl -s -b "$JA" -o /tmp/kc_doc.png -w "   کارشناس: %{http_code} %{content_type} %{size_download}B\n" "$B/adminPanel/verifications/$KID_A/selfie"
curl -s -o /dev/null -w "   مهمان: %{http_code} %{redirect_url}\n" "$B/adminPanel/verifications/$KID_A/selfie"
curl -s -b "$J1" -o /dev/null -w "   خود کاربر (نه کارشناس): %{http_code} %{redirect_url}\n" "$B/adminPanel/verifications/$KID_A/selfie"

# ---------- ۴) تایید A و رد B ----------
TA=$(token /tmp/kc_admin.html)
curl -s -b "$JA" -c "$JA" -o /dev/null -w "-- تایید رکورد A: %{http_code}\n" -X POST "$B/adminPanel/verifications/$KID_A/approve" --data-urlencode "_token=$TA"
REASON=$(cat "$ROOT/storage/logs/kyc_reason.val")
curl -s -b "$JA" -c "$JA" -o /dev/null -w "-- رد رکورد B: %{http_code}\n" -X POST "$B/adminPanel/verifications/$KID_B/reject" \
  --data-urlencode "_token=$TA" --data-urlencode "reason@$ROOTW/storage/logs/kyc_reason.val"

tinker "foreach (['$KID_A' => 'A', '$KID_B' => 'B'] as \$id => \$l) { \$k = \\App\\Models\\KycVerification::find(\$id); echo \$l.': '.\$k->status.' | دلیل='.(\$k->reject_reason ?: '-').' | بررسی‌کننده='.(\$k->reviewed_by ?: '-').' | وضعیت کاربر='.\$k->user->kyc_status.PHP_EOL; }"

# ---------- ۵) وضعیت سمت کاربر ----------
curl -s -b "$J1" "$B/userPanel/verify" -o /tmp/kc_user1.html
curl -s -b "$J2" "$B/userPanel/verify" -o /tmp/kc_user2.html
echo "-- کاربر A (تاییدشده): «هویت شما تایید شده است» → $(grep -c 'هویت شما تایید شده است' /tmp/kc_user1.html) بار"
echo "-- کاربر B (ردشده): «احراز هویت رد شد» → $(grep -c 'احراز هویت رد شد' /tmp/kc_user2.html) بار | نمایش دلیل → $(grep -c -F "$REASON" /tmp/kc_user2.html) بار"
echo "-- /add برای کاربر A: $(curl -s -b "$J1" -o /dev/null -w '%{http_code}' "$B/add")"
echo "-- /add برای کاربر B: $(curl -s -b "$J2" -o /dev/null -w '%{http_code} %{redirect_url}' "$B/add")"

# ---------- ۶) نمایش مجدد در پنل ادمین ----------
curl -s -b "$JA" "$B/adminPanel/verifications" -o /tmp/kc_admin2.html
echo "-- دلیل رد رکورد B در صفحه ادمین: $(grep -c -F "$REASON" /tmp/kc_admin2.html) بار | تعداد «رد شده»: $(grep -c 'pn-pill rejected' /tmp/kc_admin2.html) | «تایید شده»: $(grep -c 'pn-pill approved' /tmp/kc_admin2.html)"
echo "COOKIES: $J1 $J2 $JA"
