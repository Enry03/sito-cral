<?php

namespace App\Models;

use App\Services\DatiAzienda;
use Illuminate\Database\Eloquent\Model;

/**
 * Richiesta di partecipare a un'attività (torneo, gita, uscita...). Passa sempre
 * dall'amministratore: nasce "in attesa" e diventa "confermata" o "rifiutata".
 */
class IscrizioneAttivita extends Model
{
    public const IN_ATTESA = 'in_attesa';

    public const CONFERMATA = 'confermata';

    public const RIFIUTATA = 'rifiutata';

    protected $table = 'iscrizioni_attivita';

    /**
     * Chi crea un'iscrizione senza indicare lo stato (l'amministratore dal pannello,
     * o i dati di prima di questa funzione) la crea già confermata. Le richieste dal
     * sito passano invece sempre da "in attesa" (vedi IscrizioneAttivitaService).
     */
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
    ];

    protected $casts = [
        'importo' => 'decimal:2',
        'pagato' => 'boolean',
        'pagato_il' => 'datetime',
        'gestita_il' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attivita()
    {
        return $this->belongsTo(Attivita::class);
    }

    public function scopeInAttesa($query)
    {
        return $query->where('stato', self::IN_ATTESA);
    }

    public function scopeConfermate($query)
    {
        return $query->where('stato', self::CONFERMATA);
    }

    /**
     * @return array<string, string>
     */
    public static function stati(): array
    {
        return [
            self::IN_ATTESA => 'In attesa di conferma',
            self::CONFERMATA => 'Confermata',
            self::RIFIUTATA => 'Non accolta',
        ];
    }

    public function statoLabel(): string
    {
        return self::stati()[$this->stato] ?? (string) $this->stato;
    }

    public function isInAttesa(): bool
    {
        return $this->stato === self::IN_ATTESA;
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
     * Il pagamento si può avviare solo a iscrizione confermata, se c'è un importo
     * e non è già stato saldato.
     */
    public function richiedePagamento(): bool
    {
        return $this->confermata() && ! $this->pagato && $this->importo !== null && (float) $this->importo > 0;
    }

    public static function paypalConfigurato(): bool
    {
        return filled(app(DatiAzienda::class)->paypalEmail());
    }

    /**
     * Dati per il pulsante "Paga con PayPal". È l'unico punto da modificare
     * quando si passerà ai pagamenti con API/webhook: oggi usa il modulo
     * standard di PayPal, che richiede solo l'indirizzo email del conto
     * (impostato nel pannello, Dati dell'azienda, oppure in .env).
     *
     * @return array{action: string, campi: array<string, string>}|null
     */
    public function datiPaypal(): ?array
    {
        if (! self::paypalConfigurato() || ! $this->richiedePagamento()) {
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
                'return' => route('attivita.show', $this->attivita->slug),
                'cancel_return' => route('attivita.show', $this->attivita->slug),
                'charset' => 'utf-8',
            ],
        ];
    }
}
