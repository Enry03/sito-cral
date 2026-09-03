<x-public-layout>
    <!-- Hero: letterhead + manifest -->
    <section class="border-b-2 border-cral-navy">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 grid lg:grid-cols-5 gap-12 lg:gap-16 items-start">

            <div class="lg:col-span-3">
                <span class="inline-flex items-center gap-2 font-grotesk text-xs font-bold tracking-[0.2em] uppercase text-cral-red">
                    /// L'associazione del dopolavoro aziendale
                </span>
                <h1 class="mt-5 font-display uppercase text-5xl sm:text-6xl lg:text-7xl leading-[0.95] tracking-wide">
                    Attività, eventi e vantaggi per tutti i colleghi
                </h1>
                <p class="mt-6 text-base sm:text-lg text-cral-navy-soft max-w-xl leading-relaxed">
                    Scopri le iniziative del CRAL, guarda le foto delle nostre attività e iscriviti per ottenere
                    la tua tessera associativa digitale.
                </p>
                <div class="mt-9 flex flex-wrap items-center gap-4">
                    <a href="{{ route('register') }}" class="inline-flex items-center border-2 border-cral-navy bg-cral-red px-7 py-3.5 text-sm font-bold tracking-[0.15em] uppercase text-white stack-shadow-sm hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-none transition">
                        Iscriviti al CRAL
                    </a>
                    <a href="{{ route('attivita.index') }}" class="inline-flex items-center border-2 border-cral-navy px-7 py-3.5 text-sm font-bold tracking-[0.15em] uppercase hover:bg-cral-navy hover:text-white transition">
                        Scopri le attività
                    </a>
                </div>
            </div>

            <!-- Manifest panel -->
            <div class="lg:col-span-2">
                <div class="border-2 border-cral-navy bg-white">
                    <div class="flex items-center justify-between px-5 py-3 border-b-2 border-cral-navy">
                        <span class="font-grotesk text-xs font-bold tracking-[0.2em] uppercase text-cral-red">/// Prossime attività</span>
                        <span class="font-grotesk text-[10px] font-bold tracking-widest uppercase text-cral-navy-soft">Manifesto</span>
                    </div>

                    @if($prossimeAttivita->isEmpty())
                        <div class="px-5 py-10 text-center">
                            <p class="font-display text-2xl uppercase tracking-wide text-cral-navy-soft">Nessuna tratta<br>in programma</p>
                            <p class="mt-2 text-sm text-cral-navy-soft">Torna presto a trovarci.</p>
                        </div>
                    @else
                        <ul class="divide-y-2 divide-cral-navy">
                            @foreach($prossimeAttivita as $i => $attivita)
                                <li>
                                    <a href="{{ route('attivita.show', $attivita->slug) }}" class="ticket-card ticket-card--no-notch block px-5 py-4 hover:bg-cral-cream/60 transition">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-6 w-6 shrink-0 items-center justify-center bg-cral-navy text-white font-grotesk text-xs font-bold">{{ $i + 1 }}</span>
                                            <span class="font-grotesk text-sm font-bold uppercase tracking-wide truncate flex-1">{{ $attivita->titolo }}</span>
                                            @if($attivita->data_evento)
                                                <span class="shrink-0 bg-cral-red text-white text-[10px] font-bold tracking-widest uppercase px-2 py-1">
                                                    {{ $attivita->data_evento->translatedFormat('d M') }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($attivita->luogo)
                                            <div class="tear-line my-2.5"></div>
                                            <p class="font-grotesk text-xs text-cral-navy-soft tracking-wide">{{ $attivita->luogo }}</p>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <a href="{{ route('attivita.index') }}" class="flex items-center justify-center gap-2 border-t-2 border-cral-navy px-5 py-3 text-xs font-bold tracking-[0.15em] uppercase hover:bg-cral-navy hover:text-white transition">
                        Vedi tutte le attività &rarr;
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Tessera preview -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 grid lg:grid-cols-5 gap-12 lg:gap-16 items-center">
        <div class="lg:col-span-2 order-2 lg:order-1">
            <div class="relative max-w-sm mx-auto">
                <div class="ticket-card border-2 border-cral-navy bg-cral-navy text-white p-6 stack-shadow">
                    <div class="flex items-center justify-between">
                        <span class="font-display text-lg tracking-wide">CRAL</span>
                        <span class="font-grotesk text-[10px] font-bold tracking-[0.2em] uppercase text-white/60">Tessera socio</span>
                    </div>
                    <div class="tear-line my-4 opacity-40"></div>
                    <p class="font-grotesk text-[10px] tracking-[0.2em] uppercase text-white/50">Intestatario</p>
                    <p class="font-display text-2xl uppercase tracking-wide mt-1">Nome Cognome</p>
                    <div class="mt-6 flex items-end justify-between">
                        <div>
                            <p class="font-grotesk text-[10px] tracking-[0.2em] uppercase text-white/50">N. tessera</p>
                            <p class="font-grotesk font-bold tracking-widest">CRAL-0000</p>
                        </div>
                        <div class="h-12 w-12 border-2 border-white/40 grid grid-cols-3 grid-rows-3 gap-px p-1">
                            @for($i = 0; $i < 9; $i++)
                                <span class="{{ in_array($i, [0,2,4,6,8]) ? 'bg-white/70' : '' }}"></span>
                            @endfor
                        </div>
                    </div>
                </div>
                <p class="mt-4 text-center font-grotesk text-[11px] tracking-widest uppercase text-cral-navy-soft">Anteprima — il design definitivo arriva a breve</p>
            </div>
        </div>
        <div class="lg:col-span-3 order-1 lg:order-2">
            <span class="font-grotesk text-xs font-bold tracking-[0.2em] uppercase text-cral-red">/// La tua tessera</span>
            <h2 class="mt-3 font-display uppercase text-4xl sm:text-5xl leading-[0.95] tracking-wide">Una tessera vera, non una semplice mail</h2>
            <p class="mt-5 text-cral-navy-soft leading-relaxed max-w-lg">
                Quando ti iscrivi, la richiesta viene esaminata da un amministratore. Una volta approvata, ricevi
                una tessera digitale con numero identificativo e codice QR, verificabile all'ingresso delle
                nostre attività.
            </p>
            <a href="{{ route('register') }}" class="mt-7 inline-flex items-center border-2 border-cral-navy bg-cral-red px-7 py-3.5 text-sm font-bold tracking-[0.15em] uppercase text-white stack-shadow-sm hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-none transition">
                Richiedi la tua tessera
            </a>
        </div>
    </section>

    <!-- Foto teaser -->
    @if($fotoRecenti->isNotEmpty())
        <section class="border-y-2 border-cral-navy bg-white py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between mb-10">
                    <div>
                        <span class="font-grotesk text-xs font-bold tracking-[0.2em] uppercase text-cral-red">/// Dalla galleria</span>
                        <h2 class="mt-3 font-display uppercase text-3xl sm:text-4xl tracking-wide">Un assaggio delle nostre attività</h2>
                    </div>
                    <a href="{{ route('foto.index') }}" class="hidden sm:inline-flex text-xs font-bold tracking-[0.15em] uppercase hover:text-cral-red transition">
                        Vedi tutte &rarr;
                    </a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach($fotoRecenti as $foto)
                        <div class="aspect-square border-2 border-cral-navy overflow-hidden">
                            <img src="{{ asset('uploads/'.$foto->percorso) }}" alt="{{ $foto->didascalia }}" class="h-full w-full object-cover hover:scale-105 transition duration-300">
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Closing stamp CTA -->
    <section class="bg-cral-red text-white py-24 relative overflow-hidden border-t-4 border-cral-navy">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
            <div class="mx-auto h-20 w-20 border-4 border-white rounded-full flex items-center justify-center rotate-[-8deg] mb-8">
                <span class="font-display text-xs tracking-widest uppercase leading-tight">Iscriviti<br>ora</span>
            </div>
            <h2 class="font-display uppercase text-4xl sm:text-5xl tracking-wide">Diventa socio del CRAL</h2>
            <p class="mt-5 text-white/85 max-w-xl mx-auto leading-relaxed">
                Iscriviti in pochi minuti: otterrai la tua tessera digitale e l'accesso all'area riservata con
                documenti e informazioni per i soci.
            </p>
            <a href="{{ route('register') }}" class="mt-8 inline-flex items-center border-2 border-white bg-cral-navy px-8 py-4 text-sm font-bold tracking-[0.15em] uppercase text-white hover:bg-white hover:text-cral-navy transition">
                Iscriviti ora
            </a>
        </div>
    </section>
</x-public-layout>
