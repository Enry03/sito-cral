<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Ripulisce l'HTML scritto con l'editor del pannello (descrizione delle attività)
 * prima di mostrarlo ai visitatori: restano titoli, paragrafi, elenchi, grassetto,
 * link e immagini; script, moduli, iframe, gestori di eventi (onclick...) e indirizzi
 * "javascript:" vengono tolti. Così anche un account amministratore compromesso, o un
 * testo copiato da un sito ostile, non può eseguire codice nel browser dei soci.
 */
class HtmlSicuro
{
    public static function pulisci(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        static $sanitizzatore = null;

        $sanitizzatore ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                ->allowSafeElements()
                ->allowRelativeLinks()
                ->allowRelativeMedias()
                ->allowLinkSchemes(['https', 'http', 'mailto', 'tel'])
                ->allowMediaSchemes(['https', 'http'])
                ->forceAttribute('a', 'rel', 'noopener noreferrer')
                ->withMaxInputLength(200000)
        );

        return $sanitizzatore->sanitize($html);
    }
}
