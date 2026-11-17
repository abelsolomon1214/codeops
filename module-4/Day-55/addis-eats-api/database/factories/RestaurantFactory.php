<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RestaurantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'          => fake()->unique()->company(),
            'slug'          => fake()->unique()->slug(2),
            'area'          => fake()->randomElement(['Bole', 'Kazanchis', 'Piassa', 'Megenagna']),
            'phone'         => '+2519' . fake()->numerify('########'),
            'opening_hours' => '08:00 - 22:00',
        ];
    }
}