<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rebate_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loyalty_tier_id')
                ->nullable()
                ->constrained('loyalty_tiers')
                ->nullOnDelete();
            $table->decimal('percent_of_interest', 5, 2);
            $table->unsignedInteger('max_missed_days_to_qualify');
            $table->enum('default_application', ['credit_next_loan', 'cash'])->default('credit_next_loan');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rebate_rules');
    }
};
