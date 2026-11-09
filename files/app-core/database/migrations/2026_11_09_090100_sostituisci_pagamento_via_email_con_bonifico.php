<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * "Contattaci per email" non esiste più: al suo posto c'è il bonifico. Chi aveva
     * scelto l'email non ha scelto il bonifico, quindi la scelta viene azzerata e
     * potrà indicare il nuovo modo di pagamento dalla pagina della sua richiesta.
     */
    public function up(): void
    {
        DB::table('users')->where('metodo_pagamento', 'email')->update(['metodo_pagamento' => null]);
    }

    public function down(): void
    {
        // Non c'è modo di sapere chi aveva scelto l'email: nulla da ripristinare.
    }
};
