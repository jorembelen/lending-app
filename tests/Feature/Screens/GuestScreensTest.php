<?php

namespace Tests\Feature\Screens;

use PHPUnit\Framework\Attributes\Test;

class GuestScreensTest extends ScreenTestCase
{
    #[Test]
    public function admin_login_page_renders(): void
    {
        $this->get(route('login'))
            ->assertOk();
    }

    #[Test]
    public function collector_login_page_renders(): void
    {
        $this->get(route('collector.login'))
            ->assertOk();
    }

    #[Test]
    public function borrower_login_page_renders(): void
    {
        $this->get(route('borrower.login'))
            ->assertOk();
    }

    #[Test]
    public function authenticated_admin_is_redirected_from_collector_login(): void
    {
        $this->actingAs($this->admin)
            ->get(route('collector.login'))
            ->assertRedirect();
    }

    #[Test]
    public function authenticated_user_is_redirected_from_borrower_login(): void
    {
        $this->actingAs($this->admin)
            ->get(route('borrower.login'))
            ->assertRedirect();
    }

    #[Test]
    public function home_route_redirects_unauthenticated_to_login(): void
    {
        $this->get('/')
            ->assertRedirect();
    }
}
