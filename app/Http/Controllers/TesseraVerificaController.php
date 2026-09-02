<?php

namespace App\Http\Controllers;

use App\Models\User;

class TesseraVerificaController extends Controller
{
    public function show(string $numeroTessera)
    {
        $socio = User::where('numero_tessera', $numeroTessera)->first();

        return view('tessera.verifica', compact('socio'));
    }
}
