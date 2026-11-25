<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * - Gli amministratori sono anche soci: chi non ha ancora una tessera la riceve
     *   (numero progressivo, dopo l'ultimo assegnato).
     * - Album dentro un album ("macro-album" e sotto-album) e data dell'album.
     * - Categoria delle convenzioni, scelta dall'amministratore.
     */
    public function up(): void
    {
        Schema::table('album', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('attivita_id')->constrained('album')->nullOnDelete();
            // Data dell'album (per l'ordine cronologico): se manca vale quella dell'attività.
            $table->date('data_album')->nullable()->after('titolo')->index();
        });

        Schema::table('convenzioni', function (Blueprint $table) {
            $table->string('categoria', 100)->nullable()->after('nome')->index();
        });

        $ultimo = DB::table('users')->whereNotNull('numero_tessera')->pluck('numero_tessera')
            ->map(fn ($n) => preg_match('/^CRAL-(\d+)$/', (string) $n, $m) ? (int) $m[1] : 0)
            ->max() ?? 0;

        foreach (DB::table('users')->where('ruolo', 'admin')->where(fn ($q) => $q->whereNull('numero_tessera')->orWhere('numero_tessera', ''))->orderBy('id')->pluck('id') as $id) {
            $ultimo++;

            DB::table('users')->where('id', $id)->update([
                'numero_tessera' => 'CRAL-'.str_pad((string) $ultimo, 4, '0', STR_PAD_LEFT),
                'data_iscrizione' => DB::raw('coalesce(data_iscrizione, current_timestamp)'),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('convenzioni', function (Blueprint $table) {
            $table->dropIndex(['categoria']);
            $table->dropColumn('categoria');
        });

        Schema::table('album', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropIndex(['data_album']);
            $table->dropColumn('data_album');
        });
    }
};
