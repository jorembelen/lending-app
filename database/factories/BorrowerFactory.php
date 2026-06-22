<?php

namespace Database\Factories;

use App\Models\LoyaltyTier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BorrowerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name(),
            'phone_number' => '09' . $this->faker->numerify('#########'),
            'address' => $this->faker->address(),
            'qr_reference' => Str::uuid()->toString(),
            'photo_path' => null,
            'current_tier_id' => null,
        ];
    }

    public function withTier(LoyaltyTier $tier): static
    {
        return $this->state(['current_tier_id' => $tier->id]);
    }
}
