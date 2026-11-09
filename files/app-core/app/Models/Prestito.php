<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Prestito extends Model
{
    protected $table = 'prestiti';

    protected $fillable = [
        'libro_id',
        'user_id',
        'libro_titolo',
        'libro_codice',
        'utente_nome',
        'utente_tessera',
        'stato',
        'data_prestito',
        'scadenza',
        'restituito_il',
        'note',
    ];

    protected $casts = [
        'data_prestito' => 'date',
        'scadenza' => 'date',
        'restituito_il' => 'date',
    ];

    protected static function booted(): void
    {
        // Copia di titolo, codice e nome nello storico (vedi migration).
        static::creating(function (Prestito $prestito) {
            if ($prestito->libro_id && empty($prestito->libro_titolo)) {
                $libro = Libro::find($prestito->libro_id);
                $prestito->libro_titolo = $libro?->titolo ?? '';
                $prestito->libro_codice = $libro?->codice ?? $libro?->isbn;
            }

            if ($prestito->user_id && empty($prestito->utente_nome)) {
                $utente = User::find($prestito->user_id);
                $prestito->utente_nome = $utente?->name ?? '';
                $prestito->utente_tessera = $utente?->numero_tessera;
            }
        });
    }

    public function libro()
    {
        return $this->belongsTo(Libro::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeInCorso(Builder $query): Builder
    {
        return $query->where('stato', 'in_corso');
    }

    public function scopeScaduti(Builder $query): Builder
    {
        return $query->where('stato', 'in_corso')->whereDate('scadenza', '<', today());
    }

    public function isInCorso(): bool
    {
        return $this->stato === 'in_corso';
    }

    public function isInRitardo(): bool
    {
        return $this->isInCorso() && $this->scadenza->toDateString() < today()->toDateString();
    }

    public function statoLabel(): string
    {
        return match (true) {
            $this->isInRitardo() => 'In ritardo',
            $this->stato === 'in_corso' => 'In prestito',
            $this->stato === 'restituito' => 'Restituito',
            default => 'Annullato',
        };
    }

    public function registraRestituzione(): void
    {
        $this->update(['stato' => 'restituito', 'restituito_il' => today()]);
    }

    public function annulla(): void
    {
        $this->update(['stato' => 'annullato']);
    }

    public function prolunga(int $giorni): void
    {
        $this->update(['scadenza' => $this->scadenza->copy()->addDays($giorni)]);
    }
}
