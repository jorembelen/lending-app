<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_turnovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collector_user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->date('turnover_date')->index();
            $table->decimal('system_total', 12, 2);
            $table->decimal('cash_remitted', 12, 2);
            $table->decimal('variance', 12, 2);
            $table->foreignId('reconciled_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->datetime('reconciled_at')->nullable();
            $table->text('note')->nullable();
            $table->unique(['collector_user_id', 'turnover_date']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_turnovers');
    }
};
