<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Il pagamento della quota è obbligatorio per tutti. Chi si era iscritto a
     * un'attività prima che la quota fosse indicata (o prima che esistesse la quota
     * dei soci) risultava "gratuito": adesso ha l'importo che gli spetta, ridotto se
     * è socio CRAL. Si toccano solo le iscrizioni valide, non pagate e senza
     * pagamento già segnalato.
     */
    public function up(): void
    {
        $iscrizioni = DB::table('iscrizioni_attivita as i')
            ->join('attivita as a', 'a.id', '=', 'i.attivita_id')
            ->join('users as u', 'u.id', '=', 'i.user_id')
            ->where('i.stato', 'confermata')
            ->where('i.pagato', false)
            ->whereNull('i.pagamento_dichiarato_il')
            ->where(fn ($q) => $q->whereNull('i.importo')->orWhere('i.importo', '<=', 0))
            ->where('a.prezzo', '>', 0)
            ->get(['i.id', 'a.prezzo', 'a.prezzo_soci', 'u.ruolo', 'u.stato_iscrizione', 'u.numero_tessera']);

        foreach ($iscrizioni as $riga) {
            $socio = $riga->ruolo === 'admin'
                || ($riga->ruolo === 'socio' && $riga->stato_iscrizione === 'attivo' && ! empty($riga->numero_tessera));

            $importo = $socio && $riga->prezzo_soci !== null && (float) $riga->prezzo_soci < (float) $riga->prezzo
                ? (float) $riga->prezzo_soci
                : (float) $riga->prezzo;

            DB::table('iscrizioni_attivita')->where('id', $riga->id)->update(['importo' => $importo > 0 ? $importo : null]);
        }
    }

    public function down(): void
    {
        // Gli importi calcolati restano: non c'è modo di sapere quali erano vuoti.
    }
};
