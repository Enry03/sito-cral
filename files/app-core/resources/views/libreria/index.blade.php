<x-public-layout title="Libreria — CRAL">
    <x-page-hero kicker="Biblioteca del CRAL" title="Libreria"
        :subtitle="'Il catalogo dei libri del CRAL. '.(auth()->user()->isSocioCral() ? 'Scegli un libro e prendilo in prestito.' : 'Il prestito è riservato ai soci con tessera.')">
        @if(auth()->user()->isSocioCral())
            <a href="{{ route('libreria.prestiti') }}" class="btn btn-ghost-light btn-sm">I miei prestiti</a>
        @endif
    </x-page-hero>

    <section class="container-x pb-24 sm:pb-32">
        <form method="GET" action="{{ route('libreria.index') }}" class="surface grid items-end gap-4 p-4 sm:grid-cols-[1fr_14rem_auto] sm:p-5" data-reveal>
            <div>
                <label for="q" class="label">Cerca</label>
                <input id="q" type="search" name="q" value="{{ $ricerca }}" placeholder="Titolo, autore, ISBN…" class="field">
            </div>
            <div>
                <label for="categoria" class="label">Categoria</label>
                <select id="categoria" name="categoria" class="field">
                    <option value="">Tutte</option>
                    @foreach($categorie as $nomeCategoria)
                        <option value="{{ $nomeCategoria }}" @selected($categoria === $nomeCategoria)>{{ $nomeCategoria }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap items-center gap-4">
                <label class="flex min-h-[3.25rem] items-center gap-2.5 text-sm font-semibold">
                    <input type="checkbox" name="disponibili" value="1" @checked($soloDisponibili) class="h-5 w-5 rounded-md border-cral-navy/30 text-cral-red focus:ring-cral-red/30">
                    Solo disponibili
                </label>
                <button type="submit" class="btn btn-primary">Cerca</button>
            </div>
        </form>

        @if($libri->isEmpty())
            <div class="mt-10 rounded-[1.75rem] bg-cral-navy-tint px-6 py-16 text-center sm:py-20" data-reveal>
                <p class="font-display text-2xl font-bold tracking-tight">Nessun libro trovato</p>
                <p class="mt-2 text-cral-navy-soft">Prova a cambiare la ricerca o i filtri.</p>
            </div>
        @else
            <p class="mt-8 text-sm font-semibold text-cral-navy-soft">{{ $libri->total() }} {{ $libri->total() === 1 ? 'libro' : 'libri' }}</p>

            <div class="mt-4 grid grid-cols-2 gap-x-4 gap-y-8 sm:grid-cols-3 sm:gap-x-6 lg:grid-cols-4" data-stagger="0.04">
                @foreach($libri as $libro)
                    <a href="{{ route('libreria.show', $libro) }}" class="group flex flex-col" data-reveal>
                        <div class="relative aspect-[3/4] overflow-hidden rounded-2xl bg-cral-navy-tint shadow-soft transition duration-500 ease-premium group-hover:-translate-y-1.5 group-hover:shadow-lift">
                            @if($libro->copertinaUrl())
                                <img src="{{ $libro->copertinaUrl() }}" alt="Copertina di {{ $libro->titolo }}" loading="lazy" decoding="async" class="h-full w-full object-cover">
                            @else
                                <div class="hero-bg flex h-full items-center justify-center p-5 text-center">
                                    <p class="line-clamp-5 font-display text-xl font-bold leading-tight tracking-tight text-white">{{ $libro->titolo }}</p>
                                </div>
                            @endif
                            <span class="chip absolute left-2.5 top-2.5 shadow-soft {{ $libro->isDisponibile() ? 'bg-white text-cral-navy' : 'chip-red' }}">
                                {{ $libro->isDisponibile() ? 'Disponibile' : 'In prestito' }}
                            </span>
                        </div>
                        <div class="px-1 pt-4">
                            <h2 class="line-clamp-2 font-display text-base font-bold leading-snug tracking-tight transition group-hover:text-cral-red">{{ $libro->titolo }}</h2>
                            @if($libro->autore)
                                <p class="mt-1 line-clamp-1 text-sm text-cral-navy-soft">{{ $libro->autore }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-14">
                {{ $libri->links() }}
            </div>
        @endif
    </section>
</x-public-layout>
