<?php

namespace App\Http\Controllers;

use App\Models\Convenzione;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Convenzioni con aziende e negozi: le vedono gli iscritti al sito, i soci CRAL e gli
 * amministratori, raggruppate per categoria. Gli ospiti (e chi aspetta l'approvazione)
 * trovano una pagina con un'anteprima sfocata e finta, che invoglia a iscriversi: nessun
 * nome né dato delle convenzioni vere esce dal server.
 */
class ConvenzioneController extends Controller
{
    public function index(Request $request): View
    {
        if (! $request->user()?->haAccessoSito()) {
            return view('convenzioni.riservate', [
                'totale' => Convenzione::pubblicate()->count(),
            ]);
        }

        $convenzioni = Convenzione::pubblicate()->orderBy('nome')->get();

        return view('convenzioni.index', [
            'convenzioni' => $convenzioni,
            'gruppi' => self::raggruppaPerCategoria($convenzioni),
        ]);
    }

    public function show(Request $request, string $slug): View|RedirectResponse
    {
        // Prima il controllo dell'accesso, poi la ricerca: a chi non può vedere
        // le convenzioni non si dice nemmeno se una convenzione esiste.
        if (! $request->user()?->haAccessoSito()) {
            return redirect()->route('convenzioni.index');
        }

        $convenzione = Convenzione::pubblicate()->where('slug', $slug)->firstOrFail();

        return view('convenzioni.show', compact('convenzione'));
    }

    /**
     * Le convenzioni divise per categoria: le categorie in ordine alfabetico, quelle senza
     * categoria in fondo ("Altre convenzioni").
     *
     * @param  \Illuminate\Support\Collection<int, Convenzione>  $convenzioni
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, Convenzione>>
     */
    public static function raggruppaPerCategoria($convenzioni)
    {
        $conCategoria = $convenzioni->whereNotNull('categoria')
            ->groupBy('categoria')
            ->sortKeysUsing(fn ($a, $b) => strcasecmp((string) $a, (string) $b));

        $senza = $convenzioni->whereNull('categoria');

        return $senza->isEmpty() ? $conCategoria : $conCategoria->put('Altre convenzioni', $senza->values());
    }
}
