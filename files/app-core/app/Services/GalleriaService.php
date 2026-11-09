<?php

namespace App\Services;

use App\Models\Album;
use App\Models\Attivita;
use App\Models\Foto;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class GalleriaService
{
    /**
     * Aggiunge delle foto a un'attività seguendo le regole del CRAL:
     *
     * - scegliendo un album esistente dell'attività, le foto vanno lì;
     * - con più di una foto l'album si crea da solo (o si usa quello già
     *   esistente dell'attività) e le foto sono riservate ai soci oltre le prime due;
     * - con una sola foto resta una foto singola, visibile a tutti, a meno che
     *   l'amministratore non chieda esplicitamente di creare/usare un album.
     *
     * @param  array<int, string>|string|null  $percorsi
     * @return array{foto: int, album: ?Album}
     */
    public function aggiungiFotoAttivita(
        Attivita $attivita,
        array|string|null $percorsi,
        ?string $didascalia = null,
        ?int $albumId = null,
        bool $creaAlbum = false,
    ): array {
        $percorsi = array_values(array_filter(Arr::wrap($percorsi)));

        if ($percorsi === []) {
            return ['foto' => 0, 'album' => null];
        }

        return DB::transaction(function () use ($attivita, $percorsi, $didascalia, $albumId, $creaAlbum) {
            $album = $this->albumPerAttivita($attivita, count($percorsi), $albumId, $creaAlbum);

            foreach ($percorsi as $percorso) {
                Foto::create([
                    'percorso' => $percorso,
                    'didascalia' => filled($didascalia) ? $didascalia : null,
                    'attivita_id' => $attivita->id,
                    'album_id' => $album?->id,
                ]);
            }

            return ['foto' => count($percorsi), 'album' => $album];
        });
    }

    /**
     * Collega a un'attività delle foto già caricate (l'archivio del sito), con le
     * stesse regole di aggiungiFotoAttivita(). Si possono riprendere solo foto
     * che non sono già in un album.
     *
     * @param  array<int, int|string>  $fotoIds
     * @return array{foto: int, album: ?Album}
     */
    public function collegaFotoAttivita(
        Attivita $attivita,
        array $fotoIds,
        ?int $albumId = null,
        bool $creaAlbum = false,
    ): array {
        return DB::transaction(function () use ($attivita, $fotoIds, $albumId, $creaAlbum) {
            $foto = $this->fotoSciolte($fotoIds);

            if ($foto->isEmpty()) {
                return ['foto' => 0, 'album' => null];
            }

            $album = $this->albumPerAttivita($attivita, $foto->count(), $albumId, $creaAlbum);

            Foto::whereIn('id', $foto->modelKeys())->update([
                'attivita_id' => $attivita->id,
                'album_id' => $album?->id,
                'in_archivio' => false,
            ]);

            return ['foto' => $foto->count(), 'album' => $album];
        });
    }

    /**
     * Mette delle foto già caricate in un album (che resta collegato alla sua
     * attività, se ne ha una). Le foto escono dall'archivio e diventano visibili
     * secondo le regole dell'album.
     *
     * @param  array<int, int|string>  $fotoIds
     */
    public function spostaInAlbum(Album $album, array $fotoIds): int
    {
        return DB::transaction(function () use ($album, $fotoIds) {
            $ids = Foto::whereIn('id', array_filter($fotoIds))->pluck('id');

            if ($ids->isEmpty()) {
                return 0;
            }

            $dati = ['album_id' => $album->id, 'in_archivio' => false];

            if ($album->attivita_id) {
                $dati['attivita_id'] = $album->attivita_id;
            }

            Foto::whereIn('id', $ids)->update($dati);

            return $ids->count();
        });
    }

    /**
     * Crea un album e ci mette dentro delle foto già caricate.
     *
     * @param  array<int, int|string>  $fotoIds
     */
    public function creaAlbum(string $titolo, ?int $attivitaId = null, array $fotoIds = []): Album
    {
        return DB::transaction(function () use ($titolo, $attivitaId, $fotoIds) {
            $album = Album::create(['titolo' => $titolo, 'attivita_id' => $attivitaId]);

            $this->spostaInAlbum($album, $fotoIds);

            return $album;
        });
    }

    /**
     * Rimette delle foto nell'archivio: non fanno più parte di un album e non
     * compaiono sul sito finché non vengono riutilizzate.
     *
     * @param  array<int, int|string>  $fotoIds
     */
    public function mettiInArchivio(array $fotoIds): int
    {
        return Foto::whereIn('id', array_filter($fotoIds))->update([
            'album_id' => null,
            'attivita_id' => null,
            'in_archivio' => true,
        ]);
    }

    /**
     * @param  array<int, int|string>  $fotoIds
     * @return \Illuminate\Database\Eloquent\Collection<int, Foto>
     */
    private function fotoSciolte(array $fotoIds)
    {
        return Foto::sciolte()->whereIn('id', array_filter($fotoIds))->get(['id']);
    }

    private function albumPerAttivita(Attivita $attivita, int $numeroFoto, ?int $albumId, bool $creaAlbum): ?Album
    {
        if ($albumId) {
            return Album::where('id', $albumId)->where('attivita_id', $attivita->id)->firstOrFail();
        }

        if ($numeroFoto > 1 || $creaAlbum) {
            return $attivita->album()->oldest()->first()
                ?? Album::create(['titolo' => $attivita->titolo, 'attivita_id' => $attivita->id]);
        }

        return null;
    }
}
