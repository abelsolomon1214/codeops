<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreRestaurantDishRequest;
use App\Http\Requests\V1\UpdateDishRequest;
use App\Http\Resources\DishResource;
use App\Http\Responses\ApiResponse;
use App\Models\Dish;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class RestaurantDishController extends Controller
{
    private const SORTABLE = ['name', 'price', 'created_at'];
    private const PER_PAGE_MAX = 100;

    public function index(Request $request, Restaurant $restaurant): AnonymousResourceCollection
    {
        $request->validate([
            'area' => ['nullable', 'string', Rule::in(config('addiseats.zones', []))],
        ]);

        $sort      = $request->string('sort', 'price')->toString();
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column    = ltrim($sort, '-');

        if (! in_array($column, self::SORTABLE, true)) {
            $column = 'price';
        }

        $perPage = min(
            self::PER_PAGE_MAX,
            max(1, $request->integer('per_page', 15) ?: 15)
        );

        $dishes = $restaurant->dishes()
            ->inArea($request->query('area'))
            ->under($request->integer('max_price'))
            ->fasting($request->boolean('fasting'))
            ->when($request->filled('q'), fn ($qb) =>
                $qb->where('name', 'like', '%' . $request->query('q') . '%'))
            ->orderBy($column, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return DishResource::collection($dishes);
    }

    public function store(StoreRestaurantDishRequest $request, Restaurant $restaurant): JsonResponse
    {
        $validated = $request->validated();
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

    public function update(UpdateDishRequest $request, Dish $dish): JsonResponse
    {
        $dish->update($request->validated());

        return ApiResponse::ok(new DishResource($dish));
    }

    public function destroy(Dish $dish): Response
    {
        $dish->delete();

        return response()->noContent();
    }
}
