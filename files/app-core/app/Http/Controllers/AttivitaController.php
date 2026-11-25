<?php

namespace App\Http\Controllers;

use App\Models\Attivita;
use App\Models\IscrizioneAttivita;
use App\Services\IscrizioneAttivitaService;
use App\Services\OperazioneNonConsentita;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AttivitaController extends Controller
{
    public function index()
    {
        // Le attività in programma sono poche: si ordinano per la prossima data
        // utile (anche per quelle ricorrenti). Le passate si sfogliano a pagine.
        $inProgramma = Attivita::where('pubblicata', true)
            ->inProgramma()
            ->get()
            ->sortBy(fn (Attivita $a) => $a->dataOrdinamento()?->timestamp ?? PHP_INT_MAX)
            ->values();

        $passate = Attivita::where('pubblicata', true)
            ->passate()
            ->orderByDesc('data_ultima')
            ->orderByDesc('data_evento')
            ->paginate(9)
            ->withQueryString();

        return view('attivita.index', compact('inProgramma', 'passate'));
    }

    public function show(Request $request, string $slug)
    {
        $attivita = Attivita::where('slug', $slug)
            ->where('pubblicata', true)
            ->withCount('iscrizioniConfermate')
            ->firstOrFail();

        $fotoSingole = $attivita->foto()->pubbliche()->sciolte()->oldest()->get();

        $album = $attivita->album()
            ->has('foto')
            ->with('copertinaFoto')
            ->withCount('foto')
            ->oldest()
            ->get();

        $utente = $request->user();
        $iscrizione = $utente
            ? $attivita->iscrizioni()->where('user_id', $utente->id)->first()
            : null;

        return view('attivita.show', [
            'attivita' => $attivita,
            'fotoSingole' => $fotoSingole,
            'album' => $album,
            'statoIscrizioni' => $attivita->statoIscrizioni(),
            'iscrizione' => $iscrizione,
            'accesso' => (bool) $utente?->puoVedereFoto(),
        ]);
    }

    public function partecipa(Request $request, string $slug, IscrizioneAttivitaService $servizio): RedirectResponse
    {
        $attivita = Attivita::where('slug', $slug)->where('pubblicata', true)->firstOrFail();

        $dovuto = ($attivita->prezzoPer($request->user()) ?? 0) > 0;
        $validatore = $this->validatoreRisposte($request, $attivita, $dovuto);

        if ($validatore->fails()) {
            return redirect(route('attivita.show', $slug).'#iscrizione')->withErrors($validatore)->withInput();
        }

        try {
            $iscrizione = $servizio->iscrivi($request->user(), $attivita, $this->risposte($request), $dovuto ? $request->string('metodo_pagamento')->toString() : null);
        } catch (OperazioneNonConsentita $e) {
            return redirect(route('attivita.show', $slug).'#iscrizione')->with('errore_iscrizione', $e->getMessage())->withInput();
        }

        return redirect(route('attivita.show', $slug).'#iscrizione')->with(
            'ok_iscrizione',
            $iscrizione->richiedePagamento()
                ? 'Sei iscritto! Ora completa il pagamento della quota.'
                : 'Sei iscritto! Ti aspettiamo.',
        );
    }

    /** La persona corregge le risposte date al modulo di iscrizione. */
    public function aggiornaRisposte(Request $request, string $slug, IscrizioneAttivitaService $servizio): RedirectResponse
    {
        $attivita = Attivita::where('slug', $slug)->where('pubblicata', true)->firstOrFail();

        $validatore = $this->validatoreRisposte($request, $attivita);

        if ($validatore->fails()) {
            return redirect(route('attivita.show', $slug).'#iscrizione')->withErrors($validatore)->withInput();
        }

        try {
            $servizio->aggiornaRisposte($request->user(), $attivita, $this->risposte($request));
        } catch (OperazioneNonConsentita $e) {
            return redirect(route('attivita.show', $slug).'#iscrizione')->with('errore_iscrizione', $e->getMessage())->withInput();
        }

        return redirect(route('attivita.show', $slug).'#iscrizione')->with('ok_iscrizione', 'Le tue risposte sono state aggiornate.');
    }

    public function annullaPartecipazione(Request $request, string $slug, IscrizioneAttivitaService $servizio): RedirectResponse
    {
        $attivita = Attivita::where('slug', $slug)->where('pubblicata', true)->firstOrFail();

        try {
            $servizio->annulla($request->user(), $attivita);
        } catch (OperazioneNonConsentita $e) {
            return redirect(route('attivita.show', $slug).'#iscrizione')->with('errore_iscrizione', $e->getMessage());
        }

        return redirect(route('attivita.show', $slug).'#iscrizione')->with('ok_iscrizione', 'La tua iscrizione è stata annullata.');
    }

    private function validatoreRisposte(Request $request, Attivita $attivita, bool $conPagamento = false)
    {
        $dati = ['risposte' => $this->risposte($request), 'metodo_pagamento' => $request->input('metodo_pagamento')];
        $regole = $attivita->regoleRisposte();
        $messaggi = [];

        if ($conPagamento) {
            $regole['metodo_pagamento'] = ['required', 'string', Rule::in(IscrizioneAttivita::metodiAmmessi())];
            $messaggi = [
                'metodo_pagamento.required' => 'Scegli come vuoi pagare la quota: il pagamento è obbligatorio.',
                'metodo_pagamento.in' => 'Questo modo di pagamento non è disponibile.',
            ];
        }

        return Validator::make($dati, $regole, $messaggi, $attivita->nomiRisposte());
    }

    /** @return array<string, mixed> */
    private function risposte(Request $request): array
    {
        $risposte = $request->input('risposte', []);

        return is_array($risposte) ? $risposte : [];
    }
}
