<?php

use App\Http\Controllers\DishController;
use App\Http\Controllers\RestaurantController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Dishes: full 5-endpoint REST resource with numeric constraint on {dish}
    Route::apiResource('dishes', DishController::class)
        ->whereNumber('dish');

    // Restaurants: read endpoints bound by slug (e.g., /api/v1/restaurants/yod-abyssinia)
    Route::get('/restaurants', [RestaurantController::class, 'index'])
        ->name('restaurants.index');

    Route::get('/restaurants/{restaurant:slug}', [RestaurantController::class, 'show'])
        ->name('restaurants.show');

    // New: Full menu for a specific restaurant by slug
    Route::get('/restaurants/{restaurant:slug}/dishes', [RestaurantController::class, 'dishes'])
        ->name('restaurants.dishes');
});
