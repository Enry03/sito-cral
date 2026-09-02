<?php

namespace App\Http\Controllers;

use App\Models\Attivita;

class AttivitaController extends Controller
{
    public function index()
    {
        $attivita = Attivita::where('pubblicata', true)
            ->orderByDesc('data_evento')
            ->paginate(9);

        return view('attivita.index', compact('attivita'));
    }

    public function show(string $slug)
    {
        $attivita = Attivita::where('slug', $slug)
            ->where('pubblicata', true)
            ->with('foto')
            ->firstOrFail();

        return view('attivita.show', compact('attivita'));
    }
}
