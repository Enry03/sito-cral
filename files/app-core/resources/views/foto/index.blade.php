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
                        <x-album-card :album="$albumItem" :accesso="$accesso" :lazy="$loop->index > 2" />
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
