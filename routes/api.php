<?php

use App\Http\Controllers\Api\V1\AdventureController;
use App\Models\Adventure;
use Illuminate\Support\Facades\Route;

// These private author endpoints require a session; ownership is enforced by the policy.
Route::middleware('auth')
    ->name('api.v1.')
    ->group(function (): void {
        Route::apiResource('adventures', AdventureController::class)
            ->only(['index', 'store', 'show'])
            ->middlewareFor('index', 'can:viewAny,'.Adventure::class)
            ->middlewareFor('store', 'can:create,'.Adventure::class)
            ->middlewareFor('show', 'can:view,adventure')
            ->whereNumber('adventure');
    });
