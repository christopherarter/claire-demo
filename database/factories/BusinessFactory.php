<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Business>
 */
class BusinessFactory extends Factory
{
    protected $model = \App\Models\Business::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'              => $this->faker->company(),
            'external_id'       => $this->faker->unique()->uuid(),
            'deduction_percent' => $this->faker->optional()->numberBetween(1, 10000), // 1 and 100%
            'enabled'           => true,
        ];
    }
}
