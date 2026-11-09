{{--
    Finestra che spiega perché le foto sono sfocate. Va una volta sola per pagina:
    si apre toccando qualsiasi anteprima con l'attributo data-foto-riservata.
    Le foto sono per chi ha un account approvato (iscritti al sito, soci CRAL).
--}}
@php
    $utente = auth()->user();
    $inAttesa = $utente && ! $utente->haAccessoSito();
@endphp

<div x-data="fotoRiservate" @click.window="apriDa($event)" @keydown.escape.window="chiudi()">
    <div x-show="aperta" x-cloak x-transition.opacity.duration.250ms style="display: none;"
        class="fixed inset-0 z-[90] grid place-items-center overflow-y-auto bg-cral-navy-deep/70 p-4 backdrop-blur-sm"
        role="dialog" aria-modal="true" aria-labelledby="titolo-foto-riservate" @click.self="chiudi()">

        <div class="relative w-full max-w-md rounded-[2rem] bg-white p-7 text-center shadow-lift sm:p-9"
            x-show="aperta" x-transition:enter="transition duration-500 ease-premium" x-transition:enter-start="translate-y-6 scale-95 opacity-0" x-transition:enter-end="translate-y-0 scale-100 opacity-100">

            <button type="button" @click="chiudi()" class="absolute right-4 top-4 grid h-10 w-10 place-items-center rounded-full bg-cral-navy-tint text-cral-navy transition hover:bg-cral-navy/10" aria-label="Chiudi">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>

            <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-cral-red-tint text-cral-red">
                <svg class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
            </span>

            <h2 id="titolo-foto-riservate" class="mt-5 font-display text-2xl font-bold tracking-tight">Foto riservate</h2>

            @if($inAttesa)
                <p class="mt-3 leading-relaxed text-cral-navy-soft">
                    La tua richiesta è in attesa di approvazione: appena un amministratore l'avrà accolta potrai vedere tutte le foto.
                </p>
                <a x-ref="azione" href="{{ route('attesa-approvazione') }}" class="btn btn-primary mt-6 w-full">Stato della richiesta</a>
            @else
                <p class="mt-3 leading-relaxed text-cral-navy-soft">
                    Iscriviti al sito o diventa socio CRAL per visualizzare le foto.
                </p>
                <a x-ref="azione" href="{{ route('register') }}" class="btn btn-primary mt-6 w-full">Iscriviti</a>
                <a href="{{ route('login') }}" class="mt-4 inline-block text-sm font-semibold text-cral-navy-soft transition hover:text-cral-red">Hai già un account? Accedi</a>
            @endif
        </div>
    </div>
</div>
