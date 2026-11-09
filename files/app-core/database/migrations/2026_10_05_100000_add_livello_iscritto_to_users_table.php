<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Introduce il livello "iscritto" (account del sito senza tessera) e il
     * flag con cui un iscritto può chiedere di diventare socio CRAL.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('ruolo', ['iscritto', 'socio', 'admin'])->default('socio')->change();
            $table->boolean('richiede_tessera')->default(false)->after('stato_iscrizione');
        });
    }

    public function down(): void
    {
        DB::table('users')->where('ruolo', 'iscritto')->update(['ruolo' => 'socio']);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('richiede_tessera');
            $table->enum('ruolo', ['socio', 'admin'])->default('socio')->change();
        });
    }
};
