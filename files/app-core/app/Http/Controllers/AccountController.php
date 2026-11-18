<?php

namespace App\Http\Controllers;

use App\Services\TesseraCard;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    /**
     * Dati del socio, la sua tessera e il codice di verifica. La tessera compare
     * solo qui, dopo che l'amministratore ha approvato l'iscrizione, ed è sempre
     * quella di chi ha fatto l'accesso: nessuna immagine "vuota" è raggiungibile.
     */
    public function show()
    {
        $user = Auth::user();

        $qrDataUri = null;

        if ($user->numero_tessera) {
            $qrCode = new QrCode(data: route('tessera.verifica', $user->numero_tessera));
            $writer = new SvgWriter();
            $qrDataUri = $writer->write($qrCode)->getDataUri();
        }

        return view('account.show', [
            'user' => $user,
            'qrDataUri' => $qrDataUri,
            'tesseraImmagine' => TesseraCard::disponibile(),
            'annoTessera' => (int) config('cral.tessera_anno'),
        ]);
    }

    /**
     * Immagine della tessera personale (nome e numero scritti sulla grafica).
     * Con ?scarica=1 il browser la salva invece di mostrarla.
     */
    public function tessera(): Response
    {
        $user = Auth::user();
        $jpeg = app(TesseraCard::class)->jpeg($user->name, $user->numero_tessera);

        abort_if($jpeg === null, 404);

        $nomeFile = 'tessera-cral-'.TesseraCard::numeroBreve($user->numero_tessera ?: 'socio').'.jpg';

        return response($jpeg, 200, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => (request()->boolean('scarica') ? 'attachment' : 'inline').'; filename="'.$nomeFile.'"',
            // È una tessera personale: niente copie nelle cache condivise né nei motori di ricerca.
            'Cache-Control' => 'private, no-store',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
