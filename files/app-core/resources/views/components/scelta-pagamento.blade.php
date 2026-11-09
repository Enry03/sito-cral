{{--
    Scelta del modo di pagamento della quota di tessera (tre schede cliccabili,
    funzionano anche senza JavaScript). Scegliendo "bonifico" compaiono subito sotto
    i dati per pagare. I modi non ancora attivi sono visibili ma non scegliibili.
    - selected:       modo già scelto
    - requiredSocio:  true nel modulo di iscrizione (il JavaScript del modulo li rende
                      obbligatori solo per chi diventa socio)
    - required:       true se la scelta è sempre obbligatoria
--}}
@props(['selected' => null, 'requiredSocio' => false, 'required' => false, 'nome' => null])

@php
    $ammessi = \App\Models\User::metodiAmmessi();
    $importo = \App\Models\User::quotaTessera() > 0 ? '€ '.number_format(\App\Models\User::quotaTessera(), 2, ',', '.') : null;

    $descrizioni = [
        'contanti' => 'Porti la quota a un amministratore del CRAL, in azienda.',
        'bonifico' => in_array('bonifico', $ammessi, true) ? 'Ti mostriamo subito i dati per fare il bonifico.' : 'Presto disponibile.',
        'paypal' => in_array('paypal', $ammessi, true) ? 'Dopo l\'invio della richiesta trovi il pulsante per pagare.' : 'Presto disponibile.',
    ];
@endphp

<div {{ $attributes }}>
    <div class="grid gap-3 sm:grid-cols-3">
        @foreach(\App\Models\User::metodiPagamento() as $valore => $etichetta)
            <label class="block">
                <input type="radio" name="metodo_pagamento" value="{{ $valore }}" class="peer sr-only"
                    @if($requiredSocio) data-socio-required @endif
                    @required($required)
                    @checked($selected === $valore) @disabled(! in_array($valore, $ammessi, true))>
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

    @if(in_array('bonifico', $ammessi, true))
        {{-- Si apre da solo quando è scelto "bonifico" (vedi .when-bonifico in app.css) --}}
        <div class="collapse-rows when-bonifico">
            <div>
                <x-dati-bonifico class="mt-4" :nome="$nome" :importo="$importo" />
                <p class="help mt-3">Fai il bonifico quando vuoi: un amministratore approva la tessera appena vede arrivare l'accredito.</p>
            </div>
        </div>
    @endif
</div>
