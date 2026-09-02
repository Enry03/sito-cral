<x-public-layout :title="$attivita->titolo.' - CRAL'">
    <section class="bg-slate-950">
        <div class="aspect-[21/9] max-h-[420px] bg-gradient-to-br from-orange-900/40 to-slate-950 flex items-center justify-center">
            @if($attivita->immagine_copertina)
                <img src="{{ asset('storage/'.$attivita->immagine_copertina) }}" alt="{{ $attivita->titolo }}" class="h-full w-full object-cover opacity-80">
            @else
                <svg class="h-16 w-16 text-orange-500/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            @endif
        </div>
    </section>

    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <a href="{{ route('attivita.index') }}" class="text-sm font-semibold text-orange-600 hover:text-orange-700">&larr; Tutte le attività</a>

        <h1 class="mt-4 text-3xl sm:text-4xl font-extrabold text-slate-900">{{ $attivita->titolo }}</h1>

        <div class="mt-4 flex flex-wrap gap-4 text-sm text-slate-500">
            @if($attivita->data_evento)
                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    {{ $attivita->data_evento->translatedFormat('d F Y \a\l\l\e H:i') }}
                </span>
            @endif
            @if($attivita->luogo)
                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    {{ $attivita->luogo }}
                </span>
            @endif
        </div>

        <div class="mt-8 prose prose-slate max-w-none">
            {!! $attivita->descrizione !!}
        </div>

        @if($attivita->foto->isNotEmpty())
            <div class="mt-12">
                <h2 class="text-xl font-bold text-slate-900 mb-6">Foto dell'evento</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @foreach($attivita->foto as $foto)
                        <div class="aspect-square rounded-xl overflow-hidden bg-slate-100">
                            <img src="{{ asset('storage/'.$foto->percorso) }}" alt="{{ $foto->didascalia }}" class="h-full w-full object-cover hover:scale-105 transition duration-300">
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-14 rounded-2xl bg-slate-50 border border-slate-100 p-8 text-center">
            <h3 class="font-bold text-slate-900">Vuoi partecipare alle prossime attività?</h3>
            <p class="mt-2 text-sm text-slate-500">Iscriviti al CRAL per ricevere tutte le informazioni.</p>
            <a href="{{ route('register') }}" class="mt-5 inline-flex items-center rounded-lg bg-orange-600 px-6 py-3 text-sm font-semibold text-white hover:bg-orange-700 transition">
                Iscriviti ora
            </a>
        </div>
    </section>
</x-public-layout>
