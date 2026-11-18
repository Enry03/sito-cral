<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * L'iscrizione a un'attività è automatica (niente più conferma
     * dell'amministratore) e subito dopo si sceglie come pagare, con gli stessi
     * modi della tessera: contanti, bonifico, PayPal.
     *
     * Le richieste rimaste "in attesa" diventano iscrizioni a tutti gli effetti.
     */
    public function up(): void
    {
        Schema::table('iscrizioni_attivita', function (Blueprint $table) {
            $table->string('metodo_pagamento', 20)->nullable()->after('pagato_il');
            $table->dateTime('pagamento_dichiarato_il')->nullable()->after('metodo_pagamento');
            $table->string('paypal_txn_id', 64)->nullable()->unique()->after('pagamento_dichiarato_il');
        });

        DB::table('iscrizioni_attivita')
            ->where('stato', 'in_attesa')
            ->update(['stato' => 'confermata', 'gestita_il' => now()]);
    }

    public function down(): void
    {
        Schema::table('iscrizioni_attivita', function (Blueprint $table) {
            $table->dropUnique(['paypal_txn_id']);
            $table->dropColumn(['metodo_pagamento', 'pagamento_dichiarato_il', 'paypal_txn_id']);
        });
    }
};
