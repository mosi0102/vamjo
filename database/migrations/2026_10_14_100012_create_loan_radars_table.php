<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | رادار وام: شرط‌های مورد نظر کاربر؛ با انتشار آگهی منطبق، ردیف در radar_matches ساخته می‌شود.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_radars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->foreignId('bank_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20)->nullable();
            $table->string('city')->nullable();
            $table->unsignedBigInteger('min_amount')->nullable();
            $table->unsignedBigInteger('max_amount')->nullable();
            $table->unsignedSmallInteger('months')->nullable();
            $table->decimal('max_rate', 5, 2)->nullable();
            $table->boolean('notify_sms')->default(true);
            $table->boolean('notify_panel')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_radars');
    }
};
