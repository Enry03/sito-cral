<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IscrizioneAttivita extends Model
{
    protected $table = 'iscrizioni_attivita';

    protected $fillable = [
        'user_id',
        'attivita_id',
        'importo',
        'pagato',
        'pagato_il',
    ];

    protected $casts = [
        'importo' => 'decimal:2',
        'pagato' => 'boolean',
        'pagato_il' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attivita()
    {
        return $this->belongsTo(Attivita::class);
    }

    /**
     * Il pagamento si può avviare solo se c'è un importo e non è già stato saldato.
     */
    public function richiedePagamento(): bool
    {
        return ! $this->pagato && $this->importo !== null && (float) $this->importo > 0;
    }

    public static function paypalConfigurato(): bool
    {
        return filled(config('cral.paypal.email'));
    }

    /**
     * Dati per il pulsante "Paga con PayPal". È l'unico punto da modificare
     * quando si passerà ai pagamenti con API/webhook: oggi usa il modulo
     * standard di PayPal, che richiede solo l'indirizzo email del conto
     * (PAYPAL_BUSINESS_EMAIL nel file .env).
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
                'business' => (string) config('cral.paypal.email'),
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
