<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | نام کامل بانک در فرم‌ها (مثل «بانک رسالت») و نام کوتاه در کارت‌ها/نوار بانک‌ها (مثل «رسالت»).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->string('short_name')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->dropColumn('short_name');
        });
    }
};
