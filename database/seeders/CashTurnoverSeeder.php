<?php

namespace Database\Seeders;

use App\Models\CashTurnover;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CashTurnoverSeeder extends Seeder
{
    public function run(): void
    {
        $collectors = User::role('collector')->get();
        $admin      = User::role('admin')->first();
        $count      = 0;

        foreach ($collectors as $collector) {
            // Find all distinct dates this collector has payments
            $dates = Payment::where('collector_user_id', $collector->id)
                ->where('is_voided', false)
                ->selectRaw('DATE(collected_at) as collection_date')
                ->groupBy('collection_date')
                ->pluck('collection_date');

            foreach ($dates as $date) {
                $systemTotal = (float) Payment::where('collector_user_id', $collector->id)
                    ->where('is_voided', false)
                    ->whereDate('collected_at', $date)
                    ->sum('amount');

                if ($systemTotal <= 0) {
                    continue;
                }

                // Introduce deliberate variance for some rows (roughly 1 in 4)
                $variance    = 0.0;
                $cashRemitted = $systemTotal;
                if (rand(1, 4) === 1) {
                    // Small over/under: –50 to +50 (never zero variance)
                    do {
                        $variance = rand(-50, 50);
                    } while ($variance === 0);
                    $cashRemitted = round($systemTotal + $variance, 2);
                }

                $isReconciled = (bool) rand(0, 1);
                CashTurnover::firstOrCreate(
                    ['collector_user_id' => $collector->id, 'turnover_date' => $date],
                    [
                        'system_total'         => round($systemTotal, 2),
                        'cash_remitted'        => $cashRemitted,
                        'variance'             => round($variance, 2),
                        'reconciled_by_user_id' => $isReconciled ? $admin?->id : null,
                        'reconciled_at'        => $isReconciled ? Carbon::parse($date)->addHours(rand(17, 21)) : null,
                        'note'                 => $variance != 0 ? 'Variance noted; pending explanation from collector.' : null,
                    ]
                );
                $count++;
            }
        }

        $this->command->info("CashTurnoverSeeder: {$count} turnover rows generated.");
    }
}
