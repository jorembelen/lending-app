<?php

namespace Tests\Feature\Screens;

use App\Models\Payment;
use Illuminate\Support\Str;

class CollectorScreensTest extends ScreenTestCase
{
    public function test_collector_today_route_renders(): void
    {
        $this->actingAs($this->collector)
            ->get(route('collector.route'))
            ->assertOk();
    }

    public function test_collector_borrower_detail_renders(): void
    {
        $this->actingAs($this->collector)
            ->get(route('collector.borrower', $this->borrower->id))
            ->assertOk();
    }

    public function test_collector_qr_scanner_renders(): void
    {
        $this->actingAs($this->collector)
            ->get(route('collector.scan'))
            ->assertOk();
    }

    public function test_collector_record_payment_renders(): void
    {
        $this->actingAs($this->collector)
            ->get(route('collector.payment', $this->borrower->id))
            ->assertOk();
    }

    public function test_collector_payment_confirmation_renders(): void
    {
        $payment = $this->loan->payments()->first();

        $this->actingAs($this->collector)
            ->get(route('collector.payment.confirmed', $payment->id))
            ->assertOk();
    }

    public function test_collector_end_of_day_summary_renders(): void
    {
        $this->actingAs($this->collector)
            ->get(route('collector.summary'))
            ->assertOk();
    }

    public function test_collector_routes_redirect_unauthenticated_users(): void
    {
        $this->get(route('collector.route'))->assertRedirect(route('login'));
        $this->get(route('collector.summary'))->assertRedirect(route('login'));
    }

    public function test_admin_cannot_access_collector_routes(): void
    {
        $this->actingAs($this->admin)
            ->get(route('collector.route'))
            ->assertForbidden();
    }

    public function test_collector_summary_with_voided_payment_renders(): void
    {
        $payment = $this->loan->payments()->first();
        $payment->update([
            'is_voided'         => true,
            'voided_by_user_id' => $this->admin->id,
            'voided_reason'     => 'Test void',
            'voided_at'         => now(),
        ]);

        $this->actingAs($this->collector)
            ->get(route('collector.summary'))
            ->assertOk();
    }
}
