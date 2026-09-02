<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;

    protected $table = 'documenti';

    protected $fillable = [
        'titolo',
        'descrizione',
        'percorso',
        'caricato_da',
    ];

    public function caricatoDa()
    {
        return $this->belongsTo(User::class, 'caricato_da');
    }
}
