<?php

namespace App\Models;

use App\Services\ImageOptimizer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Foto extends Model
{
    use HasFactory;

    protected $table = 'foto';

    protected $fillable = [
        'attivita_id',
        'album_id',
        'percorso',
        'didascalia',
        'in_archivio',
    ];

    protected function casts(): array
    {
        return ['in_archivio' => 'boolean'];
    }

    /**
     * Foto visibili sul sito: quelle ancora "in archivio" (caricate ma non usate)
     * restano riservate al pannello di amministrazione.
     */
    public function scopePubbliche($query)
    {
        return $query->where('in_archivio', false);
    }

    /**
     * Foto che non fanno parte di nessun album: si possono riprendere per
     * un'attività o un album.
     */
    public function scopeSciolte($query)
    {
        return $query->whereNull('album_id');
    }

    protected static function booted(): void
    {
        // Una foto che entra in un album è per forza visibile: esce dall'archivio.
        static::saving(function (Foto $foto) {
            if ($foto->album_id) {
                $foto->in_archivio = false;
            }
        });

        // Appena caricata, la foto viene alleggerita e ne vengono create la
        // miniatura e l'anteprima sfocata. Un errore qui non deve mai impedire
        // il salvataggio: al peggio si userà l'originale.
        static::created(function (Foto $foto) {
            $foto->elaboraImmagine();
        });

        static::updated(function (Foto $foto) {
            if ($foto->wasChanged('percorso')) {
                $vecchio = $foto->getOriginal('percorso');

                if ($vecchio && ! static::where('percorso', $vecchio)->exists()) {
                    app(ImageOptimizer::class)->eliminaDerivati($vecchio);
                }

                $foto->elaboraImmagine();
            }
        });

        static::deleted(function (Foto $foto) {
            if ($foto->percorso && ! static::where('percorso', $foto->percorso)->exists()) {
                app(ImageOptimizer::class)->elimina($foto->percorso);
            }
        });
    }

    public function elaboraImmagine(): void
    {
        try {
            app(ImageOptimizer::class)->elabora($this->percorso);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function attivita()
    {
        return $this->belongsTo(Attivita::class);
    }

    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    public function url(): string
    {
        return asset('uploads/'.$this->percorso);
    }

    /**
     * Versione leggera per le griglie (se non esiste usa l'originale).
     */
    public function miniaturaUrl(): string
    {
        $miniatura = ImageOptimizer::percorsoMiniatura($this->percorso);

        return Storage::disk('public')->exists($miniatura) ? asset('uploads/'.$miniatura) : $this->url();
    }

    /**
     * Anteprima minuscola mostrata (sfocata) al posto della foto riservata:
     * l'indirizzo della foto vera non compare mai nella pagina.
     */
    public function sfocataUrl(): string
    {
        $sfocata = ImageOptimizer::percorsoSfocata($this->percorso);

        return Storage::disk('public')->exists($sfocata)
            ? asset('uploads/'.$sfocata)
            : asset('images/foto-riservata.svg');
    }
}
