<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Mail\NuovaRichiestaIscrizione;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;

/**
 * Un iscritto al sito (senza tessera) può chiedere di diventare socio CRAL:
 * lascia i dati anagrafici e l'amministratore approva la richiesta.
 */
class RichiestaSocioController extends Controller
{
    public function show(Request $request)
    {
        $utente = $request->user();

        abort_unless($utente->ruolo === 'iscritto', 404);

        return view('socio.richiesta', ['utente' => $utente]);
    }

    public function store(Request $request): RedirectResponse
    {
        $utente = $request->user();

        abort_unless($utente->ruolo === 'iscritto', 404);

        if ($utente->richiede_tessera) {
            return redirect()->route('socio.richiesta');
        }

        $dati = $request->validate([
            'telefono' => ['required', 'string', 'max:255'],
            'data_nascita' => ['required', 'date', 'before:today'],
            'codice_fiscale' => ['required', 'string', 'max:16'],
            'indirizzo' => ['required', 'string', 'max:255'],
            'metodo_pagamento' => ['required', Rule::in(RegisteredUserController::metodiAmmessi())],
        ], [
            'metodo_pagamento.required' => 'Scegli come vuoi pagare la quota di tessera.',
            'metodo_pagamento.in' => 'Questo modo di pagamento non è disponibile: scegline un altro.',
        ]);

        $dati['codice_fiscale'] = strtoupper($dati['codice_fiscale']);

        $utente->fill($dati);
        $utente->richiede_tessera = true;
        $utente->richiesta_il = now();
        $utente->save();

        try {
            Mail::to(config('mail.admin_address'))->send(new NuovaRichiestaIscrizione($utente));
        } catch (\Throwable $e) {
            Log::warning('Invio email richiesta tessera fallito: '.$e->getMessage());
        }

        return redirect()->route('socio.richiesta');
    }
}
