<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | معامله (درخواست خرید تایید‌شده) با جریان هفت‌مرحله‌ای:
 | 1 ثبت درخواست | 2 تایید درخواست | 3 پرداخت | 4 تایید پرداخت
 | 5 انتقال امتیاز | 6 تایید انتقال | 7 تکمیل
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->foreignId('ad_id')->constrained();
            $table->foreignId('offer_id')->nullable()->constrained('ad_offers')->nullOnDelete();
            $table->foreignId('buyer_id')->constrained('users');
            $table->foreignId('seller_id')->constrained('users');
            $table->unsignedBigInteger('amount');      // مبلغ توافق‌شده
            $table->unsignedBigInteger('price');       // مبلغ وام
            $table->unsignedTinyInteger('step')->default(1);
            $table->string('status', 20)->default('active'); // active | completed | rejected | cancelled | expired
            $table->text('reject_reason')->nullable();
            $table->timestamp('pay_due_at')->nullable();          // مهلت ۷ روزه پرداخت
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('transfer_due_at')->nullable();     // مهلت ۷ روزه انتقال امتیاز
            $table->timestamp('transfer_confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['buyer_id', 'status']);
            $table->index(['seller_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_requests');
    }
};
