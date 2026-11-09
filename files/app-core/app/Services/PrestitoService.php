<?php

namespace App\Services;

use App\Models\Libro;
use App\Models\Prestito;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PrestitoService
{
    /**
     * Registra un prestito per un socio CRAL.
     *
     * Le regole sono controllate dentro una transazione con il libro bloccato,
     * così due soci che premono insieme sull'ultima copia non la prendono
     * entrambi.
     *
     * @throws OperazioneNonConsentita
     */
    public function presta(User $utente, Libro $libro, ?string $note = null, ?int $giorni = null): Prestito
    {
        if (! $utente->isSocioCral()) {
            throw new OperazioneNonConsentita('Il prestito è riservato ai soci CRAL con tessera.');
        }

        $giorni ??= (int) config('cral.prestito.giorni');

        return DB::transaction(function () use ($utente, $libro, $note, $giorni) {
            $libro = Libro::whereKey($libro->getKey())->lockForUpdate()->firstOrFail();

            $inCorsoUtente = Prestito::inCorso()->where('user_id', $utente->id);

            if ((clone $inCorsoUtente)->where('libro_id', $libro->id)->exists()) {
                throw new OperazioneNonConsentita('Hai già in prestito questo libro.');
            }

            $max = (int) config('cral.prestito.max_attivi');

            if ($max > 0 && (clone $inCorsoUtente)->count() >= $max) {
                throw new OperazioneNonConsentita("Puoi avere al massimo {$max} libri in prestito contemporaneamente: restituiscine uno prima di prenderne un altro.");
            }

            if ($libro->prestiti()->where('stato', 'in_corso')->count() >= $libro->copie_totali) {
                throw new OperazioneNonConsentita('Questo libro al momento non è disponibile: tutte le copie sono in prestito.');
            }

            return Prestito::create([
                'libro_id' => $libro->id,
                'user_id' => $utente->id,
                'libro_titolo' => $libro->titolo,
                'libro_codice' => $libro->codice ?: $libro->isbn,
                'utente_nome' => $utente->name,
                'utente_tessera' => $utente->numero_tessera,
                'stato' => 'in_corso',
                'data_prestito' => today(),
                'scadenza' => today()->addDays($giorni),
                'note' => filled($note) ? $note : null,
            ]);
        });
    }
}
