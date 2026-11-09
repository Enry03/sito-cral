<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Le foto del "secondo torneo di biliardino" erano state caricate come foto
 * singole: le riunisce in un album con quel titolo. Si può rilanciare senza
 * effetti doppi (riconosce l'album già creato) e non tocca le altre foto.
 */
return new class extends Migration
{
    private const TITOLO = 'Secondo torneo di biliardino';

    public function up(): void
    {
        $foto = DB::table('foto')
            ->whereNull('album_id')
            ->where('didascalia', 'like', '%secondo torneo di biliardino%')
            ->orderBy('id')
            ->get(['id', 'attivita_id']);

        if ($foto->isEmpty()) {
            return;
        }

        $album = DB::table('album')->where('titolo', self::TITOLO)->first();

        if (! $album) {
            $attivitaId = $foto->pluck('attivita_id')->filter()->countBy()->sortDesc()->keys()->first();

            $albumId = DB::table('album')->insertGetId([
                'titolo' => self::TITOLO,
                'attivita_id' => $attivitaId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $albumId = $album->id;
        }

        DB::table('foto')->whereIn('id', $foto->pluck('id'))->update([
            'album_id' => $albumId,
            'in_archivio' => false,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Operazione sui dati: non si annulla.
    }
};
