<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Mail\PagamentoTesseraDichiarato;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

/**
 * Pagamento della quota di tessera.
 *
 * - ritorno(): PayPal riporta qui l'utente dopo il pagamento. L'incasso vero lo
 *   conferma la notifica automatica di PayPal (vedi PaypalIpnController): qui si
 *   registra solo che l'utente dice di aver pagato, se la conferma non è ancora arrivata.
 * - metodo(): chi ha una richiesta aperta può cambiare idea sul modo di pagamento.
 */
class PagamentoTesseraController extends Controller
{
    public function ritorno(Request $request): RedirectResponse
    {
        $utente = $request->user();
        $destinazione = $this->destinazione($request);

        if ($utente->quota_pagata && $utente->pagatoConPaypal()) {
            return redirect()->route($destinazione)->with('ok_pagamento', 'Pagamento ricevuto, grazie! Un amministratore approverà la tua tessera a breve.');
        }

        if (! $utente->puoPagareTessera()) {
            return redirect()->route($destinazione);
        }

        $utente->update(['pagamento_dichiarato_il' => now(), 'metodo_pagamento' => 'paypal']);

        try {
            Mail::to(config('mail.admin_address'))->send(new PagamentoTesseraDichiarato($utente));
        } catch (\Throwable $e) {
            Log::warning('Invio email pagamento tessera fallito: '.$e->getMessage());
        }

        return redirect()->route($destinazione)->with('ok_pagamento', 'Grazie! Abbiamo avvisato gli amministratori: verificheranno il pagamento e approveranno la tua tessera.');
    }

    public function metodo(Request $request): RedirectResponse
    {
        $utente = $request->user();

        if (! $utente->chiedeTessera() || $utente->quota_pagata) {
            return redirect()->route($this->destinazione($request));
        }

        $dati = $request->validate([
            'metodo_pagamento' => ['required', Rule::in(RegisteredUserController::metodiAmmessi())],
        ]);

        $utente->update(['metodo_pagamento' => $dati['metodo_pagamento']]);

        return redirect()->route($this->destinazione($request))->with('ok_pagamento', 'Modo di pagamento aggiornato.');
    }

    private function destinazione(Request $request): string
    {
        return $request->user()->ruolo === 'socio' ? 'attesa-approvazione' : 'socio.richiesta';
    }
}
