<x-public-layout title="Foto — CRAL" description="I momenti più belli delle attività del CRAL: foto e album per chi è iscritto al sito.">
    <x-page-hero kicker="Album fotografico" title="Galleria foto" subtitle="I momenti più belli delle nostre attività." />

    <section class="container-x pb-24 sm:pb-32" @if($accesso) x-data="galleria" @endif>
        @if($album->isEmpty() && $foto->isEmpty())
            <div class="rounded-[1.75rem] bg-cral-navy-tint px-6 py-16 text-center sm:py-20" data-reveal>
                <p class="font-display text-2xl font-bold tracking-tight">Nessuna foto per ora</p>
                <p class="mt-2 text-cral-navy-soft">Torna presto a trovarci!</p>
            </div>
        @else
            @unless($accesso)
                <x-foto-riservate-avviso class="mb-12" />
            @endunless

            @if($album->isNotEmpty())
                <h2 class="text-3xl font-bold tracking-[-0.03em]" data-reveal>Album</h2>
                <div class="mt-8 grid gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3" data-stagger="0.07">
                    @foreach($album as $albumItem)
                        <a href="{{ route('foto.album', $albumItem) }}" class="group block" data-reveal @unless($accesso) data-foto-riservata @endunless>
                            <div class="relative pt-4">
                                {{-- Le due "foto" dietro la copertina fanno sembrare l'album una pila --}}
                                <span class="absolute inset-x-8 top-0 h-full rounded-[1.5rem] bg-cral-navy/10 transition duration-500 ease-premium group-hover:-translate-y-1.5" aria-hidden="true"></span>
                                <span class="absolute inset-x-4 top-2 h-full rounded-[1.5rem] bg-cral-navy/15 transition duration-500 ease-premium group-hover:-translate-y-1" aria-hidden="true"></span>
                                <div class="relative aspect-[4/3] overflow-hidden rounded-[1.5rem] bg-cral-navy shadow-soft transition duration-500 ease-premium group-hover:shadow-lift">
                                    <img src="{{ $accesso ? $albumItem->copertinaFoto->miniaturaUrl() : $albumItem->copertinaFoto->sfocataUrl() }}" alt="" @if($loop->index > 2) loading="lazy" @endif decoding="async"
                                        class="h-full w-full object-cover transition duration-[900ms] ease-premium group-hover:scale-110 {{ $accesso ? '' : 'scale-110 blur-md' }}">
                                    <div class="absolute inset-0 bg-gradient-to-t from-cral-navy-deep/60 via-transparent to-transparent"></div>
                                    <span class="chip chip-glass absolute left-4 top-4">{{ $albumItem->foto_count }} foto</span>
                                    @unless($accesso)
                                        <span class="absolute right-4 top-4 grid h-9 w-9 place-items-center rounded-full bg-white/90 text-cral-red shadow-soft" aria-hidden="true">
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
                                        </span>
                                    @endunless
                                </div>
                            </div>
                            <h3 class="mt-4 px-1 font-display text-xl font-bold tracking-tight transition duration-300 group-hover:text-cral-red">{{ $albumItem->titolo }}</h3>
                        </a>
                    @endforeach
                </div>
            @endif

            @if($foto->isNotEmpty())
                <div class="{{ $album->isNotEmpty() ? 'mt-20 sm:mt-28' : '' }}">
                    @if($album->isNotEmpty())
                        <h2 class="text-3xl font-bold tracking-[-0.03em]" data-reveal>Altre foto</h2>
                    @endif

                    <div class="mt-8 columns-2 gap-3 sm:columns-3 sm:gap-4 lg:columns-4">
                        @foreach($foto as $item)
                            <div class="relative mb-3 break-inside-avoid sm:mb-4" data-reveal="scale">
                                <x-foto-miniatura :foto="$item" :accesso="$accesso" :lazy="$loop->index > 3" />
                                @if($item->attivita)
                                    <a href="{{ route('attivita.show', $item->attivita->slug) }}" class="chip chip-glass absolute left-2 top-2 max-w-[calc(100%-1rem)] truncate !bg-cral-navy-deep/70 transition hover:!bg-cral-red">
                                        {{ $item->attivita->titolo }}
                                    </a>
                                @endif
                                @if($accesso && $item->didascalia)
                                    <p class="mt-2 px-1 text-sm text-cral-navy-soft">{{ $item->didascalia }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-10">
                        {{ $foto->links() }}
                    </div>
                </div>
            @endif

            @if($accesso)
                <x-lightbox />
            @else
                <x-foto-riservate-popup />
            @endif
        @endif
    </section>
</x-public-layout>
