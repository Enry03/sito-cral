<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Foto;
use Illuminate\Http\Request;

/**
 * Foto e album: le vede solo chi ha un account approvato (iscritto al sito, socio
 * CRAL o amministratore). Gli altri vedono le stesse pagine con anteprime sfocate
 * di pochi pixel: l'indirizzo delle foto vere non compare mai nella pagina.
 */
class FotoController extends Controller
{
    public function index(Request $request)
    {
        $album = Album::has('foto')
            ->with('copertinaFoto')
            ->withCount('foto')
            ->latest()
            ->get();

        $foto = Foto::pubbliche()->sciolte()->with('attivita')->latest()->paginate(24);

        return view('foto.index', [
            'album' => $album,
            'foto' => $foto,
            'accesso' => (bool) $request->user()?->puoVedereFoto(),
        ]);
    }

    public function album(Request $request, Album $album)
    {
        abort_unless($album->foto()->exists(), 404);

        $album->load(['foto' => fn ($query) => $query->oldest(), 'attivita']);

        return view('foto.album', [
            'album' => $album,
            'accesso' => (bool) $request->user()?->puoVedereFoto(),
        ]);
    }
}
