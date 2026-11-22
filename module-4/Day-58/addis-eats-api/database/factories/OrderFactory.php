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
        $restaurant  = Restaurant::inRandomOrder()->first() ?? Restaurant::factory()->create();
        $subtotal    = fake()->numberBetween(18000, 95000);
        $deliveryFee = 5000;

        return [
            'user_id'             => User::inRandomOrder()->value('id'),
            'restaurant_id'       => $restaurant->id,
            'area'                => $restaurant->area,
            'phone'               => '09' . fake()->numerify('########'),
            'status'              => fake()->randomElement(OrderStatus::cases()),
            'subtotal_santim'     => $subtotal,
            'delivery_fee_santim' => $deliveryFee,
            'total_santim'        => $subtotal + $deliveryFee,
            'notes'               => fake()->optional(0.4)->sentence(),
        ];
    }
}
