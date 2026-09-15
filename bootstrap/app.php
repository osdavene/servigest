<?php

use App\Http\Middleware\InquilinoActivo;
use App\Http\Middleware\VerificarRol;
use App\Http\Middleware\VerificarSesionUnica;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('web', [
            VerificarSesionUnica::class,
        ]);

        $middleware->alias([
            'inquilino.activo' => InquilinoActivo::class,
            'rol' => VerificarRol::class,
            'sesion.unica' => VerificarSesionUnica::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
