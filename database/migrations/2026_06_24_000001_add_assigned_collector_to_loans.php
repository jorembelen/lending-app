<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->foreignId('assigned_collector_id')
                ->nullable()
                ->after('disbursed_by_user_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->index('assigned_collector_id');
        });

        // Backfill: assign each active loan to the collector who most recently
        // recorded a valid payment on it, so existing routes aren't empty.
        $activeLoanIds = DB::table('loans')->where('status', 'active')->pluck('id');

        foreach ($activeLoanIds as $loanId) {
            $collectorId = DB::table('payments')
                ->where('loan_id', $loanId)
                ->where('is_voided', false)
                ->orderByDesc('collected_at')
                ->value('collector_user_id');

            if ($collectorId) {
                DB::table('loans')->where('id', $loanId)
                    ->update(['assigned_collector_id' => $collectorId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['assigned_collector_id']);
            $table->dropColumn('assigned_collector_id');
        });
    }
};
