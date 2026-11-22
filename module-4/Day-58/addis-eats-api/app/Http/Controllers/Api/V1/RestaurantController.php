<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreRestaurantRequest;
use App\Http\Requests\V1\UpdateRestaurantRequest;
use App\Http\Resources\RestaurantResource;
use App\Http\Responses\ApiResponse;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class RestaurantController extends Controller
{
    private const SORTABLE = ['name', 'area', 'created_at'];
    private const PER_PAGE_MAX = 100;

    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'area' => ['nullable', 'string', Rule::in(config('addiseats.zones', []))],
        ]);

        $sort      = $request->string('sort', 'name')->toString();
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column    = ltrim($sort, '-');

        if (! in_array($column, self::SORTABLE, true)) {
            $column = 'name';
        }

        $perPage = min(
            self::PER_PAGE_MAX,
            max(1, $request->integer('per_page', 15) ?: 15)
        );

        $restaurants = Restaurant::query()
            ->withCount('dishes')
            ->when($request->filled('area'), fn ($q) =>
                $q->where('area', $request->query('area')))
            ->when($request->filled('q'), fn ($q) =>
                $q->where('name', 'like', '%' . $request->query('q') . '%'))
            ->when($request->query('include') === 'dishes', fn ($q) =>
                $q->with('dishes'))
            ->orderBy($column, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return RestaurantResource::collection($restaurants);
    }

    public function store(StoreRestaurantRequest $request): JsonResponse
    {
        $restaurant = Restaurant::create($request->validated());
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

    public function update(UpdateRestaurantRequest $request, Restaurant $restaurant): JsonResponse
    {
        $restaurant->update($request->validated());
        $restaurant->loadCount('dishes');

        return ApiResponse::ok(new RestaurantResource($restaurant));
    }

    public function destroy(Restaurant $restaurant): Response
    {
        $restaurant->delete();

        return response()->noContent();
    }
}
