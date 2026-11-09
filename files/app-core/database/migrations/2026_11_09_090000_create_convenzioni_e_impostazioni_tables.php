<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Aziende e negozi convenzionati, visibili solo ai soci con tessera.
        if (! Schema::hasTable('convenzioni')) {
            Schema::create('convenzioni', function (Blueprint $table) {
                $table->id();
                $table->string('nome');
                $table->string('slug')->unique();
                $table->string('convenzione');
                $table->text('descrizione')->nullable();
                $table->string('sito_web')->nullable();
                $table->json('immagini')->nullable();
                $table->boolean('pubblicata')->default(true);
                $table->timestamps();
            });
        }

        // Dati dell'associazione modificabili dal pannello (IBAN, quota, PayPal...):
        // una riga per ogni dato, così se ne possono aggiungere altri senza migrazioni.
        if (! Schema::hasTable('impostazioni')) {
            Schema::create('impostazioni', function (Blueprint $table) {
                $table->id();
                $table->string('chiave')->unique();
                $table->text('valore')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('impostazioni');
        Schema::dropIfExists('convenzioni');
    }
};
