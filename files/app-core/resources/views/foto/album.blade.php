<x-public-layout :title="$album->titolo.' — CRAL'">
    <x-page-hero tone="navy" :title="$album->titolo" :back="route('foto.index')" backLabel="Torna alla galleria">
        @if($album->attivita)
            <a href="{{ route('attivita.show', $album->attivita->slug) }}" class="btn btn-ghost-light btn-sm">{{ $album->attivita->titolo }}</a>
        @endif
    </x-page-hero>

    <section class="container-x pb-24 sm:pb-32" x-data="galleria">
        <div class="columns-2 gap-3 sm:columns-3 sm:gap-4 lg:columns-4">
            @foreach($fotoSbloccate as $item)
                <div class="mb-3 break-inside-avoid sm:mb-4" data-reveal="scale">
                    <button type="button" data-foto data-src="{{ $item->url() }}" data-didascalia="{{ $item->didascalia }}" @click="apri($el)"
                        class="group block w-full cursor-zoom-in overflow-hidden rounded-2xl bg-cral-navy-tint" aria-label="Ingrandisci la foto{{ $item->didascalia ? ': '.$item->didascalia : '' }}">
                        <img src="{{ $item->miniaturaUrl() }}" alt="{{ $item->didascalia }}" @if($loop->index > 3) loading="lazy" @endif decoding="async"
                            class="block h-auto w-full transition duration-[900ms] ease-premium group-hover:scale-105">
                    </button>
                    @if($item->didascalia)
                        <p class="mt-2 px-1 text-sm text-cral-navy-soft">{{ $item->didascalia }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        @if($fotoBloccate->isNotEmpty())
            {{-- Foto riservate: al posto dell'immagine vera c'è solo un'anteprima di
                 pochi pixel, così l'indirizzo della foto non compare nella pagina. --}}
            <div class="relative mt-3 overflow-hidden rounded-[1.75rem] sm:mt-4" data-reveal>
                <div class="grid select-none grid-cols-2 gap-3 pointer-events-none sm:grid-cols-3 sm:gap-4 lg:grid-cols-4" aria-hidden="true">
                    @foreach($fotoBloccate as $item)
                        <div class="aspect-square overflow-hidden rounded-2xl">
                            <img src="{{ $item->sfocataUrl() }}" alt="" loading="lazy" decoding="async" class="h-full w-full scale-110 object-cover blur-md">
                        </div>
                    @endforeach
                </div>
                <div class="absolute inset-0 flex items-center justify-center bg-white/60 p-4 backdrop-blur-[2px]">
                    <div class="max-w-sm rounded-[1.75rem] bg-white px-7 py-8 text-center shadow-lift">
                        <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-cral-red-tint text-cral-red">
                            <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
                        </span>
                        <p class="mt-4 font-display text-2xl font-bold tracking-tight">Contenuto riservato ai soci</p>
                        <p class="mt-2 text-cral-navy-soft">Diventa socio CRAL per visualizzare l'intero album.</p>
                        @auth
                            <a href="{{ auth()->user()->ruolo === 'iscritto' ? route('socio.richiesta') : route('dashboard') }}" class="btn btn-primary mt-5">
                                {{ auth()->user()->ruolo === 'iscritto' ? 'Richiedi la tessera' : 'Vai alla tua area' }}
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="btn btn-primary mt-5">Diventa socio</a>
                            <p class="mt-4">
                                <a href="{{ route('login') }}" class="text-sm font-semibold text-cral-navy-soft transition hover:text-cral-red">Hai già un account? Accedi</a>
                            </p>
                        @endauth
                    </div>
                </div>
            </div>
        @endif

        <x-lightbox />
    </section>
</x-public-layout>
