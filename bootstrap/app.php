<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RequireAdventureJson;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // The browser API shares Fortify sessions and CSRF protection through the web group.
            Route::prefix('api/v1')
                ->middleware([RequireAdventureJson::class, 'web'])
                ->group(__DIR__.'/../routes/api.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Preserve story whitespace and JSON values; the creation request normalizes metadata itself.
        $middleware->trimStrings(except: [fn (Request $request): bool => $request->is('api/v1/adventures')]);
        $middleware->convertEmptyStringsToNull(except: [fn (Request $request): bool => $request->is('api/v1/adventures')]);
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpExceptionInterface $exception, Request $request): ?JsonResponse {
            // Keep private, missing and trashed records indistinguishable, including in debug mode.
            if ($request->is('api/v1/*')) {
                return response()
                    ->json([
                        'message' => $exception->getStatusCode() === 404 ? 'Not found.' : $exception->getMessage(),
                    ], $exception->getStatusCode(), $exception->getHeaders());
            }

            return null;
        });
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $request->is('api/*') || $request->expectsJson(),
        );
    })
    ->create();
