<?php

use App\Http\Middleware\CheckRole;
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
        // Daftarkan alias middleware 'role'
        $middleware->alias([
            'role' => CheckRole::class,
        ]);

        // Beritahu Laravel ke mana redirect jika belum login
        $middleware->redirectGuestsTo(fn () => route('login'));

        // Beritahu Laravel ke mana redirect jika sudah login (buka /login lagi)
        $middleware->redirectUsersTo(fn () => route('karyawan.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
