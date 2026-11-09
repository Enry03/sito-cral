<?php

namespace App\Services;

use App\Models\Impostazione;
use App\Support\Iban;

/**
 * Dati dell'associazione che l'amministratore gestisce dal pannello: conto per i
 * bonifici, quota di tessera e indirizzo PayPal.
 *
 * Quota e PayPal si possono ancora impostare nel file .env (config/cral.php): il
 * valore scritto nel pannello ha la precedenza, se c'è. Se la tabella non esiste
 * ancora (aggiornamento a metà) si usano i valori del .env senza dare errore.
 *
 * Una sola istanza per richiesta (vedi AppServiceProvider): letta una volta e basta.
 */
class DatiAzienda
{
    public const CHIAVI = ['intestatario', 'iban', 'banca', 'bic', 'causale', 'quota_tessera', 'paypal_email'];

    public const CAUSALE_PREDEFINITA = 'Quota tessera CRAL';

    /** @var array<string, string>|null */
    private ?array $valori = null;

    /**
     * @return array<string, string>
     */
    public function tutti(): array
    {
        return $this->valori ??= rescue(fn () => Impostazione::query()->pluck('valore', 'chiave')->all(), [], false);
    }

    public function valore(string $chiave): ?string
    {
        $valore = $this->tutti()[$chiave] ?? null;

        return filled($valore) ? trim((string) $valore) : null;
    }

    /**
     * Salva i dati: un campo lasciato vuoto viene tolto (si torna al valore del .env).
     *
     * @param  array<string, mixed>  $dati
     */
    public function salva(array $dati): void
    {
        foreach (self::CHIAVI as $chiave) {
            $valore = $dati[$chiave] ?? null;
            $valore = is_string($valore) ? trim($valore) : $valore;

            if ($chiave === 'iban') {
                $valore = Iban::normalizza($valore);
            }

            if ($chiave === 'quota_tessera' && filled($valore)) {
                $valore = str_replace(',', '.', (string) $valore);
            }

            if (blank($valore)) {
                Impostazione::where('chiave', $chiave)->delete();
            } else {
                Impostazione::updateOrCreate(['chiave' => $chiave], ['valore' => (string) $valore]);
            }
        }

        $this->valori = null;
    }

    public function quotaTessera(): float
    {
        $valore = str_replace(',', '.', (string) $this->valore('quota_tessera'));

        return is_numeric($valore) && (float) $valore > 0
            ? (float) $valore
            : (float) config('cral.quota_tessera');
    }

    public function paypalEmail(): ?string
    {
        return $this->valore('paypal_email')
            ?? (filled(config('cral.paypal.email')) ? (string) config('cral.paypal.email') : null);
    }

    /**
     * I dati per il bonifico, se l'amministratore li ha inseriti (servono almeno
     * intestatario e IBAN valido).
     *
     * @return array{intestatario: string, iban: string, iban_formattato: string, banca: ?string, bic: ?string}|null
     */
    public function bonifico(): ?array
    {
        $iban = $this->valore('iban');
        $intestatario = $this->valore('intestatario');

        if (! $iban || ! $intestatario || ! Iban::valido($iban)) {
            return null;
        }

        return [
            'intestatario' => $intestatario,
            'iban' => Iban::normalizza($iban),
            'iban_formattato' => Iban::formatta($iban),
            'banca' => $this->valore('banca'),
            'bic' => $this->valore('bic'),
        ];
    }

    public function bonificoAttivo(): bool
    {
        return $this->bonifico() !== null;
    }

    /**
     * Causale da scrivere nel bonifico: il testo scelto dall'amministratore seguito
     * dal nome di chi paga, così si riconosce subito il pagamento (max 140 caratteri).
     */
    public function causale(?string $nome = null): string
    {
        $base = $this->valore('causale') ?? self::CAUSALE_PREDEFINITA;
        $causale = filled($nome) ? $base.' - '.trim($nome) : $base;

        return mb_substr($causale, 0, 140);
    }
}
