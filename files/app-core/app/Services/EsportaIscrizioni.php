<?php

namespace App\Services;

use App\Models\Attivita;
use App\Models\IscrizioneAttivita;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Elenco degli iscritti a un'attività in formato CSV (si apre con Excel), con le
 * risposte date al modulo di iscrizione: utile per formare le squadre, ordinare le
 * magliette, preparare l'elenco per il ristorante.
 */
class EsportaIscrizioni
{
    /**
     * @return list<string>
     */
    public function intestazioni(Attivita $attivita): array
    {
        return [
            'Nome',
            'Email',
            'Telefono',
            'Tessera',
            'Tipo',
            'Stato',
            'Quota',
            'Modo di pagamento',
            'Pagamento',
            'Iscritto il',
            ...$attivita->campi()->pluck('etichetta')->all(),
        ];
    }

    /**
     * @return list<string>
     */
    public function riga(Attivita $attivita, IscrizioneAttivita $iscrizione): array
    {
        $utente = $iscrizione->user;
        $risposte = collect($iscrizione->risposte ?? [])->keyBy('id');

        return [
            (string) $utente->name,
            (string) $utente->email,
            (string) $utente->telefono,
            (string) $utente->numero_tessera,
            $utente->isSocioCral() ? 'Socio CRAL' : 'Iscritto al sito',
            $iscrizione->statoLabel(),
            $iscrizione->importo !== null ? number_format((float) $iscrizione->importo, 2, ',', '') : '',
            (string) $iscrizione->metodoPagamentoLabel(),
            $iscrizione->confermata() ? $iscrizione->statoPagamento() : '',
            $iscrizione->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '',
            ...$attivita->campi()->map(function (array $campo) use ($risposte) {
                $valore = $risposte->get($campo['id'])['valore'] ?? '';

                return is_array($valore) ? implode(', ', $valore) : (string) $valore;
            })->all(),
        ];
    }

    public function scarica(Attivita $attivita): StreamedResponse
    {
        $nome = 'iscritti-'.\Illuminate\Support\Str::slug($attivita->titolo).'.csv';

        return response()->streamDownload(function () use ($attivita) {
            $uscita = fopen('php://output', 'w');
            // Il BOM fa riconoscere a Excel le lettere accentate.
            fwrite($uscita, "\xEF\xBB\xBF");

            fputcsv($uscita, $this->intestazioni($attivita), ';');

            $attivita->iscrizioni()->with('user')->orderBy('created_at')->each(function (IscrizioneAttivita $iscrizione) use ($uscita, $attivita) {
                fputcsv($uscita, array_map([$this, 'proteggi'], $this->riga($attivita, $iscrizione)), ';');
            });

            fclose($uscita);
        }, $nome, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Le risposte le scrive chiunque: una cella che inizia con = + - @ verrebbe
     * eseguita come formula da Excel. Si neutralizza anteponendo un apice.
     */
    public function proteggi(string $cella): string
    {
        // I numeri e i telefoni (+39 333 1234567) sono innocui e restano come sono.
        if ($cella === '' || preg_match('/^[+\-]?[0-9][0-9 .,\/]*$/', $cella)) {
            return $cella;
        }

        return str_contains("=+-@\t\r", $cella[0]) ? "'".$cella : $cella;
    }
}
