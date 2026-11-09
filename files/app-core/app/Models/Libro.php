<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Libro extends Model
{
    protected $table = 'libri';

    protected $fillable = [
        'titolo',
        'autore',
        'isbn',
        'codice',
        'librarything_id',
        'editore',
        'anno',
        'categoria',
        'descrizione',
        'copertina',
        'copie_totali',
    ];

    protected $casts = [
        'anno' => 'integer',
        'copie_totali' => 'integer',
    ];

    public function prestiti()
    {
        return $this->hasMany(Prestito::class);
    }

    /**
     * Aggiunge il conteggio dei prestiti in corso (serve a calcolare le copie
     * disponibili senza una query per ogni libro).
     */
    public function scopeConDisponibilita(Builder $query): Builder
    {
        return $query->withCount(['prestiti as in_prestito_count' => fn ($q) => $q->where('stato', 'in_corso')]);
    }

    public function copieInPrestito(): int
    {
        return $this->in_prestito_count ?? $this->prestiti()->where('stato', 'in_corso')->count();
    }

    public function copieDisponibili(): int
    {
        return max(0, $this->copie_totali - $this->copieInPrestito());
    }

    public function isDisponibile(): bool
    {
        return $this->copieDisponibili() > 0;
    }

    public function copertinaUrl(): ?string
    {
        return $this->copertina ? asset('uploads/'.$this->copertina) : null;
    }

    protected static function booted(): void
    {
        static::deleting(function (Libro $libro) {
            if ($libro->prestiti()->where('stato', 'in_corso')->exists()) {
                throw new \RuntimeException('Il libro ha prestiti in corso: registra prima la restituzione.');
            }
        });

        static::deleted(function (Libro $libro) {
            if ($libro->copertina) {
                Storage::disk('public')->delete($libro->copertina);
            }
        });
    }
}
