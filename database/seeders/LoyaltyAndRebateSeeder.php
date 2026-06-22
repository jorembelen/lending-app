<?php

namespace Database\Seeders;

use App\Models\LoyaltyTier;
use App\Models\RebateRule;
use Illuminate\Database\Seeder;

class LoyaltyAndRebateSeeder extends Seeder
{
    public function run(): void
    {
        // Loyalty tiers — Standard, Trusted, Preferred
        $standard = LoyaltyTier::firstOrCreate(
            ['name' => 'Standard'],
            [
                'rank'                       => 1,
                'max_missed_days_to_qualify' => 999, // everyone starts here
                'loan_ceiling_multiplier'    => null,
                'rate_discount_per_1000'     => 0,
                'priority_reloan'            => false,
            ]
        );

        $trusted = LoyaltyTier::firstOrCreate(
            ['name' => 'Trusted'],
            [
                'rank'                       => 2,
                'max_missed_days_to_qualify' => 5,
                'loan_ceiling_multiplier'    => 1.50,
                'rate_discount_per_1000'     => 1.00,
                'priority_reloan'            => false,
            ]
        );

        $preferred = LoyaltyTier::firstOrCreate(
            ['name' => 'Preferred'],
            [
                'rank'                       => 3,
                'max_missed_days_to_qualify' => 2,
                'loan_ceiling_multiplier'    => 2.00,
                'rate_discount_per_1000'     => 2.00,
                'priority_reloan'            => true,
            ]
        );

        // Rebate rules
        // Universal rule: 5% rebate if ≤ 5 missed days
        RebateRule::firstOrCreate(
            ['percent_of_interest' => 5.00, 'max_missed_days_to_qualify' => 5, 'loyalty_tier_id' => null],
            [
                'default_application' => 'credit_next_loan',
                'is_active'           => true,
            ]
        );

        // Trusted tier: 8% rebate if ≤ 5 missed days
        RebateRule::firstOrCreate(
            ['percent_of_interest' => 8.00, 'max_missed_days_to_qualify' => 5, 'loyalty_tier_id' => $trusted->id],
            [
                'default_application' => 'credit_next_loan',
                'is_active'           => true,
            ]
        );

        // Preferred tier: 12% rebate if ≤ 2 missed days
        RebateRule::firstOrCreate(
            ['percent_of_interest' => 12.00, 'max_missed_days_to_qualify' => 2, 'loyalty_tier_id' => $preferred->id],
            [
                'default_application' => 'cash',
                'is_active'           => true,
            ]
        );

        $this->command->info('LoyaltyAndRebateSeeder: 3 loyalty tiers, 3 rebate rules seeded.');
    }
}
