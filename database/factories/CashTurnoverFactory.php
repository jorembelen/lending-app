<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CashTurnoverFactory extends Factory
{
    public function definition(): array
    {
        $systemTotal = $this->faker->randomFloat(2, 500, 5000);

        return [
            'collector_user_id' => User::role('collector')->inRandomOrder()->first()?->id
                ?? User::inRandomOrder()->first()?->id,
            'turnover_date' => now()->subDays(rand(1, 30)),
            'system_total' => $systemTotal,
            'cash_remitted' => $systemTotal,
            'variance' => 0,
            'reconciled_by_user_id' => null,
            'reconciled_at' => null,
            'note' => null,
        ];
    }

    public function withVariance(float $variance): static
    {
        return $this->afterMaking(function ($model) use ($variance) {
            $model->cash_remitted = round((float) $model->system_total + $variance, 2);
            $model->variance = $variance;
        });
    }
}
