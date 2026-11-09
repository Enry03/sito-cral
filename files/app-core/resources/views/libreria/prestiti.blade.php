<x-public-layout title="I miei prestiti — CRAL">
    <x-page-hero kicker="Libreria" title="I miei prestiti" subtitle="I libri che hai preso in prestito e quelli già restituiti." tone="navy" />

    <section class="container-x max-w-4xl pb-24 sm:pb-32">
        @if($prestiti->isEmpty())
            <div class="rounded-[1.75rem] bg-cral-navy-tint px-6 py-16 text-center sm:py-20" data-reveal>
                <p class="font-display text-2xl font-bold tracking-tight">Nessun prestito</p>
                <p class="mt-2 text-cral-navy-soft">Non hai ancora preso in prestito nessun libro.</p>
                <a href="{{ route('libreria.index') }}" class="btn btn-primary mt-7">Sfoglia la libreria</a>
            </div>
        @else
            <ul class="space-y-3" data-stagger="0.06">
                @foreach($prestiti as $prestito)
                    <li class="surface flex flex-wrap items-center justify-between gap-4 p-5" data-reveal>
                        <div class="min-w-0">
                            @if($prestito->libro)
                                <a href="{{ route('libreria.show', $prestito->libro) }}" class="font-display text-lg font-bold tracking-tight transition hover:text-cral-red">{{ $prestito->libro_titolo }}</a>
                            @else
                                <span class="font-display text-lg font-bold tracking-tight">{{ $prestito->libro_titolo }}</span>
                            @endif
                            <p class="mt-1 text-sm text-cral-navy-soft">
                                Preso il {{ $prestito->data_prestito->translatedFormat('d M Y') }}
                                @if($prestito->stato === 'restituito' && $prestito->restituito_il)
                                    · restituito il {{ $prestito->restituito_il->translatedFormat('d M Y') }}
                                @elseif($prestito->isInCorso())
                                    · da restituire entro il <strong class="text-cral-navy">{{ $prestito->scadenza->translatedFormat('d M Y') }}</strong>
                                @endif
                            </p>
                        </div>
                        <span class="chip shrink-0 {{ $prestito->isInRitardo() ? 'chip-red' : ($prestito->isInCorso() ? 'chip-navy' : 'bg-cral-navy-tint text-cral-navy-soft') }}">
                            {{ $prestito->statoLabel() }}
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-public-layout>
