<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrower_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrower_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('email')->unique();
            $table->string('pin_hash');
            $table->unsignedInteger('failed_attempts')->default(0);
            $table->datetime('locked_at')->nullable();
            $table->datetime('email_verified_at')->nullable();
            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrower_accounts');
    }
};
