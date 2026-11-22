<?php

use App\Http\Controllers\Api\V1\DishController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\OrderStatusController;
use App\Http\Controllers\Api\V1\RestaurantController;
use App\Http\Controllers\Api\V1\RestaurantDishController;
use Illuminate\Support\Facades\Route;

Route::apiResource('dishes', DishController::class)
    ->only(['index', 'store']);

Route::apiResource('restaurants', RestaurantController::class)
    ->where(['restaurant' => '[a-z0-9-]+']);

Route::apiResource('restaurants.dishes', RestaurantDishController::class)
    ->shallow()
    ->where([
        'restaurant' => '[a-z0-9-]+',
        'dish'       => '[0-9]+',
    ]);

Route::apiResource('orders', OrderController::class)
    ->only(['index', 'store', 'show'])
    ->whereNumber('order');

Route::patch('orders/{order}/status', OrderStatusController::class)
    ->whereNumber('order')
    ->name('orders.status.update');
