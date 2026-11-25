{{--
    Scheda di un album (o macro-album) nelle gallerie. Chi non ha l'accesso vede la
    copertina sfocata: l'indirizzo della foto vera non compare mai nella pagina.
    Un macro-album mostra anche quanti album contiene.
--}}
@props(['album', 'accesso' => false, 'lazy' => false])

@php
    $copertina = $album->copertinaVisibile();
    $sotto = (int) ($album->sotto_visibili_count ?? 0);
@endphp

@if($copertina)
    <a href="{{ route('foto.album', $album) }}" {{ $attributes->merge(['class' => 'group block']) }} data-reveal @unless($accesso) data-foto-riservata @endunless>
        <div class="relative pt-4">
            {{-- Le due "foto" dietro la copertina fanno sembrare l'album una pila --}}
            <span class="absolute inset-x-8 top-0 h-full rounded-[1.5rem] bg-cral-navy/10 transition duration-500 ease-premium group-hover:-translate-y-1.5" aria-hidden="true"></span>
            <span class="absolute inset-x-4 top-2 h-full rounded-[1.5rem] bg-cral-navy/15 transition duration-500 ease-premium group-hover:-translate-y-1" aria-hidden="true"></span>
            <div class="relative aspect-[4/3] overflow-hidden rounded-[1.5rem] bg-cral-navy shadow-soft transition duration-500 ease-premium group-hover:shadow-lift">
                <img src="{{ $accesso ? $copertina->miniaturaUrl() : $copertina->sfocataUrl() }}" alt="" @if($lazy) loading="lazy" @endif decoding="async"
                    class="h-full w-full object-cover transition duration-[900ms] ease-premium group-hover:scale-110 {{ $accesso ? '' : 'scale-110 blur-md' }}">
                <div class="absolute inset-0 bg-gradient-to-t from-cral-navy-deep/60 via-transparent to-transparent"></div>
                <span class="absolute left-4 top-4 flex flex-wrap gap-2">
                    @if($sotto > 0)
                        <span class="chip chip-red">{{ $sotto }} album</span>
                    @endif
                    <span class="chip chip-glass">{{ $album->fotoTotali() }} foto</span>
                </span>
                @unless($accesso)
                    <span class="absolute right-4 top-4 grid h-9 w-9 place-items-center rounded-full bg-white/90 text-cral-red shadow-soft" aria-hidden="true">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
                    </span>
                @endunless
            </div>
        </div>
        <h3 class="mt-4 px-1 font-display text-xl font-bold tracking-tight transition duration-300 group-hover:text-cral-red">{{ $album->titolo }}</h3>
        @if($album->dataEffettiva())
            <p class="mt-0.5 px-1 text-sm text-cral-navy-soft">{{ $album->dataEffettiva()->translatedFormat('j F Y') }}</p>
        @endif
        @if($sotto > 0)
            <p class="mt-0.5 px-1 text-sm text-cral-navy-soft">Contiene {{ $sotto }} album</p>
        @endif
    </a>
@endif
