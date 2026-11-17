<?php

namespace App\Http\Controllers;

use App\Http\Resources\DishResource;
use App\Models\Dish;
use Illuminate\Http\Request;

class DishController extends Controller
{
    public function index(Request $request)
    {
        $query = Dish::query();

        $query->when($request->filled('area'), fn ($q) =>
            $q->where('area', $request->query('area')));

        $query->when($request->filled('max_price'), fn ($q) =>
            $q->where('price', '<=', (int) $request->query('max_price')));

        $query->when($request->boolean('fasting'), fn ($q) =>
            $q->where('is_fasting', true));

        return DishResource::collection($query->orderBy('price')->get());
    }

    public function store(Request $request)
    {
        // Only whitelisted keys are read — never $request->all()
        $dish = Dish::create($request->only([
            'name',
            'restaurant',
            'price',
            'is_fasting',
            'area',
        ]));

        return (new DishResource($dish))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('dishes.show', $dish));
    }

    public function show(Dish $dish)
    {
        return new DishResource($dish);
    }

    public function update(Request $request, Dish $dish)
    {
        $dish->update($request->only([
            'name',
            'restaurant',
            'price',
            'is_fasting',
            'area',
        ]));

        return new DishResource($dish);
    }

    public function destroy(Dish $dish)
    {
        $dish->delete();

        return response()->noContent();
    }
}