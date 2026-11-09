<?php

namespace App\Services;

use App\Models\Attivita;
use App\Models\IscrizioneAttivita;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class IscrizioneAttivitaService
{
    /**
     * Iscrive un socio CRAL (con tessera) a un'attività.
     *
     * @throws OperazioneNonConsentita
     */
    public function iscrivi(User $utente, Attivita $attivita): IscrizioneAttivita
    {
        if (! $utente->isSocioCral()) {
            throw new OperazioneNonConsentita('Solo i soci CRAL con tessera possono iscriversi alle attività.');
        }

        return DB::transaction(function () use ($utente, $attivita) {
            // Si rilegge l'attività bloccandola: i posti si contano senza che
            // nessun altro possa iscriversi nello stesso istante.
            $attivita = Attivita::whereKey($attivita->getKey())->lockForUpdate()->firstOrFail();

            if ($attivita->iscrizioni()->where('user_id', $utente->id)->exists()) {
                throw new OperazioneNonConsentita('Sei già iscritto a questa attività.');
            }

            $attivita->unsetRelation('iscrizioni');
            $attivita->setAttribute('iscrizioni_count', $attivita->iscrizioni()->count());

            match ($attivita->statoIscrizioni()) {
                'aperte' => null,
                'non_ancora' => throw new OperazioneNonConsentita('Le iscrizioni non sono ancora aperte.'),
                'chiuse' => throw new OperazioneNonConsentita('Le iscrizioni a questa attività sono chiuse.'),
                'esaurite' => throw new OperazioneNonConsentita('Posti esauriti.'),
                'terminata' => throw new OperazioneNonConsentita('Questa attività si è già conclusa.'),
                default => throw new OperazioneNonConsentita('Per questa attività non sono previste iscrizioni.'),
            };

            return IscrizioneAttivita::create([
                'user_id' => $utente->id,
                'attivita_id' => $attivita->id,
                'importo' => $attivita->haPrezzo() ? $attivita->prezzo : null,
            ]);
        });
    }

    /**
     * Annulla l'iscrizione. Non è possibile se è già stata pagata (in quel caso
     * serve contattare il CRAL per il rimborso) o se l'attività è conclusa.
     *
     * @throws OperazioneNonConsentita
     */
    public function annulla(User $utente, Attivita $attivita): void
    {
        $iscrizione = $attivita->iscrizioni()->where('user_id', $utente->id)->first();

        if (! $iscrizione) {
            throw new OperazioneNonConsentita('Non risulti iscritto a questa attività.');
        }

        if ($iscrizione->pagato) {
            throw new OperazioneNonConsentita('Hai già pagato la quota: per annullare l\'iscrizione contatta il CRAL.');
        }

        if ($attivita->isPassata()) {
            throw new OperazioneNonConsentita('Questa attività si è già conclusa.');
        }

        $iscrizione->delete();
    }
}
