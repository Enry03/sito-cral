<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    use HasFactory;

    protected $table = 'foto';

    protected $fillable = [
        'attivita_id',
        'percorso',
        'didascalia',
    ];

    public function attivita()
    {
        return $this->belongsTo(Attivita::class);
    }
}
