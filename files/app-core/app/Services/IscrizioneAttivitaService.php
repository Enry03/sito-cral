<?php

namespace App\Services;

use App\Mail\IscrizioneAttivitaEsito;
use App\Mail\NuovaIscrizioneAttivita;
use App\Models\Attivita;
use App\Models\IscrizioneAttivita;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Iscrizioni alle attività (tornei, gite, uscite...). Passano sempre
 * dall'amministratore:
 *
 * 1. chi ha un account approvato manda la richiesta (richiedi): nessun dato da
 *    compilare, nasce "in attesa" e gli amministratori ricevono un'email;
 * 2. l'amministratore la conferma (conferma) o la declina (rifiuta): l'utente
 *    riceve un'email con l'esito;
 * 3. solo le iscrizioni confermate occupano un posto e, se c'è una quota, danno
 *    accesso al pagamento.
 */
class IscrizioneAttivitaService
{
    /**
     * Manda la richiesta di iscrizione. Possono farlo iscritti al sito, soci CRAL
     * e amministratori (cioè chi ha un account approvato).
     *
     * @throws OperazioneNonConsentita
     */
    public function richiedi(User $utente, Attivita $attivita): IscrizioneAttivita
    {
        if (! $utente->haAccessoSito()) {
            throw new OperazioneNonConsentita('Per iscriverti a un\'attività serve un account approvato.');
        }

        $iscrizione = DB::transaction(function () use ($utente, $attivita) {
            // Si rilegge l'attività bloccandola: i posti si contano senza che
            // nessun altro possa richiedere nello stesso istante.
            $attivita = Attivita::whereKey($attivita->getKey())->lockForUpdate()->firstOrFail();

            if ($esistente = $attivita->iscrizioni()->where('user_id', $utente->id)->first()) {
                throw new OperazioneNonConsentita(match ($esistente->stato) {
                    IscrizioneAttivita::CONFERMATA => 'Sei già iscritto a questa attività.',
                    IscrizioneAttivita::RIFIUTATA => 'La tua richiesta per questa attività non è stata accolta.',
                    default => 'Hai già mandato la richiesta: attendi la conferma di un amministratore.',
                });
            }

            $attivita->unsetRelation('iscrizioni');
            $attivita->setAttribute('iscrizioni_confermate_count', $attivita->iscrizioni()->confermate()->count());

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
                'stato' => IscrizioneAttivita::IN_ATTESA,
                'importo' => $attivita->haPrezzo() ? $attivita->prezzo : null,
            ]);
        });

        $this->invia(config('mail.admin_address'), new NuovaIscrizioneAttivita($iscrizione->load(['user', 'attivita'])));

        return $iscrizione;
    }

    /**
     * Conferma la richiesta (o la riapre se era stata declinata per errore).
     *
     * @return array{iscrizione: IscrizioneAttivita, email_inviata: bool}
     *
     * @throws OperazioneNonConsentita
     */
    public function conferma(IscrizioneAttivita $iscrizione): array
    {
        $iscrizione = DB::transaction(function () use ($iscrizione) {
            $iscrizione = IscrizioneAttivita::whereKey($iscrizione->getKey())->lockForUpdate()->firstOrFail();

            if ($iscrizione->confermata()) {
                throw new OperazioneNonConsentita('Questa iscrizione è già confermata.');
            }

            $attivita = Attivita::whereKey($iscrizione->attivita_id)->lockForUpdate()->firstOrFail();
            $attivita->setAttribute('iscrizioni_confermate_count', $attivita->iscrizioni()->confermate()->count());

            if ($attivita->postiEsauriti()) {
                throw new OperazioneNonConsentita('I posti sono esauriti: per confermare questa iscrizione aumenta i posti disponibili o declina un\'altra richiesta.');
            }

            $iscrizione->update(['stato' => IscrizioneAttivita::CONFERMATA, 'gestita_il' => now()]);

            return $iscrizione;
        });

        $iscrizione->load(['user', 'attivita']);

        return ['iscrizione' => $iscrizione, 'email_inviata' => $this->invia($iscrizione->user->email, new IscrizioneAttivitaEsito($iscrizione))];
    }

    /**
     * Declina la richiesta. Una iscrizione già pagata non si declina da qui: va
     * prima gestito il rimborso.
     *
     * @return array{iscrizione: IscrizioneAttivita, email_inviata: bool}
     *
     * @throws OperazioneNonConsentita
     */
    public function rifiuta(IscrizioneAttivita $iscrizione): array
    {
        $iscrizione = DB::transaction(function () use ($iscrizione) {
            $iscrizione = IscrizioneAttivita::whereKey($iscrizione->getKey())->lockForUpdate()->firstOrFail();

            if ($iscrizione->rifiutata()) {
                throw new OperazioneNonConsentita('Questa richiesta è già stata declinata.');
            }

            if ($iscrizione->pagato) {
                throw new OperazioneNonConsentita('La quota risulta già pagata: gestisci prima il rimborso, poi elimina l\'iscrizione.');
            }

            $iscrizione->update(['stato' => IscrizioneAttivita::RIFIUTATA, 'gestita_il' => now()]);

            return $iscrizione;
        });

        $iscrizione->load(['user', 'attivita']);

        return ['iscrizione' => $iscrizione, 'email_inviata' => $this->invia($iscrizione->user->email, new IscrizioneAttivitaEsito($iscrizione))];
    }

    /**
     * Annulla la propria richiesta o iscrizione. Non è possibile se la quota è già
     * stata pagata (serve contattare il CRAL per il rimborso) o se l'attività è
     * conclusa.
     *
     * @throws OperazioneNonConsentita
     */
    public function annulla(User $utente, Attivita $attivita): void
    {
        $iscrizione = $attivita->iscrizioni()->where('user_id', $utente->id)->first();

        if (! $iscrizione) {
            throw new OperazioneNonConsentita('Non hai nessuna iscrizione a questa attività.');
        }

        if ($iscrizione->rifiutata()) {
            throw new OperazioneNonConsentita('La tua richiesta non è stata accolta: non c\'è nulla da annullare.');
        }

        if ($iscrizione->pagato) {
            throw new OperazioneNonConsentita('Hai già pagato la quota: per annullare l\'iscrizione contatta il CRAL.');
        }

        if ($attivita->isPassata()) {
            throw new OperazioneNonConsentita('Questa attività si è già conclusa.');
        }

        $iscrizione->delete();
    }

    /**
     * Un errore di invio non deve mai bloccare l'operazione già fatta.
     */
    private function invia(?string $destinatario, $mail): bool
    {
        if (blank($destinatario)) {
            return false;
        }

        try {
            Mail::to($destinatario)->send($mail);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Invio email iscrizione attività fallito: '.$e->getMessage());

            return false;
        }
    }
}
