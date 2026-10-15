<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 30);                    // balance | national_card | selfie | other
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->unsignedInteger('size')->default(0);
            $table->boolean('must_replace')->default(false); // بعد از رد آگهی، مدرک باید جایگزین شود
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_documents');
    }
};
