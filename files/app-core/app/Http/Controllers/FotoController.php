<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Foto;
use Illuminate\Http\Request;

class FotoController extends Controller
{
    public function index()
    {
        $album = Album::has('foto')
            ->with('copertinaFoto')
            ->withCount('foto')
            ->latest()
            ->get();

        $foto = Foto::pubbliche()->sciolte()->with('attivita')->latest()->paginate(24);

        return view('foto.index', compact('album', 'foto'));
    }

    public function album(Request $request, Album $album)
    {
        abort_unless($album->foto()->exists(), 404);

        $album->load(['foto' => fn ($query) => $query->oldest(), 'attivita']);

        // Solo i soci CRAL con tessera vedono tutto l'album: gli altri vedono le
        // prime foto e, al posto delle altre, un'anteprima sfocata.
        $accessoCompleto = (bool) $request->user()?->isSocioCral();
        $visibili = (int) config('cral.album_foto_visibili_ospiti');

        $fotoSbloccate = $accessoCompleto ? $album->foto : $album->foto->take($visibili);
        $fotoBloccate = $accessoCompleto ? collect() : $album->foto->slice($visibili);

        return view('foto.album', compact('album', 'fotoSbloccate', 'fotoBloccate', 'accessoCompleto'));
    }
}
