<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'luogo',
        'immagine_copertina',
        'pubblicata',
    ];

    protected $casts = [
        'data_evento' => 'datetime',
        'pubblicata' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Attivita $attivita) {
            if (empty($attivita->slug)) {
                $attivita->slug = Str::slug($attivita->titolo).'-'.Str::random(6);
            }
        });
    }

    public function foto()
    {
        return $this->hasMany(Foto::class);
    }
}
