<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('rank');
            $table->unsignedInteger('max_missed_days_to_qualify');
            $table->decimal('loan_ceiling_multiplier', 4, 2)->nullable();
            $table->decimal('rate_discount_per_1000', 10, 2)->nullable()->default(0);
            $table->boolean('priority_reloan')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_tiers');
    }
};
