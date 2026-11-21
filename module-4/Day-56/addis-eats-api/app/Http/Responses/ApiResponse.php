<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function ok(mixed $data, array $meta = []): JsonResponse
    {
        return response()->json(array_filter([
            'data' => $data,
            'meta' => $meta ?: null,
        ], fn (mixed $value) => $value !== null));
    }

    public static function created(mixed $data, string $location): JsonResponse
    {
        return response()->json(['data' => $data], 201)
            ->header('Location', $location);
    }

    public static function error(string $code, string $message, int $status, array $meta = []): JsonResponse
    {
        return response()->json([
            'error' => compact('code', 'message', 'meta'),
        ], $status);
    }
}