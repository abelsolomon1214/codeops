<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Exceptions\IllegalTransitionException;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderLine;
use Illuminate\Support\Facades\DB;

class OrderService
{
    private const DELIVERY_FEE_SANTIM = 5000;

    public function placeOrder(array $validated, ?int $userId = null): Order
    {
        return DB::transaction(function () use ($validated, $userId): Order {
            $linesInput = collect($validated['lines']);
            $dishes     = Dish::whereIn('id', $linesInput->pluck('dish_id'))
                ->get(['id', 'restaurant_id', 'price'])
                ->keyBy('id');

            $firstDish   = $dishes->first();
            $subtotal    = 0;
            $deliveryFee = self::DELIVERY_FEE_SANTIM;

            $order = Order::create([
                'user_id'             => $userId,
                'restaurant_id'       => $firstDish->restaurant_id,
                'area'                => $validated['area'],
                'phone'               => $validated['phone'],
                'status'              => OrderStatus::Placed,
                'subtotal_santim'     => 0,
                'delivery_fee_santim' => $deliveryFee,
                'total_santim'        => $deliveryFee,
                'notes'               => $validated['notes'] ?? null,
            ]);

            $now      = now();
            $lineRows = [];

            foreach ($linesInput as $line) {
                $dish      = $dishes->get((int) $line['dish_id']);
                $qty       = (int) $line['qty'];
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
                'total_santim'    => $subtotal + $deliveryFee,
            ]);

            return $order->load(['restaurant', 'lines.dish']);
        });
    }

    public function transitionStatus(Order $order, OrderStatus $target): Order
    {
        $allowed = match ($order->status) {
            OrderStatus::Placed     => [OrderStatus::Confirmed, OrderStatus::Cancelled],
            OrderStatus::Confirmed  => [OrderStatus::Preparing, OrderStatus::Cancelled],
            OrderStatus::Preparing  => [OrderStatus::Dispatched],
            OrderStatus::Dispatched => [OrderStatus::Delivered],
            OrderStatus::Delivered,
            OrderStatus::Cancelled  => [],
        };

        if (! in_array($target, $allowed, true)) {
            throw new IllegalTransitionException($order->status->value, $target->value);
        }

        $order->update(['status' => $target]);

        return $order->load(['restaurant', 'lines.dish']);
    }
}
