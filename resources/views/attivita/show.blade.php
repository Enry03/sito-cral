<x-public-layout :title="$attivita->titolo.' - CRAL'">
    <section class="bg-cral-navy border-b-4 border-cral-red">
        <div class="aspect-[21/9] max-h-[420px] bg-cral-navy flex items-center justify-center overflow-hidden">
            @if($attivita->immagine_copertina)
                <img src="{{ asset('uploads/'.$attivita->immagine_copertina) }}" alt="{{ $attivita->titolo }}" class="h-full w-full object-cover opacity-80">
            @else
                <span class="font-display text-7xl text-white/10 uppercase">CRAL</span>
            @endif
        </div>
    </section>

    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <a href="{{ route('attivita.index') }}" class="font-grotesk text-xs font-bold tracking-[0.15em] uppercase hover:text-cral-red transition">&larr; Tutte le attività</a>

        <h1 class="mt-5 font-display uppercase text-4xl sm:text-5xl leading-[0.95] tracking-wide">{{ $attivita->titolo }}</h1>

        <div class="mt-6 border-2 border-cral-navy bg-white inline-flex flex-wrap divide-x-2 divide-cral-navy">
            @if($attivita->data_evento)
                <div class="px-5 py-3">
                    <p class="font-grotesk text-[10px] font-bold tracking-[0.2em] uppercase text-cral-navy-soft">Data</p>
                    <p class="font-grotesk font-bold">{{ $attivita->data_evento->translatedFormat('d F Y — H:i') }}</p>
                </div>
            @endif
            @if($attivita->luogo)
                <div class="px-5 py-3">
                    <p class="font-grotesk text-[10px] font-bold tracking-[0.2em] uppercase text-cral-navy-soft">Luogo</p>
                    <p class="font-grotesk font-bold">{{ $attivita->luogo }}</p>
                </div>
            @endif
        </div>

        <div class="mt-10 prose prose-slate max-w-none prose-headings:font-grotesk">
            {!! $attivita->descrizione !!}
        </div>

        @if($attivita->foto->isNotEmpty())
            <div class="mt-14">
                <span class="font-grotesk text-xs font-bold tracking-[0.2em] uppercase text-cral-red">/// Foto dell'evento</span>
                <div class="mt-5 grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @foreach($attivita->foto as $foto)
                        <div class="aspect-square border-2 border-cral-navy overflow-hidden">
                            <img src="{{ asset('uploads/'.$foto->percorso) }}" alt="{{ $foto->didascalia }}" class="h-full w-full object-cover hover:scale-105 transition duration-300">
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-16 border-2 border-cral-navy bg-cral-navy text-white p-8 text-center">
            <h3 class="font-display uppercase text-2xl tracking-wide">Vuoi partecipare alle prossime attività?</h3>
            <p class="mt-2 text-sm text-white/75">Iscriviti al CRAL per ricevere tutte le informazioni.</p>
            <a href="{{ route('register') }}" class="mt-6 inline-flex items-center border-2 border-white bg-cral-red px-7 py-3.5 text-sm font-bold tracking-[0.15em] uppercase text-white hover:bg-white hover:text-cral-red transition">
                Iscriviti ora
            </a>
        </div>
    </section>
</x-public-layout>
