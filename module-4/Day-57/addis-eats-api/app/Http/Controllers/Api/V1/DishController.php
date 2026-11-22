<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DishResource;
use App\Http\Responses\ApiResponse;
use App\Models\Dish;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class DishController extends Controller
{
    private const SORTABLE = ['name', 'price', 'created_at'];
    private const PER_PAGE_MAX = 100;

    public function index(Request $request): AnonymousResourceCollection
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

        $dishes = Dish::query()
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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'restaurant_id' => 'required|integer|exists:restaurants,id',
            'price'         => 'required|integer|min:100',
            'is_fasting'    => 'required|boolean',
            'area'          => ['required', 'string', Rule::in(config('addiseats.zones', []))],
        ], [
            'price.min' => 'The price must be a positive amount of at least 100 santim (1.00 ETB).',
        ]);

        $dish = Dish::create($validated);

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
            'area'          => ['sometimes', 'required', 'string', Rule::in(config('addiseats.zones', []))],
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
