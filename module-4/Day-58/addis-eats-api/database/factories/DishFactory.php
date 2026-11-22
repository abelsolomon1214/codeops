<?php

namespace Database\Factories;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

class DishFactory extends Factory
{
    public function definition(): array
    {
        $price      = fake()->numberBetween(12000, 55000);
        $restaurant = Restaurant::inRandomOrder()->first() ?? Restaurant::factory()->create();

        return [
            'name'          => fake()->randomElement(['Doro Wat', 'Shiro', 'Kitfo', 'Tibs', 'Firfir', 'Beyaynetu', 'Gored Gored', 'Kikil']),
            'restaurant_id' => $restaurant->id,
            'price'         => $price,
            'is_fasting'    => fake()->boolean(),
            'area'          => $restaurant->area,
            'cost_price'    => (int) round($price * 0.55),
            'supplier_note' => 'Verify morning stock with kitchen lead',
        ];
    }
}
