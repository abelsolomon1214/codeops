<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DishResource;
use App\Http\Responses\ApiResponse;
use App\Models\Dish;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RestaurantDishController extends Controller
{
    private const SORTABLE = ['name', 'price', 'created_at'];

    public function index(Request $request, Restaurant $restaurant): JsonResponse
    {
        $sort      = $request->string('sort', 'price')->toString();
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column    = ltrim($sort, '-');

        if (! in_array($column, self::SORTABLE, true)) {
            $column = 'price';
        }

        $dishes = $restaurant->dishes()
            ->inArea($request->query('area'))
            ->under($request->integer('max_price'))
            ->fasting($request->boolean('fasting'))
            ->when($request->filled('q'), fn ($qb) =>
                $qb->where('name', 'like', '%' . $request->query('q') . '%'))
            ->orderBy($column, $direction)
            ->get();

        return ApiResponse::ok(
            DishResource::collection($dishes),
            array_filter([
                'count'      => $dishes->count(),
                'restaurant' => $restaurant->slug,
            ])
        );
    }

    public function store(Request $request, Restaurant $restaurant): JsonResponse
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'price'      => 'required|integer|min:100',
            'is_fasting' => 'required|boolean',
            'area'       => 'sometimes|string|max:100',
        ], [
            'price.min' => 'The price must be a positive amount of at least 100 santim (1.00 ETB).',
        ]);

        $validated['area'] ??= $restaurant->area;

        $dish = $restaurant->dishes()->create($validated);

        return ApiResponse::created(
            new DishResource($dish),
            route('v1.dishes.show', $dish)
        );
    }

    public function show(Dish $dish): JsonResponse
    {
        return ApiResponse::ok(new DishResource($dish));
    }

    public function update(Request $request, Dish $dish): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'sometimes|required|string|max:255',
            'restaurant_id' => 'sometimes|required|integer|exists:restaurants,id',
            'price'         => 'sometimes|required|integer|min:100',
            'is_fasting'    => 'sometimes|required|boolean',
            'area'          => 'sometimes|required|string|max:100',
        ], [
            'price.min' => 'The price must be a positive amount of at least 100 santim (1.00 ETB).',
        ]);

        $dish->update($validated);

        return ApiResponse::ok(new DishResource($dish));
    }

    public function destroy(Dish $dish): Response
    {
        $dish->delete();

        return response()->noContent();
    }
}
