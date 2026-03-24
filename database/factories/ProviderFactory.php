<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Provider>
 */
class ProviderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'company_id' => Company::factory(),
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d H:i:s'),
        ];
    }
}
