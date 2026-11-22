<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Http\Responses\ApiResponse;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderStatusController extends Controller
{
    public function __invoke(
        UpdateOrderStatusRequest $request,
        Order $order,
        OrderService $orderService
    ): JsonResponse {
        $target = OrderStatus::from($request->validated('status'));
        $updated = $orderService->transitionStatus($order, $target);

        return ApiResponse::ok(new OrderResource($updated));
    }
}
