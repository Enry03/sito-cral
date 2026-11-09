{{--
    Intestazione di una pagina: fascia rossa (o navy) con titolo grande e una
    curva che la raccorda al contenuto bianco sotto.
    - kicker:   piccola etichetta sopra il titolo (facoltativa)
    - title:    titolo della pagina
    - subtitle: frase di spiegazione (facoltativa)
    - tone:     red (predefinito) | navy
    Lo slot contiene pulsanti o altri elementi sotto il titolo.
--}}
@props(['title', 'kicker' => null, 'subtitle' => null, 'tone' => 'red', 'back' => null, 'backLabel' => 'Indietro'])

<section class="relative isolate overflow-hidden text-white {{ $tone === 'navy' ? 'navy-bg' : 'hero-bg' }}">
    <div class="pointer-events-none absolute -right-24 -top-24 -z-10 h-[26rem] w-[26rem] animate-drift rounded-full bg-cral-red-glow/30 blur-3xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-32 -left-24 -z-10 h-[24rem] w-[24rem] animate-drift rounded-full bg-cral-navy-deep/60 blur-3xl [animation-delay:-6s]" aria-hidden="true"></div>

    <div class="container-x relative pb-24 pt-32 sm:pb-32 sm:pt-44">
        @if($back)
            <a href="{{ $back }}" class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-white/80 transition hover:text-white" data-reveal="fade">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L9.06 10l3.71 3.71a.75.75 0 11-1.06 1.06l-4.25-4.25a.75.75 0 010-1.06l4.25-4.25a.75.75 0 011.06 0z" clip-rule="evenodd"/></svg>
                {{ $backLabel }}
            </a>
        @endif

        @if($kicker)
            <p class="chip chip-glass" data-reveal="fade">{{ $kicker }}</p>
        @endif

        <h1 class="mt-4 max-w-3xl text-[2.6rem] font-bold leading-[1.02] tracking-[-0.03em] sm:text-6xl" data-reveal data-delay="0.05">{{ $title }}</h1>

        @if($subtitle)
            <p class="mt-5 max-w-xl text-lg leading-relaxed text-white/85" data-reveal data-delay="0.12">{{ $subtitle }}</p>
        @endif

        @if(trim((string) $slot) !== '')
            <div class="mt-8 flex flex-wrap items-center gap-3" data-reveal data-delay="0.2">
                {{ $slot }}
            </div>
        @endif
    </div>

    <x-wave class="absolute inset-x-0 -bottom-0.5 h-8 text-white sm:h-14" />
</section>
