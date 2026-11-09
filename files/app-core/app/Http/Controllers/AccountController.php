<?php

namespace App\Http\Controllers;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    /**
     * Dati del socio e codice di verifica. La tessera in sé arriva solo via email
     * quando l'amministratore approva la richiesta: sul sito non se ne mostra
     * l'immagine, così non si può copiare cambiando il nome.
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

        return view('account.show', compact('user', 'qrDataUri'));
    }
}
