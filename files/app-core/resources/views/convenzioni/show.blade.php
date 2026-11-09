<x-public-layout :title="$convenzione->nome.' — Convenzioni CRAL'" :description="\Illuminate\Support\Str::limit($convenzione->convenzione.'. '.$convenzione->descrizione, 150)">
    <x-page-hero tone="navy" kicker="Convenzione" :title="$convenzione->nome" :subtitle="$convenzione->convenzione"
        :back="route('convenzioni.index')" backLabel="Tutte le convenzioni">
        @if($convenzione->sitoWebUrl())
            <a href="{{ $convenzione->sitoWebUrl() }}" target="_blank" rel="noopener noreferrer nofollow" class="btn btn-white btn-sm">
                Vai al sito web
                <svg class="arrow h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 14.78a.75.75 0 001.06 0l7.22-7.22v5.69a.75.75 0 001.5 0v-7.5a.75.75 0 00-.75-.75h-7.5a.75.75 0 000 1.5h5.69l-7.22 7.22a.75.75 0 000 1.06z" clip-rule="evenodd"/></svg>
            </a>
        @endif
    </x-page-hero>

    <section class="container-x max-w-3xl pb-24 sm:pb-32" x-data="galleria">
        <div class="rounded-[1.75rem] bg-cral-red-tint p-6 sm:p-8" data-reveal>
            <p class="text-sm font-semibold text-cral-red-dark">La convenzione</p>
            <p class="mt-1 font-display text-2xl font-bold leading-snug tracking-tight sm:text-3xl">{{ $convenzione->convenzione }}</p>
        </div>

        @if($convenzione->descrizione)
            <div class="mt-10 whitespace-pre-line text-lg leading-relaxed text-cral-navy/90" data-reveal>{{ $convenzione->descrizione }}</div>
        @endif

        @if($convenzione->immaginiUrl())
            <div class="mt-12 grid grid-cols-2 gap-3 sm:grid-cols-3" data-stagger="0.05">
                @foreach($convenzione->immaginiUrl() as $url)
                    <div data-reveal="scale">
                        <button type="button" data-foto data-src="{{ $url }}" data-didascalia="{{ $convenzione->nome }}" @click="apri($el)"
                            class="group block aspect-square w-full cursor-zoom-in overflow-hidden rounded-2xl bg-cral-navy-tint" aria-label="Ingrandisci la foto {{ $loop->iteration }} di {{ $convenzione->nome }}">
                            <img src="{{ $url }}" alt="" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-[900ms] ease-premium group-hover:scale-110">
                        </button>
                    </div>
                @endforeach
            </div>
            <x-lightbox />
        @endif

        @if($convenzione->sitoWebUrl())
            <div class="mt-12" data-reveal>
                <a href="{{ $convenzione->sitoWebUrl() }}" target="_blank" rel="noopener noreferrer nofollow" class="btn btn-primary">
                    Visita {{ $convenzione->sitoWebEtichetta() }}
                    <svg class="arrow h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.17 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>
                </a>
            </div>
        @endif

        <p class="mt-12 text-sm text-cral-navy-soft" data-reveal>Per usufruire della convenzione fai riferimento al tuo numero di tessera CRAL: lo trovi nella tua area, sotto «Il mio account», insieme al codice di verifica.</p>
    </section>
</x-public-layout>
