{{--
    Come pagare la quota di un'attività. Compare subito dopo l'iscrizione automatica,
    dentro il riquadro "Partecipazione". Gli stessi modi della tessera:
    - Paga in azienda in contanti: promemoria di portare la quota a chi incassa.
    - Paga tramite bonifico: mostra i dati inseriti dall'amministratore (pannello →
      Dati dell'azienda) e il pulsante "Ho fatto il bonifico".
    - Paga con PayPal: si apre in una nuova scheda; PayPal avvisa da solo il sito.
--}}
@props(['iscrizione'])

@php
    $attivita = $iscrizione->attivita;
    $utente = $iscrizione->user;
    $importo = '€ '.number_format((float) $iscrizione->importo, 2, ',', '.');
    $metodo = $iscrizione->metodo_pagamento;
    $ammessi = \App\Models\IscrizioneAttivita::metodiAmmessi();
    $paypal = $iscrizione->datiPaypal();
    $puoCambiare = $iscrizione->puoScegliereMetodo();
    $contatto = config('mail.admin_address');
    $referente = app(\App\Services\DatiAzienda::class)->referenteContanti();
    $causale = app(\App\Services\DatiAzienda::class)->causaleAttivita($attivita->titolo, $utente->name);
@endphp

<div {{ $attributes->merge(['class' => 'rounded-[1.5rem] bg-white p-5 text-cral-navy sm:p-6']) }} aria-labelledby="titolo-pagamento-attivita">
    <h3 id="titolo-pagamento-attivita" class="font-display text-xl font-bold tracking-tight">Paga la quota — {{ $importo }}</h3>

    @if($iscrizione->pagamento_dichiarato_il)
        <div class="mt-3 rounded-2xl bg-cral-navy-tint p-4">
            <p class="font-semibold">
                @if($metodo === 'bonifico')
                    Hai segnalato il bonifico il {{ $iscrizione->pagamento_dichiarato_il->translatedFormat('j F Y') }}.
                @else
                    Stiamo verificando il tuo pagamento con PayPal.
                @endif
            </p>
            <p class="mt-1 text-sm leading-relaxed text-cral-navy-soft">
                @if($metodo === 'bonifico')
                    Un amministratore controllerà l'accredito sul conto e segnerà la quota come pagata.
                @else
                    Di solito PayPal ce lo conferma in pochi minuti: ricarica questa pagina tra poco e vedrai la quota saldata.
                @endif
            </p>
        </div>
    @else
        <p class="mt-1 text-sm leading-relaxed text-cral-navy-soft">Scegli come preferisci pagare: puoi farlo adesso o tornare più tardi su questa pagina.</p>

        {{-- Scelta del modo di pagamento --}}
        <form method="POST" action="{{ route('attivita.pagamento.metodo', $attivita->slug) }}" class="mt-4 flex flex-wrap gap-2" aria-label="Modo di pagamento">
            @csrf
            @foreach(\App\Models\User::metodiPagamento() as $valore => $etichetta)
                @php $disattivo = ! in_array($valore, $ammessi, true); @endphp
                <button type="submit" name="metodo_pagamento" value="{{ $valore }}" @disabled($disattivo)
                    aria-pressed="{{ $metodo === $valore ? 'true' : 'false' }}"
                    class="rounded-full px-4 py-2.5 text-sm font-semibold transition duration-300 disabled:cursor-not-allowed disabled:opacity-50 {{ $metodo === $valore ? 'bg-cral-red text-white shadow-glow' : 'bg-cral-navy-tint text-cral-navy hover:bg-cral-navy/10' }}">
                    {{ $etichetta }}@if($disattivo) <span class="font-normal">(presto)</span>@endif
                </button>
            @endforeach
        </form>

        <div class="mt-4 rounded-2xl bg-cral-navy-tint p-4 sm:p-5">
            @if($metodo === 'contanti')
                <p class="font-semibold">Paga in azienda in contanti</p>
                <p class="mt-1 text-cral-navy-soft">
                    Ricordati di portare la quota di <strong class="text-cral-navy">{{ $importo }}</strong> a <strong class="text-cral-navy">{{ $referente }}</strong>, in azienda.
                    Quando l'avrà ricevuta segnerà la tua iscrizione come pagata.
                </p>
            @elseif($metodo === 'bonifico')
                @if(in_array('bonifico', $ammessi, true))
                    <p class="font-semibold">Paga tramite bonifico</p>
                    <p class="mt-1 text-cral-navy-soft">Fai il bonifico con questi dati, scrivendo la causale indicata. Poi premi il pulsante per avvisarci: così sappiamo di controllare l'accredito.</p>
                    <x-dati-bonifico class="mt-4" :nome="$utente->name" :importo="$importo" :causale="$causale" />
                    <form method="POST" action="{{ route('attivita.pagamento.bonifico', $attivita->slug) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">Ho fatto il bonifico</button>
                    </form>
                @else
                    <p class="font-semibold">Paga tramite bonifico</p>
                    <p class="mt-1 text-cral-navy-soft">I dati per il bonifico non sono ancora disponibili. Scrivi a <a class="font-semibold text-cral-red hover:underline" href="mailto:{{ $contatto }}">{{ $contatto }}</a> oppure scegli un altro modo di pagamento.</p>
                @endif
            @elseif($metodo === 'paypal')
                @if($paypal)
                    <p class="font-semibold">Paga con PayPal</p>
                    <p class="mt-1 text-cral-navy-soft">Il pagamento si apre in una nuova scheda, su PayPal, in sicurezza. Quando hai finito puoi chiudere quella scheda e tornare qui: la quota risulterà saldata da sola.</p>
                    <form action="{{ $paypal['action'] }}" method="post" target="_blank" class="mt-4">
                        @foreach($paypal['campi'] as $nome => $valore)
                            <input type="hidden" name="{{ $nome }}" value="{{ $valore }}">
                        @endforeach
                        <button type="submit" class="btn w-full bg-[#FFC439] text-cral-navy hover:bg-[#f2b82f] sm:w-auto">
                            Paga {{ $importo }} con <span class="font-black italic"><span class="text-[#003087]">Pay</span><span class="text-[#009cde]">Pal</span></span>
                        </button>
                    </form>
                @else
                    <p class="font-semibold">Paga con PayPal</p>
                    <p class="mt-1 text-cral-navy-soft">Il pagamento online non è ancora disponibile. Scegli un altro modo oppure scrivi a <a class="font-semibold text-cral-red hover:underline" href="mailto:{{ $contatto }}">{{ $contatto }}</a>.</p>
                    <button type="button" disabled class="btn mt-4 bg-[#FFC439]/60 text-cral-navy/60">Paga con PayPal</button>
                @endif
            @else
                <p class="font-semibold">Scegli un modo di pagamento qui sopra.</p>
                <p class="mt-1 text-cral-navy-soft">Puoi pagare in contanti in azienda, con un bonifico oppure con PayPal.</p>
            @endif
        </div>
    @endif
</div>
