@php
    $utente = auth()->user();
    $inAttesa = $utente && ! $utente->haAccessoSito();

    // Anteprima finta: nessun nome né dato delle convenzioni vere arriva al browser di chi non ha un account.
    $categorieFinte = ['Food & Beverage', 'Salute e benessere', 'Tempo libero'];
@endphp

<x-public-layout title="Convenzioni — CRAL" description="Sconti e vantaggi per gli iscritti e i soci CRAL presso aziende e negozi convenzionati.">
    <x-page-hero kicker="Vantaggi per gli iscritti" title="Convenzioni" subtitle="Sconti e vantaggi presso le aziende e i negozi convenzionati con il CRAL." />

    <section class="container-x pb-24 sm:pb-32">
        <div class="relative">
            {{-- Anteprima sfocata: serve solo a far venire voglia di entrare --}}
            <div class="pointer-events-none select-none space-y-14 blur-[7px]" aria-hidden="true" inert>
                @foreach($categorieFinte as $categoria)
                    <div>
                        <div class="h-8 w-56 rounded-full bg-cral-navy/20"></div>
                        <div class="mt-8 grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach(range(1, $loop->first ? 3 : 2) as $n)
                                <div>
                                    <div class="hero-bg aspect-[4/3] rounded-[1.5rem] shadow-soft"></div>
                                    <div class="mt-5 h-5 w-3/4 rounded-full bg-cral-navy/25"></div>
                                    <div class="mt-3 h-6 w-1/2 rounded-full bg-cral-red-tint"></div>
                                    <div class="mt-3 h-3 w-full rounded-full bg-cral-navy/10"></div>
                                    <div class="mt-2 h-3 w-5/6 rounded-full bg-cral-navy/10"></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Invito, sopra l'anteprima --}}
            <div class="absolute inset-0 flex items-start justify-center px-2 pt-10 sm:pt-20">
                <div class="navy-bg relative isolate w-full max-w-2xl overflow-hidden rounded-[2rem] p-8 text-white shadow-lift sm:p-10" data-reveal role="note">
                    <div class="pointer-events-none absolute -right-20 -top-20 -z-10 h-64 w-64 rounded-full bg-cral-red/40 blur-3xl" aria-hidden="true"></div>

                    <span class="grid h-14 w-14 place-items-center rounded-full bg-white/15">
                        <svg class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
                    </span>

                    <h2 class="mt-6 text-3xl font-bold leading-tight tracking-[-0.03em] sm:text-4xl">Le convenzioni sono riservate a chi è iscritto</h2>

                    @if($inAttesa)
                        <p class="mt-4 max-w-lg text-lg leading-relaxed text-white/80">
                            La tua richiesta è in attesa di approvazione. Appena sarà accolta potrai vedere tutte le convenzioni.
                        </p>
                        <div class="mt-8">
                            <a href="{{ route('attesa-approvazione') }}" class="btn btn-primary">Stato della richiesta</a>
                        </div>
                    @else
                        <p class="mt-4 max-w-lg text-lg leading-relaxed text-white/80">
                            @if($totale > 0)
                                Ci sono {{ $totale }} {{ $totale === 1 ? 'convenzione attiva' : 'convenzioni attive' }}: sconti e vantaggi presso aziende e negozi, divisi per categoria.
                            @else
                                Qui trovi gli sconti e i vantaggi presso aziende e negozi convenzionati, divisi per categoria.
                            @endif
                            Iscriviti al sito, è gratuito: se poi diventi socio CRAL hai anche le attività a prezzo ridotto.
                        </p>
                        <div class="mt-8 flex flex-wrap items-center gap-4">
                            <a href="{{ route('register') }}" class="btn btn-primary">Iscriviti</a>
                            <a href="{{ route('login') }}" class="text-sm font-semibold text-white/80 underline underline-offset-4 transition hover:text-white">Hai già un account? Accedi</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</x-public-layout>
