<?php

namespace App\Http\Controllers;

use App\Http\Resources\RestaurantResource;
use App\Models\Restaurant;
use App\Http\Resources\DishResource;
use App\Models\Dish;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $query = Restaurant::query();

        $query->when($request->filled('area'), fn($q) =>
            $q->where('area', $request->query('area')));

        return RestaurantResource::collection(
            $query->orderBy('name')->get()
        );
    }

    public function show(Restaurant $restaurant)
    {
        return new RestaurantResource($restaurant);
    }
    public function dishes(Restaurant $restaurant)
    {
        $dishes = Dish::where('restaurant', $restaurant->name)
            ->orderBy('price')
            ->get();

        return DishResource::collection($dishes);
    }
}
