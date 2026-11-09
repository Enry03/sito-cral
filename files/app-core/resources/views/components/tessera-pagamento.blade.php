{{--
    Come pagare la quota di tessera. Mostrato a chi ha una richiesta di tessera in
    attesa: sceglie (o cambia) il modo di pagamento e vede cosa fare.
    - Bonifico: si attiva da solo quando l'amministratore inserisce i dati del conto
      (pannello → Dati dell'azienda).
    - PayPal: si attiva da solo quando sono impostati l'indirizzo PayPal e la quota
      (pannello → Dati dell'azienda, oppure PAYPAL_BUSINESS_EMAIL e CRAL_QUOTA_TESSERA in .env).
--}}
@props(['utente'])

@php
    $paypal = $utente->datiPaypalTessera();
    $quota = \App\Models\User::quotaTessera();
    $contatto = config('mail.admin_address');
    $metodo = $utente->metodo_pagamento;
    $ammessi = \App\Models\User::metodiAmmessi();
    $importo = $quota > 0 ? '€ '.number_format($quota, 2, ',', '.') : null;
    $puoCambiare = $utente->puoPagareTessera();
@endphp

<section {{ $attributes->merge(['class' => 'rounded-[1.75rem] bg-white p-6 shadow-soft sm:p-8']) }} aria-labelledby="titolo-quota">
    @if($utente->quota_pagata)
        <div class="flex items-start gap-4">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-cral-red text-white">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
            </span>
            <div>
                <h2 id="titolo-quota" class="text-xl font-bold tracking-tight">Quota saldata, grazie!</h2>
                <p class="mt-1 text-cral-navy-soft">
                    @if($utente->pagatoConPaypal())
                        Abbiamo ricevuto il tuo pagamento con PayPal.
                    @endif
                    Un amministratore approverà la richiesta e ti invierà la tessera via email.
                </p>
            </div>
        </div>
    @else
        <h2 id="titolo-quota" class="text-xl font-bold tracking-tight">
            Quota associativa{{ $importo ? ' — '.$importo : '' }}
        </h2>
        <p class="mt-1 text-cral-navy-soft">
            Scegli come preferisci pagare. Appena la quota è stata saldata, un amministratore approva la richiesta e ti invia la tessera via email.
        </p>

        {{-- Scelta del modo di pagamento (finché non hai segnalato di aver pagato) --}}
        @if($puoCambiare)
            <form method="POST" action="{{ route('tessera.metodo') }}" class="mt-5 flex flex-wrap gap-2" aria-label="Modo di pagamento">
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
            <x-input-error :messages="$errors->get('metodo_pagamento')" />
        @endif

        <div class="mt-6 rounded-2xl bg-cral-navy-tint p-5">
            @if($metodo === 'contanti')
                <p class="font-semibold">Paga in azienda in contanti</p>
                <p class="mt-1 text-cral-navy-soft">
                    Porta la quota{{ $importo ? ' di '.$importo : '' }} a un amministratore del CRAL. Quando l'avrà ricevuta approverà la tua richiesta.
                </p>
            @elseif($metodo === 'bonifico')
                @if($utente->pagamento_dichiarato_il)
                    <p class="font-semibold">Hai segnalato il bonifico il {{ $utente->pagamento_dichiarato_il->translatedFormat('j F Y') }}.</p>
                    <p class="mt-1 text-cral-navy-soft">Un amministratore controllerà l'accredito sul conto e approverà la tessera: ti scriveremo via email.</p>
                @elseif(in_array('bonifico', $ammessi, true))
                    <p class="font-semibold">Paga tramite bonifico</p>
                    <p class="mt-1 text-cral-navy-soft">Fai il bonifico con questi dati, scrivendo la causale indicata. Poi premi il pulsante per avvisarci: così sappiamo di controllare l'accredito.</p>
                    <x-dati-bonifico class="mt-4" :nome="$utente->name" :importo="$importo" />
                    <form method="POST" action="{{ route('tessera.bonifico') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">Ho fatto il bonifico</button>
                    </form>
                @else
                    <p class="font-semibold">Paga tramite bonifico</p>
                    <p class="mt-1 text-cral-navy-soft">I dati per il bonifico non sono ancora disponibili. Scrivi a <a class="font-semibold text-cral-red hover:underline" href="mailto:{{ $contatto }}">{{ $contatto }}</a> oppure scegli un altro modo di pagamento.</p>
                @endif
            @elseif($metodo === 'paypal')
                @if($utente->pagamento_dichiarato_il)
                    <p class="font-semibold">Hai segnalato il pagamento il {{ $utente->pagamento_dichiarato_il->translatedFormat('j F Y') }}.</p>
                    <p class="mt-1 text-cral-navy-soft">Un amministratore verificherà l'incasso e approverà la tessera: ti scriveremo via email.</p>
                @elseif($paypal)
                    <p class="font-semibold">Paga con PayPal</p>
                    <p class="mt-1 text-cral-navy-soft">Verrai portato su PayPal per pagare in sicurezza. Gli amministratori ricevono subito la tua richiesta e la conferma del pagamento.</p>
                    <form action="{{ $paypal['action'] }}" method="post" class="mt-4">
                        @foreach($paypal['campi'] as $nome => $valore)
                            <input type="hidden" name="{{ $nome }}" value="{{ $valore }}">
                        @endforeach
                        <button type="submit" class="btn w-full bg-[#FFC439] text-cral-navy hover:bg-[#f2b82f] sm:w-auto">
                            Paga {{ $importo }} con <span class="font-black italic"><span class="text-[#003087]">Pay</span><span class="text-[#009cde]">Pal</span></span>
                        </button>
                    </form>
                @else
                    <p class="font-semibold">Paga con PayPal</p>
                    <p class="mt-1 text-cral-navy-soft">Il pagamento online sarà disponibile a breve. Nel frattempo scegli un altro modo oppure scrivi a <a class="font-semibold text-cral-red hover:underline" href="mailto:{{ $contatto }}">{{ $contatto }}</a>.</p>
                    <button type="button" disabled class="btn mt-4 bg-[#FFC439]/60 text-cral-navy/60">Paga con PayPal</button>
                @endif
            @else
                <p class="font-semibold">Scegli un modo di pagamento qui sopra.</p>
                <p class="mt-1 text-cral-navy-soft">Puoi pagare in contanti in azienda, con un bonifico oppure con PayPal.</p>
            @endif
        </div>
    @endif
</section>
