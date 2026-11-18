<?php

namespace App\Http\Controllers;

use App\Models\Attivita;
use App\Models\IscrizioneAttivita;
use App\Services\IscrizioneAttivitaService;
use App\Services\OperazioneNonConsentita;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Pagamento della quota di un'attività, dopo l'iscrizione automatica. Stessi modi
 * della tessera:
 *
 * - metodo(): sceglie (o cambia) contanti / bonifico / PayPal, finché non ha
 *   segnalato di aver pagato;
 * - bonificoEffettuato(): "ho fatto il bonifico", avvisa gli amministratori;
 * - ritorno(): PayPal riporta qui la persona (la conferma vera dell'incasso arriva
 *   dalla notifica automatica, vedi PaypalIpnController).
 */
class PagamentoAttivitaController extends Controller
{
    public function __construct(private IscrizioneAttivitaService $servizio)
    {
    }

    public function metodo(Request $request, string $slug): RedirectResponse
    {
        [$attivita, $iscrizione] = $this->iscrizione($request, $slug);

        $dati = $request->validate([
            'metodo_pagamento' => ['required', Rule::in(array_keys(\App\Models\User::metodiPagamento()))],
        ]);

        try {
            $this->servizio->scegliMetodo($iscrizione, $dati['metodo_pagamento']);
        } catch (OperazioneNonConsentita $e) {
            return $this->torna($attivita)->with('errore_iscrizione', $e->getMessage());
        }

        return $this->torna($attivita);
    }

    public function bonificoEffettuato(Request $request, string $slug): RedirectResponse
    {
        [$attivita, $iscrizione] = $this->iscrizione($request, $slug);

        if ($iscrizione->metodo_pagamento !== 'bonifico' || ! $iscrizione->puoScegliereMetodo()) {
            return $this->torna($attivita);
        }

        try {
            $this->servizio->segnalaPagamento($iscrizione, 'bonifico');
        } catch (OperazioneNonConsentita $e) {
            return $this->torna($attivita)->with('errore_iscrizione', $e->getMessage());
        }

        return $this->torna($attivita)->with('ok_iscrizione', 'Grazie! Abbiamo avvisato gli amministratori: controlleranno l\'accredito del bonifico.');
    }

    public function ritorno(Request $request, string $slug): RedirectResponse
    {
        [$attivita, $iscrizione] = $this->iscrizione($request, $slug);

        if ($iscrizione->pagato) {
            return $this->torna($attivita)->with('ok_iscrizione', 'Pagamento ricevuto, grazie! La tua quota è saldata.');
        }

        if (! $iscrizione->puoScegliereMetodo()) {
            return $this->torna($attivita);
        }

        // PayPal non ha ancora confermato (di solito pochi minuti): si registra che la
        // persona dice di aver pagato e gli amministratori verificano l'incasso.
        $this->servizio->segnalaPagamento($iscrizione, 'paypal');

        return $this->torna($attivita)->with('ok_iscrizione', 'Grazie! Stiamo verificando il pagamento: la conferma arriva a breve.');
    }

    /**
     * @return array{0: Attivita, 1: IscrizioneAttivita}
     */
    private function iscrizione(Request $request, string $slug): array
    {
        $attivita = Attivita::where('slug', $slug)->where('pubblicata', true)->firstOrFail();

        $iscrizione = $attivita->iscrizioni()
            ->with('attivita')
            ->where('user_id', $request->user()->id)
            ->confermate()
            ->firstOrFail();

        return [$attivita, $iscrizione];
    }

    private function torna(Attivita $attivita): RedirectResponse
    {
        return redirect(route('attivita.show', $attivita->slug).'#iscrizione');
    }
}
