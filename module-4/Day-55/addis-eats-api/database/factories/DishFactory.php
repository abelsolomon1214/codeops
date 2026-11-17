<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DishFactory extends Factory
{
    public function definition(): array
    {
        $price = fake()->numberBetween(12000, 55000);

        return [
            'name'          => fake()->randomElement(['Doro Wat', 'Shiro', 'Kitfo', 'Tibs', 'Firfir', 'Beyaynetu', 'Gored Gored', 'Kikil']),
            'restaurant'    => fake()->randomElement(['Yod Abyssinia', 'Kategna', 'Sishu', '2000 Habesha', 'Totot']),
            'price'         => $price,
            'is_fasting'    => fake()->boolean(),
            'area'          => fake()->randomElement(['Bole', 'Kazanchis', 'Piassa', 'Megenagna']),
            'cost_price'    => (int) round($price * 0.55),
            'supplier_note' => 'Verify morning stock with kitchen lead',
        ];
    }
}