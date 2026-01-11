<?php

use Illuminate\Session\Middleware\StartSession;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Application;
use App\Http\Middleware\TokenToHdr;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\CheckIsAdmin;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: "",
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'logout',
        ]);
        $middleware->api(prepend: [TokenToHdr::class]);
        $middleware->api(append: [CheckIsAdmin::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
