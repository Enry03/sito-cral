<?php

namespace App\Services;

use App\Mail\IscrizioneSitoApprovata;
use App\Mail\TesseraApprovata;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Gestione delle richieste di iscrizione da parte degli amministratori.
 *
 * - Accettare una richiesta di socio: l'account diventa "socio" attivo, riceve il
 *   numero di tessera (scelto dall'amministratore o progressivo) e un'email di
 *   congratulazioni con l'immagine della tessera.
 * - Accettare l'iscrizione al solo sito: l'account diventa attivo e riceve
 *   un'email di conferma.
 * - Rifiutare: l'utente non riceve nessuna email.
 */
class RichiesteIscrizioneService
{
    /**
     * @return array{utente: User, email_inviata: bool}
     *
     * @throws OperazioneNonConsentita
     */
    public function accetta(User $utente, ?string $numeroTessera = null, bool $quotaPagata = false): array
    {
        $numeroTessera = filled($numeroTessera) ? trim($numeroTessera) : null;

        $utente = DB::transaction(function () use ($utente, $numeroTessera, $quotaPagata) {
            $utente = User::whereKey($utente->getKey())->lockForUpdate()->firstOrFail();
            $tipo = $utente->tipoRichiesta();

            if ($tipo === null) {
                throw new OperazioneNonConsentita('Questa richiesta è già stata gestita.');
            }

            if ($tipo === 'sito') {
                $utente->update(['stato_iscrizione' => 'attivo']);

                return $utente;
            }

            if ($numeroTessera !== null) {
                $giaUsato = User::where('numero_tessera', $numeroTessera)->where('id', '!=', $utente->id)->exists();

                if ($giaUsato) {
                    throw new OperazioneNonConsentita("Il numero di tessera {$numeroTessera} è già assegnato a un altro socio.");
                }

                $utente->numero_tessera = $numeroTessera;
            }

            $utente->ruolo = 'socio';
            $utente->stato_iscrizione = 'attivo';
            $utente->richiede_tessera = false;

            if ($quotaPagata && ! $utente->quota_pagata) {
                $utente->quota_pagata = true;
                $utente->quota_pagata_il = now();
            }

            $utente->save(); // se manca, il numero viene generato dal modello

            return $utente;
        });

        return ['utente' => $utente, 'email_inviata' => $this->inviaConferma($utente)];
    }

    /**
     * @throws OperazioneNonConsentita
     */
    public function rifiuta(User $utente): User
    {
        return DB::transaction(function () use ($utente) {
            $utente = User::whereKey($utente->getKey())->lockForUpdate()->firstOrFail();
            $tipo = $utente->tipoRichiesta();

            if ($tipo === null) {
                throw new OperazioneNonConsentita('Questa richiesta è già stata gestita.');
            }

            if ($tipo === 'upgrade') {
                // Resta iscritto al sito: viene solo chiusa la richiesta di tessera.
                $utente->update(['richiede_tessera' => false, 'pagamento_dichiarato_il' => null]);
            } else {
                $utente->update(['stato_iscrizione' => 'rifiutato']);
            }

            return $utente;
        });
    }

    /**
     * Un errore di invio non deve mai annullare l'approvazione: la richiesta è
     * già stata accettata, l'amministratore viene avvisato e può riprovare.
     */
    private function inviaConferma(User $utente): bool
    {
        try {
            $mail = $utente->ruolo === 'socio'
                ? new TesseraApprovata($utente)
                : new IscrizioneSitoApprovata($utente);

            Mail::to($utente->email)->send($mail);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Invio email di conferma iscrizione fallito: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Rimanda l'email di benvenuto (ad esempio se non era arrivata).
     */
    public function reinviaConferma(User $utente): bool
    {
        return $this->inviaConferma($utente);
    }
}
