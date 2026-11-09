{{--
    Una foto in una griglia.
    - Chi ha l'accesso (account approvato): miniatura cliccabile che si ingrandisce
      nella finestra della galleria (serve un genitore con x-data="galleria").
    - Tutti gli altri: solo un'anteprima di pochi pixel, sfocata. L'indirizzo della
      foto vera e la didascalia non compaiono mai nella pagina. Toccandola si apre
      la finestra "Foto riservate" (x-foto-riservate-popup).
    - foto:     il modello Foto
    - accesso:  se chi guarda può vedere le foto
    - quadrata: riquadro quadrato (griglie ordinate) invece della proporzione originale
    - lazy:     caricamento ritardato (false per le prime foto della pagina)
--}}
@props(['foto', 'accesso' => false, 'quadrata' => false, 'lazy' => true])

@if($accesso)
    <button type="button" data-foto data-src="{{ $foto->url() }}" data-didascalia="{{ $foto->didascalia }}" @click="apri($el)"
        {{ $attributes->merge(['class' => 'group block w-full cursor-zoom-in overflow-hidden rounded-2xl bg-cral-navy-tint '.($quadrata ? 'aspect-square' : '')]) }}
        aria-label="Ingrandisci la foto{{ $foto->didascalia ? ': '.$foto->didascalia : '' }}">
        <img src="{{ $foto->miniaturaUrl() }}" alt="{{ $foto->didascalia }}" @if($lazy) loading="lazy" @endif decoding="async"
            class="block w-full transition duration-[900ms] ease-premium group-hover:scale-105 {{ $quadrata ? 'h-full object-cover' : 'h-auto' }}">
    </button>
@else
    <a href="{{ route('register') }}" data-foto-riservata
        {{ $attributes->merge(['class' => 'group relative block w-full cursor-pointer overflow-hidden rounded-2xl bg-cral-navy-tint '.($quadrata ? 'aspect-square' : '')]) }}
        aria-label="Foto riservata: iscriviti al sito o diventa socio per vederla">
        <img src="{{ $foto->sfocataUrl() }}" alt="" @if($lazy) loading="lazy" @endif decoding="async"
            class="block w-full scale-110 blur-md transition duration-500 ease-premium group-hover:scale-125 {{ $quadrata ? 'h-full object-cover' : 'h-auto min-h-[7rem]' }}">
        <span class="absolute inset-0 grid place-items-center bg-cral-navy-deep/10 transition duration-300 group-hover:bg-cral-navy-deep/25" aria-hidden="true">
            <span class="grid h-11 w-11 place-items-center rounded-full bg-white/90 text-cral-red shadow-soft backdrop-blur">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
            </span>
        </span>
    </a>
@endif
