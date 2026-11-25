<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * Pagina a cui porta il QR della tessera: dice se la tessera è valida. È pubblica (chi
 * controlla la tessera non ha un account), quindi mostra il minimo: nome e iniziale del
 * cognome. I numeri di tessera sono progressivi: con il nome intero chiunque potrebbe
 * scorrerli tutti e ricavare l'elenco dei soci.
 */
class TesseraVerificaController extends Controller
{
    public function show(string $numeroTessera)
    {
        $socio = User::where('numero_tessera', Str::limit($numeroTessera, 30, ''))->first();

        $valida = $socio && $socio->isAttivo();

        return response()
            ->view('tessera.verifica', [
                'socio' => $valida ? $socio : null,
                'nomeMascherato' => $valida ? self::mascheraNome((string) $socio->name) : null,
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Cache-Control', 'no-store, private');
    }

    /** "Mario Rossi" → "Mario R."; "Anna Maria De Luca" → "Anna M." */
    public static function mascheraNome(string $nome): string
    {
        $parti = preg_split('/\s+/u', trim($nome), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($parti) < 2) {
            return $parti[0] ?? '';
        }

        return $parti[0].' '.mb_strtoupper(mb_substr($parti[1], 0, 1)).'.';
    }
}
