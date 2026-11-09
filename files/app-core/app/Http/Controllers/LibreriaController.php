<?php

namespace App\Http\Controllers;

use App\Models\Libro;
use App\Models\Prestito;
use App\Services\OperazioneNonConsentita;
use App\Services\PrestitoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LibreriaController extends Controller
{
    public function index(Request $request)
    {
        $ricerca = trim((string) $request->query('q', ''));
        $categoria = (string) $request->query('categoria', '');
        $soloDisponibili = $request->boolean('disponibili');

        $libri = Libro::query()
            ->conDisponibilita()
            ->when($ricerca !== '', function ($query) use ($ricerca) {
                $like = '%'.addcslashes($ricerca, '%_\\').'%';

                $query->where(fn ($q) => $q
                    ->where('titolo', 'like', $like)
                    ->orWhere('autore', 'like', $like)
                    ->orWhere('isbn', 'like', $like)
                    ->orWhere('codice', 'like', $like));
            })
            ->when($categoria !== '', fn ($query) => $query->where('categoria', $categoria))
            ->when($soloDisponibili, fn ($query) => $query->whereRaw(
                'copie_totali > (select count(*) from prestiti where prestiti.libro_id = libri.id and prestiti.stato = ?)',
                ['in_corso']
            ))
            ->orderBy('titolo')
            ->paginate(12)
            ->withQueryString();

        $categorie = Libro::query()
            ->whereNotNull('categoria')
            ->where('categoria', '!=', '')
            ->distinct()
            ->orderBy('categoria')
            ->pluck('categoria');

        return view('libreria.index', compact('libri', 'categorie', 'ricerca', 'categoria', 'soloDisponibili'));
    }

    public function show(Request $request, Libro $libro)
    {
        $libro = Libro::conDisponibilita()->findOrFail($libro->id);

        $utente = $request->user();
        $prestitoUtente = $utente
            ? $libro->prestiti()->inCorso()->where('user_id', $utente->id)->first()
            : null;

        return view('libreria.show', compact('libro', 'prestitoUtente'));
    }

    public function presta(Request $request, Libro $libro, PrestitoService $servizio): RedirectResponse
    {
        $request->validate(['note' => ['nullable', 'string', 'max:500']]);

        try {
            $prestito = $servizio->presta($request->user(), $libro, $request->input('note'));
        } catch (OperazioneNonConsentita $e) {
            return redirect()->route('libreria.show', $libro)->with('errore_prestito', $e->getMessage());
        }

        return redirect()->route('libreria.show', $libro)->with(
            'ok_prestito',
            'Prestito registrato: ritira il libro presso il CRAL e restituiscilo entro il '.$prestito->scadenza->translatedFormat('d F Y').'.'
        );
    }

    public function mieiPrestiti(Request $request)
    {
        $prestiti = Prestito::with('libro')
            ->where('user_id', $request->user()->id)
            ->orderByRaw("case stato when 'in_corso' then 0 else 1 end")
            ->orderByDesc('data_prestito')
            ->get();

        return view('libreria.prestiti', compact('prestiti'));
    }
}
