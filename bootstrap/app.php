<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'socio.attivo' => \App\Http\Middleware\EnsureSocioAttivo::class,
        ]);

        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

// Su hosting senza SSH l'app viene caricata da una sottocartella mentre
// la cartella pubblica (ex "public/") vive un livello sopra, alla radice
// FTP. Se non troviamo una cartella "public" accanto all'app, siamo in
// quel layout: reindirizziamo Laravel a usare la cartella superiore.
if (! is_dir($app->basePath('public'))) {
    $app->usePublicPath(dirname($app->basePath()));
}

return $app;
