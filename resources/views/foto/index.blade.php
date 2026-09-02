<x-public-layout title="Foto - CRAL">
    <section class="bg-slate-950 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-white">Galleria foto</h1>
            <p class="mt-3 text-slate-300 max-w-2xl">I momenti più belli delle nostre attività.</p>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        @if($foto->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-200 p-16 text-center text-slate-500">
                Nessuna foto pubblicata al momento. Torna presto a trovarci!
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($foto as $item)
                    <div class="group relative aspect-square rounded-xl overflow-hidden bg-slate-100">
                        <img src="{{ asset('storage/'.$item->percorso) }}" alt="{{ $item->didascalia }}" class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
                        @if($item->attivita)
                            <a href="{{ route('attivita.show', $item->attivita->slug) }}" class="absolute inset-0 flex items-end bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition">
                                <span class="p-3 text-xs font-semibold text-white">{{ $item->attivita->titolo }}</span>
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
