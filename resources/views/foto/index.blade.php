<x-public-layout title="Foto - CRAL">
    <section class="bg-cral-navy text-white border-b-4 border-cral-red">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <span class="font-grotesk text-xs font-bold tracking-[0.2em] uppercase text-white/60">/// Album fotografico</span>
            <h1 class="mt-3 font-display uppercase text-4xl sm:text-5xl tracking-wide">Galleria foto</h1>
            <p class="mt-3 text-white/75 max-w-xl">I momenti più belli delle nostre attività.</p>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        @if($foto->isEmpty())
            <div class="border-2 border-dashed border-cral-navy/40 p-16 text-center">
                <p class="font-display text-2xl uppercase tracking-wide text-cral-navy-soft">Rullino vuoto</p>
                <p class="mt-2 text-sm text-cral-navy-soft">Nessuna foto pubblicata al momento. Torna presto a trovarci!</p>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($foto as $item)
                    <div class="group relative aspect-square border-2 border-cral-navy overflow-hidden">
                        <img src="{{ asset('uploads/'.$item->percorso) }}" alt="{{ $item->didascalia }}" class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
                        @if($item->attivita)
                            <a href="{{ route('attivita.show', $item->attivita->slug) }}" class="absolute inset-0 flex items-end bg-cral-navy/0 group-hover:bg-cral-navy/70 transition">
                                <span class="p-3 font-grotesk text-xs font-bold uppercase tracking-wide text-white opacity-0 group-hover:opacity-100 transition">{{ $item->attivita->titolo }}</span>
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-12">
                {{ $foto->links() }}
            </div>
        @endif
    </section>
</x-public-layout>
