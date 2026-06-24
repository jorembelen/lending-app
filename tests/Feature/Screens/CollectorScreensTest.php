<?php

namespace Tests\Feature\Screens;

use PHPUnit\Framework\Attributes\Test;

class CollectorScreensTest extends ScreenTestCase
{
    // ── Auth guards ──────────────────────────────────────────────────────────

    #[Test]
    public function collector_routes_redirect_unauthenticated_users(): void
    {
        $routes = [
            route('collector.route'),
            route('collector.borrower', $this->borrower->id),
            route('collector.scan'),
            route('collector.payment', $this->borrower->id),
            route('collector.payment.confirmed', $this->payment->id),
            route('collector.summary'),
        ];

        foreach ($routes as $url) {
            $this->get($url)->assertRedirect();
        }
    }

    #[Test]
    public function admin_cannot_access_collector_routes(): void
    {
        $this->actingAs($this->admin)
            ->get(route('collector.route'))
            ->assertForbidden();
    }

    #[Test]
    public function borrower_user_cannot_access_collector_routes(): void
    {
        $this->actingAs($this->borrowerUser)
            ->get(route('collector.route'))
            ->assertForbidden();
    }

    // ── Screens ──────────────────────────────────────────────────────────────

    #[Test]
    public function collector_today_route_renders(): void
    {
        $this->actingAs($this->collector)
            ->get(route('collector.route'))
            ->assertOk();
    }

    #[Test]
    public function collector_today_route_with_search_renders(): void
    {
        $this->actingAs($this->collector)
            ->get(route('collector.route') . '?search=Juan')
            ->assertOk();
    }

    #[Test]
    public function collector_borrower_detail_renders(): void
    {
        $this->actingAs($this->collector)
            ->get(route('collector.borrower', $this->borrower->id))
            ->assertOk();
    }

    #[Test]
    public function collector_borrower_detail_with_nonexistent_id_renders(): void
    {
        $this->actingAs($this->collector)
            ->get(route('collector.borrower', 999999))
            ->assertOk();
    }

    #[Test]
    public function collector_qr_scanner_renders(): void
    {
        $this->actingAs($this->collector)
            ->get(route('collector.scan'))
            ->assertOk();
    }

    #[Test]
    public function collector_record_payment_renders(): void
    {
        $this->actingAs($this->collector)
            ->get(route('collector.payment', $this->borrower->id))
            ->assertOk();
    }

    #[Test]
    public function collector_record_payment_with_nonexistent_borrower_renders(): void
    {
        $this->actingAs($this->collector)
            ->get(route('collector.payment', 999999))
            ->assertOk();
    }

    #[Test]
    public function collector_payment_confirmation_renders(): void
    {
        $this->actingAs($this->collector)
            ->get(route('collector.payment.confirmed', $this->payment->id))
            ->assertOk();
    }

    #[Test]
    public function collector_payment_confirmation_with_nonexistent_payment_renders(): void
    {
        $this->actingAs($this->collector)
            ->get(route('collector.payment.confirmed', 999999))
            ->assertOk();
    }

    #[Test]
    public function collector_end_of_day_summary_renders(): void
    {
        $this->actingAs($this->collector)
            ->get(route('collector.summary'))
            ->assertOk();
    }

    #[Test]
    public function collector_end_of_day_summary_with_voided_payment_renders(): void
    {
        $this->payment->update(['is_voided' => true]);

        $this->actingAs($this->collector)
            ->get(route('collector.summary'))
            ->assertOk();
    }

    // ── Onboarding (admin/office-facing PWA setup checklist) ──────────────────

    #[Test]
    public function onboarding_page_redirects_unauthenticated_users(): void
    {
        $this->get(route('collector.onboarding'))->assertRedirect();
    }

    #[Test]
    public function onboarding_page_renders_for_admin(): void
    {
        $this->actingAs($this->admin)
            ->get(route('collector.onboarding'))
            ->assertOk()
            ->assertSee('Collector App Setup');
    }

    #[Test]
    public function onboarding_page_renders_for_collector(): void
    {
        $this->actingAs($this->collector)
            ->get(route('collector.onboarding'))
            ->assertOk();
    }

    #[Test]
    public function onboarding_page_forbidden_for_borrower(): void
    {
        $this->actingAs($this->borrowerUser)
            ->get(route('collector.onboarding'))
            ->assertForbidden();
    }
}
