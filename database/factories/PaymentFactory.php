<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    // Base coordinates for a Philippine barangay service area (Quezon City area)
    private const BASE_LAT = 14.6760;
    private const BASE_LNG = 121.0437;

    public function definition(): array
    {
        $collectedAt = now()->subDays(rand(1, 60));

        return [
            'loan_id' => Loan::factory(),
            'collector_user_id' => User::role('collector')->inRandomOrder()->first()?->id
                ?? User::inRandomOrder()->first()?->id,
            'amount' => 0,
            'collected_at' => $collectedAt,
            'recorded_at' => $collectedAt->addSeconds(rand(0, 300)),
            'latitude' => self::BASE_LAT + $this->faker->randomFloat(6, -0.02, 0.02),
            'longitude' => self::BASE_LNG + $this->faker->randomFloat(6, -0.02, 0.02),
            'device_identifier' => 'DEV-' . strtoupper(Str::random(8)),
            'idempotency_key' => Str::uuid()->toString(),
            'is_voided' => false,
            'voided_by_user_id' => null,
            'voided_reason' => null,
            'voided_at' => null,
        ];
    }

    public function voided(User $voidedBy, string $reason = 'Data entry error'): static
    {
        return $this->state([
            'is_voided' => true,
            'voided_by_user_id' => $voidedBy->id,
            'voided_reason' => $reason,
            'voided_at' => now()->subDays(rand(1, 5)),
        ]);
    }
}
