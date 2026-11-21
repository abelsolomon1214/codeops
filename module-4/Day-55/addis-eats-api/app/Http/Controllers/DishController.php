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

        $query->when($request->filled('restaurant'), fn ($q) =>
            $q->where('restaurant', $request->query('restaurant')));

        $query->when($request->filled('search'), fn ($q) =>
            $q->where('name', 'LIKE', '%' . $request->query('search') . '%'));

        match ($request->query('sort')) {
            'price_desc' => $query->orderBy('price', 'desc'),
            'name'       => $query->orderBy('name', 'asc'),
            default      => $query->orderBy('price', 'asc'),
        };

        $perPage = min(max((int) $request->query('per_page', 5), 1), 50);

        return DishResource::collection($query->paginate($perPage)->withQueryString());
    }

    public function store(Request $request)
    {
       $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'restaurant' => 'required|string|max:255',
            'price'      => 'required|integer|min:100',
            'is_fasting' => 'required|boolean',
            'area'       => 'required|string|max:100',
       ],
       [
         'price.min' => 'The price must be a positive amount of at least 100 santim (1.00 ETB).',
       ]);

        $dish = Dish::create($validated);

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
        $validated = $request->validate([
            'name'       => 'sometimes|required|string|max:255',
            'restaurant' => 'sometimes|required|string|max:255',
            'price'      => 'sometimes|required|integer|min:100',
            'is_fasting' => 'sometimes|required|boolean',
            'area'       => 'sometimes|required|string|max:100',
        ]);

        $dish->update($validated);

        return new DishResource($dish);
    }

    public function destroy(Dish $dish)
    {
        $dish->delete();

        return response()->noContent();
    }
}