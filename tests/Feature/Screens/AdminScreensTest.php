<?php

namespace Tests\Feature\Screens;

use PHPUnit\Framework\Attributes\Test;

class AdminScreensTest extends ScreenTestCase
{
    // ── Auth guards ──────────────────────────────────────────────────────────

    #[Test]
    public function admin_routes_redirect_unauthenticated_users(): void
    {
        $routes = [
            route('admin.dashboard'),
            route('admin.borrowers'),
            route('admin.borrowers.show', $this->borrower->id),
            route('admin.loans.create'),
            route('admin.monitor'),
            route('admin.settings'),
            route('admin.loyalty'),
            route('admin.rebates'),
        ];

        foreach ($routes as $url) {
            $this->get($url)->assertRedirect();
        }
    }

    #[Test]
    public function collector_cannot_access_admin_routes(): void
    {
        $this->actingAs($this->collector)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    #[Test]
    public function borrower_user_cannot_access_admin_routes(): void
    {
        $this->actingAs($this->borrowerUser)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    // ── Screens ──────────────────────────────────────────────────────────────

    #[Test]
    public function admin_dashboard_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    #[Test]
    public function admin_borrowers_list_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.borrowers'))
            ->assertOk();
    }

    #[Test]
    public function admin_borrowers_list_shows_borrower(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.borrowers'))
            ->assertOk()
            ->assertSee($this->borrower->full_name);
    }

    #[Test]
    public function admin_borrowers_list_with_search_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.borrowers') . '?search=Juan')
            ->assertOk();
    }

    #[Test]
    public function admin_borrower_detail_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.borrowers.show', $this->borrower->id))
            ->assertOk();
    }

    #[Test]
    public function admin_borrower_detail_with_nonexistent_id_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.borrowers.show', 999999))
            ->assertOk();
    }

    #[Test]
    public function admin_release_new_loan_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.loans.create'))
            ->assertOk();
    }

    #[Test]
    public function admin_collections_monitor_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.monitor'))
            ->assertOk();
    }

    #[Test]
    public function admin_settings_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.settings'))
            ->assertOk();
    }

    #[Test]
    public function admin_loyalty_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.loyalty'))
            ->assertOk();
    }

    #[Test]
    public function admin_pending_rebates_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.rebates'))
            ->assertOk();
    }

    #[Test]
    public function admin_pending_rebates_shows_pending_approval_records(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.rebates'))
            ->assertOk()
            ->assertSee($this->borrower->full_name);
    }
}
