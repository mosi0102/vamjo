#!/usr/bin/env bash
# ورود با OTP و بررسی صفحات نیازمند احراز هویت (فقط برای تست محلی)
set -u
B=${B:-http://127.0.0.1:8123}
MOBILE=${1:-09301021000}
J=${2:-storage/logs/cookies_$$.txt}
PHPBIN="php -d extension=mbstring -d extension=fileinfo"

rm -f "$J"
token() { sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' "$1" | head -1; }

curl -s -c "$J" "$B/login" -o /tmp/login.html
T=$(token /tmp/login.html)
[ -n "$T" ] || { echo "NO_CSRF on /login"; exit 1; }

code=$(curl -s -o /tmp/login_flash.html -w "%{http_code}" -b "$J" -c "$J" -L -X POST "$B/login" \
  --data-urlencode "_token=$T" --data-urlencode "phone=$MOBILE")
echo "POST /login -> $code"

OTP=$(grep -o 'کد تست: [0-9]\{5\}' /tmp/login_flash.html | grep -o '[0-9]\{5\}' | tail -1)
[ -n "$OTP" ] || OTP=$($PHPBIN artisan tinker --execute="echo optional(\App\Models\OtpCode::latestFor('$MOBILE'))->code;" 2>/dev/null | grep -E '^[0-9]{5}$' | tail -1)
[ -n "$OTP" ] || { echo "NO_OTP"; exit 1; }
echo "OTP=$OTP"

curl -s -b "$J" "$B/login" -o /tmp/login2.html
T2=$(token /tmp/login2.html)
verify=$(curl -s -o /dev/null -w "%{http_code}" -b "$J" -c "$J" -X POST "$B/login/verify" \
  --data-urlencode "_token=$T2" --data-urlencode "phone=$MOBILE" --data-urlencode "code=$OTP")
echo "POST /login/verify -> $verify"

for u in /userPanel /userPanel/ads /userPanel/radar /userPanel/tickets /userPanel/verify /add /userPanel/ads/13/edit /userPanel/requests /userPanel/payments /userPanel/favorites; do
  printf "%-28s " "$u"
  curl -s -o /tmp/page_$$.html -w "%{http_code} %{size_download}\n" -b "$J" "$B$u"
done
echo "COOKIE_JAR=$J"
