<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | درخواست خریدار برای یک آگهی؛ پس از تایید فروشنده، به یک loan_request (معامله هفت‌مرحله‌ای) تبدیل می‌شود.
 | status: pending | accepted | rejected | expired
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();  // خریدار
            $table->unsignedBigInteger('amount');
            $table->text('note')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->timestamps();

            $table->index(['ad_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_offers');
    }
};
