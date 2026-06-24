<?php

namespace App\Http\Controllers\Collector;

use App\Http\Controllers\Controller;
use App\Models\ScheduleItem;
use Illuminate\Http\JsonResponse;

/**
 * Serves the authenticated collector's "Today's Collection Route" as JSON so
 * the PWA can cache it in IndexedDB once (online) and read from the cache for
 * the rest of the day regardless of connectivity. Mirrors the dataset that
 * TodayRouteComponent renders server-side, plus the per-borrower detail the
 * offline scan/record-payment screens need.
 */
class RouteApiController extends Controller
{
    public function index(): JsonResponse
    {
        $collectorId = auth()->id();

        $items = ScheduleItem::with(['loan.borrower'])
            ->whereHas('loan', fn ($q) => $q
                ->where('status', 'active')
                ->where('assigned_collector_id', $collectorId))
            ->where(function ($q) {
                $q->whereDate('due_date', today())
                  ->orWhere(fn ($q2) => $q2
                      ->whereDate('due_date', '<', today())
                      ->whereIn('status', ['pending', 'partially_paid', 'missed']));
            })
            ->orderByRaw("FIELD(status, 'missed', 'partially_paid', 'pending', 'paid')")
            ->orderBy('due_date')
            ->get();

        // Group route stops by borrower so the cached record carries everything
        // the offline detail/scan/payment screens resolve against locally.
        $borrowers = $items
            ->filter(fn ($item) => $item->loan && $item->loan->borrower)
            ->groupBy(fn ($item) => $item->loan->borrower_id)
            ->map(function ($group) {
                /** @var \App\Models\ScheduleItem $first */
                $first    = $group->first();
                $loan     = $first->loan;
                $borrower = $loan->borrower;

                $dueToday = $group
                    ->filter(fn ($i) => optional($i->due_date)->isToday() || in_array($i->status, ['pending', 'partially_paid', 'missed'], true))
                    ->sum(fn ($i) => max(0, (float) $i->amount_due - (float) $i->amount_paid));

                return [
                    'borrower_id'    => $borrower->id,
                    'borrower_code'  => $borrower->borrower_code,
                    'qr_reference'   => $borrower->qr_reference,
                    'full_name'      => $borrower->full_name,
                    'address'        => $borrower->address,
                    'phone_number'   => $borrower->phone_number,
                    'photo_path'     => $borrower->photo_path,
                    'loan_id'        => $loan->id,
                    'status'         => $loan->status,
                    'daily_installment' => (float) $loan->daily_installment,
                    'remaining_balance' => (float) $loan->remaining_balance,
                    'amount_due'     => round($dueToday, 2),
                    'schedule'       => $group->map(fn ($i) => [
                        'id'              => $i->id,
                        'sequence_number' => $i->sequence_number,
                        'due_date'        => optional($i->due_date)->toDateString(),
                        'amount_due'      => (float) $i->amount_due,
                        'amount_paid'     => (float) $i->amount_paid,
                        'status'          => $i->status,
                    ])->values(),
                ];
            })
            ->values();

        return response()->json([
            'date'      => today()->toDateString(),
            'fetched_at' => now()->toIso8601String(),
            'borrowers' => $borrowers,
        ]);
    }
}
