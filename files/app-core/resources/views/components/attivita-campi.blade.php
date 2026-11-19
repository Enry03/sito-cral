{{--
    Le domande che l'amministratore ha aggiunto al modulo di iscrizione di un'attività
    (scelte, risposte libere, sì/no...). Si usa dentro un <form>, sia per iscriversi sia
    per correggere le risposte; $valori sono le risposte già date (codice => risposta).
    Funziona anche senza JavaScript.
--}}
@props(['attivita', 'valori' => []])

<div {{ $attributes->merge(['class' => 'grid gap-5']) }}>
    @foreach($attivita->campi() as $campo)
        @php
            $codice = $campo['id'];
            $nome = 'risposte['.$codice.']';
            $domId = 'risposta-'.$codice;
            $valore = old('risposte.'.$codice, $valori[$codice] ?? null);
            $errore = $errors->first('risposte.'.$codice) ?: $errors->first('risposte.'.$codice.'.*');
            $obbligatorio = $campo['obbligatorio'];
            $tipo = $campo['tipo'];
            $aiutoId = $campo['aiuto'] ? $domId.'-aiuto' : null;
        @endphp

        <div>
            @if($tipo === \App\Models\Attivita::CAMPO_ACCETTAZIONE)
                <label class="flex cursor-pointer items-start gap-3">
                    <input type="checkbox" id="{{ $domId }}" name="{{ $nome }}" value="1" class="peer sr-only" @checked($valore) @if($aiutoId) aria-describedby="{{ $aiutoId }}" @endif>
                    <span class="choice-box flex w-full items-start gap-3">
                        <span class="choice-dot mt-0.5" aria-hidden="true"></span>
                        <span class="font-semibold leading-snug">{{ $campo['etichetta'] }} <span class="text-cral-red" aria-hidden="true">*</span><span class="sr-only">(obbligatoria)</span></span>
                    </span>
                </label>
            @elseif(in_array($tipo, [\App\Models\Attivita::CAMPO_SCELTA, \App\Models\Attivita::CAMPO_SCELTA_MULTIPLA, \App\Models\Attivita::CAMPO_SI_NO], true))
                @php
                    $multipla = $tipo === \App\Models\Attivita::CAMPO_SCELTA_MULTIPLA;
                    $opzioni = $tipo === \App\Models\Attivita::CAMPO_SI_NO ? ['si' => 'Sì', 'no' => 'No'] : array_combine($campo['opzioni'], $campo['opzioni']);
                    $selezionati = array_map('strval', (array) $valore);
                @endphp
                <fieldset @if($aiutoId) aria-describedby="{{ $aiutoId }}" @endif>
                    <legend class="label">{{ $campo['etichetta'] }}@if($obbligatorio) <span class="text-cral-red" aria-hidden="true">*</span><span class="sr-only">(obbligatoria)</span>@endif</legend>
                    <div class="grid gap-2.5 {{ count($opzioni) > 2 ? 'sm:grid-cols-2' : 'grid-cols-2' }}">
                        @foreach($opzioni as $chiave => $etichetta)
                            <label class="block">
                                <input type="{{ $multipla ? 'checkbox' : 'radio' }}" name="{{ $nome }}{{ $multipla ? '[]' : '' }}" value="{{ $chiave }}" class="peer sr-only"
                                    @checked(in_array((string) $chiave, $selezionati, true)) @if($obbligatorio && ! $multipla) required @endif>
                                <span class="choice-box flex items-center gap-3 !p-3.5">
                                    <span class="choice-dot" aria-hidden="true"></span>
                                    <span class="font-semibold leading-snug">{{ $etichetta }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @else
                <label for="{{ $domId }}" class="label">{{ $campo['etichetta'] }}@if($obbligatorio) <span class="text-cral-red" aria-hidden="true">*</span><span class="sr-only">(obbligatoria)</span>@endif</label>
                @if($tipo === \App\Models\Attivita::CAMPO_TESTO_LUNGO)
                    <textarea id="{{ $domId }}" name="{{ $nome }}" rows="3" maxlength="1500" class="field" @required($obbligatorio) @if($errore) aria-invalid="true" @endif @if($aiutoId) aria-describedby="{{ $aiutoId }}" @endif>{{ is_array($valore) ? '' : $valore }}</textarea>
                @elseif($tipo === \App\Models\Attivita::CAMPO_NUMERO)
                    <input type="number" inputmode="decimal" min="0" max="100000" step="any" id="{{ $domId }}" name="{{ $nome }}" value="{{ is_array($valore) ? '' : $valore }}" class="field" @required($obbligatorio) @if($errore) aria-invalid="true" @endif @if($aiutoId) aria-describedby="{{ $aiutoId }}" @endif>
                @else
                    <input type="text" id="{{ $domId }}" name="{{ $nome }}" maxlength="255" value="{{ is_array($valore) ? '' : $valore }}" class="field" @required($obbligatorio) @if($errore) aria-invalid="true" @endif @if($aiutoId) aria-describedby="{{ $aiutoId }}" @endif>
                @endif
            @endif

            @if($campo['aiuto'])
                <p id="{{ $aiutoId }}" class="help">{{ $campo['aiuto'] }}</p>
            @endif
            @if($errore)
                <p class="error-msg" role="alert">{{ $errore }}</p>
            @endif
        </div>
    @endforeach
</div>
