<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use Illuminate\Support\Facades\Storage;

class DocumentoController extends Controller
{
    public function index()
    {
        $documenti = Documento::latest()->paginate(15);

        return view('documenti.index', compact('documenti'));
    }

    public function download(Documento $documento)
    {
        return Storage::disk('public')->download($documento->percorso, $documento->titolo);
    }
}
