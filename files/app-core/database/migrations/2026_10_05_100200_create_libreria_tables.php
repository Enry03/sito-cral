<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('libri', function (Blueprint $table) {
            $table->id();
            $table->string('titolo');
            $table->string('autore')->nullable();
            $table->string('isbn', 20)->nullable()->index();
            $table->string('codice')->nullable()->unique();
            $table->string('librarything_id')->nullable()->unique();
            $table->string('editore')->nullable();
            $table->unsignedSmallInteger('anno')->nullable();
            $table->string('categoria')->nullable()->index();
            $table->text('descrizione')->nullable();
            $table->string('copertina')->nullable();
            $table->unsignedSmallInteger('copie_totali')->default(1);
            $table->timestamps();
        });

        Schema::create('prestiti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('libro_id')->nullable()->constrained('libri')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // Copia dei dati al momento del prestito: lo storico resta leggibile
            // anche se libro o utente vengono eliminati in seguito.
            $table->string('libro_titolo');
            $table->string('libro_codice')->nullable();
            $table->string('utente_nome');
            $table->string('utente_tessera')->nullable();
            $table->enum('stato', ['in_corso', 'restituito', 'annullato'])->default('in_corso');
            $table->date('data_prestito');
            $table->date('scadenza');
            $table->date('restituito_il')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['libro_id', 'stato']);
            $table->index(['user_id', 'stato']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestiti');
        Schema::dropIfExists('libri');
    }
};
