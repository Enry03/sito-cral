<x-public-layout title="CRAL Chieti — attività, eventi e vantaggi per i colleghi" description="Scopri le iniziative del CRAL, iscriviti per vedere le foto delle attività e diventa socio per usare le convenzioni.">

    {{-- ====================================================== apertura --}}
    <section class="hero-bg relative isolate overflow-hidden text-white">
        <div class="pointer-events-none absolute -right-32 -top-32 -z-10 h-[34rem] w-[34rem] animate-drift rounded-full bg-cral-red-glow/35 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-40 -left-32 -z-10 h-[30rem] w-[30rem] animate-drift rounded-full bg-cral-navy-deep/70 blur-3xl [animation-delay:-7s]" aria-hidden="true"></div>

        <div class="container-x relative grid items-center gap-14 pb-32 pt-32 sm:pt-40 lg:grid-cols-[1.2fr_0.8fr] lg:gap-12 lg:pb-44 lg:pt-48">
            <div>
                <p class="chip chip-glass" data-reveal="fade">Chi dice che dopo il lavoro devi per forza tornare a casa?</p>

                <h1 class="mt-6 text-[2.4rem] font-bold leading-[1.02] tracking-[-0.035em] sm:text-6xl lg:text-7xl">
                    <span class="line-mask"><span style="--d: .1s">Attività, eventi</span></span>
                    <span class="line-mask"><span style="--d: .22s">e vantaggi per</span></span>
                    <span class="line-mask"><span style="--d: .34s" class="font-medium text-white/80">tutti i colleghi</span></span>
                </h1>

                <p class="mt-7 max-w-lg text-lg leading-relaxed text-white/85" data-reveal data-delay="0.45">
                    Scopri le iniziative del CRAL, iscriviti per vedere le foto delle nostre attività e diventa socio per ricevere la tua tessera associativa e usare le convenzioni.
                </p>

                <div class="mt-9 flex flex-wrap items-center gap-3" data-reveal data-delay="0.55">
                    <a href="{{ route('register') }}" class="btn btn-white">
                        Iscriviti al CRAL
                        <svg class="arrow h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.17 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>
                    </a>
                    <a href="{{ route('attivita.index') }}" class="btn btn-ghost-light">Scopri le attività</a>
                </div>
            </div>

            {{-- Prossime attività --}}
            <div data-reveal="scale" data-delay="0.5" data-parallax="-0.06">
                <div class="animate-float-slow rounded-[2rem] bg-white/12 p-4 shadow-lift backdrop-blur-xl sm:p-5">
                    <p class="px-3 pb-3 pt-1 text-sm font-semibold text-white/80">Prossime attività</p>

                    @if($prossimeAttivita->isEmpty())
                        <div class="rounded-2xl bg-white/10 px-5 py-9 text-center">
                            <p class="font-display text-xl font-bold">Nessuna attività in programma</p>
                            <p class="mt-1 text-sm text-white/75">Torna presto a trovarci.</p>
                        </div>
                    @else
                        <ul class="space-y-1.5">
                            @foreach($prossimeAttivita as $attivita)
                                <li>
                                    <a href="{{ route('attivita.show', $attivita->slug) }}" class="group flex items-center gap-4 rounded-2xl p-3 transition duration-300 hover:bg-white/15">
                                        <span class="grid min-h-[3.25rem] min-w-[3.6rem] place-items-center rounded-xl bg-white px-2 text-center text-xs font-bold leading-tight text-cral-red">
                                            {{ $attivita->etichettaBreve() ?: 'CRAL' }}
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate font-semibold">{{ $attivita->titolo }}</span>
                                            @if($attivita->luogo)
                                                <span class="block truncate text-sm text-white/70">{{ $attivita->luogo }}</span>
                                            @endif
                                        </span>
                                        <svg class="h-5 w-5 shrink-0 text-white/60 transition duration-300 group-hover:translate-x-1 group-hover:text-white" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.17 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <a href="{{ route('attivita.index') }}" class="mt-3 flex items-center justify-center gap-2 rounded-2xl bg-white/15 px-5 py-3 text-sm font-semibold transition hover:bg-white hover:text-cral-red">
                        Vedi tutte le attività
                    </a>
                </div>
            </div>
        </div>

        <x-wave class="absolute inset-x-0 -bottom-0.5 h-10 text-white sm:h-20 lg:h-28" />
    </section>

    {{-- ===================================================== la tessera --}}
    <section class="container-x py-16 sm:py-24">
        <div class="max-w-2xl" data-reveal>
            <p class="kicker">La tessera CRAL</p>
            <h2 class="mt-3 text-4xl font-bold leading-[1.05] tracking-[-0.03em] sm:text-5xl">Diventare socio è semplice</h2>
            <p class="mt-5 text-lg leading-relaxed text-cral-navy-soft">
                Ti iscrivi dal sito, scegli come pagare la quota e un amministratore approva la richiesta.
                La tessera ti arriva via email, intestata a te.
            </p>
        </div>

        <div class="relative mt-14 sm:mt-20">
            <div class="absolute left-0 right-0 top-7 hidden lg:block" data-reveal="fade" aria-hidden="true">
                <div class="draw-line h-0.5 rounded-full bg-gradient-to-r from-cral-red via-cral-red to-cral-navy/15"></div>
            </div>

            <ol class="relative grid gap-12 lg:grid-cols-3 lg:gap-10" data-stagger="0.14">
                <li data-reveal>
                    <span class="grid h-14 w-14 place-items-center rounded-full bg-cral-red font-display text-xl font-bold text-white shadow-glow">1</span>
                    <h3 class="mt-6 text-xl font-bold tracking-tight">Iscriviti dal sito</h3>
                    <p class="mt-2 leading-relaxed text-cral-navy-soft">Compili il modulo e scegli se diventare socio CRAL o iscriverti solo al sito.</p>
                </li>
                <li data-reveal>
                    <span class="grid h-14 w-14 place-items-center rounded-full bg-cral-red font-display text-xl font-bold text-white shadow-glow">2</span>
                    <h3 class="mt-6 text-xl font-bold tracking-tight">Scegli come pagare la quota</h3>
                    <p class="mt-2 leading-relaxed text-cral-navy-soft">In contanti in azienda, scrivendoci per email oppure con PayPal.</p>
                </li>
                <li data-reveal>
                    <span class="grid h-14 w-14 place-items-center rounded-full bg-cral-navy font-display text-xl font-bold text-white shadow-lift">3</span>
                    <h3 class="mt-6 text-xl font-bold tracking-tight">Ricevi la tessera via email</h3>
                    <p class="mt-2 leading-relaxed text-cral-navy-soft">Un amministratore approva la richiesta e ti scrive con la tua tessera personale e il tuo numero.</p>
                </li>
            </ol>
        </div>

        <div class="mt-16 grid gap-px overflow-hidden rounded-[2rem] bg-cral-navy/10 sm:grid-cols-2" data-reveal>
            <div class="bg-cral-navy-tint p-7 sm:p-9">
                <h3 class="text-xl font-bold tracking-tight">Iscritto al sito</h3>
                <ul class="mt-4 space-y-3 text-cral-navy-soft">
                    <li class="flex gap-3"><svg class="mt-0.5 h-5 w-5 shrink-0 text-cral-navy" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>Vedi foto e album degli eventi</li>
                    <li class="flex gap-3"><svg class="mt-0.5 h-5 w-5 shrink-0 text-cral-navy" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>Consulti il catalogo della libreria</li>
                    <li class="flex gap-3"><svg class="mt-0.5 h-5 w-5 shrink-0 text-cral-navy" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>Puoi chiedere la tessera quando vuoi</li>
                </ul>
            </div>
            <div class="bg-cral-red-tint p-7 sm:p-9">
                <h3 class="text-xl font-bold tracking-tight text-cral-red-dark">Socio CRAL con tessera</h3>
                <ul class="mt-4 space-y-3 text-cral-navy">
                    <li class="flex gap-3"><svg class="mt-0.5 h-5 w-5 shrink-0 text-cral-red" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>Convenzioni con aziende e negozi</li>
                    <li class="flex gap-3"><svg class="mt-0.5 h-5 w-5 shrink-0 text-cral-red" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>Iscrizione alle attività e agli eventi</li>
                    <li class="flex gap-3"><svg class="mt-0.5 h-5 w-5 shrink-0 text-cral-red" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>Prestito dei libri e documenti dell'associazione</li>
                </ul>
            </div>
        </div>

        <div class="mt-10 flex flex-wrap items-center gap-3" data-reveal>
            <a href="{{ route('register') }}" class="btn btn-primary">
                Richiedi la tua tessera
                <svg class="arrow h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.17 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>
            </a>
            <a href="{{ route('login') }}" class="btn btn-ghost">Ho già un account</a>
        </div>
    </section>

    {{-- ==================================================== le attività --}}
    @if($attivitaRecenti->isNotEmpty())
        <section class="relative bg-cral-navy-tint pb-24 pt-8 sm:pb-32">
            <x-wave flip class="absolute inset-x-0 -top-0.5 h-8 text-white sm:h-16" />

            <div class="container-x relative pt-10 sm:pt-16">
                <div class="flex flex-wrap items-end justify-between gap-4" data-reveal>
                    <div class="max-w-xl">
                        <p class="kicker">Le nostre attività</p>
                        <h2 class="mt-3 text-4xl font-bold leading-[1.05] tracking-[-0.03em] sm:text-5xl">Un assaggio di cosa facciamo insieme</h2>
                    </div>
                    <a href="{{ route('attivita.index') }}" class="btn btn-ghost btn-sm">Vedi tutte</a>
                </div>

                @php
                    $tessere = $attivitaRecenti->take(5);
                    // Con cinque attività la prima è in grande; con meno, tutte uguali (così la griglia non resta mai con buchi).
                    $conGrande = $tessere->count() === 5;
                    $colonne = [1 => 'lg:grid-cols-1', 2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3'][$tessere->count()] ?? 'lg:grid-cols-4';
                @endphp
                <div class="mt-10 grid grid-cols-2 gap-3 sm:gap-4 {{ $colonne }}" data-stagger="0.08">
                    @foreach($tessere as $attivita)
                        <a href="{{ route('attivita.show', $attivita->slug) }}" data-reveal="scale"
                            class="group relative isolate block overflow-hidden rounded-[1.5rem] bg-cral-navy shadow-soft [&:last-child:nth-child(odd)]:col-span-2 lg:[&:last-child:nth-child(odd)]:col-span-1
                                {{ $conGrande && $loop->first ? 'col-span-2 aspect-[16/10] lg:row-span-2 lg:aspect-auto' : 'aspect-square' }}">
                            @if($attivita->immagine_copertina)
                                <img src="{{ asset('uploads/'.$attivita->immagine_copertina) }}" alt="" loading="lazy" decoding="async"
                                    class="absolute inset-0 -z-10 h-full w-full object-cover transition duration-[900ms] ease-premium group-hover:scale-110">
                            @else
                                <div class="hero-bg absolute inset-0 -z-10"></div>
                            @endif
                            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-cral-navy-deep/85 via-cral-navy-deep/10 to-transparent"></div>
                            <div class="flex h-full flex-col justify-end p-4 sm:p-6">
                                @if($attivita->etichettaBreve())
                                    <span class="chip chip-glass mb-2 self-start">{{ $attivita->etichettaBreve() }}</span>
                                @endif
                                <h3 class="font-display font-bold leading-tight tracking-tight text-white {{ $conGrande && $loop->first ? 'text-2xl sm:text-3xl' : 'text-base sm:text-lg' }}">{{ $attivita->titolo }}</h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <x-wave class="absolute inset-x-0 -bottom-0.5 h-8 text-white sm:h-16" variant="2" />
        </section>
    @endif

    {{-- ========================================================= invito --}}
    <section class="cta-bg relative isolate overflow-hidden text-white">
        <x-wave flip class="absolute inset-x-0 -top-0.5 h-8 text-white sm:h-16" />
        <div class="pointer-events-none absolute -left-24 bottom-0 -z-10 h-80 w-80 animate-drift rounded-full bg-cral-navy-deep/50 blur-3xl" aria-hidden="true"></div>

        <div class="container-x py-28 text-center sm:py-40">
            <h2 class="mx-auto max-w-3xl text-4xl font-bold leading-[1.04] tracking-[-0.035em] sm:text-6xl" data-reveal>Diventa socio del CRAL</h2>
            <p class="mx-auto mt-6 max-w-xl text-lg leading-relaxed text-white/85" data-reveal data-delay="0.1">
                Iscriviti in pochi minuti: dopo l'approvazione ricevi la tessera via email e accedi all'area riservata
                con documenti, libreria e iscrizioni alle attività.
            </p>
            <div class="mt-10" data-reveal data-delay="0.2">
                <a href="{{ route('register') }}" class="btn btn-white">
                    Iscriviti ora
                    <svg class="arrow h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.17 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>
                </a>
            </div>
        </div>
    </section>
</x-public-layout>
