<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrowers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('phone_number');
            $table->text('address')->nullable();
            $table->string('borrower_code')->unique()->nullable();
            $table->string('qr_reference')->unique();
            $table->string('photo_path')->nullable();
            $table->foreignId('current_tier_id')
                ->nullable()
                ->constrained('loyalty_tiers')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrowers');
    }
};
