<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | بازه نرخ سود رادار: علاوه بر max_rate، حداقل نرخ سود هم ذخیره می‌شود
 | تا جستجوی «از X تا Y درصد» ممکن شود.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_radars', function (Blueprint $table) {
            $table->decimal('min_rate', 5, 2)->nullable()->after('max_rate');
        });
    }

    public function down(): void
    {
        Schema::table('loan_radars', function (Blueprint $table) {
            $table->dropColumn('min_rate');
        });
    }
};
