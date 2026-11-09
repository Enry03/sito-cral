<x-public-layout :title="$album->titolo.' — CRAL'">
    <x-page-hero tone="navy" :title="$album->titolo" :back="route('foto.index')" backLabel="Torna alla galleria">
        @if($album->attivita)
            <a href="{{ route('attivita.show', $album->attivita->slug) }}" class="btn btn-ghost-light btn-sm">{{ $album->attivita->titolo }}</a>
        @endif
    </x-page-hero>

    <section class="container-x pb-24 sm:pb-32" @if($accesso) x-data="galleria" @endif>
        @unless($accesso)
            <x-foto-riservate-avviso class="mb-8" />
        @endunless

        <div class="columns-2 gap-3 sm:columns-3 sm:gap-4 lg:columns-4">
            @foreach($album->foto as $item)
                <div class="mb-3 break-inside-avoid sm:mb-4" data-reveal="scale">
                    <x-foto-miniatura :foto="$item" :accesso="$accesso" :lazy="$loop->index > 3" />
                    @if($accesso && $item->didascalia)
                        <p class="mt-2 px-1 text-sm text-cral-navy-soft">{{ $item->didascalia }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        @if($accesso)
            <x-lightbox />
        @else
            <x-foto-riservate-popup />
        @endif
    </section>
</x-public-layout>
