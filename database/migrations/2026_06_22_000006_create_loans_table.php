<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrower_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rate_preset_id')->constrained()->restrictOnDelete();
            $table->decimal('principal', 12, 2);
            $table->decimal('rate_per_1000_locked', 10, 2);
            $table->unsignedInteger('term_days_locked');
            $table->decimal('daily_installment', 10, 2);
            $table->decimal('total_payable', 12, 2);
            $table->date('disbursed_at');
            $table->foreignId('disbursed_by_user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->enum('status', ['active', 'completed', 'defaulted', 'voided'])->default('active')->index();
            $table->date('closed_at')->nullable();
            $table->unsignedInteger('missed_days_at_closure')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
