<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radar_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('radar_id')->constrained('loan_radars')->cascadeOnDelete();
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_new')->default(true);
            $table->timestamp('seen_at')->nullable();
            $table->timestamps();

            $table->unique(['radar_id', 'ad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radar_matches');
    }
};
