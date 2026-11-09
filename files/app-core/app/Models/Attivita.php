<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
    ];

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
            }

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

    public function iscrittiCount(): int
    {
        return $this->iscrizioni_count ?? $this->iscrizioni()->count();
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

    public function haPrezzo(): bool
    {
        return $this->prezzo !== null && (float) $this->prezzo > 0;
    }

    public function prezzoLabel(): ?string
    {
        return $this->haPrezzo() ? '€ '.number_format((float) $this->prezzo, 2, ',', '.') : null;
    }
}
