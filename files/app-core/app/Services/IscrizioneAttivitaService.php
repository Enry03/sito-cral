<?php

namespace App\Services;

use App\Mail\IscrizioneAttivitaEsito;
use App\Mail\NuovaIscrizioneAttivita;
use App\Mail\PagamentoIscrizioneSegnalato;
use App\Models\Attivita;
use App\Models\IscrizioneAttivita;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Iscrizioni alle attività (tornei, gite, uscite...).
 *
 * - Chi ha un account approvato (iscritto al sito o socio) si iscrive da solo con
 *   un clic, in automatico: nessun dato da compilare e nessuna conferma. Gli
 *   amministratori ricevono solo un'email informativa.
 * - Se c'è una quota la persona sceglie subito come pagare (contanti, bonifico,
 *   PayPal) dalla pagina dell'attività.
 * - L'amministratore può escludere qualcuno (rifiuta) o riammetterlo (conferma).
 */
class IscrizioneAttivitaService
{
    /**
     * Iscrive la persona all'attività. Possono farlo iscritti al sito, soci CRAL e
     * amministratori (cioè chi ha un account approvato).
     *
     * Se l'attività ha domande nel modulo di iscrizione, le risposte (già validate
     * dal controller, nella forma codice => risposta) vengono salvate con l'iscrizione.
     * L'importo è quello che spetta alla persona (quota ridotta se è socio CRAL) e il pagamento
     * è obbligatorio: con una quota da pagare serve scegliere subito come pagarla.
     *
     * @param  array<string, mixed>  $risposte
     *
     * @throws OperazioneNonConsentita
     */
    public function iscrivi(User $utente, Attivita $attivita, array $risposte = [], ?string $metodo = null): IscrizioneAttivita
    {
        if (! $utente->haAccessoSito()) {
            throw new OperazioneNonConsentita('Per iscriverti a un\'attività serve un account approvato.');
        }

        $iscrizione = DB::transaction(function () use ($utente, $attivita, $risposte, $metodo) {
            // Si rilegge l'attività bloccandola: i posti si contano senza che
            // nessun altro possa iscriversi nello stesso istante.
            $attivita = Attivita::whereKey($attivita->getKey())->lockForUpdate()->firstOrFail();

            if ($esistente = $attivita->iscrizioni()->where('user_id', $utente->id)->first()) {
                throw new OperazioneNonConsentita(match ($esistente->stato) {
                    IscrizioneAttivita::RIFIUTATA => 'Non puoi iscriverti a questa attività: per informazioni scrivi a '.config('mail.admin_address').'.',
                    default => 'Sei già iscritto a questa attività.',
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

            $date = $attivita->preparaRisposte($risposte);
            $mancanti = $attivita->campi()->filter(fn (array $c) => $c['obbligatorio'] && ! in_array($c['id'], array_column($date, 'id'), true));

            if ($mancanti->isNotEmpty()) {
                throw new OperazioneNonConsentita('Rispondi a tutte le domande obbligatorie: manca «'.$mancanti->first()['etichetta'].'».');
            }

            $importo = $attivita->prezzoPer($utente);
            $dovuto = $importo !== null && $importo > 0;

            // La quota è obbligatoria: non ci si iscrive senza aver scelto come pagarla.
            if ($dovuto && ! in_array($metodo, IscrizioneAttivita::metodiAmmessi(), true)) {
                throw new OperazioneNonConsentita('Per iscriverti scegli come pagare la quota.');
            }

            return IscrizioneAttivita::create([
                'user_id' => $utente->id,
                'attivita_id' => $attivita->id,
                'stato' => IscrizioneAttivita::CONFERMATA,
                'gestita_il' => now(),
                'importo' => $dovuto ? $importo : null,
                'metodo_pagamento' => $dovuto ? $metodo : null,
                'risposte' => $date ?: null,
            ]);
        });

        $this->invia(config('mail.admin_address'), new NuovaIscrizioneAttivita($iscrizione->load(['user', 'attivita'])));

        return $iscrizione;
    }

    /**
     * La persona corregge le risposte date al modulo di iscrizione (finché
     * l'attività non è conclusa).
     *
     * @param  array<string, mixed>  $risposte
     *
     * @throws OperazioneNonConsentita
     */
    public function aggiornaRisposte(User $utente, Attivita $attivita, array $risposte): IscrizioneAttivita
    {
        $iscrizione = $attivita->iscrizioni()->where('user_id', $utente->id)->first();

        if (! $iscrizione || ! $iscrizione->confermata()) {
            throw new OperazioneNonConsentita('Non risulti iscritto a questa attività.');
        }

        if ($attivita->isPassata()) {
            throw new OperazioneNonConsentita('Questa attività si è già conclusa.');
        }

        if (! $attivita->haCampi()) {
            throw new OperazioneNonConsentita('Questa attività non ha un modulo da compilare.');
        }

        $date = $attivita->preparaRisposte($risposte);
        $mancanti = $attivita->campi()->filter(fn (array $c) => $c['obbligatorio'] && ! in_array($c['id'], array_column($date, 'id'), true));

        if ($mancanti->isNotEmpty()) {
            throw new OperazioneNonConsentita('Rispondi a tutte le domande obbligatorie: manca «'.$mancanti->first()['etichetta'].'».');
        }

        $iscrizione->update(['risposte' => $date ?: null]);

        return $iscrizione;
    }

    /**
     * La persona sceglie (o cambia) come pagare la quota. Finché non ha segnalato di
     * aver pagato.
     *
     * @throws OperazioneNonConsentita
     */
    public function scegliMetodo(IscrizioneAttivita $iscrizione, string $metodo): IscrizioneAttivita
    {
        if (! $iscrizione->richiedePagamento()) {
            throw new OperazioneNonConsentita('Per questa iscrizione non c\'è nulla da pagare.');
        }

        if (! $iscrizione->puoScegliereMetodo()) {
            throw new OperazioneNonConsentita('Hai già segnalato il pagamento: se devi cambiare qualcosa scrivi a '.config('mail.admin_address').'.');
        }

        if (! in_array($metodo, IscrizioneAttivita::metodiAmmessi(), true)) {
            throw new OperazioneNonConsentita('Questo modo di pagamento non è ancora disponibile.');
        }

        $iscrizione->update(['metodo_pagamento' => $metodo]);

        return $iscrizione;
    }

    /**
     * La persona dice di aver pagato (bonifico fatto, o ritorno da PayPal prima che
     * PayPal abbia confermato): gli amministratori ricevono l'avviso di controllare
     * l'incasso. Per PayPal la conferma vera arriva dalla notifica automatica.
     *
     * @throws OperazioneNonConsentita
     */
    public function segnalaPagamento(IscrizioneAttivita $iscrizione, string $metodo): IscrizioneAttivita
    {
        if (! $iscrizione->puoScegliereMetodo()) {
            return $iscrizione;
        }

        if (! in_array($metodo, ['bonifico', 'paypal'], true)) {
            throw new OperazioneNonConsentita('Il pagamento in contanti lo registra un amministratore quando riceve la quota.');
        }

        $iscrizione->update(['metodo_pagamento' => $metodo, 'pagamento_dichiarato_il' => now()]);

        $this->invia(config('mail.admin_address'), new PagamentoIscrizioneSegnalato($iscrizione->load(['user', 'attivita'])));

        return $iscrizione;
    }

    /**
     * Segna la quota come incassata (lo fa l'amministratore, oppure PayPal con la
     * notifica automatica).
     */
    public function segnaPagata(IscrizioneAttivita $iscrizione, ?string $metodo = null, ?string $transazione = null): IscrizioneAttivita
    {
        $iscrizione->update(array_filter([
            'pagato' => true,
            'pagato_il' => now(),
            'metodo_pagamento' => $metodo,
            'paypal_txn_id' => $transazione,
        ], fn ($valore) => $valore !== null));

        return $iscrizione;
    }

    /**
     * Riammette chi era stato escluso.
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
                throw new OperazioneNonConsentita('Questa persona è già iscritta.');
            }

            $attivita = Attivita::whereKey($iscrizione->attivita_id)->lockForUpdate()->firstOrFail();
            $attivita->setAttribute('iscrizioni_confermate_count', $attivita->iscrizioni()->confermate()->count());

            if ($attivita->postiEsauriti()) {
                throw new OperazioneNonConsentita('I posti sono esauriti: per riammettere questa persona aumenta i posti disponibili o escludi un\'altra iscrizione.');
            }

            $iscrizione->update(['stato' => IscrizioneAttivita::CONFERMATA, 'gestita_il' => now()]);

            return $iscrizione;
        });

        $iscrizione->load(['user', 'attivita']);

        return ['iscrizione' => $iscrizione, 'email_inviata' => $this->invia($iscrizione->user->email, new IscrizioneAttivitaEsito($iscrizione))];
    }

    /**
     * Esclude la persona dall'attività. Una iscrizione già pagata non si esclude da
     * qui: va prima gestito il rimborso.
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
                throw new OperazioneNonConsentita('Questa persona è già stata esclusa.');
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
     * Annulla la propria iscrizione. Non è possibile se la quota è già stata pagata
     * o segnalata come pagata (serve contattare il CRAL per il rimborso) o se
     * l'attività è conclusa.
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
            throw new OperazioneNonConsentita('Non risulti iscritto a questa attività: non c\'è nulla da annullare.');
        }

        if ($iscrizione->pagato) {
            throw new OperazioneNonConsentita('Hai già pagato la quota: per annullare l\'iscrizione contatta il CRAL.');
        }

        if ($iscrizione->pagamento_dichiarato_il !== null) {
            throw new OperazioneNonConsentita('Hai già segnalato il pagamento: per annullare l\'iscrizione contatta il CRAL.');
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
