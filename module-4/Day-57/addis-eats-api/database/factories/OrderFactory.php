<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal    = fake()->numberBetween(18000, 95000);
        $deliveryFee = 5000;

        return [
            'user_id'             => User::inRandomOrder()->value('id') ?? User::factory(),
            'restaurant_id'       => Restaurant::inRandomOrder()->value('id') ?? Restaurant::factory(),
            'area'                => fake()->randomElement(config('addiseats.zones', ['Bole', 'Kazanchis', 'Piassa', 'Megenagna'])),
            'status'              => fake()->randomElement(OrderStatus::cases()),
            'subtotal_santim'     => $subtotal,
            'delivery_fee_santim' => $deliveryFee,
            'total_santim'        => $subtotal + $deliveryFee,
            'notes'               => fake()->optional(0.4)->sentence(),
        ];
    }
}
