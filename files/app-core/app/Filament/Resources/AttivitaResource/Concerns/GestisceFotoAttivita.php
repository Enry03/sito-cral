<?php

namespace App\Filament\Resources\AttivitaResource\Concerns;

use App\Services\GalleriaService;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;

/**
 * Le foto si caricano direttamente dal modulo dell'attività: i campi non
 * appartengono alla tabella "attivita", quindi si estraggono dai dati del
 * modulo prima del salvataggio e si elaborano dopo, quando l'attività esiste.
 */
trait GestisceFotoAttivita
{
    /** @var array<int, string> */
    protected array $fotoDaAggiungere = [];

    /** @var array<int, int|string> foto già caricate scelte dall'archivio */
    protected array $fotoDaArchivio = [];

    protected ?string $didascaliaFoto = null;

    protected ?int $albumEsistenteId = null;

    protected bool $creaAlbumSeSingola = false;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function estraiCampiFoto(array $data): array
    {
        $this->fotoDaAggiungere = array_values(array_filter(Arr::wrap($data['foto_nuove'] ?? [])));
        $this->fotoDaArchivio = array_values(array_filter(Arr::wrap($data['foto_archivio'] ?? [])));
        $this->didascaliaFoto = $data['didascalia_foto'] ?? null;
        $this->albumEsistenteId = filled($data['album_esistente'] ?? null) ? (int) $data['album_esistente'] : null;
        $this->creaAlbumSeSingola = (bool) ($data['crea_album'] ?? false);

        unset($data['foto_nuove'], $data['foto_archivio'], $data['didascalia_foto'], $data['album_esistente'], $data['crea_album'], $data['multi_giorno']);

        return $data;
    }

    protected function elaboraFotoAttivita(): bool
    {
        if ($this->fotoDaAggiungere === [] && $this->fotoDaArchivio === []) {
            return false;
        }

        $galleria = app(GalleriaService::class);
        $totale = 0;
        $album = null;

        if ($this->fotoDaAggiungere !== []) {
            $esito = $galleria->aggiungiFotoAttivita(
                $this->record,
                $this->fotoDaAggiungere,
                $this->didascaliaFoto,
                $this->albumEsistenteId,
                $this->creaAlbumSeSingola,
            );

            $totale += $esito['foto'];
            $album = $esito['album'];
        }

        if ($this->fotoDaArchivio !== []) {
            // Insieme alle foto appena caricate contano per la regola "più foto = album".
            $esito = $galleria->collegaFotoAttivita(
                $this->record,
                $this->fotoDaArchivio,
                $this->albumEsistenteId ?? $album?->id,
                $this->creaAlbumSeSingola || $totale > 0,
            );

            $totale += $esito['foto'];
            $album ??= $esito['album'];
        }

        $messaggio = $totale.($totale === 1 ? ' foto aggiunta' : ' foto aggiunte');
        $messaggio .= $album
            ? " all'album \"".$album->titolo.'" (visibile a chi è iscritto al sito o socio CRAL).'
            : ' come foto singola (visibile a chi è iscritto al sito o socio CRAL).';

        Notification::make()->success()->title($messaggio)->send();

        $this->fotoDaAggiungere = [];
        $this->fotoDaArchivio = [];

        return true;
    }
}
