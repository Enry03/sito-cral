<?php

namespace App\Http\Controllers;

use App\Models\Attivita;

class HomeController extends Controller
{
    public function index()
    {
        $prossimeAttivita = Attivita::where('pubblicata', true)
            ->inProgramma()
            ->get()
            ->sortBy(fn (Attivita $a) => $a->dataOrdinamento()?->timestamp ?? PHP_INT_MAX)
            ->take(3)
            ->values();

        $attivitaRecenti = Attivita::where('pubblicata', true)
            ->orderByDesc('data_ultima')
            ->orderByDesc('data_evento')
            ->take(8)
            ->get();

        return view('home', compact('prossimeAttivita', 'attivitaRecenti'));
    }
}
