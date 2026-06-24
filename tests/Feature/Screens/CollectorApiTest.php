<?php

namespace Tests\Feature\Screens;

use App\Models\Payment;
use App\Models\ScheduleItem;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

/**
 * Covers the collector PWA's offline-sync surface: the route JSON used to
 * seed the device cache, and the idempotent payments endpoint the queue
 * flushes to.
 */
class CollectorApiTest extends ScreenTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Assign the seeded loan to the test collector and give it a stop due today.
        $this->loan->update(['assigned_collector_id' => $this->collector->id]);

        ScheduleItem::create([
            'loan_id'         => $this->loan->id,
            'sequence_number' => 99,
            'due_date'        => today()->toDateString(),
            'amount_due'      => $this->loan->daily_installment,
            'amount_paid'     => 0,
            'status'          => 'pending',
        ]);
    }

    // ── Auth guards ──────────────────────────────────────────────────────────

    #[Test]
    public function route_api_requires_authentication(): void
    {
        $this->getJson(route('collector.api.route'))->assertUnauthorized();
    }

    #[Test]
    public function non_collector_cannot_call_route_api(): void
    {
        $this->actingAs($this->admin)
            ->getJson(route('collector.api.route'))
            ->assertForbidden();
    }

    #[Test]
    public function payments_api_requires_collector_role(): void
    {
        $this->actingAs($this->borrowerUser)
            ->postJson(route('collector.api.payments'), [
                'idempotency_key' => Str::uuid()->toString(),
                'loan_id'         => $this->loan->id,
                'amount'          => 100,
            ])
            ->assertForbidden();
    }

    // ── Route JSON ───────────────────────────────────────────────────────────

    #[Test]
    public function route_api_returns_todays_route_for_assigned_collector(): void
    {
        $this->actingAs($this->collector)
            ->getJson(route('collector.api.route'))
            ->assertOk()
            ->assertJsonStructure([
                'date',
                'fetched_at',
                'borrowers' => [
                    ['borrower_id', 'qr_reference', 'full_name', 'loan_id', 'amount_due', 'remaining_balance', 'schedule'],
                ],
            ])
            ->assertJsonPath('borrowers.0.borrower_id', $this->borrower->id);
    }

    #[Test]
    public function route_api_excludes_loans_assigned_to_other_collectors(): void
    {
        $this->loan->update(['assigned_collector_id' => $this->admin->id]);

        $this->actingAs($this->collector)
            ->getJson(route('collector.api.route'))
            ->assertOk()
            ->assertJsonPath('borrowers', []);
    }

    // ── Payments: happy path ────────────────────────────────────────────────

    #[Test]
    public function payment_is_recorded_and_allocated_to_schedule(): void
    {
        $key  = Str::uuid()->toString();
        $item = ScheduleItem::where('loan_id', $this->loan->id)->where('sequence_number', 99)->first();

        $this->actingAs($this->collector)
            ->postJson(route('collector.api.payments'), [
                'idempotency_key' => $key,
                'loan_id'         => $this->loan->id,
                'amount'          => (float) $this->loan->daily_installment,
            ])
            ->assertCreated()
            ->assertJsonPath('status', 'recorded')
            ->assertJsonPath('idempotency_key', $key);

        $this->assertDatabaseHas('payments', [
            'idempotency_key'   => $key,
            'loan_id'           => $this->loan->id,
            'collector_user_id' => $this->collector->id,
        ]);

        $this->assertEquals('paid', $item->fresh()->status);
    }

    // ── Payments: idempotency ───────────────────────────────────────────────

    #[Test]
    public function reposting_same_idempotency_key_does_not_duplicate(): void
    {
        $key     = Str::uuid()->toString();
        $payload = [
            'idempotency_key' => $key,
            'loan_id'         => $this->loan->id,
            'amount'          => 100,
        ];

        $first = $this->actingAs($this->collector)
            ->postJson(route('collector.api.payments'), $payload)
            ->assertCreated()
            ->assertJsonPath('status', 'recorded');

        $second = $this->actingAs($this->collector)
            ->postJson(route('collector.api.payments'), $payload)
            ->assertOk()
            ->assertJsonPath('status', 'duplicate');

        $this->assertEquals(
            $first->json('payment_id'),
            $second->json('payment_id'),
            'Duplicate submission must resolve to the original payment.'
        );

        $this->assertEquals(1, Payment::where('idempotency_key', $key)->count());
    }

    // ── Payments: real rejections ───────────────────────────────────────────

    #[Test]
    public function payment_for_missing_loan_is_rejected(): void
    {
        $this->actingAs($this->collector)
            ->postJson(route('collector.api.payments'), [
                'idempotency_key' => Str::uuid()->toString(),
                'loan_id'         => 999999,
                'amount'          => 100,
            ])
            ->assertStatus(422)
            ->assertJsonPath('reason', 'loan_not_found');
    }

    #[Test]
    public function payment_for_closed_loan_is_rejected(): void
    {
        $this->loan->update(['status' => 'completed']);

        $this->actingAs($this->collector)
            ->postJson(route('collector.api.payments'), [
                'idempotency_key' => Str::uuid()->toString(),
                'loan_id'         => $this->loan->id,
                'amount'          => 100,
            ])
            ->assertStatus(409)
            ->assertJsonPath('reason', 'loan_closed');
    }

    #[Test]
    public function payment_validation_rejects_non_positive_amount(): void
    {
        $this->actingAs($this->collector)
            ->postJson(route('collector.api.payments'), [
                'idempotency_key' => Str::uuid()->toString(),
                'loan_id'         => $this->loan->id,
                'amount'          => 0,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('amount');
    }
}
