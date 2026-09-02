<?php

namespace App\Http\Controllers;

use App\Models\Attivita;
use App\Models\Foto;

class HomeController extends Controller
{
    public function index()
    {
        $prossimeAttivita = Attivita::where('pubblicata', true)
            ->orderBy('data_evento')
            ->take(3)
            ->get();

        $fotoRecenti = Foto::latest()->take(8)->get();

        return view('home', compact('prossimeAttivita', 'fotoRecenti'));
    }
}
