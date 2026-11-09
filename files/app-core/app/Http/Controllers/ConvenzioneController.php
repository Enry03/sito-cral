<?php

namespace App\Http\Controllers;

use App\Models\Convenzione;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

/**
 * Convenzioni con aziende e negozi: le vedono solo i soci CRAL con tessera (e gli
 * amministratori). Gli altri trovano una pagina che spiega come ottenere l'accesso,
 * senza nessun dato delle convenzioni (nemmeno i nomi).
 */
class ConvenzioneController extends Controller
{
    public function index(Request $request): View
    {
        if (! $request->user()?->isSocioCral()) {
            return view('convenzioni.riservate');
        }

        $convenzioni = Convenzione::pubblicate()->orderBy('nome')->get();

        return view('convenzioni.index', compact('convenzioni'));
    }

    public function show(Request $request, string $slug): View|RedirectResponse
    {
        // Prima il controllo dell'accesso, poi la ricerca: a chi non può vedere
        // le convenzioni non si dice nemmeno se una convenzione esiste.
        if (! $request->user()?->isSocioCral()) {
            return redirect()->route('convenzioni.index');
        }

        $convenzione = Convenzione::pubblicate()->where('slug', $slug)->firstOrFail();

        return view('convenzioni.show', compact('convenzione'));
    }
}
