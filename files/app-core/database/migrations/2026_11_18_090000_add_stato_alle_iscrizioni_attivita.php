<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ogni iscrizione a un'attività è ora una richiesta che l'amministratore
     * conferma o declina. Le iscrizioni già presenti (e quelle inserite a mano
     * dall'amministratore) restano confermate: per questo il valore di partenza
     * della colonna è "confermata".
     */
    public function up(): void
    {
        Schema::table('iscrizioni_attivita', function (Blueprint $table) {
            $table->string('stato', 20)->default('confermata')->index()->after('attivita_id');
            $table->dateTime('gestita_il')->nullable()->after('pagato_il');
        });
    }

    public function down(): void
    {
        Schema::table('iscrizioni_attivita', function (Blueprint $table) {
            $table->dropIndex(['stato']);
            $table->dropColumn(['stato', 'gestita_il']);
        });
    }
};
