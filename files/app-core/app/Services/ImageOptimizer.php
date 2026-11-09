<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Alleggerisce le foto caricate: ridimensiona l'originale, crea una miniatura
 * per le griglie e una versione minuscola (24px) usata al posto della foto vera
 * quando questa è riservata ai soci.
 *
 * Tutto è facoltativo: se sul server manca l'estensione GD (o EXIF, necessaria
 * per non ruotare male le foto da telefono) la foto resta com'è e il sito
 * continua a funzionare usando l'originale.
 */
class ImageOptimizer
{
    public const LATO_MAX = 1920;

    public const LATO_MINIATURA = 640;

    public const LATO_SFOCATA = 24;

    /** Sotto questa dimensione e entro LATO_MAX l'originale non viene riscritto. */
    public const PESO_MAX_ORIGINALE = 900 * 1024;

    public static function disponibile(): bool
    {
        return extension_loaded('gd') && function_exists('imagecreatetruecolor');
    }

    public static function percorsoMiniatura(string $percorso): string
    {
        return 'thumbs/'.$percorso;
    }

    public static function percorsoSfocata(string $percorso): string
    {
        return 'blur/'.$percorso;
    }

    /**
     * @param  bool  $creaDerivati  false per immagini che non servono in galleria (es. copertine)
     * @return array{originale: bool, miniatura: bool, sfocata: bool, nota: ?string}
     */
    public function elabora(string $percorso, bool $ridimensionaOriginale = true, bool $creaDerivati = true): array
    {
        $esito = ['originale' => false, 'miniatura' => false, 'sfocata' => false, 'nota' => null];

        if (! self::disponibile()) {
            $esito['nota'] = 'GD non disponibile';

            return $esito;
        }

        $disco = Storage::disk('public');

        if (! $disco->exists($percorso)) {
            $esito['nota'] = 'file mancante';

            return $esito;
        }

        $assoluto = $disco->path($percorso);
        $info = @getimagesize($assoluto);

        if (! $info || ! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            $esito['nota'] = 'formato non supportato';

            return $esito;
        }

        $tipo = $info[2];

        if ($tipo === IMAGETYPE_WEBP && ! function_exists('imagewebp')) {
            $esito['nota'] = 'WebP non supportato';

            return $esito;
        }

        $orientamento = 1;

        if ($tipo === IMAGETYPE_JPEG) {
            if (! function_exists('exif_read_data')) {
                // Senza EXIF non si può sapere se la foto è girata: meglio non toccarla.
                $esito['nota'] = 'EXIF non disponibile';

                return $esito;
            }

            $exif = @exif_read_data($assoluto);
            $orientamento = (int) ($exif['Orientation'] ?? 1);
        }

        @ini_set('memory_limit', '256M');

        $immagine = $this->carica($assoluto, $tipo);

        if (! $immagine) {
            $esito['nota'] = 'immagine non leggibile';

            return $esito;
        }

        $immagine = $this->ruota($immagine, $orientamento);
        $larghezza = imagesx($immagine);
        $altezza = imagesy($immagine);

        $troppoGrande = max($larghezza, $altezza) > self::LATO_MAX
            || filesize($assoluto) > self::PESO_MAX_ORIGINALE;

        if ($ridimensionaOriginale && $troppoGrande) {
            $ridotta = $this->ridimensiona($immagine, self::LATO_MAX);
            $esito['originale'] = $this->salva($ridotta, $assoluto, $tipo, 82);

            // Da qui in poi si lavora sulla versione ridotta (già ruotata).
            $this->libera($immagine);
            $immagine = $ridotta;
        }

        if ($creaDerivati) {
            $miniatura = $this->ridimensiona($immagine, self::LATO_MINIATURA);
            $esito['miniatura'] = $this->salva($miniatura, $disco->path(self::percorsoMiniatura($percorso)), $tipo, 78);
            $this->libera($miniatura);

            $sfocata = $this->ridimensiona($immagine, self::LATO_SFOCATA);
            $esito['sfocata'] = $this->salva($sfocata, $disco->path(self::percorsoSfocata($percorso)), $tipo, 40);
            $this->libera($sfocata);
        }

        $this->libera($immagine);

        return $esito;
    }

