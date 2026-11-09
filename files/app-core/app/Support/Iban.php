<?php

namespace App\Support;

/**
 * Controlli sull'IBAN: formato e cifre di controllo (modulo 97), così un errore
 * di battitura nel pannello non finisce sul sito come "dati per il bonifico".
 */
class Iban
{
    public static function normalizza(?string $iban): string
    {
        return strtoupper((string) preg_replace('/\s+/', '', (string) $iban));
    }

    public static function valido(?string $iban): bool
    {
        $iban = self::normalizza($iban);

        if (! preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]{11,30}$/', $iban)) {
            return false;
        }

        // Gli IBAN italiani hanno sempre 27 caratteri.
        if (str_starts_with($iban, 'IT') && strlen($iban) !== 27) {
            return false;
        }

        $spostato = substr($iban, 4).substr($iban, 0, 4);
        $numerico = '';

        foreach (str_split($spostato) as $carattere) {
            $numerico .= ctype_alpha($carattere) ? (string) (ord($carattere) - 55) : $carattere;
        }

        $resto = 0;

        foreach (str_split($numerico, 7) as $blocco) {
            $resto = (int) ($resto.$blocco) % 97;
        }

        return $resto === 1;
    }

    /** Gruppi di quattro caratteri, come si legge meglio: IT60 X054 2811 ... */
    public static function formatta(?string $iban): string
    {
        return trim(chunk_split(self::normalizza($iban), 4, ' '));
    }
}
