<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rebate_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->restrictOnDelete();
            $table->foreignId('borrower_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rebate_rule_id')
                ->constrained('rebate_rules')
                ->restrictOnDelete();
            $table->decimal('interest_amount', 10, 2);
            $table->decimal('rebate_amount', 10, 2);
            $table->enum('status', ['pending_approval', 'approved', 'rejected', 'applied'])->default('pending_approval');
            $table->foreignId('approved_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->datetime('approved_at')->nullable();
            $table->foreignId('applied_to_loan_id')
                ->nullable()
                ->constrained('loans')
                ->nullOnDelete();
            $table->datetime('applied_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rebate_grants');
    }
};
