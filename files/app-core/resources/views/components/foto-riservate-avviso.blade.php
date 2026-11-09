{{--
    Fascia sopra le foto sfocate: dice perché sono sfocate e cosa fare.
    Si mostra solo a chi non ha ancora l'accesso (ospiti e richieste in attesa).
--}}
@php
    $inAttesa = auth()->check() && ! auth()->user()->haAccessoSito();
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center justify-between gap-4 rounded-[1.75rem] bg-cral-red-tint p-5 sm:p-6']) }} role="note" data-reveal>
    <div class="flex items-start gap-4">
        <span class="mt-0.5 grid h-11 w-11 shrink-0 place-items-center rounded-full bg-white text-cral-red shadow-soft">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
        </span>
        <div>
            <p class="font-display text-lg font-bold tracking-tight text-cral-red-dark">Foto riservate</p>
            <p class="text-cral-navy-soft">
                @if($inAttesa)
                    Potrai vederle appena la tua richiesta sarà approvata.
                @else
                    Iscriviti al sito o diventa socio CRAL per visualizzare le foto.
                @endif
            </p>
        </div>
    </div>

    @if($inAttesa)
        <a href="{{ route('attesa-approvazione') }}" class="btn btn-primary btn-sm">Stato della richiesta</a>
    @else
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Iscriviti</a>
            <a href="{{ route('login') }}" class="text-sm font-semibold text-cral-navy-soft transition hover:text-cral-red">Accedi</a>
        </div>
    @endif
</div>
