<x-public-layout :title="$libro->titolo.' — Libreria CRAL'">
    <x-page-hero tone="navy" :title="$libro->titolo" :kicker="$libro->categoria" :subtitle="$libro->autore" :back="route('libreria.index')" backLabel="Tutta la libreria" />

    <section class="container-x grid max-w-5xl gap-10 pb-24 sm:pb-32 md:grid-cols-[16rem_1fr] md:gap-14">
        <div class="aspect-[3/4] self-start overflow-hidden rounded-[1.5rem] bg-cral-navy-tint shadow-lift md:-mt-24" data-reveal="scale">
            @if($libro->copertinaUrl())
                <img src="{{ $libro->copertinaUrl() }}" alt="Copertina di {{ $libro->titolo }}" decoding="async" class="h-full w-full object-cover">
            @else
                <div class="hero-bg flex h-full items-center justify-center p-6 text-center">
                    <p class="font-display text-2xl font-bold leading-tight tracking-tight text-white">{{ $libro->titolo }}</p>
                </div>
            @endif
        </div>

        <div>
            <dl class="grid gap-x-8 gap-y-5 sm:grid-cols-2" data-reveal>
                @if($libro->editore)
                    <div><dt class="text-sm font-semibold text-cral-navy-soft">Editore</dt><dd class="mt-0.5 font-semibold">{{ $libro->editore }}</dd></div>
                @endif
                @if($libro->anno)
                    <div><dt class="text-sm font-semibold text-cral-navy-soft">Anno</dt><dd class="mt-0.5 font-semibold">{{ $libro->anno }}</dd></div>
                @endif
                @if($libro->isbn)
                    <div><dt class="text-sm font-semibold text-cral-navy-soft">ISBN</dt><dd class="mt-0.5 font-semibold">{{ $libro->isbn }}</dd></div>
                @endif
                @if($libro->codice)
                    <div><dt class="text-sm font-semibold text-cral-navy-soft">Codice</dt><dd class="mt-0.5 font-semibold">{{ $libro->codice }}</dd></div>
                @endif
                <div>
                    <dt class="text-sm font-semibold text-cral-navy-soft">Disponibilità</dt>
                    <dd class="mt-0.5 font-semibold">{{ $libro->copieDisponibili() }} {{ $libro->copieDisponibili() === 1 ? 'copia disponibile' : 'copie disponibili' }} su {{ $libro->copie_totali }}</dd>
                </div>
            </dl>

            @if($libro->descrizione)
                <div class="mt-8 whitespace-pre-line leading-relaxed text-cral-navy-soft" data-reveal>{{ $libro->descrizione }}</div>
            @endif

            <div class="navy-bg relative isolate mt-10 overflow-hidden rounded-[2rem] p-7 text-white shadow-lift sm:p-8" data-reveal>
                <div class="pointer-events-none absolute -right-16 -top-16 -z-10 h-52 w-52 rounded-full bg-cral-red/40 blur-3xl" aria-hidden="true"></div>
                <p class="text-sm font-semibold text-white/70">Prestito</p>

                @if(session('ok_prestito'))
                    <p class="mt-3 rounded-2xl bg-white/15 px-4 py-3 text-sm font-semibold" role="status">{{ session('ok_prestito') }}</p>
                @endif
                @if(session('errore_prestito'))
                    <p class="mt-3 rounded-2xl bg-cral-red px-4 py-3 text-sm font-semibold" role="alert">{{ session('errore_prestito') }}</p>
                @endif

                @if(auth()->user()->isSocioCral())
                    @if($prestitoUtente)
                        <p class="mt-3 text-lg font-bold">Hai già questo libro in prestito: da restituire entro il {{ $prestitoUtente->scadenza->translatedFormat('d F Y') }}.</p>
                        <a href="{{ route('libreria.prestiti') }}" class="mt-3 inline-flex text-sm font-semibold underline underline-offset-4 text-white/75 transition hover:text-white">Vai ai miei prestiti</a>
                    @elseif($libro->isDisponibile())
                        <form method="POST" action="{{ route('libreria.prestito', $libro) }}" class="mt-3 space-y-4">
                            @csrf
                            <p class="leading-relaxed text-white/80">
                                Il prestito dura {{ config('cral.prestito.giorni') }} giorni. Registrandolo ritiri il libro presso il CRAL e il tuo nome risulta nell'elenco dei prestiti.
                            </p>
                            <div>
                                <label for="note" class="mb-1.5 block text-sm font-semibold text-white/70">Note per il CRAL (facoltative)</label>
                                <textarea id="note" name="note" rows="2" maxlength="500" class="field !border-transparent">{{ old('note') }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Prendi in prestito</button>
                        </form>
                    @else
                        <p class="mt-3 text-lg font-bold">Tutte le copie sono attualmente in prestito.</p>
                    @endif
                @else
                    <p class="mt-3 text-lg font-bold">Il prestito dei libri è riservato ai soci CRAL con tessera.</p>
                    <a href="{{ route('socio.richiesta') }}" class="btn btn-primary mt-5">Diventa socio CRAL</a>
                @endif
            </div>
        </div>
    </section>
</x-public-layout>
