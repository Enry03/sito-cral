@props(['item', 'numero' => null, 'passata' => false])

<a href="{{ route('attivita.show', $item->slug) }}" class="group block" data-reveal>
    <div class="relative aspect-[4/3] overflow-hidden rounded-[1.5rem] bg-cral-navy shadow-soft transition duration-500 ease-premium group-hover:-translate-y-1 group-hover:shadow-lift">
        @if($item->immagine_copertina)
            <img src="{{ asset('uploads/'.$item->immagine_copertina) }}" alt="" loading="lazy" decoding="async"
                class="h-full w-full object-cover transition duration-[900ms] ease-premium group-hover:scale-110 {{ $passata ? 'saturate-50' : '' }}">
        @else
            <div class="hero-bg grid h-full w-full place-items-center">
                <span class="font-display text-6xl font-bold tracking-tighter text-white/20">CRAL</span>
            </div>
        @endif

        <div class="absolute inset-0 bg-gradient-to-t from-cral-navy-deep/50 via-transparent to-transparent opacity-80"></div>

        <div class="absolute left-3 top-3 flex flex-wrap gap-2 sm:left-4 sm:top-4">
            @if($item->periodoLabel())
                <span class="chip {{ $passata ? 'bg-white/90 text-cral-navy' : 'bg-white text-cral-red' }} shadow-soft">{{ $item->periodoLabel() }}</span>
            @endif
            @if($item->ricorrente && $item->date_ricorrenti)
                <span class="chip chip-glass">+{{ count($item->date_ricorrenti) }} {{ count($item->date_ricorrenti) === 1 ? 'data' : 'date' }}</span>
            @endif
        </div>
    </div>

    <div class="px-1 pt-5">
        <h3 class="font-display text-xl font-bold leading-snug tracking-tight transition duration-300 group-hover:text-cral-red">{{ $item->titolo }}</h3>
        @if($item->luogo)
            <p class="mt-1 flex items-center gap-1.5 text-[0.95rem] text-cral-navy-soft">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9.69 18.93l.01.01.01-.01a.75.75 0 00.6 0l.01.01.01-.01C14.5 16.5 17 13.2 17 9.5a7 7 0 10-14 0c0 3.7 2.5 7 6.69 9.43zM10 12a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" clip-rule="evenodd"/></svg>
                {{ $item->luogo }}
            </p>
        @endif
        @if(! $passata && $item->iscrizione_abilitata)
            <p class="mt-3"><span class="chip chip-soft">Iscrizioni per i soci</span></p>
        @endif
    </div>
</a>
