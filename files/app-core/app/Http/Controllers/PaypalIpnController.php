<?php

namespace App\Http\Controllers;

use App\Mail\PagamentoIscrizioneSegnalato;
use App\Mail\PagamentoPaypalRicevuto;
use App\Models\IscrizioneAttivita;
use App\Models\User;
use App\Services\IscrizioneAttivitaService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Notifica automatica di PayPal (IPN): è il modo in cui il sito sa che un
 * pagamento è davvero arrivato, senza fidarsi di chi dice "ho pagato".
 *
 * Ogni notifica viene rimandata a PayPal per la verifica; si registra solo un
 * pagamento completato, sul conto giusto, per l'importo giusto e mai due volte.
 * Risponde sempre 200: PayPal ripete la notifica finché non lo riceve.
 */
class PaypalIpnController extends Controller
{
    public function __invoke(Request $request): Response
    {
        // Senza un conto PayPal impostato non c'è nulla da registrare. La quota di
        // tessera si controlla più sotto, solo per i pagamenti della tessera.
        if (blank(app(\App\Services\DatiAzienda::class)->paypalEmail())) {
            return response('', 200);
        }

        try {
            $this->elabora($request);
        } catch (\Throwable $e) {
            Log::error('Notifica PayPal non elaborata: '.$e->getMessage());
        }

        return response('', 200);
    }

    private function elabora(Request $request): void
    {
        $corpo = $request->getContent();
        $dati = $request->all();

        if (! $this->verificataDaPaypal($corpo)) {
            Log::warning('Notifica PayPal non verificata, ignorata.');

            return;
        }

        if (($dati['payment_status'] ?? '') !== 'Completed') {
            return;
        }

        $contoAtteso = strtolower((string) app(\App\Services\DatiAzienda::class)->paypalEmail());
        $contoRicevente = strtolower((string) ($dati['receiver_email'] ?? $dati['business'] ?? ''));

        if ($contoAtteso === '' || $contoRicevente !== $contoAtteso
            || strtoupper((string) ($dati['mc_currency'] ?? '')) !== strtoupper((string) config('cral.paypal.valuta'))) {
            Log::warning('Notifica PayPal con conto o valuta non corretti, ignorata.', ['txn' => $dati['txn_id'] ?? null]);

            return;
        }

        $transazione = (string) ($dati['txn_id'] ?? '');
        $custom = (string) ($dati['custom'] ?? '');
        $importo = (float) ($dati['mc_gross'] ?? 0);

        if ($transazione === '') {
            return;
        }

        if (preg_match('/^iscrizione-(\d+)$/', $custom, $m)) {
            $this->registraIscrizione((int) $m[1], $transazione, $importo);

            return;
        }

        if (! preg_match('/^tessera-(\d+)$/', $custom, $m)) {
            return;
        }

        if (! User::paypalAttivo() || $importo + 0.001 < User::quotaTessera()) {
            Log::warning('Notifica PayPal con importo non corretto, ignorata.', ['txn' => $transazione]);

            return;
        }

        $utente = DB::transaction(function () use ($m, $transazione) {
            $utente = User::whereKey((int) $m[1])->lockForUpdate()->first();

            if (! $utente || $utente->paypal_txn_id !== null || User::where('paypal_txn_id', $transazione)->exists()) {
                return null;
            }

            $utente->update([
                'metodo_pagamento' => 'paypal',
                'paypal_txn_id' => $transazione,
                'quota_pagata' => true,
                'quota_pagata_il' => now(),
            ]);

            return $utente;
        });

        if (! $utente) {
            return;
        }

        try {
            Mail::to(config('mail.admin_address'))->send(new PagamentoPaypalRicevuto($utente));
        } catch (\Throwable $e) {
            Log::warning('Invio email pagamento PayPal ricevuto fallito: '.$e->getMessage());
        }
    }

    /**
     * Pagamento della quota di un'attività: l'iscrizione risulta pagata (una volta
     * sola, solo se l'importo basta) e gli amministratori ricevono l'avviso.
     */
    private function registraIscrizione(int $id, string $transazione, float $importo): void
    {
        $iscrizione = DB::transaction(function () use ($id, $transazione, $importo) {
            $iscrizione = IscrizioneAttivita::whereKey($id)->lockForUpdate()->first();

            if (! $iscrizione || ! $iscrizione->confermata() || $iscrizione->pagato
                || IscrizioneAttivita::where('paypal_txn_id', $transazione)->exists()) {
                return null;
            }

            if ($iscrizione->importo === null || $importo + 0.001 < (float) $iscrizione->importo) {
                Log::warning('Notifica PayPal per un\'attività con importo non corretto, ignorata.', ['txn' => $transazione]);

                return null;
            }

            return app(IscrizioneAttivitaService::class)->segnaPagata($iscrizione, 'paypal', $transazione);
        });

        if (! $iscrizione) {
            return;
        }

        try {
            Mail::to(config('mail.admin_address'))->send(new PagamentoIscrizioneSegnalato($iscrizione->load(['user', 'attivita'])));
        } catch (\Throwable $e) {
            Log::warning('Invio email pagamento PayPal attività fallito: '.$e->getMessage());
        }
    }

    private function verificataDaPaypal(string $corpo): bool
    {
        $host = config('cral.paypal.sandbox') ? 'ipnpb.sandbox.paypal.com' : 'ipnpb.paypal.com';

        $risposta = Http::timeout(15)
            ->withBody('cmd=_notify-validate&'.$corpo, 'application/x-www-form-urlencoded')
            ->post("https://{$host}/cgi-bin/webscr");

        return $risposta->successful() && trim($risposta->body()) === 'VERIFIED';
    }
}
