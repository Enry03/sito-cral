<x-public-layout title="Attività - CRAL">
    <section class="bg-slate-950 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-white">Attività</h1>
            <p class="mt-3 text-slate-300 max-w-2xl">Tutte le iniziative organizzate dal CRAL per i soci.</p>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        @if($attivita->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-200 p-16 text-center text-slate-500">
                Nessuna attività pubblicata al momento. Torna presto a trovarci!
            </div>
        @else
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($attivita as $item)
                    <a href="{{ route('attivita.show', $item->slug) }}" class="group block rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden">
                        <div class="aspect-[16/10] bg-gradient-to-br from-orange-100 to-amber-50 flex items-center justify-center">
                            @if($item->immagine_copertina)
                                <img src="{{ asset('storage/'.$item->immagine_copertina) }}" alt="{{ $item->titolo }}" class="h-full w-full object-cover">
                            @else
                                <svg class="h-12 w-12 text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            @endif
                        </div>
                        <div class="p-6">
                            @if($item->data_evento)
                                <div class="text-xs font-semibold text-orange-600 mb-2">
                                    {{ $item->data_evento->translatedFormat('d F Y') }}
                                </div>
                            @endif
                            <h3 class="font-bold text-slate-900 group-hover:text-orange-600 transition">{{ $item->titolo }}</h3>
                            @if($item->luogo)
                                <p class="mt-1 text-sm text-slate-500">{{ $item->luogo }}</p>
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
