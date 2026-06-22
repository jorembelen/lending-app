<?php

namespace Tests\Feature\Screens;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Support\Str;

class AdminScreensTest extends ScreenTestCase
{
    public function test_admin_dashboard_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_admin_borrowers_list_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.borrowers'))
            ->assertOk();
    }

    public function test_admin_borrower_detail_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.borrowers.show', $this->borrower->id))
            ->assertOk();
    }

    public function test_admin_release_new_loan_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.loans.create'))
            ->assertOk();
    }

    public function test_admin_collections_monitor_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.monitor'))
            ->assertOk();
    }

    public function test_admin_settings_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.settings'))
            ->assertOk();
    }

    public function test_admin_loyalty_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.loyalty'))
            ->assertOk();
    }

    public function test_admin_pending_rebates_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.rebates'))
            ->assertOk();
    }

    public function test_admin_routes_redirect_unauthenticated_users(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.borrowers'))->assertRedirect(route('login'));
    }

    public function test_collector_cannot_access_admin_routes(): void
    {
        $this->actingAs($this->collector)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_borrower_detail_with_multiple_loans_renders(): void
    {
        $extra = Borrower::create([
            'full_name'       => 'Repeat Borrower',
            'phone_number'    => '09991234567',
            'address'         => 'QC',
            'qr_reference'    => Str::uuid(),
            'current_tier_id' => $this->standardTier->id,
        ]);

        foreach (range(1, 2) as $i) {
            Loan::create([
                'borrower_id'          => $extra->id,
                'rate_preset_id'       => $this->preset->id,
                'principal'            => 5000,
                'rate_per_1000_locked' => 20.00,
                'term_days_locked'     => 60,
                'daily_installment'    => 100.00,
                'total_payable'        => 6000.00,
                'disbursed_at'         => now()->subDays(90 * $i)->toDateString(),
                'disbursed_by_user_id' => $this->staff->id,
                'status'               => 'completed',
                'closed_at'            => now()->subDays(30 * $i)->toDateString(),
                'missed_days_at_closure' => 0,
            ]);
        }

        $this->actingAs($this->admin)
            ->get(route('admin.borrowers.show', $extra->id))
            ->assertOk();
    }
}
