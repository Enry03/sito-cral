<x-public-layout>
    <!-- Hero -->
    <section class="relative overflow-hidden bg-slate-950">
        <div class="absolute inset-0 bg-gradient-to-br from-orange-600/30 via-slate-950 to-slate-950"></div>
        <div class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-orange-500/20 blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-amber-500/10 blur-3xl"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32">
            <div class="max-w-2xl">
                <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-orange-300 ring-1 ring-inset ring-white/20">
                    L'associazione del dopolavoro aziendale
                </span>
                <h1 class="mt-6 text-4xl sm:text-5xl font-extrabold tracking-tight text-white">
                    Attività, eventi e vantaggi per tutti i colleghi
                </h1>
                <p class="mt-6 text-lg text-slate-300">
                    Scopri le iniziative del CRAL, guarda le foto delle nostre attività e iscriviti per ottenere
                    la tua tessera associativa digitale.
                </p>
                <div class="mt-10 flex flex-wrap items-center gap-4">
                    <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg bg-orange-600 px-6 py-3 text-sm font-semibold text-white hover:bg-orange-700 transition shadow-lg shadow-orange-600/30">
                        Iscriviti al CRAL
                    </a>
                    <a href="{{ route('attivita.index') }}" class="inline-flex items-center rounded-lg bg-white/10 px-6 py-3 text-sm font-semibold text-white ring-1 ring-inset ring-white/20 hover:bg-white/20 transition">
                        Scopri le attività
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Prossime attività -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="flex items-end justify-between mb-10">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900">Prossime attività</h2>
                <p class="mt-2 text-slate-500">Le iniziative in programma per i soci.</p>
            </div>
            <a href="{{ route('attivita.index') }}" class="hidden sm:inline-flex text-sm font-semibold text-orange-600 hover:text-orange-700">
                Vedi tutte &rarr;
            </a>
        </div>

        @if($prossimeAttivita->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-200 p-12 text-center text-slate-500">
                Nessuna attività pubblicata al momento. Torna presto a trovarci!
            </div>
        @else
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($prossimeAttivita as $attivita)
                    <a href="{{ route('attivita.show', $attivita->slug) }}" class="group block rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden">
                        <div class="aspect-[16/10] bg-gradient-to-br from-orange-100 to-amber-50 flex items-center justify-center">
                            @if($attivita->immagine_copertina)
                                <img src="{{ asset('uploads/'.$attivita->immagine_copertina) }}" alt="{{ $attivita->titolo }}" class="h-full w-full object-cover">
                            @else
                                <svg class="h-12 w-12 text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            @endif
                        </div>
                        <div class="p-6">
                            @if($attivita->data_evento)
                                <div class="text-xs font-semibold text-orange-600 mb-2">
                                    {{ $attivita->data_evento->translatedFormat('d F Y') }}
                                </div>
                            @endif
                            <h3 class="font-bold text-slate-900 group-hover:text-orange-600 transition">{{ $attivita->titolo }}</h3>
                            @if($attivita->luogo)
                                <p class="mt-1 text-sm text-slate-500">{{ $attivita->luogo }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <!-- Foto teaser -->
    @if($fotoRecenti->isNotEmpty())
        <section class="bg-slate-50 py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between mb-10">
                    <div>
                        <h2 class="text-2xl font-extrabold text-slate-900">Dalla galleria</h2>
                        <p class="mt-2 text-slate-500">Un assaggio delle nostre attività.</p>
                    </div>
                    <a href="{{ route('foto.index') }}" class="hidden sm:inline-flex text-sm font-semibold text-orange-600 hover:text-orange-700">
                        Vedi tutte le foto &rarr;
                    </a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach($fotoRecenti as $foto)
                        <div class="aspect-square rounded-xl overflow-hidden bg-slate-200">
                            <img src="{{ asset('uploads/'.$foto->percorso) }}" alt="{{ $foto->didascalia }}" class="h-full w-full object-cover hover:scale-105 transition duration-300">
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- CTA iscrizione -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="rounded-3xl bg-slate-900 px-8 py-14 sm:px-16 text-center relative overflow-hidden">
            <div class="absolute -top-16 -right-16 h-64 w-64 rounded-full bg-orange-500/20 blur-3xl"></div>
            <h2 class="relative text-3xl font-extrabold text-white">Diventa socio del CRAL</h2>
            <p class="relative mt-4 text-slate-300 max-w-xl mx-auto">
                Iscriviti in pochi minuti: otterrai la tua tessera digitale e l'accesso all'area riservata con
                documenti e informazioni per i soci.
            </p>
            <a href="{{ route('register') }}" class="relative mt-8 inline-flex items-center rounded-lg bg-orange-600 px-6 py-3 text-sm font-semibold text-white hover:bg-orange-700 transition">
                Iscriviti ora
            </a>
        </div>
    </section>
</x-public-layout>
