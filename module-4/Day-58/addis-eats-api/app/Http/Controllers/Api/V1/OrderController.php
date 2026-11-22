<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Http\Responses\ApiResponse;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    private const PER_PAGE_MAX = 100;

    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'area'   => ['nullable', 'string', Rule::in(config('addiseats.zones', []))],
            'status' => ['nullable', 'string', Rule::enum(OrderStatus::class)],
        ]);

        $perPage = min(
            self::PER_PAGE_MAX,
            max(1, $request->integer('per_page', 15) ?: 15)
        );

        $orders = Order::query()
            ->with(['restaurant', 'lines.dish'])
            ->when($request->filled('area'), fn ($q) =>
                $q->where('area', $request->query('area')))
            ->when($request->filled('status'), fn ($q) =>
                $q->where('status', $request->query('status')))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request, OrderService $orderService): JsonResponse
    {
        $order = $orderService->placeOrder(
            $request->validated(),
            $request->user()?->id
        );

        return ApiResponse::created(
            new OrderResource($order),
            route('v1.orders.show', $order)
        );
    }

    public function show(Order $order): JsonResponse
    {
        $order->load(['restaurant', 'lines.dish']);

        return ApiResponse::ok(new OrderResource($order));
    }
}
