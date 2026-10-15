<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | status: pending (منتظر تایید) | published (منتشر شده) | transfer (در حال معامله/انتقال)
 |         completed (تکمیل شده) | rejected (رد شده)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();                 // شناسه وام نمایشی
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();  // فروشنده
            $table->foreignId('bank_id')->constrained();
            $table->string('type', 20)->default('qarz');          // qarz|marriage|child|housing|mehrbani|commercial|medical
            $table->unsignedBigInteger('amount');
            $table->unsignedSmallInteger('months')->default(24);
            $table->decimal('rate', 5, 2)->default(0);
            $table->unsignedBigInteger('price');                  // قیمت پیشنهادی فروشنده
            $table->unsignedBigInteger('max_price')->nullable();   // سقف پیشنهاد خریدار
            $table->unsignedBigInteger('installment')->nullable();
            $table->unsignedBigInteger('total_repayment')->nullable();
            $table->text('guarantor')->nullable();
            $table->string('city')->nullable();
            $table->text('description')->nullable();
            $table->string('sheba', 26)->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->text('reject_reason')->nullable();
            $table->boolean('featured')->default(false);
            $table->boolean('urgent')->default(false);
            $table->unsignedInteger('views')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'bank_id']);
            $table->index(['user_id', 'status']);
            $table->index('amount');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
