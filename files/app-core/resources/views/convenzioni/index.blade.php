<x-public-layout title="Convenzioni — CRAL" description="Sconti e vantaggi per gli iscritti e i soci CRAL presso aziende e negozi convenzionati.">
    <x-page-hero kicker="Vantaggi per i soci" title="Convenzioni" subtitle="Sconti e vantaggi presso le aziende e i negozi convenzionati con il CRAL, divisi per categoria." />

    <section class="container-x pb-24 sm:pb-32">
        @if($convenzioni->isEmpty())
            <div class="rounded-[1.75rem] bg-cral-navy-tint px-6 py-16 text-center sm:py-20" data-reveal>
                <p class="font-display text-2xl font-bold tracking-tight">Nessuna convenzione per ora</p>
                <p class="mt-2 text-cral-navy-soft">Le nuove convenzioni compariranno qui appena saranno attive.</p>
            </div>
        @else
            {{-- Salti rapidi tra le categorie --}}
            @if($gruppi->count() > 1)
                <nav class="mb-10 flex flex-wrap gap-2" aria-label="Categorie" data-reveal>
                    @foreach($gruppi as $categoria => $elenco)
                        <a href="#cat-{{ \Illuminate\Support\Str::slug($categoria) }}" class="chip bg-cral-navy-tint !px-4 !py-2 !text-sm text-cral-navy transition hover:bg-cral-red hover:text-white">
                            {{ $categoria }} <span class="font-normal opacity-70">{{ $elenco->count() }}</span>
                        </a>
                    @endforeach
                </nav>
            @endif

            <div class="space-y-16 sm:space-y-20">
                @foreach($gruppi as $categoria => $elenco)
                    <section id="cat-{{ \Illuminate\Support\Str::slug($categoria) }}" class="scroll-mt-28" aria-labelledby="titolo-cat-{{ $loop->index }}">
                        @if($gruppi->count() > 1 || ! $loop->first)
                            <h2 id="titolo-cat-{{ $loop->index }}" class="text-3xl font-bold tracking-[-0.03em]" data-reveal>{{ $categoria }}</h2>
                        @else
                            <h2 id="titolo-cat-{{ $loop->index }}" class="sr-only">{{ $categoria }}</h2>
                        @endif

                        <div class="{{ $gruppi->count() > 1 ? 'mt-8 ' : '' }}grid gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3" data-stagger="0.07">
                            @foreach($elenco as $item)
                    <a href="{{ route('convenzioni.show', $item->slug) }}" class="group block" data-reveal>
                        <div class="relative aspect-[4/3] overflow-hidden rounded-[1.5rem] bg-cral-navy shadow-soft transition duration-500 ease-premium group-hover:-translate-y-1 group-hover:shadow-lift">
                            @if($item->copertinaUrl())
                                <img src="{{ $item->copertinaUrl() }}" alt="" @if($loop->index > 2 || ! $loop->parent->first) loading="lazy" @endif decoding="async"
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
                            <h3 class="font-display text-xl font-bold leading-snug tracking-tight transition duration-300 group-hover:text-cral-red">{{ $item->nome }}</h3>
                            <p class="mt-2"><span class="chip bg-cral-red-tint font-semibold text-cral-red-dark">{{ $item->convenzione }}</span></p>
                            @if($item->descrizione)
                                <p class="mt-3 line-clamp-3 text-[0.95rem] leading-relaxed text-cral-navy-soft">{{ $item->descrizione }}</p>
                            @endif
                        </div>
                    </a>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </section>
</x-public-layout>
