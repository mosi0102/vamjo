<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | ورود با شماره موبایل و کد یکبار مصرف است؛ پس ایمیل و رمز عبور اختیاری می‌شوند.
 | نقش‌ها: user | admin | support  (مطابق config/panel.php)
 | وضعیت احراز هویت: unverified | pending | verified | rejected
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();

            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('mobile', 15)->nullable()->after('last_name');
            $table->string('national_id', 10)->nullable()->after('mobile');
            $table->string('birth_date', 10)->nullable()->after('national_id');
            $table->string('sheba', 26)->nullable()->after('birth_date');
            $table->string('bank_name')->nullable()->after('sheba');
            $table->string('city')->nullable()->after('bank_name');
            $table->string('avatar')->nullable()->after('city');
            $table->string('role', 20)->default('user')->after('avatar');
            $table->string('kyc_status', 20)->default('unverified')->after('role');
            $table->timestamp('blocked_at')->nullable()->after('kyc_status');
            $table->string('block_reason')->nullable()->after('blocked_at');
            $table->timestamp('last_login_at')->nullable()->after('block_reason');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('mobile');
            $table->unique('national_id');
            $table->index('role');
            $table->index('kyc_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['mobile']);
            $table->dropUnique(['national_id']);
            $table->dropIndex(['role']);
            $table->dropIndex(['kyc_status']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name', 'last_name', 'mobile', 'national_id', 'birth_date', 'sheba',
                'bank_name', 'city', 'avatar', 'role', 'kyc_status', 'blocked_at',
                'block_reason', 'last_login_at', 'last_login_ip',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
};
