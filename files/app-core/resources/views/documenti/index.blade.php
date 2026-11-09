<x-public-layout title="Documenti — CRAL">
    <x-page-hero kicker="La mia area" title="Documenti" subtitle="I documenti caricati dall'associazione, riservati ai soci." />

    <div class="container-x max-w-4xl pb-24 sm:pb-32">
        @if($documenti->isEmpty())
            <div class="surface px-6 py-16 text-center" data-reveal>
                <p class="font-display text-2xl font-bold tracking-tight">Nessun documento per ora</p>
                <p class="mt-2 text-cral-navy-soft">Quando l'associazione ne caricherà, li troverai qui.</p>
            </div>
        @else
            <ul class="space-y-3" data-stagger="0.06">
                @foreach($documenti as $documento)
                    <li data-reveal class="surface flex items-center justify-between gap-4 p-4 sm:p-5">
                        <div class="flex min-w-0 items-center gap-4">
                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-cral-red-tint text-cral-red">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="truncate font-semibold">{{ $documento->titolo }}</p>
                                @if($documento->descrizione)
                                    <p class="truncate text-sm text-cral-navy-soft">{{ $documento->descrizione }}</p>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('documenti.download', $documento) }}" class="btn btn-navy btn-sm shrink-0">Scarica</a>
                    </li>
                @endforeach
            </ul>

            <div class="mt-10">
                {{ $documenti->links() }}
            </div>
        @endif
    </div>
</x-public-layout>
