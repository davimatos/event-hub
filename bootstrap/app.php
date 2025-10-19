<?php

use App\Modules\Shared\Application\Exceptions\Contract\ApplicationException;
use App\Modules\Shared\Domain\Exceptions\Contract\DomainException;
use App\Modules\Shared\Infra\Exceptions\Contract\InfrastructureException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\App;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->use([
            \Illuminate\Http\Middleware\TrustHosts::class,
            \Illuminate\Http\Middleware\TrustProxies::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);

        $middleware->group('api', [
            'throttle:api',
        ]);

        $middleware->alias([
            'user.organizer' => \App\Framework\Http\Middleware\OrganizerUserMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->dontReport([
            DomainException::class,
            ApplicationException::class,
        ]);

        $exceptions->renderable(function (DomainException|ApplicationException|InfrastructureException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                ...$e->getContext() ? ['errors' => $e->getContext()] : [],
            ], $e->getStatusCode());
        });

        if (App::isProduction()) {
            $exceptions->renderable(function (\Throwable $e) {
                return response()->json([
                    'message' => 'Ocorreu um erro inesperado. Tente novamente mais tarde.',
                ], 500);
            });
        }

    })->create();
