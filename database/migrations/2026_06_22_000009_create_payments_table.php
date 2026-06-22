<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->restrictOnDelete();
            $table->foreignId('collector_user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            $table->datetime('collected_at')->index();
            $table->datetime('recorded_at');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('device_identifier')->nullable();
            $table->string('idempotency_key')->unique();
            $table->boolean('is_voided')->default(false);
            $table->foreignId('voided_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('voided_reason')->nullable();
            $table->datetime('voided_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
