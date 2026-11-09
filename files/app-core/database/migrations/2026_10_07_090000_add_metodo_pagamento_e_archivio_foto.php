<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Come chi chiede la tessera intende pagare la quota: contanti | email | paypal.
            $table->string('metodo_pagamento', 20)->nullable()->after('richiede_tessera');
            // Codice dell'operazione PayPal confermata dalla notifica automatica di PayPal.
            $table->string('paypal_txn_id', 64)->nullable()->unique()->after('pagamento_dichiarato_il');
        });

        Schema::table('foto', function (Blueprint $table) {
            // Foto caricata ma non ancora usata: resta nell'archivio, non compare sul sito.
            $table->boolean('in_archivio')->default(false)->after('didascalia');
        });
    }

    public function down(): void
    {
        Schema::table('foto', function (Blueprint $table) {
            $table->dropColumn('in_archivio');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['paypal_txn_id']);
            $table->dropColumn(['metodo_pagamento', 'paypal_txn_id']);
        });
    }
};
