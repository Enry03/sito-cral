<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;

    protected $table = 'album';

    protected $fillable = [
        'titolo',
        'attivita_id',
    ];

    public function attivita()
    {
        return $this->belongsTo(Attivita::class);
    }

    public function foto()
    {
        return $this->hasMany(Foto::class);
    }

    /**
     * La prima foto dell'album, usata come copertina (visibile a tutti).
     */
    public function copertinaFoto()
    {
        return $this->hasOne(Foto::class)->oldestOfMany();
    }
}
