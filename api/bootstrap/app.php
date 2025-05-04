<?php

use App\Helpers\ApiResponse;
use App\Helpers\Logger;
use App\Helpers\ResponseCodes;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api'
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'authorized' => \App\Http\Middleware\EnsureHasPermission::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $exception) {
            if ($exception instanceof AuthenticationException) {
                return ApiResponse::notAuthenticated();
            }
            if ($exception instanceof NotFoundHttpException) {
                return ApiResponse::resourceNotFound();
            }
            if ($exception instanceof HttpException && $exception->getMessage() == 'CSRF token mismatch.') {
                return ApiResponse::notAuthenticated('CSRF Token Mismatch', ResponseCodes::CSRF_TOKEN_MISMATCH);
            }

            Logger::error($exception->getMessage(), $exception);
            return ApiResponse::generalError();
        });
    })->create();
