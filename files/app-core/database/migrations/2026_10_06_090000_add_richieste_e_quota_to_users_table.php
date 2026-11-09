<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gestione delle richieste di iscrizione: data della richiesta, stato
     * "rifiutato" e tracciamento della quota associativa.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('stato_iscrizione', ['in_attesa', 'attivo', 'sospeso', 'scaduto', 'rifiutato'])->default('in_attesa')->change();
            $table->boolean('quota_pagata')->default(false)->after('richiede_tessera');
            $table->dateTime('quota_pagata_il')->nullable()->after('quota_pagata');
            $table->dateTime('pagamento_dichiarato_il')->nullable()->after('quota_pagata_il');
            $table->dateTime('richiesta_il')->nullable()->after('pagamento_dichiarato_il');
        });

        // Per le richieste già presenti la data è quella di registrazione.
        DB::table('users')->update(['richiesta_il' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        DB::table('users')->where('stato_iscrizione', 'rifiutato')->update(['stato_iscrizione' => 'sospeso']);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['quota_pagata', 'quota_pagata_il', 'pagamento_dichiarato_il', 'richiesta_il']);
            $table->enum('stato_iscrizione', ['in_attesa', 'attivo', 'sospeso', 'scaduto'])->default('in_attesa')->change();
        });
    }
};
