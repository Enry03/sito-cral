<x-public-layout title="Attività - CRAL">
    <section class="bg-cral-navy text-white border-b-4 border-cral-red">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <span class="font-grotesk text-xs font-bold tracking-[0.2em] uppercase text-white/60">/// Manifesto attività</span>
            <h1 class="mt-3 font-display uppercase text-4xl sm:text-5xl tracking-wide">Attività</h1>
            <p class="mt-3 text-white/75 max-w-xl">Tutte le iniziative organizzate dal CRAL per i soci.</p>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        @if($attivita->isEmpty())
            <div class="border-2 border-dashed border-cral-navy/40 p-16 text-center">
                <p class="font-display text-2xl uppercase tracking-wide text-cral-navy-soft">Nessuna tratta in programma</p>
                <p class="mt-2 text-sm text-cral-navy-soft">Torna presto a trovarci!</p>
            </div>
        @else
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($attivita as $i => $item)
                    <a href="{{ route('attivita.show', $item->slug) }}" class="group block border-2 border-cral-navy bg-white hover:stack-shadow-sm transition-shadow">
                        <div class="aspect-[16/10] border-b-2 border-cral-navy bg-cral-cream flex items-center justify-center overflow-hidden">
                            @if($item->immagine_copertina)
                                <img src="{{ asset('uploads/'.$item->immagine_copertina) }}" alt="{{ $item->titolo }}" class="h-full w-full object-cover">
                            @else
                                <span class="font-display text-5xl text-cral-navy/15 uppercase">CRAL</span>
                            @endif
                        </div>
                        <div class="p-5">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center bg-cral-navy text-white font-grotesk text-xs font-bold">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                @if($item->data_evento)
                                    <span class="bg-cral-red text-white text-[10px] font-bold tracking-widest uppercase px-2 py-1">
                                        {{ $item->data_evento->translatedFormat('d M Y') }}
                                    </span>
                                @endif
                            </div>
                            <h3 class="font-grotesk font-bold uppercase tracking-wide group-hover:text-cral-red transition">{{ $item->titolo }}</h3>
                            @if($item->luogo)
                                <div class="tear-line my-2.5"></div>
                                <p class="text-xs text-cral-navy-soft tracking-wide">{{ $item->luogo }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-12">
                {{ $attivita->links() }}
            </div>
        @endif
    </section>
</x-public-layout>
