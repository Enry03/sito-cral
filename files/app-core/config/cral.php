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
    | Anno della tessera
    |--------------------------------------------------------------------------
    | Anno stampato sulla grafica della tessera e da cui vale: chi diventa socio
    | adesso lo è dal 2027. Serve solo per i testi del sito.
    */
    'tessera_anno' => (int) env('CRAL_TESSERA_ANNO', 2027),

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
    | Contanti
    |--------------------------------------------------------------------------
    | A chi si portano i contanti (quota di tessera o di un'attività). Compare nel
    | promemoria di chi sceglie "Paga in azienda in contanti". Si può cambiare dal
    | pannello (Dati dell'azienda) oppure con CRAL_REFERENTE_CONTANTI nel .env.
    */
    'referente_contanti' => env('CRAL_REFERENTE_CONTANTI', 'Bruna'),

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
