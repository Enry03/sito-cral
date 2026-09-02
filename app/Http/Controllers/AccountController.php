<?php

namespace App\Http\Controllers;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
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
