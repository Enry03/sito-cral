<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'numero_tessera',
        'ruolo',
        'stato_iscrizione',
        'richiede_tessera',
        'metodo_pagamento',
        'paypal_txn_id',
        'quota_pagata',
        'quota_pagata_il',
        'pagamento_dichiarato_il',
        'richiesta_il',
        'telefono',
        'data_nascita',
        'codice_fiscale',
        'indirizzo',
        'foto_profilo',
        'data_iscrizione',
        'data_scadenza_quota',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'data_nascita' => 'date',
            'data_iscrizione' => 'date',
            'data_scadenza_quota' => 'date',
            'richiede_tessera' => 'boolean',
            'quota_pagata' => 'boolean',
            'quota_pagata_il' => 'datetime',
            'pagamento_dichiarato_il' => 'datetime',
            'richiesta_il' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            // La tessera spetta solo ai soci CRAL approvati: si genera quando un
            // account diventa "socio" e "attivo" (sia per una nuova iscrizione
            // approvata, sia per un iscritto promosso a socio) e non ce l'ha ancora.
            if ($user->ruolo === 'socio' && $user->stato_iscrizione === 'attivo' && empty($user->numero_tessera)) {
                $user->numero_tessera = static::prossimoNumeroTessera();

                if (empty($user->data_iscrizione)) {
                    $user->data_iscrizione = now();
                }
            }

            // Una richiesta di tessera si chiude quando l'iscritto diventa socio.
            if ($user->ruolo !== 'iscritto' && $user->richiede_tessera) {
                $user->richiede_tessera = false;
            }
        });
    }

    /**
     * Prossimo numero progressivo nel formato CRAL-0001. È solo un suggerimento:
     * l'amministratore può assegnare un numero diverso quando approva la tessera.
     */
    public static function prossimoNumeroTessera(): string
    {
        $ultimo = static::whereNotNull('numero_tessera')
            ->pluck('numero_tessera')
            ->map(fn ($valore) => preg_match('/^CRAL-(\d+)$/', $valore, $m) ? (int) $m[1] : 0)
            ->max();

        return 'CRAL-'.str_pad(((int) $ultimo) + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Richieste da gestire: nuove iscrizioni in attesa e iscritti al sito che
     * hanno chiesto la tessera.
     */
    public function scopeRichiesteInSospeso($query)
    {
        return $query->where(function ($q) {
            $q->where('stato_iscrizione', 'in_attesa')
                ->orWhere(fn ($q) => $q->where('stato_iscrizione', 'attivo')
                    ->where('ruolo', 'iscritto')
                    ->where('richiede_tessera', true));
        })->where('ruolo', '!=', 'admin');
    }

    /**
     * socio   = nuova iscrizione come socio CRAL (con tessera)
     * upgrade = un iscritto al sito che chiede la tessera
     * sito    = iscrizione al solo sito
     * null    = nessuna richiesta da gestire
     */
    public function tipoRichiesta(): ?string
    {
        if ($this->isAdmin()) {
            return null;
        }

        if ($this->stato_iscrizione === 'in_attesa') {
            return $this->ruolo === 'socio' ? 'socio' : 'sito';
        }

        if ($this->stato_iscrizione === 'attivo' && $this->ruolo === 'iscritto' && $this->richiede_tessera) {
            return 'upgrade';
        }

        return null;
    }

    public function richiestaLabel(): ?string
    {
        return match ($this->tipoRichiesta()) {
            'socio' => 'Diventare socio CRAL',
            'upgrade' => 'Diventare socio (già iscritto)',
            'sito' => 'Iscrizione al sito',
            default => null,
        };
    }

    /**
     * Vero se la richiesta riguarda la tessera di socio (nuova o da iscritto).
     */
    public function chiedeTessera(): bool
    {
        return in_array($this->tipoRichiesta(), ['socio', 'upgrade'], true);
    }

    /**
     * Il pagamento della quota è possibile solo per chi ha una richiesta di
     * tessera aperta e non ha ancora pagato o dichiarato il pagamento.
     */
    public function puoPagareTessera(): bool
    {
        return $this->chiedeTessera() && ! $this->quota_pagata && $this->pagamento_dichiarato_il === null;
    }

    public static function quotaTessera(): float
    {
        return (float) config('cral.quota_tessera');
    }

    /**
     * Modi con cui si può pagare la quota di tessera.
     *
     * @return array<string, string>
     */
    public static function metodiPagamento(): array
    {
        return [
            'contanti' => 'Paga in contanti in azienda',
            'email' => 'Contattaci per email',
            'paypal' => 'Paga con PayPal',
        ];
    }

    public static function paypalAttivo(): bool
    {
        return filled(config('cral.paypal.email')) && self::quotaTessera() > 0;
    }

    public function metodoPagamentoLabel(): ?string
    {
        return self::metodiPagamento()[$this->metodo_pagamento] ?? null;
    }

    /**
     * Il pagamento è arrivato davvero: PayPal lo ha confermato con la notifica
     * automatica (diverso dal semplice "sono tornato sul sito dopo aver pagato").
     */
    public function pagatoConPaypal(): bool
    {
        return filled($this->paypal_txn_id);
    }

    /**
     * Dati per il pulsante "Paga con PayPal" della quota di tessera. Restituisce
     * null finché non sono impostati l'indirizzo PayPal e l'importo (vedi
     * config/cral.php): in quel caso la pagina mostra il pulsante disattivato.
     *
     * @return array{action: string, campi: array<string, string>}|null
     */
    public function datiPaypalTessera(): ?array
    {
        if (! $this->puoPagareTessera() || ! self::paypalAttivo()) {
            return null;
        }

        $host = config('cral.paypal.sandbox') ? 'www.sandbox.paypal.com' : 'www.paypal.com';

        return [
            'action' => "https://{$host}/cgi-bin/webscr",
            'campi' => [
                'cmd' => '_xclick',
                'business' => (string) config('cral.paypal.email'),
                'item_name' => 'CRAL - Quota tessera socio',
                'amount' => number_format(self::quotaTessera(), 2, '.', ''),
                'currency_code' => (string) config('cral.paypal.valuta'),
                'custom' => 'tessera-'.$this->id,
                'no_shipping' => '1',
                'return' => route('tessera.pagamento.ritorno'),
                'notify_url' => route('paypal.ipn'),
                'cancel_return' => route($this->ruolo === 'socio' ? 'attesa-approvazione' : 'socio.richiesta'),
                'charset' => 'utf-8',
            ],
        ];
    }

    public function isAdmin(): bool
    {
        return $this->ruolo === 'admin';
    }

    public function isAttivo(): bool
    {
        return $this->stato_iscrizione === 'attivo';
    }

    /**
     * Socio CRAL: ha la tessera (account "socio" approvato). L'amministratore
     * ha gli stessi accessi.
     */
    public function isSocioCral(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->ruolo === 'socio' && $this->isAttivo() && ! empty($this->numero_tessera);
    }

    /**
     * Iscritto al sito: account approvato dall'amministratore ma senza tessera.
     */
    public function isIscrittoSito(): bool
    {
        return $this->ruolo === 'iscritto' && $this->isAttivo();
    }

    /**
     * Può entrare nell'area utente (account approvato, oppure amministratore).
     */
    public function haAccessoSito(): bool
    {
        return $this->isAdmin() || $this->isAttivo();
    }

    /**
     * Livello di accesso: admin | socio | iscritto | in_attesa.
     */
    public function livello(): string
    {
        return match (true) {
            $this->isAdmin() => 'admin',
            $this->isSocioCral() => 'socio',
            $this->isIscrittoSito() => 'iscritto',
            default => 'in_attesa',
        };
    }

    public function iscrizioniAttivita()
    {
        return $this->hasMany(IscrizioneAttivita::class);
    }

    public function prestiti()
    {
        return $this->hasMany(Prestito::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }

    public function documentiCaricati()
    {
        return $this->hasMany(Documento::class, 'caricato_da');
    }
}
