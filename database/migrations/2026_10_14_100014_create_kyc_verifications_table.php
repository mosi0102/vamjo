<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | درخواست‌های احراز هویت؛ در هر ارسال یک ردیف جدید ثبت می‌شود و آخرین ردیف ملاک وضعیت کاربر است.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('national_id', 10);
            $table->string('birth_date', 10)->nullable();
            $table->string('sheba', 26)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('national_card_path')->nullable();
            $table->string('selfie_path')->nullable();
            $table->string('status', 20)->default('pending')->index(); // pending | approved | rejected
            $table->text('reject_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_verifications');
    }
};
