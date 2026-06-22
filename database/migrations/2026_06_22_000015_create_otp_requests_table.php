<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrower_account_id')
                ->constrained('borrower_accounts')
                ->cascadeOnDelete();
            $table->enum('purpose', ['account_activation', 'pin_reset']);
            $table->string('code_hash');
            $table->datetime('expires_at');
            $table->datetime('consumed_at')->nullable();
            $table->string('requested_ip')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_requests');
    }
};
