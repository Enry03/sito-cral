<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('numero_tessera')->nullable()->unique()->after('id');
            $table->enum('ruolo', ['socio', 'admin'])->default('socio')->after('email');
            $table->enum('stato_iscrizione', ['in_attesa', 'attivo', 'sospeso', 'scaduto'])->default('in_attesa')->after('ruolo');
            $table->string('telefono')->nullable()->after('stato_iscrizione');
            $table->date('data_nascita')->nullable()->after('telefono');
            $table->string('codice_fiscale', 16)->nullable()->after('data_nascita');
            $table->string('indirizzo')->nullable()->after('codice_fiscale');
            $table->string('foto_profilo')->nullable()->after('indirizzo');
            $table->date('data_iscrizione')->nullable()->after('foto_profilo');
            $table->date('data_scadenza_quota')->nullable()->after('data_iscrizione');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'numero_tessera',
                'ruolo',
                'stato_iscrizione',
                'telefono',
                'data_nascita',
                'codice_fiscale',
                'indirizzo',
                'foto_profilo',
                'data_iscrizione',
                'data_scadenza_quota',
            ]);
        });
    }
};
