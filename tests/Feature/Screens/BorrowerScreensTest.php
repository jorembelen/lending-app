<?php

namespace Tests\Feature\Screens;

use PHPUnit\Framework\Attributes\Test;

class BorrowerScreensTest extends ScreenTestCase
{
    // ── Auth guards ──────────────────────────────────────────────────────────

    #[Test]
    public function borrower_routes_redirect_unauthenticated_users(): void
    {
        $routes = [
            route('borrower.home'),
            route('borrower.schedule'),
            route('borrower.rewards'),
            route('borrower.history'),
            route('borrower.profile'),
        ];

        foreach ($routes as $url) {
            $this->get($url)->assertRedirect();
        }
    }

    #[Test]
    public function admin_cannot_access_borrower_routes(): void
    {
        $this->actingAs($this->admin)
            ->get(route('borrower.home'))
            ->assertForbidden();
    }

    #[Test]
    public function collector_cannot_access_borrower_routes(): void
    {
        $this->actingAs($this->collector)
            ->get(route('borrower.home'))
            ->assertForbidden();
    }

    // ── Screens ──────────────────────────────────────────────────────────────

    #[Test]
    public function borrower_home_renders(): void
    {
        $this->actingAs($this->borrowerUser)
            ->get(route('borrower.home'))
            ->assertOk();
    }

    #[Test]
    public function borrower_repayment_schedule_renders(): void
    {
        $this->actingAs($this->borrowerUser)
            ->get(route('borrower.schedule'))
            ->assertOk();
    }

    #[Test]
    public function borrower_rewards_renders(): void
    {
        $this->actingAs($this->borrowerUser)
            ->get(route('borrower.rewards'))
            ->assertOk();
    }

    #[Test]
    public function borrower_loan_history_renders(): void
    {
        $this->actingAs($this->borrowerUser)
            ->get(route('borrower.history'))
            ->assertOk();
    }

    #[Test]
    public function borrower_profile_renders(): void
    {
        $this->actingAs($this->borrowerUser)
            ->get(route('borrower.profile'))
            ->assertOk();
    }
}
