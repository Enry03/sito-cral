<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Due prezzi per le attività (soci CRAL e non soci), domande da compilare
     * durante l'iscrizione (campi personalizzati) e un messaggio per gli iscritti.
     *
     * "prezzo" resta la quota intera, per chi non è socio.
     */
    public function up(): void
    {
        Schema::table('attivita', function (Blueprint $table) {
            $table->decimal('prezzo_soci', 8, 2)->nullable()->after('prezzo');
            $table->json('campi_iscrizione')->nullable()->after('prezzo_soci');
            $table->text('messaggio_iscritti')->nullable()->after('campi_iscrizione');
        });

        Schema::table('iscrizioni_attivita', function (Blueprint $table) {
            $table->json('risposte')->nullable()->after('importo');
        });
    }

    public function down(): void
    {
        Schema::table('iscrizioni_attivita', function (Blueprint $table) {
            $table->dropColumn('risposte');
        });

        Schema::table('attivita', function (Blueprint $table) {
            $table->dropColumn(['prezzo_soci', 'campi_iscrizione', 'messaggio_iscritti']);
        });
    }
};
