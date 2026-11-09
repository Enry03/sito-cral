<?php

namespace App\Http\Controllers;

use App\Models\Attivita;
use App\Services\IscrizioneAttivitaService;
use App\Services\OperazioneNonConsentita;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
            ->withCount('iscrizioni')
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

        try {
            $servizio->iscrivi($request->user(), $attivita);
        } catch (OperazioneNonConsentita $e) {
            return redirect(route('attivita.show', $slug).'#iscrizione')->with('errore_iscrizione', $e->getMessage());
        }

        return redirect(route('attivita.show', $slug).'#iscrizione')->with('ok_iscrizione', 'Iscrizione registrata: ci vediamo all\'evento!');
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
}
