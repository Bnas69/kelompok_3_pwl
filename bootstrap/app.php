<?php

use App\Http\Middleware\CheckSessionTimeout;
use App\Http\Middleware\EnsureHrAuthenticated;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\ThrottleLogins;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'hr.auth' => EnsureHrAuthenticated::class,
            'hr.throttle.login' => ThrottleLogins::class,
            'hr.session.timeout' => CheckSessionTimeout::class,
        ]);
        $middleware->web(append: [
            CheckSessionTimeout::class,
            SecurityHeaders::class,
        ]);
        $middleware->api(append: [
            Illuminate\Cookie\Middleware\EncryptCookies::class,
            Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            Illuminate\Session\Middleware\StartSession::class,
            Illuminate\View\Middleware\ShareErrorsFromSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $exception, Request $request) {
            if ($request->expectsJson() || str_starts_with($request->path(), 'api/')) {
                if ($exception instanceof ValidationException) {
                    return response()->json([
                        'message' => 'Input belum sesuai.',
                        'errors' => $exception->errors(),
                    ], 422);
                }

                $status = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;
                $message = config('app.debug')
                    ? str($exception->getMessage())->limit(500, '')->toString()
                    : 'Terjadi kesalahan. Silakan coba beberapa saat lagi.';

                return response()->json([
                    'message' => 'Gagal memproses permintaan.',
                    'error' => $message,
                ], $status >= 400 ? $status : 500);
            }

            return null;
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! $request->expectsJson()) {
                return response()->view('errors.404', [], 404);
            }
        });
    })
    ->create();
