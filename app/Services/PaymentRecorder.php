<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\Payment;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for recording a collector payment.
 *
 * Both the online Livewire path and the offline-queue sync API funnel through
 * here so there is exactly one place that creates a Payment and allocates it
 * across the loan's schedule. The client-generated idempotency key makes the
 * operation safe to retry: a key that already exists returns the original
 * payment untouched (no duplicate row, no double allocation), which is what
 * lets the offline queue flush repeatedly without corrupting balances.
 */
class PaymentRecorder
{
    /**
     * Record a payment idempotently.
     *
     * @param  array{collected_at?:mixed, latitude?:mixed, longitude?:mixed, device_identifier?:mixed}  $meta
     * @return array{payment: Payment, duplicate: bool}
     */
    public function record(Loan $loan, float $amount, string $idempotencyKey, int $collectorUserId, array $meta = []): array
    {
        // Fast path: the key was already accepted on a previous attempt.
        $existing = Payment::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return ['payment' => $existing, 'duplicate' => true];
        }

        try {
            $payment = DB::transaction(function () use ($loan, $amount, $idempotencyKey, $collectorUserId, $meta) {
                $payment = Payment::create([
                    'loan_id'           => $loan->id,
                    'collector_user_id' => $collectorUserId,
                    'amount'            => $amount,
                    'collected_at'      => $meta['collected_at'] ?? now(),
                    'recorded_at'       => now(),
                    'latitude'          => $meta['latitude'] ?? null,
                    'longitude'         => $meta['longitude'] ?? null,
                    'device_identifier' => $meta['device_identifier'] ?? null,
                    'idempotency_key'   => $idempotencyKey,
                    'is_voided'         => false,
                ]);

                $this->allocateToSchedule($loan, $amount);

                return $payment;
            });

            return ['payment' => $payment, 'duplicate' => false];
        } catch (UniqueConstraintViolationException $e) {
            // A concurrent flush of the same queued payment beat us to it.
            // The unique idempotency_key guarantees only one row was created;
            // return that row and treat this attempt as a (silent) duplicate.
            $payment = Payment::where('idempotency_key', $idempotencyKey)->firstOrFail();

            return ['payment' => $payment, 'duplicate' => true];
        }
    }

    /**
     * Apply a payment across the loan's unpaid schedule items in due order
     * (FIFO), updating each item's amount_paid and status so the route and
     * summaries always reflect real collections.
     */
    public function allocateToSchedule(Loan $loan, float $amount): void
    {
        $remaining = $amount;

        $items = $loan->scheduleItems()
            ->whereIn('status', ['pending', 'partially_paid', 'missed'])
            ->orderBy('due_date')
            ->orderBy('sequence_number')
            ->get();

        foreach ($items as $item) {
            if ($remaining <= 0) {
                break;
            }

            $owed = (float) $item->amount_due - (float) $item->amount_paid;
            if ($owed <= 0) {
                continue;
            }

            $applied = min($remaining, $owed);
            $newPaid = (float) $item->amount_paid + $applied;

            $item->update([
                'amount_paid' => $newPaid,
                'status'      => $newPaid >= (float) $item->amount_due ? 'paid' : 'partially_paid',
            ]);

            $remaining -= $applied;
        }
    }
}
