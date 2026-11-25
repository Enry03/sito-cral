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
        // Sulla galleria stanno gli album principali; i sotto-album si aprono dal loro macro-album.
        $album = Album::principali()
            ->visibili()
            ->with('copertinaFoto')
            ->conConteggi()
            ->cronologici('desc')
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
        abort_unless(Album::visibili()->whereKey($album->getKey())->exists(), 404);

        $album->load([
            'foto' => fn ($query) => $query->oldest(),
            'attivita',
            'macroAlbum',
        ]);

        // Gli album contenuti: solo quelli che hanno foto da mostrare.
        $sottoAlbum = $album->sottoAlbum()
            ->has('foto')
            ->with('copertinaFoto')
            ->conConteggi()
            ->cronologici('asc')
            ->get();

        return view('foto.album', [
            'album' => $album,
            'sottoAlbum' => $sottoAlbum,
            'accesso' => (bool) $request->user()?->puoVedereFoto(),
        ]);
    }
}
