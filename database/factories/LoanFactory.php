<?php

namespace Database\Factories;

use App\Models\Borrower;
use App\Models\RatePreset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanFactory extends Factory
{
    public function definition(): array
    {
        $preset = RatePreset::where('is_active', true)->inRandomOrder()->first();
        $principal = $this->faker->randomElement([2000, 3000, 5000, 8000, 10000, 15000, 20000]);
        $rate = $preset ? (float) $preset->rate_per_1000 : 20.00;
        $termDays = $preset ? $preset->term_days : 60;
        $dailyInstallment = round(($principal / 1000) * $rate, 2);
        $totalPayable = round($dailyInstallment * $termDays, 2);

        return [
            'borrower_id' => Borrower::factory(),
            'rate_preset_id' => $preset?->id ?? RatePreset::factory(),
            'principal' => $principal,
            'rate_per_1000_locked' => $rate,
            'term_days_locked' => $termDays,
            'daily_installment' => $dailyInstallment,
            'total_payable' => $totalPayable,
            'disbursed_at' => now()->subDays(rand(10, 90))->toDateString(),
            'disbursed_by_user_id' => User::role('staff')->inRandomOrder()->first()?->id
                ?? User::inRandomOrder()->first()?->id,
            'status' => 'active',
            'closed_at' => null,
            'missed_days_at_closure' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active', 'closed_at' => null]);
    }

    public function completed(int $missedDays = 0): static
    {
        return $this->state([
            'status' => 'completed',
            'closed_at' => now()->subDays(rand(1, 30))->toDateString(),
            'missed_days_at_closure' => $missedDays,
        ]);
    }

    public function defaulted(int $missedDays = 20): static
    {
        return $this->state([
            'status' => 'defaulted',
            'closed_at' => now()->subDays(rand(1, 30))->toDateString(),
            'missed_days_at_closure' => $missedDays,
        ]);
    }
}
