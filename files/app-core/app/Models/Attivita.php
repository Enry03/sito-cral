<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class Attivita extends Model
{
    use HasFactory;

    protected $table = 'attivita';

    protected $fillable = [
        'titolo',
        'slug',
        'descrizione',
        'data_evento',
        'data_fine',
        'ricorrente',
        'date_ricorrenti',
        'data_ultima',
        'luogo',
        'immagine_copertina',
        'pubblicata',
        'iscrizione_abilitata',
        'iscrizioni_apertura',
        'iscrizioni_chiusura',
        'posti_max',
        'prezzo',
        'prezzo_soci',
        'campi_iscrizione',
        'messaggio_iscritti',
    ];

    protected $casts = [
        'data_evento' => 'datetime',
        'data_fine' => 'datetime',
        'ricorrente' => 'boolean',
        'date_ricorrenti' => 'array',
        'data_ultima' => 'date',
        'pubblicata' => 'boolean',
        'iscrizione_abilitata' => 'boolean',
        'iscrizioni_apertura' => 'datetime',
        'iscrizioni_chiusura' => 'datetime',
        'posti_max' => 'integer',
        'prezzo' => 'decimal:2',
        'prezzo_soci' => 'decimal:2',
        'campi_iscrizione' => 'array',
    ];

    /** Tipi di domanda che si possono aggiungere al modulo di iscrizione. */
    public const CAMPO_TESTO = 'testo';

    public const CAMPO_TESTO_LUNGO = 'testo_lungo';

    public const CAMPO_SCELTA = 'scelta';

    public const CAMPO_SCELTA_MULTIPLA = 'scelta_multipla';

    public const CAMPO_SI_NO = 'si_no';

    public const CAMPO_NUMERO = 'numero';

    public const CAMPO_ACCETTAZIONE = 'accettazione';

    protected static function booted(): void
    {
        static::saving(function (Attivita $attivita) {
            if (empty($attivita->slug)) {
                $attivita->slug = Str::slug($attivita->titolo).'-'.Str::random(6);
            }

            // Coerenza dei campi collegati: quello che non serve viene svuotato.
            if (! $attivita->ricorrente) {
                $attivita->date_ricorrenti = null;
            }

            if (! $attivita->iscrizione_abilitata) {
                $attivita->iscrizioni_apertura = null;
                $attivita->iscrizioni_chiusura = null;
                $attivita->posti_max = null;
                $attivita->prezzo = null;
                $attivita->campi_iscrizione = null;
                $attivita->messaggio_iscritti = null;
            }

            // Senza una quota intera non ha senso una quota ridotta per i soci.
            if (! ((float) $attivita->prezzo > 0)) {
                $attivita->prezzo_soci = null;
            }

            $attivita->campi_iscrizione = $attivita->normalizzaCampi($attivita->campi_iscrizione);
            $attivita->messaggio_iscritti = filled($attivita->messaggio_iscritti) ? trim((string) $attivita->messaggio_iscritti) : null;

            $attivita->date_ricorrenti = $attivita->normalizzaDateRicorrenti($attivita->date_ricorrenti);
            $attivita->data_ultima = $attivita->calcolaDataUltima();
        });
    }

    // ---------------------------------------------------------------- relazioni

    public function foto()
    {
        return $this->hasMany(Foto::class);
    }

    public function album()
    {
        return $this->hasMany(Album::class);
    }

    public function iscrizioni()
    {
        return $this->hasMany(IscrizioneAttivita::class);
    }

    /** Per i conteggi: withCount('iscrizioniConfermate') dà iscrizioni_confermate_count. */
    public function iscrizioniConfermate()
    {
        return $this->hasMany(IscrizioneAttivita::class)->where('stato', IscrizioneAttivita::CONFERMATA);
    }

    // ------------------------------------------------------------------- scope

    public function scopeInProgramma($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('data_ultima')->orWhereDate('data_ultima', '>=', today());
        });
    }

    public function scopePassate($query)
    {
        return $query->whereNotNull('data_ultima')->whereDate('data_ultima', '<', today());
    }

    // ------------------------------------------------------------------- date

    /**
     * Elenco ordinato di tutte le date in cui l'attività si svolge: l'inizio
     * (con eventuale fine) più le date di ricorrenza.
     *
     * @return Collection<int, array{inizio: Carbon, fine: Carbon}>
     */
    public function occorrenze(): Collection
    {
        $occorrenze = collect();

        if ($this->data_evento) {
            $occorrenze->push([
                'inizio' => $this->data_evento->copy(),
                'fine' => ($this->data_fine ?? $this->data_evento)->copy(),
            ]);
        }

        foreach ($this->date_ricorrenti ?? [] as $data) {
            $giorno = Carbon::parse($data)->startOfDay();
            $occorrenze->push(['inizio' => $giorno, 'fine' => $giorno->copy()]);
        }

        return $occorrenze->sortBy(fn ($o) => $o['inizio']->timestamp)->values();
    }

    /**
     * Prima occorrenza non ancora conclusa (confronto a livello di giorno).
     */
    public function prossimaOccorrenza(): ?array
    {
        return $this->occorrenze()->first(fn ($o) => $o['fine']->toDateString() >= today()->toDateString());
    }

    public function isInProgramma(): bool
    {
        if ($this->data_ultima === null) {
            return true;
        }

        return $this->data_ultima->toDateString() >= today()->toDateString();
    }

    public function isPassata(): bool
    {
        return ! $this->isInProgramma();
    }

    /**
     * Data usata per ordinare l'elenco "in programma": la prossima occorrenza,
     * altrimenti la prima.
     */
    public function dataOrdinamento(): ?Carbon
    {
        return ($this->prossimaOccorrenza() ?? $this->occorrenze()->first())['inizio'] ?? null;
    }

    /**
     * "12 set 2026", "10 – 15 ago 2026", "28 ago – 2 set 2026".
     */
    public static function etichettaPeriodo(Carbon $inizio, ?Carbon $fine = null): string
    {
        if (! $fine || $fine->isSameDay($inizio)) {
            return $inizio->translatedFormat('j M Y');
        }

        if ($inizio->year === $fine->year && $inizio->month === $fine->month) {
            return $inizio->translatedFormat('j').' – '.$fine->translatedFormat('j M Y');
        }

        if ($inizio->year === $fine->year) {
            return $inizio->translatedFormat('j M').' – '.$fine->translatedFormat('j M Y');
        }

        return $inizio->translatedFormat('j M Y').' – '.$fine->translatedFormat('j M Y');
    }

    /**
     * Etichetta della data principale (inizio o periodo).
     */
    public function periodoLabel(): ?string
    {
        if (! $this->data_evento) {
            return null;
        }

        return self::etichettaPeriodo($this->data_evento, $this->data_fine);
    }

    /**
     * Etichetta corta ("12 set", "10 – 15 ago") della prossima occorrenza,
     * per i riquadri piccoli.
     */
    public function etichettaBreve(): ?string
    {
        $occorrenza = $this->prossimaOccorrenza() ?? $this->occorrenze()->first();

        if (! $occorrenza) {
            return null;
        }

        ['inizio' => $inizio, 'fine' => $fine] = $occorrenza;

        if ($fine->isSameDay($inizio)) {
            return $inizio->translatedFormat('j M');
        }

        if ($inizio->month === $fine->month && $inizio->year === $fine->year) {
            return $inizio->translatedFormat('j').' – '.$fine->translatedFormat('j M');
        }

        return $inizio->translatedFormat('j M').' – '.$fine->translatedFormat('j M');
    }

    /**
     * Ora di inizio, solo se è stata impostata (diversa da mezzanotte) e
     * l'evento dura un solo giorno.
     */
    public function orarioLabel(): ?string
    {
        if (! $this->data_evento || $this->data_evento->format('H:i') === '00:00') {
            return null;
        }

        return $this->data_evento->format('H:i');
    }

    /**
     * Le date di ricorrenza (escluso l'inizio), già formattate.
     *
     * @return Collection<int, string>
     */
    public function dateRicorrentiLabel(): Collection
    {
        return collect($this->date_ricorrenti ?? [])
            ->map(fn ($d) => Carbon::parse($d)->translatedFormat('l j F Y'));
    }

    /**
     * @param  mixed  $valori
     * @return list<string>|null
     */
    public function normalizzaDateRicorrenti($valori): ?array
    {
        if (empty($valori) || ! is_array($valori)) {
            return null;
        }

        $inizio = $this->data_evento?->toDateString();

        $date = collect($valori)
            ->map(fn ($v) => is_array($v) ? ($v['data'] ?? null) : $v)
            ->filter()
            ->map(fn ($v) => Carbon::parse($v)->toDateString())
            ->reject(fn ($v) => $v === $inizio)
            ->unique()
            ->sort()
            ->values()
            ->all();

        return $date ?: null;
    }

    public function calcolaDataUltima(): ?Carbon
    {
        $candidate = collect([$this->data_fine ?? $this->data_evento])
            ->merge(collect($this->date_ricorrenti ?? [])->map(fn ($d) => Carbon::parse($d)))
            ->filter();

        return $candidate->isEmpty() ? null : $candidate->max()->copy()->startOfDay();
    }

    // -------------------------------------------------------------- iscrizioni

    /**
     * Quanti sono iscritti: solo loro occupano un posto (chi è stato escluso
     * dall'amministratore no).
     */
    public function iscrittiCount(): int
    {
        return $this->iscrizioni_confermate_count ?? $this->iscrizioni()->confermate()->count();
    }

    public function postiRimasti(): ?int
    {
        if ($this->posti_max === null) {
            return null;
        }

        return max(0, $this->posti_max - $this->iscrittiCount());
    }

    public function postiEsauriti(): bool
    {
        return $this->posti_max !== null && $this->iscrittiCount() >= $this->posti_max;
    }

    /**
     * non_prevista | terminata | non_ancora | chiuse | esaurite | aperte
     */
    public function statoIscrizioni(): string
    {
        if (! $this->iscrizione_abilitata) {
            return 'non_prevista';
        }

        if ($this->isPassata()) {
            return 'terminata';
        }

        $ora = now();

        if ($this->iscrizioni_apertura && $ora->lt($this->iscrizioni_apertura)) {
            return 'non_ancora';
        }

        if ($this->iscrizioni_chiusura && $ora->gt($this->iscrizioni_chiusura)) {
            return 'chiuse';
        }

        if ($this->postiEsauriti()) {
            return 'esaurite';
        }

        return 'aperte';
    }

    // ------------------------------------------------------------------ prezzi

    /**
     * Per qualcuno c'è una quota da pagare. "prezzo" è la quota intera, quella dei
     * non soci; se non c'è, l'attività è gratuita per tutti.
     */
    public function haPrezzo(): bool
    {
        return $this->prezzo !== null && (float) $this->prezzo > 0;
    }

    /**
     * Soci e non soci pagano importi diversi (quello dei soci è più basso).
     */
    public function haDuePrezzi(): bool
    {
        return $this->haPrezzo()
            && $this->prezzo_soci !== null
            && (float) $this->prezzo_soci < (float) $this->prezzo;
    }

    /** Quota dei non soci (o di tutti, se il prezzo è unico). */
    public function prezzoNonSoci(): ?float
    {
        return $this->haPrezzo() ? (float) $this->prezzo : null;
    }

    /** Quota dei soci CRAL: quella ridotta se esiste, altrimenti la stessa di tutti. */
    public function prezzoPerSoci(): ?float
    {
        if (! $this->haPrezzo()) {
            return null;
        }

        return $this->haDuePrezzi() ? (float) $this->prezzo_soci : (float) $this->prezzo;
    }

    /**
     * Quanto paga questa persona: il prezzo dei soci se è socio CRAL (gli
     * amministratori contano come soci), quello intero in tutti gli altri casi, anche
     * per chi non ha fatto l'accesso. Null se l'attività è gratuita; 0 se è gratuita
     * solo per i soci.
     */
    public function prezzoPer(?User $utente): ?float
    {
        if (! $this->haPrezzo()) {
            return null;
        }

        return $utente?->isSocioCral() ? $this->prezzoPerSoci() : $this->prezzoNonSoci();
    }

    /** Quanto si risparmia essendo soci. */
    public function risparmioSoci(): float
    {
        return $this->haDuePrezzi() ? round((float) $this->prezzo - (float) $this->prezzo_soci, 2) : 0.0;
    }

    public static function formattaImporto(float $importo): string
    {
        return $importo > 0 ? '€ '.number_format($importo, 2, ',', '.') : 'Gratis';
    }

    /** La quota (intera) in forma di testo; null se l'attività è gratuita. */
    public function prezzoLabel(): ?string
    {
        return $this->haPrezzo() ? self::formattaImporto((float) $this->prezzo) : null;
    }

    /** Quanto paga questa persona, in forma di testo ("€ 10,00", "Gratis"); null se non c'è una quota. */
    public function prezzoLabelPer(?User $utente): ?string
    {
        $importo = $this->prezzoPer($utente);

        return $importo === null ? null : self::formattaImporto($importo);
    }

    // ---------------------------------------------------- domande dell'iscrizione

    /**
     * @return array<string, string>
     */
    public static function tipiCampo(): array
    {
        return [
            self::CAMPO_TESTO => 'Risposta breve',
            self::CAMPO_TESTO_LUNGO => 'Risposta lunga / note',
            self::CAMPO_SCELTA => 'Una scelta tra più opzioni',
            self::CAMPO_SCELTA_MULTIPLA => 'Più scelte possibili',
            self::CAMPO_SI_NO => 'Sì / No',
            self::CAMPO_NUMERO => 'Numero',
            self::CAMPO_ACCETTAZIONE => 'Casella da accettare (es. regolamento)',
        ];
    }

    /** Domande che hanno un elenco di opzioni tra cui scegliere. */
    public static function tipiConOpzioni(): array
    {
        return [self::CAMPO_SCELTA, self::CAMPO_SCELTA_MULTIPLA];
    }

    /**
     * Le domande del modulo di iscrizione, già in ordine.
     *
     * @return Collection<int, array{id: string, etichetta: string, tipo: string, opzioni: list<string>, obbligatorio: bool, aiuto: ?string}>
     */
    public function campi(): Collection
    {
        return collect($this->campi_iscrizione ?? [])->values();
    }

    public function haCampi(): bool
    {
        return $this->iscrizione_abilitata && $this->campi()->isNotEmpty();
    }

    /**
     * Rende coerente quello che arriva dal pannello: toglie le domande senza testo,
     * dà a ognuna un codice stabile (le risposte restano legate alla domanda anche
     * se cambia l'etichetta) e pulisce le opzioni.
     *
     * @param  mixed  $valori
     * @return list<array<string, mixed>>|null
     */
    public function normalizzaCampi($valori): ?array
    {
        if (empty($valori) || ! is_array($valori)) {
            return null;
        }

        $usati = [];
        $campi = [];

        foreach (array_values($valori) as $campo) {
            if (! is_array($campo) || blank($campo['etichetta'] ?? null)) {
                continue;
            }

            $tipo = array_key_exists($campo['tipo'] ?? null, self::tipiCampo()) ? $campo['tipo'] : self::CAMPO_TESTO;

            $opzioni = [];
            if (in_array($tipo, self::tipiConOpzioni(), true)) {
                $opzioni = collect((array) ($campo['opzioni'] ?? []))
                    ->map(fn ($o) => trim((string) $o))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                if (count($opzioni) < 2) {
                    continue;
                }
            }

            $id = (string) ($campo['id'] ?? '');
            if (! preg_match('/^[a-z0-9]{4,16}$/', $id) || isset($usati[$id])) {
                do {
                    $id = strtolower(Str::random(8));
                } while (isset($usati[$id]));
            }
            $usati[$id] = true;

            $campi[] = [
                'id' => $id,
                'etichetta' => trim((string) $campo['etichetta']),
                'tipo' => $tipo,
                'opzioni' => $opzioni,
                'obbligatorio' => $tipo === self::CAMPO_ACCETTAZIONE ? true : (bool) ($campo['obbligatorio'] ?? false),
                'aiuto' => filled($campo['aiuto'] ?? null) ? trim((string) $campo['aiuto']) : null,
            ];
        }

        return $campi ?: null;
    }

    /**
     * Regole di validazione delle risposte (chiavi "risposte.<codice>").
     *
     * @return array<string, array<int, mixed>>
     */
    public function regoleRisposte(): array
    {
        $regole = [];

        foreach ($this->campi() as $campo) {
            $chiave = 'risposte.'.$campo['id'];
            $base = $campo['obbligatorio'] ? ['required'] : ['nullable'];

            switch ($campo['tipo']) {
                case self::CAMPO_TESTO_LUNGO:
                    $regole[$chiave] = [...$base, 'string', 'max:1500'];
                    break;
                case self::CAMPO_SCELTA:
                    $regole[$chiave] = [...$base, 'string', Rule::in($campo['opzioni'])];
                    break;
                case self::CAMPO_SCELTA_MULTIPLA:
                    $regole[$chiave] = [...$base, 'array'];
                    $regole[$chiave.'.*'] = ['string', Rule::in($campo['opzioni'])];
                    break;
                case self::CAMPO_SI_NO:
                    $regole[$chiave] = [...$base, Rule::in(['si', 'no'])];
                    break;
                case self::CAMPO_NUMERO:
                    $regole[$chiave] = [...$base, 'numeric', 'min:0', 'max:100000'];
                    break;
                case self::CAMPO_ACCETTAZIONE:
                    $regole[$chiave] = ['accepted'];
                    break;
                default:
                    $regole[$chiave] = [...$base, 'string', 'max:255'];
            }
        }

        return $regole;
    }

    /**
     * Nomi leggibili per i messaggi di errore.
     *
     * @return array<string, string>
     */
    public function nomiRisposte(): array
    {
        return $this->campi()->mapWithKeys(fn ($campo) => ['risposte.'.$campo['id'] => '«'.$campo['etichetta'].'»'])->all();
    }

    /**
     * Trasforma le risposte già validate nella forma che si salva: per ogni domanda
     * a cui si è risposto, codice, testo della domanda (così l'elenco resta leggibile
     * anche se la domanda viene modificata) e risposta.
     *
     * @param  array<string, mixed>  $input
     * @return list<array{id: string, etichetta: string, valore: string|list<string>}>
     */
    public function preparaRisposte(array $input): array
    {
        $risposte = [];

        foreach ($this->campi() as $campo) {
            $grezzo = $input[$campo['id']] ?? null;

            $valore = match ($campo['tipo']) {
                self::CAMPO_SCELTA_MULTIPLA => array_values(array_intersect($campo['opzioni'], array_map('strval', (array) $grezzo))),
                self::CAMPO_SI_NO => match ($grezzo) {
                    'si' => 'Sì',
                    'no' => 'No',
                    default => null,
                },
                self::CAMPO_ACCETTAZIONE => $grezzo ? 'Accettato' : null,
                self::CAMPO_NUMERO => is_numeric($grezzo) ? (string) ($grezzo + 0) : null,
                default => is_scalar($grezzo) ? trim((string) $grezzo) : null,
            };

            if ($valore === null || $valore === '' || $valore === []) {
                continue;
            }

            $risposte[] = ['id' => $campo['id'], 'etichetta' => $campo['etichetta'], 'valore' => $valore];
        }

        return $risposte;
    }
}
