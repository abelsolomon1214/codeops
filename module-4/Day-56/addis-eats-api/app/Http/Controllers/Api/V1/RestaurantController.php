<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RestaurantResource;
use App\Http\Responses\ApiResponse;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class RestaurantController extends Controller
{
    private const SORTABLE = ['name', 'area', 'created_at'];

    public function index(Request $request): JsonResponse
    {
        $sort      = $request->string('sort', 'name')->toString();
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column    = ltrim($sort, '-');

        if (! in_array($column, self::SORTABLE, true)) {
            $column = 'name';
        }

        $restaurants = Restaurant::query()
            ->withCount('dishes')
            ->when($request->filled('area'), fn ($q) =>
                $q->where('area', $request->query('area')))
            ->when($request->filled('q'), fn ($q) =>
                $q->where('name', 'like', '%' . $request->query('q') . '%'))
            ->when($request->query('include') === 'dishes', fn ($q) =>
                $q->with('dishes'))
            ->orderBy($column, $direction)
            ->get();

        return ApiResponse::ok(
            RestaurantResource::collection($restaurants),
            array_filter([
                'count' => $restaurants->count(),
                'area'  => $request->query('area'),
            ])
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'slug'          => 'required|string|max:255|alpha_dash|unique:restaurants,slug',
            'area'          => 'required|string|max:100',
            'phone'         => 'required|string|max:30',
            'opening_hours' => 'required|string|max:100',
        ]);

        $restaurant = Restaurant::create($validated);
        $restaurant->loadCount('dishes');

        return ApiResponse::created(
            new RestaurantResource($restaurant),
            route('v1.restaurants.show', $restaurant)
        );
    }

    public function show(Request $request, Restaurant $restaurant): JsonResponse
    {
        $restaurant->loadCount('dishes');

        if ($request->query('include') === 'dishes') {
            $restaurant->load('dishes');
        }

        return ApiResponse::ok(new RestaurantResource($restaurant));
    }

    public function update(Request $request, Restaurant $restaurant): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'sometimes|required|string|max:255',
            'slug'          => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('restaurants', 'slug')->ignore($restaurant->id),
            ],
            'area'          => 'sometimes|required|string|max:100',
            'phone'         => 'sometimes|required|string|max:30',
            'opening_hours' => 'sometimes|required|string|max:100',
        ]);

        $restaurant->update($validated);
        $restaurant->loadCount('dishes');

        return ApiResponse::ok(new RestaurantResource($restaurant));
    }

    public function destroy(Restaurant $restaurant): Response
    {
        $restaurant->delete();

        return response()->noContent();
    }
}