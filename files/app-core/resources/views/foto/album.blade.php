<x-public-layout :title="$album->titolo.' — CRAL'">
    @php
        $macro = $album->macroAlbum;
    @endphp

    <x-page-hero tone="navy" :title="$album->titolo"
        :back="$macro ? route('foto.album', $macro) : route('foto.index')"
        :backLabel="$macro ? 'Torna a «'.$macro->titolo.'»' : 'Torna alla galleria'">
        @if($album->attivita)
            <a href="{{ route('attivita.show', $album->attivita->slug) }}" class="btn btn-ghost-light btn-sm">{{ $album->attivita->titolo }}</a>
        @endif
    </x-page-hero>

    <section class="container-x pb-24 sm:pb-32" @if($accesso) x-data="galleria" @endif>
        @unless($accesso)
            <x-foto-riservate-avviso class="mb-8" />
        @endunless

        {{-- Gli album contenuti in questo (macro-album) --}}
        @if($sottoAlbum->isNotEmpty())
            <h2 class="text-3xl font-bold tracking-[-0.03em]" data-reveal>Album{{ $macro ? '' : ' di «'.$album->titolo.'»' }}</h2>
            <div class="mt-8 grid gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3" data-stagger="0.07">
                @foreach($sottoAlbum as $item)
                    <x-album-card :album="$item" :accesso="$accesso" :lazy="$loop->index > 2" />
                @endforeach
            </div>
        @endif

        {{-- Le foto di questo album --}}
        @if($album->foto->isNotEmpty())
            <div class="{{ $sottoAlbum->isNotEmpty() ? 'mt-20 sm:mt-28' : '' }}">
                @if($sottoAlbum->isNotEmpty())
                    <h2 class="text-3xl font-bold tracking-[-0.03em]" data-reveal>Foto</h2>
                @endif

                <div class="{{ $sottoAlbum->isNotEmpty() ? 'mt-8 ' : '' }}columns-2 gap-3 sm:columns-3 sm:gap-4 lg:columns-4">
                    @foreach($album->foto as $item)
                        <div class="mb-3 break-inside-avoid sm:mb-4" data-reveal="scale">
                            <x-foto-miniatura :foto="$item" :accesso="$accesso" :lazy="$loop->index > 3" />
                            @if($accesso && $item->didascalia)
                                <p class="mt-2 px-1 text-sm text-cral-navy-soft">{{ $item->didascalia }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($accesso)
            <x-lightbox />
        @else
            <x-foto-riservate-popup />
        @endif
    </section>
</x-public-layout>
