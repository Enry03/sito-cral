<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attivita', function (Blueprint $table) {
            // Periodo: data_evento resta la data (e ora) di inizio.
            $table->dateTime('data_fine')->nullable()->after('data_evento');
            $table->boolean('ricorrente')->default(false)->after('data_fine');
            $table->json('date_ricorrenti')->nullable()->after('ricorrente');
            // Ultimo giorno in cui l'attività è "in programma" (calcolato).
            $table->date('data_ultima')->nullable()->index()->after('date_ricorrenti');

            // Iscrizioni dei soci con tessera.
            $table->boolean('iscrizione_abilitata')->default(false)->after('luogo');
            $table->dateTime('iscrizioni_apertura')->nullable()->after('iscrizione_abilitata');
            $table->dateTime('iscrizioni_chiusura')->nullable()->after('iscrizioni_apertura');
            $table->unsignedInteger('posti_max')->nullable()->after('iscrizioni_chiusura');
            $table->decimal('prezzo', 8, 2)->nullable()->after('posti_max');
        });

        DB::table('attivita')->update(['data_ultima' => DB::raw('DATE(data_evento)')]);

        Schema::create('iscrizioni_attivita', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('attivita_id')->constrained('attivita')->cascadeOnDelete();
            $table->decimal('importo', 8, 2)->nullable();
            $table->boolean('pagato')->default(false);
            $table->dateTime('pagato_il')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'attivita_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iscrizioni_attivita');

        Schema::table('attivita', function (Blueprint $table) {
            $table->dropColumn([
                'data_fine', 'ricorrente', 'date_ricorrenti', 'data_ultima',
                'iscrizione_abilitata', 'iscrizioni_apertura', 'iscrizioni_chiusura',
                'posti_max', 'prezzo',
            ]);
        });
    }
};
