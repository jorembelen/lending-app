<?php

namespace Tests\Feature\Screens;

class GuestScreensTest extends ScreenTestCase
{
    public function test_admin_login_page_renders(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_collector_login_page_renders(): void
    {
        $this->get(route('collector.login'))->assertOk();
    }

    public function test_borrower_login_page_renders(): void
    {
        $this->get(route('borrower.login'))->assertOk();
    }

    public function test_authenticated_user_is_redirected_from_login(): void
    {
        $this->actingAs($this->admin)
            ->get(route('login'))
            ->assertRedirect();
    }

    public function test_home_route_redirects_unauthenticated_to_login(): void
    {
        $this->get(route('home'))->assertRedirect(route('login'));
    }
}
