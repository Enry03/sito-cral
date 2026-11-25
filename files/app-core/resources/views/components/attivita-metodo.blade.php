{{--
    Scelta del modo di pagamento, obbligatoria per iscriversi a un'attività con una
    quota (tre schede cliccabili, funzionano anche senza JavaScript). Si usa dentro il
    modulo di iscrizione; l'importo è quello che spetta a chi si sta iscrivendo.
--}}
@props(['importo'])

@php
    $ammessi = \App\Models\IscrizioneAttivita::metodiAmmessi();
    $scelto = old('metodo_pagamento');
    $errore = $errors->first('metodo_pagamento');
    $descrizioni = [
        'contanti' => 'Porti la quota a '.app(\App\Services\DatiAzienda::class)->referenteContanti().', in azienda.',
        'bonifico' => in_array('bonifico', $ammessi, true) ? 'Dopo l\'iscrizione ti mostriamo i dati per fare il bonifico.' : 'Presto disponibile.',
        'paypal' => in_array('paypal', $ammessi, true) ? 'Dopo l\'iscrizione trovi il pulsante per pagare.' : 'Presto disponibile.',
    ];
@endphp

<fieldset {{ $attributes->merge(['class' => 'rounded-[1.5rem] bg-white p-5 text-cral-navy sm:p-6']) }} @if($errore) aria-describedby="errore-metodo" @endif>
    <legend class="sr-only">Come vuoi pagare la quota</legend>
    <h3 class="font-display text-xl font-bold tracking-tight">Come vuoi pagare? <span class="text-cral-red" aria-hidden="true">*</span></h3>
    <p class="mt-1 text-sm text-cral-navy-soft">La quota di <strong class="text-cral-navy">{{ $importo }}</strong> è obbligatoria: scegli come preferisci pagarla. L'iscrizione è completa quando la quota risulta pagata.</p>

    <div class="mt-4 grid gap-3 sm:grid-cols-3">
        @foreach(\App\Models\User::metodiPagamento() as $valore => $etichetta)
            <label class="block">
                <input type="radio" name="metodo_pagamento" value="{{ $valore }}" class="peer sr-only" required
                    @checked($scelto === $valore) @disabled(! in_array($valore, $ammessi, true))>
                <span class="choice-box">
                    <span class="flex items-start justify-between gap-3">
                        <span class="font-semibold leading-snug">{{ $etichetta }}</span>
                        <span class="choice-dot mt-0.5" aria-hidden="true"></span>
                    </span>
                    <span class="mt-1 block text-sm leading-relaxed text-cral-navy-soft">{{ $descrizioni[$valore] }}</span>
                </span>
            </label>
        @endforeach
    </div>

    @if($errore)
        <p id="errore-metodo" class="error-msg" role="alert">{{ $errore }}</p>
    @endif
</fieldset>
