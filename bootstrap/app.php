<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureCourseMembership;
use App\Http\Middleware\VerifyCrowdApiToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // Token-gated machine API. Registered here (rather than via the
            // default `api:` group) so it carries no session/CSRF and no
            // dependency on an "api" rate limiter. Routes resolve their own
            // models, so SubstituteBindings isn't required.
            Route::middleware(VerifyCrowdApiToken::class)
                ->prefix('api/crowd')
                ->group(__DIR__.'/../routes/api.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: ['kontakt']);
        $middleware->alias([
            'course' => EnsureCourseMembership::class,
            'admin' => EnsureAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
