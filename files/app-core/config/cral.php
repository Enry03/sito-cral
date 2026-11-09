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
    | Tessera socio
    |--------------------------------------------------------------------------
    | Importo della quota associativa (in euro). Si può scrivere dal pannello di
    | amministrazione (Dati dell'azienda), che ha la precedenza; in alternativa qui
    | sotto da .env (CRAL_QUOTA_TESSERA=25). Finché non è impostato il pulsante
    | "Paga con PayPal" resta disattivato.
    */
    'quota_tessera' => (float) env('CRAL_QUOTA_TESSERA', 0),

    /*
    |--------------------------------------------------------------------------
    | PayPal
    |--------------------------------------------------------------------------
    | Per attivare il pulsante "Paga con PayPal" basta scrivere l'indirizzo PayPal
    | che riceve i pagamenti nel pannello di amministrazione (Dati dell'azienda), che
    | ha la precedenza, oppure nel file .env:
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
