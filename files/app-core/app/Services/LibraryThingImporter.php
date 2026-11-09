<?php

namespace App\Services;

use App\Models\Libro;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Importa il catalogo da un file esportato da LibraryThing
 * (La tua libreria → Esporta → CSV o file con tabulazioni).
 *
 * È tollerante: riconosce le intestazioni anche in italiano, il separatore
 * (virgola, punto e virgola o tabulazione) e la codifica del file, così può
 * leggere anche un file passato da Excel.
 */
class LibraryThingImporter
{
    /** Nomi (in minuscolo) con cui può comparire ogni campo. */
    private const ALIAS = [
        'titolo' => ['title', 'titolo'],
        'autore' => ['primary author', 'author', 'autore', 'autore principale'],
        'pubblicazione' => ['publication', 'publisher', 'editore', 'pubblicazione'],
        'anno' => ['date', 'year', 'anno', 'publication year', 'data'],
        'isbn' => ['isbn', 'isbn-13', 'isbn13', 'isbn-10', 'isbn10'],
        'riassunto' => ['summary', 'description', 'descrizione', 'riassunto'],
        'copie' => ['copies', 'copie'],
        'collezioni' => ['collections', 'collezioni', 'categoria', 'category'],
        'tag' => ['tags', 'tag'],
        'id' => ['book id', 'bookid', 'librarything id', 'id libro'],
        'codice' => ['barcode', 'codice a barre', 'codice', 'call number', 'other call number'],
    ];

    /**
     * @return array{creati: int, aggiornati: int, saltati: int, righe: int, errori: list<string>}
     */
    public function importa(string $percorsoFile, bool $aggiornaEsistenti = true): array
    {
        $righe = $this->leggiRighe($percorsoFile);

        $esito = ['creati' => 0, 'aggiornati' => 0, 'saltati' => 0, 'righe' => count($righe), 'errori' => []];

        DB::transaction(function () use ($righe, $aggiornaEsistenti, &$esito) {
            foreach ($righe as $numero => $riga) {
                $dati = $this->mappa($riga);

                if ($dati === null) {
                    $esito['saltati']++;
                    $esito['errori'][] = 'Riga '.($numero + 2).': titolo mancante.';

                    continue;
                }

                $esistente = $this->trovaEsistente($dati);

                if ($esistente) {
                    if (! $aggiornaEsistenti) {
                        $esito['saltati']++;

                        continue;
                    }

                    $esistente->update($this->datiPerAggiornamento($dati, $esistente));
                    $esito['aggiornati']++;

                    continue;
                }

                $dati['codice'] = $this->codiceLibero($dati['codice'] ?? null);
                Libro::create($dati);
                $esito['creati']++;
            }
        });

        return $esito;
    }

    /**
     * @return list<array<string, string>>  righe con chiavi = nome del campo canonico
     */
    private function leggiRighe(string $percorsoFile): array
    {
        $contenuto = (string) file_get_contents($percorsoFile);

        // BOM iniziale e codifica: i file salvati da Excel sono spesso Windows-1252.
        $contenuto = preg_replace('/^\xEF\xBB\xBF/', '', $contenuto);

        if (! mb_check_encoding($contenuto, 'UTF-8')) {
            $contenuto = mb_convert_encoding($contenuto, 'UTF-8', 'Windows-1252');
        }

        $contenuto = str_replace(["\r\n", "\r"], "\n", $contenuto);
        $primaRiga = strtok($contenuto, "\n") ?: '';

        $separatore = collect(["\t", ';', ','])
            ->sortByDesc(fn ($s) => substr_count($primaRiga, $s))
            ->first();

        $flusso = fopen('php://temp', 'r+');
        fwrite($flusso, $contenuto);
        rewind($flusso);

        $intestazioni = null;
        $righe = [];

        while (($campi = fgetcsv($flusso, 0, $separatore, '"', '\\')) !== false) {
            if ($campi === [null] || $campi === []) {
                continue;
            }

            if ($intestazioni === null) {
                $intestazioni = $this->normalizzaIntestazioni($campi);

                continue;
            }

            $riga = [];

            foreach ($intestazioni as $indice => $nome) {
                if ($nome !== null && isset($campi[$indice]) && ! isset($riga[$nome])) {
                    $riga[$nome] = trim((string) $campi[$indice]);
                }
            }

            $righe[] = $riga;
        }

        fclose($flusso);

        if ($intestazioni === null || ! in_array('titolo', $intestazioni, true)) {
            throw new \InvalidArgumentException('Il file non sembra un\'esportazione di LibraryThing: manca la colonna "Title" (Titolo).');
        }

        return $righe;
    }

    /**
     * @param  list<string|null>  $campi
     * @return list<string|null>
     */
    private function normalizzaIntestazioni(array $campi): array
    {
        return array_map(function ($campo) {
            $nome = mb_strtolower(trim((string) $campo, " \t\n\r\0\x0B\""));

            foreach (self::ALIAS as $canonico => $alias) {
                if (in_array($nome, $alias, true)) {
                    return $canonico;
                }
            }

            return null;
        }, $campi);
    }

