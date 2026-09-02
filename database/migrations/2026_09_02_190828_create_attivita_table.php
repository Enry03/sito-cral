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
        Schema::create('attivita', function (Blueprint $table) {
            $table->id();
            $table->string('titolo');
            $table->string('slug')->unique();
            $table->text('descrizione');
            $table->dateTime('data_evento')->nullable();
            $table->string('luogo')->nullable();
            $table->string('immagine_copertina')->nullable();
            $table->boolean('pubblicata')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attivita');
    }
};
