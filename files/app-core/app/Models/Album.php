<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Un album di foto. Può stare dentro un altro album (un "macro-album"): per esempio
 * l'album "Concorso fotografico" contiene i sotto-album "Giorno 1", "Giorno 2"...
 * I livelli sono due: un sotto-album non può contenere altri album.
 *
 * Un sotto-album appartiene alla stessa attività del suo macro-album.
 */
class Album extends Model
{
    use HasFactory;

    protected $table = 'album';

    protected $fillable = [
        'titolo',
        'data_album',
        'attivita_id',
        'parent_id',
    ];

    protected function casts(): array
    {
        return ['data_album' => 'date'];
    }

    protected static function booted(): void
    {
        static::saving(function (Album $album) {
            if ($album->parent_id === null) {
                return;
            }

            $macro = static::find($album->parent_id);

            if (! $macro || $macro->parent_id !== null || ($album->exists && $macro->is($album))) {
                throw new InvalidArgumentException('Un album può stare solo dentro un album principale.');
            }

            if ($album->exists && $album->sottoAlbum()->exists()) {
                throw new InvalidArgumentException('Un album che contiene altri album non può stare dentro un altro album.');
            }

            // Il sotto-album segue l'attività del macro-album.
            $album->attivita_id = $macro->attivita_id;
        });

        // Se cambia l'attività del macro-album, la seguono anche i suoi sotto-album.
        static::saved(function (Album $album) {
            if ($album->wasChanged('attivita_id') && $album->parent_id === null) {
                $album->sottoAlbum()->update(['attivita_id' => $album->attivita_id]);
            }
        });
    }

    // ---------------------------------------------------------------- relazioni

    public function attivita()
    {
        return $this->belongsTo(Attivita::class);
    }

    public function foto()
    {
        return $this->hasMany(Foto::class);
    }

    /** L'album principale in cui sta questo album (null se è esso stesso un album principale). */
    public function macroAlbum()
    {
        return $this->belongsTo(Album::class, 'parent_id');
    }

    /** Gli album che stanno dentro questo. */
    public function sottoAlbum()
    {
        return $this->hasMany(Album::class, 'parent_id');
    }

    /**
     * La prima foto dell'album, usata come copertina (visibile a tutti).
     */
    public function copertinaFoto()
    {
        return $this->hasOne(Foto::class)->oldestOfMany();
    }

    // -------------------------------------------------------------------- scope

    /** Album di primo livello: quelli che non stanno dentro un altro album. */
    public function scopePrincipali(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Album che hanno qualcosa da mostrare sul sito: delle foto, oppure sotto-album
     * con delle foto.
     */
    public function scopeVisibili(Builder $query): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->has('foto')
            ->orWhereHas('sottoAlbum', fn (Builder $sotto) => $sotto->has('foto')));
    }

    /**
     * Aggiunge i conteggi usati nelle schede: foto dell'album (foto_count), sotto-album
     * (sotto_album_count) e foto dentro i sotto-album (foto_nei_sotto_count).
     */
    public function scopeConConteggi(Builder $query): Builder
    {
        return $query
            ->withCount([
                'foto',
                'sottoAlbum',
                'sottoAlbum as sotto_visibili_count' => fn (Builder $sotto) => $sotto->has('foto'),
            ])
            ->addSelect([
                'foto_nei_sotto_count' => Foto::query()
                    ->selectRaw('count(*)')
                    ->whereIn('album_id', static::query()->from('album as sotto')->select('sotto.id')->whereColumn('sotto.parent_id', 'album.id')),
            ]);
    }

    /**
     * SQL della data con cui un album si colloca nel tempo: la data indicata per l'album,
     * altrimenti quella dell'attività collegata, altrimenti quella di creazione. Così un
     * album di foto vecchie caricato oggi sta nel suo periodo, non tra i primi.
     */
    public static function sqlDataEffettiva(string $alias = 'album'): string
    {
        return "coalesce({$alias}.data_album, (select att.data_evento from attivita att where att.id = {$alias}.attivita_id), {$alias}.created_at)";
    }

    /** In ordine di data (decrescente: i più recenti per primi; crescente: dal più vecchio). */
    public function scopeCronologici(Builder $query, string $direzione = 'desc'): Builder
    {
        $direzione = strtolower($direzione) === 'asc' ? 'asc' : 'desc';

        return $query->orderByRaw(self::sqlDataEffettiva().' '.$direzione)->orderBy('album.id', $direzione);
    }

    // ------------------------------------------------------------------ utilità

    /** La data con cui l'album sta nel tempo (vedi sqlDataEffettiva). */
    public function dataEffettiva(): ?\Illuminate\Support\Carbon
    {
        $data = $this->data_album ?? $this->attivita?->data_evento ?? $this->created_at;

        return $data ? \Illuminate\Support\Carbon::parse($data) : null;
    }

    public function isMacro(): bool
    {
        return $this->parent_id === null && $this->sottoAlbum()->exists();
    }

    /** Totale delle foto dell'album e dei suoi sotto-album (usa i conteggi se già caricati). */
    public function fotoTotali(): int
    {
        $proprie = $this->foto_count ?? $this->foto()->count();
        $nei = $this->foto_nei_sotto_count
            ?? Foto::whereIn('album_id', $this->sottoAlbum()->pluck('id'))->count();

        return (int) $proprie + (int) $nei;
    }

    /**
     * La foto di copertina: la prima dell'album oppure, per un macro-album senza foto
     * proprie, la prima foto dei suoi sotto-album.
     */
    public function copertinaVisibile(): ?Foto
    {
        if ($this->relationLoaded('copertinaFoto') && $this->copertinaFoto) {
            return $this->copertinaFoto;
        }

        return $this->foto()->oldest()->first()
            ?? Foto::whereIn('album_id', $this->sottoAlbum()->pluck('id'))->oldest()->first();
    }

    /** Titolo con il percorso, per i menu del pannello: "Concorso fotografico › Giorno 1". */
    public function titoloCompleto(): string
    {
        return $this->parent_id ? ($this->macroAlbum?->titolo ?? '…').' › '.$this->titolo : $this->titolo;
    }
}
