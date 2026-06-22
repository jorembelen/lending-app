<?php

namespace Tests\Feature\Screens;

use App\Models\Borrower;
use App\Models\BorrowerAccount;
use App\Models\HolidayCalendar;
use App\Models\Loan;
use App\Models\LoyaltyTier;
use App\Models\Payment;
use App\Models\RatePreset;
use App\Models\RebateGrant;
use App\Models\RebateRule;
use App\Models\ScheduleItem;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

abstract class ScreenTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected User $collector;
    protected Borrower $borrower;
    protected Loan $loan;
    protected LoyaltyTier $standardTier;
    protected RatePreset $preset;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->seedSettings();
        $this->seedBaseData();
    }

    private function seedRoles(): void
    {
        foreach (['super admin', 'admin', 'staff', 'collector', 'user'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $this->admin = User::factory()->create(['name' => 'Test Admin', 'username' => 'test.admin', 'status' => 1]);
        $this->admin->assignRole('admin');

        $this->staff = User::factory()->create(['name' => 'Test Staff', 'username' => 'test.staff', 'status' => 1]);
        $this->staff->assignRole('staff');

        $this->collector = User::factory()->create(['name' => 'Test Collector', 'username' => 'test.collector', 'status' => 1]);
        $this->collector->assignRole('collector');
    }

    private function seedSettings(): void
    {
        Setting::set('weekly_off_day', 0);
        Setting::set('lockout_attempt_threshold', 5);
    }

    private function seedBaseData(): void
    {
        $this->standardTier = LoyaltyTier::create([
            'name'                       => 'Standard',
            'rank'                       => 1,
            'max_missed_days_to_qualify' => 999,
            'loan_ceiling_multiplier'    => null,
            'rate_discount_per_1000'     => 0,
            'priority_reloan'            => false,
        ]);

        $trustedTier = LoyaltyTier::create([
            'name'                       => 'Trusted',
            'rank'                       => 2,
            'max_missed_days_to_qualify' => 5,
            'loan_ceiling_multiplier'    => 1.50,
            'rate_discount_per_1000'     => 1.00,
            'priority_reloan'            => false,
        ]);

        $this->preset = RatePreset::create([
            'name'          => 'Standard 20/1000/60',
            'rate_per_1000' => 20.00,
            'term_days'     => 60,
            'is_default'    => true,
            'is_active'     => true,
        ]);

        RebateRule::create([
            'loyalty_tier_id'           => null,
            'percent_of_interest'       => 5.00,
            'max_missed_days_to_qualify' => 5,
            'default_application'       => 'credit_next_loan',
            'is_active'                 => true,
        ]);

        RebateRule::create([
            'loyalty_tier_id'           => $trustedTier->id,
            'percent_of_interest'       => 8.00,
            'max_missed_days_to_qualify' => 5,
            'default_application'       => 'credit_next_loan',
            'is_active'                 => true,
        ]);

        $this->borrower = Borrower::create([
            'full_name'    => 'Juan dela Cruz',
            'phone_number' => '09171234567',
            'address'      => 'Barangay 1, Quezon City',
            'qr_reference' => Str::uuid(),
            'current_tier_id' => $this->standardTier->id,
        ]);

        BorrowerAccount::create([
            'borrower_id'        => $this->borrower->id,
            'email'              => 'juan@test.local',
            'pin_hash'           => bcrypt('1234'),
            'created_by_user_id' => $this->staff->id,
        ]);

        // Active loan with 3 paid schedule items
        $disbursedAt = Carbon::now()->subDays(10);
        $daily = round((5000 / 1000) * 20, 2);
        $this->loan = Loan::create([
            'borrower_id'          => $this->borrower->id,
            'rate_preset_id'       => $this->preset->id,
            'principal'            => 5000,
            'rate_per_1000_locked' => 20.00,
            'term_days_locked'     => 60,
            'daily_installment'    => $daily,
            'total_payable'        => $daily * 60,
            'disbursed_at'         => $disbursedAt->toDateString(),
            'disbursed_by_user_id' => $this->staff->id,
            'status'               => 'active',
        ]);

        // 3 paid schedule items and 1 payment
        for ($i = 1; $i <= 3; $i++) {
            $due = $disbursedAt->copy()->addDays($i);
            ScheduleItem::create([
                'loan_id'         => $this->loan->id,
                'sequence_number' => $i,
                'due_date'        => $due->toDateString(),
                'amount_due'      => $daily,
                'amount_paid'     => $daily,
                'status'          => 'paid',
            ]);

            Payment::create([
                'loan_id'           => $this->loan->id,
                'collector_user_id' => $this->collector->id,
                'amount'            => $daily,
                'collected_at'      => $due->copy()->addHours(9),
                'recorded_at'       => $due->copy()->addHours(9)->addSeconds(30),
                'latitude'          => 14.6760,
                'longitude'         => 121.0437,
                'device_identifier' => 'DEV-TEST01',
                'idempotency_key'   => Str::uuid(),
                'is_voided'         => false,
            ]);
        }

        // One completed loan for history/rebate screens
        $oldLoan = Loan::create([
            'borrower_id'            => $this->borrower->id,
            'rate_preset_id'         => $this->preset->id,
            'principal'              => 3000,
            'rate_per_1000_locked'   => 20.00,
            'term_days_locked'       => 60,
            'daily_installment'      => 60.00,
            'total_payable'          => 3600.00,
            'disbursed_at'           => Carbon::now()->subDays(100)->toDateString(),
            'disbursed_by_user_id'   => $this->staff->id,
            'status'                 => 'completed',
            'closed_at'              => Carbon::now()->subDays(40)->toDateString(),
            'missed_days_at_closure' => 2,
        ]);

        RebateGrant::create([
            'loan_id'         => $oldLoan->id,
            'borrower_id'     => $this->borrower->id,
            'rebate_rule_id'  => RebateRule::first()->id,
            'interest_amount' => 600.00,
            'rebate_amount'   => 30.00,
            'status'          => 'pending_approval',
        ]);
    }
}
