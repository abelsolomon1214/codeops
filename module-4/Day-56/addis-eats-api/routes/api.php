<?php

use App\Http\Controllers\Api\V1\DishController;
use App\Http\Controllers\Api\V1\RestaurantController;
use App\Http\Controllers\Api\V1\RestaurantDishController;
use Illuminate\Support\Facades\Route;

Route::name('v1.')->prefix('v1')->group(function () {
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
});
