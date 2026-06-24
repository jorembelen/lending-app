<?php

namespace App\Http\Controllers\Collector;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Services\PaymentRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Sync endpoint for the collector PWA's offline payment queue.
 *
 * Every payment — whether captured online or while offline — is written to the
 * device's pending_payments store first with a client-generated idempotency
 * key, then POSTed here. Because the key is stable across retries, the queue
 * can flush repeatedly (on app open, focus, interval, or after a mid-sync
 * crash) without ever creating a duplicate.
 */
class PaymentApiController extends Controller
{
    public function store(Request $request, PaymentRecorder $recorder): JsonResponse
    {
        $data = $request->validate([
            'idempotency_key' => ['required', 'string', 'max:64'],
            'loan_id'         => ['required', 'integer'],
            'amount'          => ['required', 'numeric', 'min:0.01'],
            'collected_at'    => ['nullable', 'date'],
            'latitude'        => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'       => ['nullable', 'numeric', 'between:-180,180'],
            'device_identifier' => ['nullable', 'string', 'max:255'],
        ]);

        $loan = Loan::find($data['loan_id']);

        // Real, non-connectivity failures the collector must see as
        // "needs attention" rather than "still waiting for signal".
        if (! $loan) {
            return response()->json([
                'status'  => 'rejected',
                'reason'  => 'loan_not_found',
                'message' => 'This loan no longer exists. Contact the office before re-collecting.',
            ], 422);
        }

        if (! in_array($loan->status, ['active', 'overdue'], true)) {
            return response()->json([
                'status'  => 'rejected',
                'reason'  => 'loan_closed',
                'message' => 'This loan is already closed and cannot accept payments.',
            ], 409);
        }

        $result = $recorder->record(
            loan: $loan,
            amount: (float) $data['amount'],
            idempotencyKey: $data['idempotency_key'],
            collectorUserId: auth()->id(),
            meta: [
                'collected_at'      => $data['collected_at'] ?? null,
                'latitude'          => $data['latitude'] ?? null,
                'longitude'         => $data['longitude'] ?? null,
                'device_identifier' => $data['device_identifier'] ?? null,
            ],
        );

        $payment = $result['payment'];

        return response()->json([
            'status'            => $result['duplicate'] ? 'duplicate' : 'recorded',
            'payment_id'        => $payment->id,
            'idempotency_key'   => $payment->idempotency_key,
            'amount'            => (float) $payment->amount,
            'collected_at'      => optional($payment->collected_at)->toIso8601String(),
            'loan_id'           => $loan->id,
            'remaining_balance' => (float) $loan->fresh()->remaining_balance,
        ], $result['duplicate'] ? 200 : 201);
    }
}