    public function elimina(string $percorso): void
    {
        $disco = Storage::disk('public');

        foreach ([$percorso, self::percorsoMiniatura($percorso), self::percorsoSfocata($percorso)] as $file) {
            if ($disco->exists($file)) {
                $disco->delete($file);
            }
        }
    }

    public function eliminaDerivati(string $percorso): void
    {
        $disco = Storage::disk('public');

        foreach ([self::percorsoMiniatura($percorso), self::percorsoSfocata($percorso)] as $file) {
            if ($disco->exists($file)) {
                $disco->delete($file);
            }
        }
    }

    private function carica(string $assoluto, int $tipo)
    {
        return match ($tipo) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($assoluto),
            IMAGETYPE_PNG => @imagecreatefrompng($assoluto),
            IMAGETYPE_WEBP => @imagecreatefromwebp($assoluto),
            default => false,
        };
    }

    private function ruota($immagine, int $orientamento)
    {
        $gradi = match ($orientamento) {
            3, 4 => 180,
            5, 6 => -90,
            7, 8 => 90,
            default => 0,
        };

        if ($gradi !== 0) {
            $ruotata = imagerotate($immagine, $gradi, 0);

            if ($ruotata) {
                imagedestroy($immagine);
                $immagine = $ruotata;
            }
        }

        // Orientamenti 2, 4, 5, 7 prevedono anche uno specchio orizzontale.
        if (in_array($orientamento, [2, 4, 5, 7], true)) {
            imageflip($immagine, IMG_FLIP_HORIZONTAL);
        }

        return $immagine;
    }

    private function ridimensiona($immagine, int $latoMax)
    {
        $larghezza = imagesx($immagine);
        $altezza = imagesy($immagine);
        $scala = min(1, $latoMax / max($larghezza, $altezza));

        $nuovaLarghezza = max(1, (int) round($larghezza * $scala));
        $nuovaAltezza = max(1, (int) round($altezza * $scala));

        $nuova = imagecreatetruecolor($nuovaLarghezza, $nuovaAltezza);
        imagealphablending($nuova, false);
        imagesavealpha($nuova, true);
        imagefill($nuova, 0, 0, imagecolorallocatealpha($nuova, 255, 255, 255, 127));
        imagecopyresampled($nuova, $immagine, 0, 0, 0, 0, $nuovaLarghezza, $nuovaAltezza, $larghezza, $altezza);

        return $nuova;
    }

    private function salva($immagine, string $destinazione, int $tipo, int $qualita): bool
    {
        $cartella = dirname($destinazione);

        if (! is_dir($cartella) && ! @mkdir($cartella, 0755, true) && ! is_dir($cartella)) {
            return false;
        }

        // Si scrive in un file temporaneo e poi si sostituisce: nessuno vede mai
        // un file a metà.
        $temporaneo = $destinazione.'.tmp';

        $ok = match ($tipo) {
            IMAGETYPE_JPEG => $this->salvaJpeg($immagine, $temporaneo, $qualita),
            IMAGETYPE_PNG => imagepng($immagine, $temporaneo, 8),
            IMAGETYPE_WEBP => imagewebp($immagine, $temporaneo, $qualita),
            default => false,
        };

        if (! $ok) {
            @unlink($temporaneo);

            return false;
        }

        return @rename($temporaneo, $destinazione) || (@copy($temporaneo, $destinazione) && @unlink($temporaneo));
    }

    private function salvaJpeg($immagine, string $destinazione, int $qualita): bool
    {
        // I JPEG non hanno trasparenza: si appoggia l'immagine su fondo bianco.
        $larghezza = imagesx($immagine);
        $altezza = imagesy($immagine);
        $piatta = imagecreatetruecolor($larghezza, $altezza);
        imagefill($piatta, 0, 0, imagecolorallocate($piatta, 255, 255, 255));
        imagecopy($piatta, $immagine, 0, 0, 0, 0, $larghezza, $altezza);
        imageinterlace($piatta, true);

        $ok = imagejpeg($piatta, $destinazione, $qualita);
        imagedestroy($piatta);

        return $ok;
    }

    private function libera(...$immagini): void
    {
        foreach ($immagini as $immagine) {
            if ($immagine) {
                imagedestroy($immagine);
            }
        }
    }
}
