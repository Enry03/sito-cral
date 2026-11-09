@php
    $utente = auth()->user();
@endphp

<x-public-layout title="Convenzioni — CRAL" description="Sconti e vantaggi riservati ai soci CRAL presso aziende e negozi convenzionati.">
    <x-page-hero kicker="Vantaggi per i soci" title="Convenzioni" subtitle="Sconti e vantaggi presso le aziende e i negozi convenzionati con il CRAL." />

    <section class="container-x max-w-3xl pb-24 sm:pb-32">
        <div class="navy-bg relative isolate overflow-hidden rounded-[2rem] p-8 text-white shadow-lift sm:p-12" data-reveal>
            <div class="pointer-events-none absolute -right-20 -top-20 -z-10 h-64 w-64 rounded-full bg-cral-red/40 blur-3xl" aria-hidden="true"></div>

            <span class="grid h-14 w-14 place-items-center rounded-full bg-white/15">
                <svg class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
            </span>

            <h2 class="mt-6 text-3xl font-bold leading-tight tracking-[-0.03em] sm:text-4xl">Le convenzioni sono riservate ai soci con tessera</h2>

            @if(! $utente)
                <p class="mt-4 max-w-lg text-lg leading-relaxed text-white/80">
                    Diventa socio CRAL: con la tessera trovi qui tutti gli sconti e i vantaggi delle aziende e dei negozi convenzionati.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <a href="{{ route('register') }}" class="btn btn-primary">Diventa socio</a>
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-white/80 underline underline-offset-4 transition hover:text-white">Hai già un account? Accedi</a>
                </div>
            @elseif(! $utente->haAccessoSito())
                <p class="mt-4 max-w-lg text-lg leading-relaxed text-white/80">
                    La tua richiesta è in attesa di approvazione. Quando sarà accolta e avrai la tessera potrai vedere le convenzioni.
                </p>
                <div class="mt-8">
                    <a href="{{ route('attesa-approvazione') }}" class="btn btn-primary">Stato della richiesta</a>
                </div>
            @else
                <p class="mt-4 max-w-lg text-lg leading-relaxed text-white/80">
                    Sei iscritto al sito, ma per usare le convenzioni serve la tessera di socio CRAL.
                </p>
                <div class="mt-8">
                    <a href="{{ route('socio.richiesta') }}" class="btn btn-primary">
                        {{ $utente->richiede_tessera ? 'Vedi la richiesta di tessera' : 'Diventa socio CRAL' }}
                    </a>
                </div>
            @endif
        </div>
    </section>
</x-public-layout>
