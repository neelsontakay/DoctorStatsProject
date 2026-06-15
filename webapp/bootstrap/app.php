<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            \Illuminate\Support\Facades\Route::prefix('internal/v1')
                ->group(function (): void {
                    \Illuminate\Support\Facades\Route::post(
                        'webhooks/zoho',
                        [\App\Http\Controllers\Internal\ZohoWebhookController::class, 'store'],
                    );
                });

            \Illuminate\Support\Facades\Route::middleware([
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
                \App\Http\Middleware\VerifyServiceToken::class,
            ])
                ->prefix('internal/v1')
                ->group(base_path('routes/internal.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->append(\App\Http\Middleware\AddSecurityHeaders::class);

        $middleware->alias([
            'org.member' => \App\Http\Middleware\EnsureOrgMember::class,
            'org.admin' => \App\Http\Middleware\EnsureOrgAdmin::class,
            'service.token' => \App\Http\Middleware\VerifyServiceToken::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e): bool => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
