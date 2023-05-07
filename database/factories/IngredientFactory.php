<?php

namespace Database\Factories;

use App\Models\Merchant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ingredient>
 */
class IngredientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'original_stock' => $stock = $this->faker->numberBetween(1000, 20000),
            'current_stock' => $stock * $this->faker->randomFloat(1, 0.1, 1),
            'merchant_id' => Merchant::factory()->create(),
            'is_merchant_notified' => false,
        ];
    }
}