    /**
     * @param  array<string, string>  $riga
     * @return array<string, mixed>|null
     */
    private function mappa(array $riga): ?array
    {
        $titolo = trim($riga['titolo'] ?? '');

        if ($titolo === '') {
            return null;
        }

        $pubblicazione = $riga['pubblicazione'] ?? '';
        $anno = $this->estraiAnno($riga['anno'] ?? '') ?? $this->estraiAnno($pubblicazione);
        $copie = (int) preg_replace('/\D/', '', $riga['copie'] ?? '');

        return array_filter([
            'titolo' => Str::limit($titolo, 250, ''),
            'autore' => $this->normalizzaAutore($riga['autore'] ?? ''),
            'isbn' => $this->normalizzaIsbn($riga['isbn'] ?? ''),
            'librarything_id' => ($riga['id'] ?? '') !== '' ? $riga['id'] : null,
            'editore' => $this->estraiEditore($pubblicazione, $anno),
            'anno' => $anno,
            'categoria' => $this->estraiCategoria($riga['collezioni'] ?? '', $riga['tag'] ?? ''),
            'descrizione' => ($riga['riassunto'] ?? '') !== '' ? trim(strip_tags($riga['riassunto'])) : null,
            'codice' => ($riga['codice'] ?? '') !== '' ? Str::limit($riga['codice'], 250, '') : null,
            // Il numero di copie si aggiorna solo se il file lo riporta.
            'copie_totali' => $copie > 0 ? $copie : null,
        ], fn ($valore) => $valore !== null && $valore !== '');
    }

    private function normalizzaAutore(string $autore): ?string
    {
        $autore = trim($autore);

        if ($autore === '') {
            return null;
        }

        // LibraryThing scrive "Cognome, Nome": si riporta a "Nome Cognome".
        if (substr_count($autore, ',') === 1) {
            [$cognome, $nome] = array_map('trim', explode(',', $autore));

            if ($cognome !== '' && $nome !== '') {
                return $nome.' '.$cognome;
            }
        }

        return Str::limit($autore, 250, '');
    }

    private function normalizzaIsbn(string $isbn): ?string
    {
        $pulito = preg_replace('/[^0-9Xx,;\s]/', '', $isbn);
        $primo = preg_split('/[,;\s]+/', trim($pulito), -1, PREG_SPLIT_NO_EMPTY)[0] ?? '';

        return in_array(strlen($primo), [10, 13], true) ? strtoupper($primo) : null;
    }

    private function estraiAnno(string $testo): ?int
    {
        if (preg_match('/\b(1[4-9]\d{2}|20\d{2})\b/', $testo, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    private function estraiEditore(string $pubblicazione, ?int $anno): ?string
    {
        if (trim($pubblicazione) === '') {
            return null;
        }

        $testo = $anno ? str_replace((string) $anno, '', $pubblicazione) : $pubblicazione;
        $testo = trim($testo, " \t,;.()[]");

        if (str_contains($testo, ':')) {
            $testo = trim(Str::afterLast($testo, ':'), " ,;.");
        } elseif (str_contains($testo, ',')) {
            $segmenti = array_values(array_filter(array_map('trim', explode(',', $testo))));
            $testo = $segmenti[count($segmenti) - 1] ?? $testo;
        }

        return $testo !== '' ? Str::limit($testo, 250, '') : null;
    }

    private function estraiCategoria(string $collezioni, string $tag): ?string
    {
        $ignorate = ['your library', 'la tua libreria', 'tua libreria'];

        foreach ([$collezioni, $tag] as $elenco) {
            foreach (preg_split('/[,;|]/', $elenco) ?: [] as $voce) {
                $voce = trim($voce);

                if ($voce !== '' && ! in_array(mb_strtolower($voce), $ignorate, true)) {
                    return Str::limit($voce, 250, '');
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $dati
     */
    private function trovaEsistente(array $dati): ?Libro
    {
        if (! empty($dati['librarything_id'])) {
            $libro = Libro::where('librarything_id', $dati['librarything_id'])->first();

            if ($libro) {
                return $libro;
            }
        }

        if (! empty($dati['isbn'])) {
            $libro = Libro::where('isbn', $dati['isbn'])->first();

            if ($libro) {
                return $libro;
            }
        }

        return Libro::where('titolo', $dati['titolo'])
            ->where(fn ($q) => isset($dati['autore']) ? $q->where('autore', $dati['autore']) : $q->whereNull('autore'))
            ->first();
    }

    /**
     * In aggiornamento si completano i dati e si rinfrescano quelli di
     * LibraryThing, senza toccare codice e copertina impostati a mano.
     *
     * @param  array<string, mixed>  $dati
     * @return array<string, mixed>
     */
    private function datiPerAggiornamento(array $dati, Libro $esistente): array
    {
        unset($dati['codice']);

        if (! empty($dati['librarything_id']) && Libro::where('librarything_id', $dati['librarything_id'])->where('id', '!=', $esistente->id)->exists()) {
            unset($dati['librarything_id']);
        }

        return $dati;
    }

    private function codiceLibero(?string $codice): ?string
    {
        if ($codice === null || $codice === '') {
            return null;
        }

        return Libro::where('codice', $codice)->exists() ? null : $codice;
    }
}
