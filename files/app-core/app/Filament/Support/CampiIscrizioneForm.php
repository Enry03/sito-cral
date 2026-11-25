<?php

namespace App\Filament\Support;

use App\Models\Attivita;
use App\Models\IscrizioneAttivita;
use Filament\Forms;

/**
 * Le domande del modulo di iscrizione di un'attività (scelte, testi, sì/no...) come
 * campi del pannello: l'amministratore le compila al posto della persona quando la
 * iscrive a mano, e le corregge quando modifica un'iscrizione.
 *
 * Le risposte non sono obbligatorie qui: chi gestisce può completarle in un secondo
 * momento (la persona le vede e le può cambiare dalla pagina dell'attività).
 */
class CampiIscrizioneForm
{
    public const STATO = 'risposte_form';

    /**
     * @return array<int, Forms\Components\Component>
     */
    public static function componenti(Attivita $attivita): array
    {
        $campi = $attivita->campi();

        if ($campi->isEmpty()) {
            return [];
        }

        return [
            Forms\Components\Section::make('Risposte al modulo di iscrizione')
                ->description('Le domande a cui la persona risponde iscrivendosi. Puoi compilarle tu e completarle o correggerle più avanti.')
                ->schema($campi->map(fn (array $campo) => self::campo($campo))->all())
                ->columns(2),
        ];
    }

    /**
     * @param  array{id: string, etichetta: string, tipo: string, opzioni: list<string>, obbligatorio: bool, aiuto: ?string}  $campo
     */
    private static function campo(array $campo): Forms\Components\Component
    {
        $nome = self::STATO.'.'.$campo['id'];
        $opzioni = array_combine($campo['opzioni'], $campo['opzioni']);

        $componente = match ($campo['tipo']) {
            Attivita::CAMPO_TESTO_LUNGO => Forms\Components\Textarea::make($nome)->rows(3)->maxLength(1500)->columnSpanFull(),
            Attivita::CAMPO_SCELTA => Forms\Components\Radio::make($nome)->options($opzioni),
            Attivita::CAMPO_SCELTA_MULTIPLA => Forms\Components\CheckboxList::make($nome)->options($opzioni),
            Attivita::CAMPO_SI_NO => Forms\Components\Radio::make($nome)->options(['si' => 'Sì', 'no' => 'No'])->inline(),
            Attivita::CAMPO_NUMERO => Forms\Components\TextInput::make($nome)->numeric()->minValue(0)->maxValue(100000),
            Attivita::CAMPO_ACCETTAZIONE => Forms\Components\Toggle::make($nome)->inline(false),
            default => Forms\Components\TextInput::make($nome)->maxLength(255),
        };

        return $componente
            ->label($campo['etichetta'].($campo['obbligatorio'] ? ' *' : ''))
            ->helperText($campo['aiuto']);
    }

    /**
     * Le risposte di un'iscrizione nella forma dei campi del pannello.
     *
     * @return array<string, mixed>
     */
    public static function statoDa(IscrizioneAttivita $iscrizione): array
    {
        $tipi = $iscrizione->attivita->campi()->pluck('tipo', 'id');
        $stato = [];

        foreach ($iscrizione->valoriPerModulo() as $id => $valore) {
            $stato[$id] = ($tipi[$id] ?? null) === Attivita::CAMPO_ACCETTAZIONE ? (bool) $valore : $valore;
        }

        return [self::STATO => $stato];
    }

    /**
     * Toglie i campi delle risposte dai dati del modulo e li trasforma nelle risposte
     * da salvare con l'iscrizione.
     *
     * @param  array<string, mixed>  $dati
     * @return array<string, mixed>
     */
    public static function applica(array $dati, Attivita $attivita): array
    {
        if (array_key_exists(self::STATO, $dati) || $attivita->haCampi()) {
            $risposte = (array) ($dati[self::STATO] ?? []);
            $dati['risposte'] = $attivita->preparaRisposte($risposte) ?: null;
        }

        unset($dati[self::STATO]);

        return $dati;
    }
}
