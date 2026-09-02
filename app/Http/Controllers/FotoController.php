<?php

namespace App\Http\Controllers;

use App\Models\Foto;

class FotoController extends Controller
{
    public function index()
    {
        $foto = Foto::with('attivita')->latest()->paginate(24);

        return view('foto.index', compact('foto'));
    }
}
