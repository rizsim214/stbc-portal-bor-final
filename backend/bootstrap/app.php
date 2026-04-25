<?php

use App\Modules\Auth\Middleware\EnsureUserHasRole;
use App\Modules\Shared\Exceptions\ApiException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ApiException $exception, Request $request) {
            return response()->json([
                'message' => $exception->getMessage(),
                'error_code' => $exception->errorCode(),
                'context' => $exception->context(),
            ], $exception->statusCode());
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error_code' => 'UNAUTHENTICATED',
            ], 401);
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            return response()->json([
                'message' => $exception->getMessage() ?: 'Forbidden.',
                'error_code' => 'FORBIDDEN',
            ], 403);
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) {
            return response()->json([
                'message' => 'Resource not found.',
                'error_code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        });

        $exceptions->render(function (ValidationException $exception, Request $request) {
            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => $exception->errors(),
                'error_code' => 'VALIDATION_ERROR',
            ], $exception->status);
        });

        $exceptions->render(function (HttpException $exception, Request $request) {
            if (!$request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => $exception->getMessage() ?: 'HTTP error.',
                'error_code' => 'HTTP_ERROR',
            ], $exception->getStatusCode());
        });

        $exceptions->render(function (\Throwable $exception, Request $request) {
            if (!$request->is('api/*')) {
                return null;
            }

            report($exception);

            return response()->json([
                'message' => 'Server error.',
                'error_code' => 'SERVER_ERROR',
            ], 500);
        });
    })->create();
