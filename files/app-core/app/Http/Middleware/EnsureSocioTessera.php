<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lascia passare solo i soci CRAL con tessera (e l'amministratore). Gli
 * iscritti al sito vengono riportati alla loro area con un avviso chiaro.
 */
class EnsureSocioTessera
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isSocioCral()) {
            return redirect()->route('dashboard')->with('avviso', 'Questa sezione è riservata ai soci CRAL con tessera.');
        }

        return $next($request);
    }
}
