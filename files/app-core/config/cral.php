<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Libreria
    |--------------------------------------------------------------------------
    | Durata di un prestito (giorni) e numero massimo di libri che un socio può
    | avere in prestito contemporaneamente.
    */
    'prestito' => [
        'giorni' => (int) env('CRAL_PRESTITO_GIORNI', 30),
        'max_attivi' => (int) env('CRAL_PRESTITO_MAX', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Foto
    |--------------------------------------------------------------------------
    | Quante foto di un album può vedere chi non è socio CRAL con tessera.
    */
    'album_foto_visibili_ospiti' => 2,

    /*
    |--------------------------------------------------------------------------
    | Tessera socio
    |--------------------------------------------------------------------------
    | Importo della quota associativa (in euro). Serve al pulsante "Paga con
    | PayPal" della richiesta di tessera: finché non è impostato (.env:
    | CRAL_QUOTA_TESSERA=25) il pulsante resta disattivato.
    */
    'quota_tessera' => (float) env('CRAL_QUOTA_TESSERA', 0),

    /*
    |--------------------------------------------------------------------------
    | PayPal
    |--------------------------------------------------------------------------
    | Per attivare il pulsante "Paga con PayPal" basta impostare nel file .env
    | l'indirizzo PayPal che riceve i pagamenti:
    |
    |     PAYPAL_BUSINESS_EMAIL=pagamenti@cralchietildo.it
    |
    | Finché non è impostato il pulsante compare ma non è cliccabile.
    */
    'paypal' => [
        'email' => env('PAYPAL_BUSINESS_EMAIL'),
        'valuta' => env('PAYPAL_CURRENCY', 'EUR'),
        'sandbox' => (bool) env('PAYPAL_SANDBOX', false),
    ],

];
