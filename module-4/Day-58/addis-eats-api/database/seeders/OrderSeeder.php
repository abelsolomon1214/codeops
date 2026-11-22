<?php

namespace Database\Seeders;

use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $restaurants = Restaurant::with('dishes')->get()->filter(fn ($r) => $r->dishes->isNotEmpty());

        for ($i = 0; $i < 15; $i++) {
            $restaurant = $restaurants->random();
            $selectedDishes = $restaurant->dishes->shuffle()->take(fake()->numberBetween(1, 3));

            $order = Order::factory()->create([
                'restaurant_id' => $restaurant->id,
                'area'          => $restaurant->area,
            ]);

            $subtotal = 0;
            $now = now();
            $lineRows = [];

            foreach ($selectedDishes as $dish) {
                $qty = fake()->numberBetween(1, 3);
                $lineTotal = $dish->price * $qty;
                $subtotal += $lineTotal;

                $lineRows[] = [
                    'order_id'          => $order->id,
                    'dish_id'           => $dish->id,
                    'qty'               => $qty,
                    'unit_price_santim' => $dish->price,
                    'line_total_santim' => $lineTotal,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];
            }

            OrderLine::insert($lineRows);

            $order->update([
                'subtotal_santim' => $subtotal,
                'total_santim'    => $subtotal + $order->delivery_fee_santim,
            ]);
        }
    }
}
