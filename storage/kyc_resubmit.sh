#!/usr/bin/env bash
# ارسال مجدد درخواست احراز هویت برای تست جریان «در انتظار بررسی» (فقط محلی)
#   bash storage/kyc_resubmit.sh [mobile]
set -u
B=${B:-http://127.0.0.1:8123}
PHPBIN="php -d extension=mbstring -d extension=fileinfo"
ROOT=$(cd "$(dirname "$0")/.." && pwd)
ROOTW=$(cd "$ROOT" && pwd -W 2>/dev/null || echo "$ROOT")
MOBILE=${1:-09120009002}
J=/tmp/kc_resub.jar
token() { sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' "$1" | head -1; }

rm -f "$J"
curl -s -c "$J" "$B/login" -o /tmp/kc_r1.html
T=$(token /tmp/kc_r1.html)
curl -s -b "$J" -c "$J" -L "$B/login" --data-urlencode "_token=$T" --data-urlencode "phone=$MOBILE" -o /tmp/kc_r2.html
OTP=$($PHPBIN "$ROOT/artisan" tinker --execute="echo optional(\\App\\Models\\OtpCode::latestFor('$MOBILE'))->code;" 2>/dev/null | tr -d '\r' | grep -E '^[0-9]{5}$' | tail -1)
curl -s -b "$J" "$B/login" -o /tmp/kc_r3.html
T2=$(token /tmp/kc_r3.html)
curl -s -o /dev/null -b "$J" -c "$J" -X POST "$B/login/verify" \
  --data-urlencode "_token=$T2" --data-urlencode "phone=$MOBILE" --data-urlencode "code=$OTP"

eval "$($PHPBIN "$ROOT/storage/kyc_prep.php" 2>/dev/null | grep -E '^(NID|SHEBA)=')"
curl -s -b "$J" -c "$J" "$B/userPanel/verify" -o /tmp/kc_r4.html
T3=$(token /tmp/kc_r4.html)
[ -n "$T3" ] || { echo "! CSRF token پیدا نشد"; exit 1; }
curl -s -b "$J" -c "$J" -o /tmp/kc_r5.html -w "ارسال مجدد: %{http_code}\n" -L "$B/userPanel/verify" \
  -F "_token=$T3" \
  -F "first_name=<$ROOTW/storage/logs/kyc_first.val" \
  -F "last_name=<$ROOTW/storage/logs/kyc_last.val" \
  -F "national_code=$NID" -F "birth_date=1371-02-03" -F "sheba=$SHEBA" \
  -F "selfie=@$ROOTW/storage/logs/kyc_selfie.png" \
  -F "id_card=@$ROOTW/storage/logs/kyc_card.png"
echo "کد ملی جدید: $NID"
