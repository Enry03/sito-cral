<x-public-layout title="Convenzioni — CRAL" description="Sconti e vantaggi riservati ai soci CRAL presso aziende e negozi convenzionati.">
    <x-page-hero kicker="Vantaggi per i soci" title="Convenzioni" subtitle="Sconti e vantaggi riservati ai soci presso le aziende e i negozi convenzionati con il CRAL." />

    <section class="container-x pb-24 sm:pb-32">
        @if($convenzioni->isEmpty())
            <div class="rounded-[1.75rem] bg-cral-navy-tint px-6 py-16 text-center sm:py-20" data-reveal>
                <p class="font-display text-2xl font-bold tracking-tight">Nessuna convenzione per ora</p>
                <p class="mt-2 text-cral-navy-soft">Le nuove convenzioni compariranno qui appena saranno attive.</p>
            </div>
        @else
            <div class="grid gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3" data-stagger="0.07">
                @foreach($convenzioni as $item)
                    <a href="{{ route('convenzioni.show', $item->slug) }}" class="group block" data-reveal>
                        <div class="relative aspect-[4/3] overflow-hidden rounded-[1.5rem] bg-cral-navy shadow-soft transition duration-500 ease-premium group-hover:-translate-y-1 group-hover:shadow-lift">
                            @if($item->copertinaUrl())
                                <img src="{{ $item->copertinaUrl() }}" alt="" @if($loop->index > 2) loading="lazy" @endif decoding="async"
                                    class="h-full w-full object-cover transition duration-[900ms] ease-premium group-hover:scale-110">
                            @else
                                <div class="hero-bg grid h-full w-full place-items-center">
                                    <span class="px-6 text-center font-display text-4xl font-bold tracking-tighter text-white/25">{{ \Illuminate\Support\Str::limit($item->nome, 22) }}</span>
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-cral-navy-deep/55 via-transparent to-transparent"></div>
                            @if(count($item->immaginiUrl()) > 1)
                                <span class="chip chip-glass absolute left-4 top-4">{{ count($item->immaginiUrl()) }} foto</span>
                            @endif
                        </div>

                        <div class="px-1 pt-5">
                            <h2 class="font-display text-xl font-bold leading-snug tracking-tight transition duration-300 group-hover:text-cral-red">{{ $item->nome }}</h2>
                            <p class="mt-2"><span class="chip bg-cral-red-tint font-semibold text-cral-red-dark">{{ $item->convenzione }}</span></p>
                            @if($item->descrizione)
                                <p class="mt-3 line-clamp-3 text-[0.95rem] leading-relaxed text-cral-navy-soft">{{ $item->descrizione }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</x-public-layout>
