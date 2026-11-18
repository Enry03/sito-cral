<?php

namespace App\Models;

use App\Services\DatiAzienda;
use Illuminate\Database\Eloquent\Model;

/**
 * Iscrizione di una persona a un'attività (torneo, gita, uscita...). Chi ha un
 * account approvato si iscrive da solo, in automatico: nessuna conferma. Se c'è
 * una quota, subito dopo sceglie come pagare (contanti, bonifico, PayPal).
 *
 * Lo stato resta "confermata" per chi partecipa; l'amministratore può escludere
 * qualcuno ("rifiutata"), che non occupa più il posto e non si può reiscrivere.
 */
class IscrizioneAttivita extends Model
{
    public const CONFERMATA = 'confermata';

    public const RIFIUTATA = 'rifiutata';

    public const PAGAMENTO_GRATUITA = 'Gratuita';

    public const PAGAMENTO_PAGATA = 'Pagata';

    public const PAGAMENTO_IN_ELABORAZIONE = 'In elaborazione';

    public const PAGAMENTO_DA_PAGARE = 'Da pagare';

    protected $table = 'iscrizioni_attivita';

    protected $attributes = [
        'stato' => self::CONFERMATA,
    ];

    protected $fillable = [
        'user_id',
        'attivita_id',
        'stato',
        'importo',
        'pagato',
        'pagato_il',
        'gestita_il',
        'metodo_pagamento',
        'pagamento_dichiarato_il',
        'paypal_txn_id',
    ];

    protected $casts = [
        'importo' => 'decimal:2',
        'pagato' => 'boolean',
        'pagato_il' => 'datetime',
        'gestita_il' => 'datetime',
        'pagamento_dichiarato_il' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attivita()
    {
        return $this->belongsTo(Attivita::class);
    }

    public function scopeConfermate($query)
    {
        return $query->where('stato', self::CONFERMATA);
    }

    /** Pagamenti segnalati dalla persona (bonifico, PayPal) ma non ancora verificati. */
    public function scopePagamentiDaVerificare($query)
    {
        return $query->where('stato', self::CONFERMATA)
            ->where('pagato', false)
            ->whereNotNull('pagamento_dichiarato_il');
    }

    /**
     * @return array<string, string>
     */
    public static function stati(): array
    {
        return [
            self::CONFERMATA => 'Iscritto',
            self::RIFIUTATA => 'Escluso',
        ];
    }

    public function statoLabel(): string
    {
        return self::stati()[$this->stato] ?? (string) $this->stato;
    }

    public function confermata(): bool
    {
        return $this->stato === self::CONFERMATA;
    }

    public function rifiutata(): bool
    {
        return $this->stato === self::RIFIUTATA;
    }

    /**
     * C'è ancora da pagare: iscrizione valida, con una quota, non ancora saldata.
     */
    public function richiedePagamento(): bool
    {
        return $this->confermata() && ! $this->pagato && $this->importo !== null && (float) $this->importo > 0;
    }

    /**
     * La persona può ancora scegliere o cambiare il modo di pagamento: finché non
     * ha segnalato di aver pagato.
     */
    public function puoScegliereMetodo(): bool
    {
        return $this->richiedePagamento() && $this->pagamento_dichiarato_il === null;
    }

    public function pagatoConPaypal(): bool
    {
        return filled($this->paypal_txn_id);
    }

    /**
     * Gratuita | Pagata | In elaborazione | Da pagare, per le liste dell'amministratore.
     */
    public function statoPagamento(): string
    {
        if ($this->pagato) {
            return self::PAGAMENTO_PAGATA;
        }

        if ($this->importo === null || (float) $this->importo <= 0) {
            return self::PAGAMENTO_GRATUITA;
        }

        return $this->pagamento_dichiarato_il !== null ? self::PAGAMENTO_IN_ELABORAZIONE : self::PAGAMENTO_DA_PAGARE;
    }

    public function metodoPagamentoLabel(): ?string
    {
        return User::metodiPagamento()[$this->metodo_pagamento] ?? null;
    }

    /**
     * Modi di pagamento selezionabili adesso per un'attività. Come per la tessera:
     * i contanti sempre; il bonifico quando l'amministratore ha inserito i dati del
     * conto; PayPal quando c'è l'indirizzo che riceve i pagamenti (qui la quota
     * dell'attività è l'importo dell'iscrizione, non serve la quota di tessera).
     *
     * @return array<int, string>
     */
    public static function metodiAmmessi(): array
    {
        return array_values(array_filter(
            array_keys(User::metodiPagamento()),
            fn (string $metodo) => match ($metodo) {
                'bonifico' => User::bonificoAttivo(),
                'paypal' => self::paypalConfigurato(),
                default => true,
            },
        ));
    }

    public static function paypalConfigurato(): bool
    {
        return filled(app(DatiAzienda::class)->paypalEmail());
    }

    /**
     * Dati per il pulsante "Paga con PayPal". Usa il modulo standard di PayPal,
     * che richiede solo l'indirizzo email del conto (impostato nel pannello, Dati
     * dell'azienda, oppure in .env). PayPal avvisa il sito del pagamento con la
     * notifica automatica (vedi PaypalIpnController): così l'iscrizione risulta
     * pagata da sola.
     *
     * @return array{action: string, campi: array<string, string>}|null
     */
    public function datiPaypal(): ?array
    {
        if (! self::paypalConfigurato() || ! $this->puoScegliereMetodo()) {
            return null;
        }

        $host = config('cral.paypal.sandbox') ? 'www.sandbox.paypal.com' : 'www.paypal.com';

        return [
            'action' => "https://{$host}/cgi-bin/webscr",
            'campi' => [
                'cmd' => '_xclick',
                'business' => (string) app(DatiAzienda::class)->paypalEmail(),
                'item_name' => 'CRAL - '.$this->attivita->titolo,
                'amount' => number_format((float) $this->importo, 2, '.', ''),
                'currency_code' => (string) config('cral.paypal.valuta'),
                'custom' => 'iscrizione-'.$this->id,
                'no_shipping' => '1',
                'return' => route('attivita.pagamento.ritorno', $this->attivita->slug),
                'notify_url' => route('paypal.ipn'),
                'cancel_return' => route('attivita.show', $this->attivita->slug).'#iscrizione',
                'charset' => 'utf-8',
            ],
        ];
    }
}
