<?php

namespace App\Services;

/**
 * Genera l'immagine della tessera CRAL con nome (e numero) scritti nei campi
 * bianchi della grafica ufficiale. Serve solo per l'email che l'amministratore
 * invia quando approva la richiesta: sul sito la tessera non viene mai mostrata
 * né scaricabile, così nessuno può copiarla scrivendoci un altro nome.
 *
 * Se sul server mancano GD o FreeType non succede nulla di grave: i metodi
 * restituiscono null e chi li usa ripiega sulla grafica "vuota" più il nome
 * scritto in testo.
 */
class TesseraCard
{
    /** La grafica vuota non è nella cartella pubblica: sul sito nessuno può scaricarla. */
    public const IMMAGINE = 'tessera/tessera-cral.jpg';

    /** Campo bianco del nominativo: x1, y1, x2, y2 (in pixel dell'immagine 1264x848). */
    public const BOX_NOME = [128, 624, 632, 713];

    /** Spazio sotto la scritta "N." dove va il numero. */
    public const BOX_NUMERO = [1067, 520, 1174, 554];

    /** Oltre questa lunghezza il nome viene abbreviato con i puntini. */
    public const LUNGHEZZA_MAX_NOME = 44;

    private const COLORE = [20, 33, 61]; // navy CRAL

    public static function disponibile(): bool
    {
        return extension_loaded('gd')
            && function_exists('imagettftext')
            && is_file(self::percorso())
            && is_file(self::font());
    }

    public static function percorso(): string
    {
        return resource_path(self::IMMAGINE);
    }

    public static function font(): string
    {
        return resource_path('fonts/Lato-Bold.ttf');
    }

    /**
     * Nome così come lo vedrà la tessera: spazi ripuliti e lunghezza limitata.
     */
    public static function normalizzaNome(?string $nome): string
    {
        $nome = trim(preg_replace('/\s+/u', ' ', (string) $nome));

        return mb_strlen($nome) > self::LUNGHEZZA_MAX_NOME
            ? rtrim(mb_substr($nome, 0, self::LUNGHEZZA_MAX_NOME - 1)).'…'
            : $nome;
    }

    /**
     * Sulla tessera compare solo la parte numerica ("CRAL-0042" → "0042").
     */
    public static function numeroBreve(?string $numero): string
    {
        $numero = trim((string) $numero);

        return preg_match('/^CRAL-(.+)$/i', $numero, $m) ? $m[1] : $numero;
    }

    /**
     * Immagine JPEG della tessera, oppure null se il server non può generarla.
     */
    public function jpeg(?string $nome, ?string $numero = null, int $qualita = 90): ?string
    {
        if (! self::disponibile()) {
            return null;
        }

        $immagine = @imagecreatefromjpeg(self::percorso());

        if (! $immagine) {
            return null;
        }

        $colore = imagecolorallocate($immagine, ...self::COLORE);
        $nome = self::normalizzaNome($nome);

        if ($nome !== '') {
            [$x1, $y1, $x2, $y2] = self::BOX_NOME;
            $this->scrivi($immagine, $nome, $colore, $x1 + 26, $y1, $x2 - 18, $y2, 44, false);
        }

        $breve = self::numeroBreve($numero);

        if ($breve !== '') {
            [$x1, $y1, $x2, $y2] = self::BOX_NUMERO;
            $this->scrivi($immagine, $breve, $colore, $x1 + 4, $y1, $x2 - 4, $y2, 24, true);
        }

        ob_start();
        imagejpeg($immagine, null, $qualita);
        $dati = ob_get_clean();
        imagedestroy($immagine);

        return $dati ?: null;
    }

    /**
     * Scrive il testo dentro il rettangolo, rimpicciolendo il carattere finché
     * ci sta, centrato in verticale (e in orizzontale se richiesto).
     */
    private function scrivi($immagine, string $testo, int $colore, int $x1, int $y1, int $x2, int $y2, int $dimensioneMax, bool $centra): void
    {
        $larghezzaUtile = $x2 - $x1;
        $dimensione = $dimensioneMax;

        do {
            $box = imagettfbbox($dimensione, 0, self::font(), $testo);
            $larghezza = $box[2] - $box[0];
            $dimensione--;
        } while ($larghezza > $larghezzaUtile && $dimensione > 14);

        $dimensione++;
        $box = imagettfbbox($dimensione, 0, self::font(), $testo);
        $larghezza = $box[2] - $box[0];
        $altezza = $box[1] - $box[7];

        $x = $centra ? $x1 + (int) (($larghezzaUtile - $larghezza) / 2) : $x1;
        $y = $y1 + (int) ((($y2 - $y1) + $altezza) / 2) - $box[1];

        imagettftext($immagine, $dimensione, 0, $x, $y, $colore, self::font(), $testo);
    }
}
