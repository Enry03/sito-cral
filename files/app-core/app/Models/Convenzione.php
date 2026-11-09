<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Azienda o negozio convenzionato con il CRAL. Le convenzioni le vedono solo i
 * soci con tessera (vedi ConvenzioneController).
 */
class Convenzione extends Model
{
    protected $table = 'convenzioni';

    protected $fillable = [
        'nome',
        'slug',
        'convenzione',
        'descrizione',
        'sito_web',
        'immagini',
        'pubblicata',
    ];

    protected function casts(): array
    {
        return [
            'immagini' => 'array',
            'pubblicata' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // L'indirizzo della pagina si crea una volta sola dal nome e poi non cambia
        // più (anche se il nome viene corretto), così i link restano validi.
        static::creating(function (Convenzione $convenzione) {
            if (blank($convenzione->slug)) {
                $convenzione->slug = static::slugUnico($convenzione->nome);
            }
        });

        // Le immagini tolte dall'elenco si cancellano dal disco.
        static::updated(function (Convenzione $convenzione) {
            if ($convenzione->wasChanged('immagini')) {
                $prima = Arr::wrap($convenzione->getOriginal('immagini'));
                static::eliminaImmagini(array_diff($prima, $convenzione->immagini ?? []));
            }
        });

        static::deleted(function (Convenzione $convenzione) {
            static::eliminaImmagini($convenzione->immagini ?? []);
        });
    }

    public static function slugUnico(string $nome): string
    {
        $base = Str::slug($nome) ?: 'convenzione';
        $slug = $base;
        $n = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$n++;
        }

        return $slug;
    }

    /**
     * @param  array<int, string>  $percorsi
     */
    private static function eliminaImmagini(array $percorsi): void
    {
        foreach (array_filter($percorsi) as $percorso) {
            try {
                Storage::disk('public')->delete($percorso);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    public function scopePubblicate($query)
    {
        return $query->where('pubblicata', true);
    }

    /**
     * Indirizzi delle immagini, nell'ordine scelto dall'amministratore.
     *
     * @return array<int, string>
     */
    public function immaginiUrl(): array
    {
        return array_values(array_map(
            fn (string $percorso) => asset('uploads/'.$percorso),
            array_filter(Arr::wrap($this->immagini))
        ));
    }

    /** La prima immagine fa da copertina. */
    public function copertinaUrl(): ?string
    {
        return $this->immaginiUrl()[0] ?? null;
    }

    /**
     * Indirizzo del sito dell'azienda, solo se è un http/https valido: così dal
     * pannello non si può inserire un link che esegue codice ("javascript:...").
     */
    public function sitoWebUrl(): ?string
    {
        $indirizzo = trim((string) $this->sito_web);

        if ($indirizzo === '') {
            return null;
        }

        if (! preg_match('#^[a-z][a-z0-9+.-]*:#i', $indirizzo)) {
            $indirizzo = 'https://'.$indirizzo;
        }

        return preg_match('#^https?://[^\s]+$#i', $indirizzo) ? $indirizzo : null;
    }

    /** Nome del sito senza "https://" né "www.", per il testo del link. */
    public function sitoWebEtichetta(): ?string
    {
        $url = $this->sitoWebUrl();

        return $url ? Str::of($url)->after('://')->replaceMatches('#^www\.#i', '')->rtrim('/')->toString() : null;
    }
}
