<?php

use App\Exceptions\IllegalTransitionException;
use App\Exceptions\OutsideZoneException;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    'validation_failed',
                    'The given data was invalid.',
                    422,
                    $e->errors(),
                );
            }
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    'not_found',
                    'The requested resource does not exist.',
                    404,
                );
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                if ($e->getPrevious() instanceof ModelNotFoundException) {
                    return ApiResponse::error(
                        'not_found',
                        'The requested resource does not exist.',
                        404,
                    );
                }

                return ApiResponse::error(
                    'route_not_found',
                    'The requested endpoint does not exist.',
                    404,
                );
            }
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    'method_not_allowed',
                    'The HTTP method is not supported for this route.',
                    405,
                );
            }
        });

        $exceptions->render(function (BadRequestHttpException|JsonException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    'bad_request',
                    'The request payload could not be parsed.',
                    400,
                );
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    'unauthenticated',
                    'Authentication is required to access this resource.',
                    401,
                );
            }
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    'forbidden',
                    'You are not authorized to perform this action.',
                    403,
                );
            }
        });

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    'rate_limited',
                    'Too many requests. Please slow down.',
                    429,
                );
            }
        });

        $exceptions->render(function (OutsideZoneException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    'outside_delivery_zone',
                    $e->getMessage(),
                    422,
                    ['area' => $e->area],
                );
            }
        });

        $exceptions->render(function (IllegalTransitionException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    'illegal_transition',
                    $e->getMessage(),
                    409,
                    ['from' => $e->from, 'to' => $e->to],
                );
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $ref = 'err_' . Str::random(6);
                Log::error($e->getMessage(), ['ref' => $ref, 'exception' => $e]);

                return ApiResponse::error(
                    'server_error',
                    'Something went wrong.',
                    500,
                    ['ref' => $ref],
                );
            }
        });
    })->create();
