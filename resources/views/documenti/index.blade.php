<x-app-layout>
    <x-slot name="header">
        <span class="font-grotesk text-xs font-bold tracking-[0.2em] uppercase text-cral-red">Area riservata</span>
        <h2 class="mt-1 font-display uppercase text-3xl tracking-wide">Documenti</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border-2 border-cral-navy">
                @if($documenti->isEmpty())
                    <div class="p-16 text-center">
                        <p class="font-display text-2xl uppercase tracking-wide text-cral-navy-soft">Archivio vuoto</p>
                        <p class="mt-2 text-sm text-cral-navy-soft">Nessun documento disponibile al momento.</p>
                    </div>
                @else
                    <ul class="divide-y-2 divide-cral-navy">
                        @foreach($documenti as $documento)
                            <li class="flex items-center justify-between gap-4 p-5">
                                <div class="flex items-center gap-4 min-w-0">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center bg-cral-navy text-white">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-grotesk font-bold uppercase tracking-wide truncate">{{ $documento->titolo }}</p>
                                        @if($documento->descrizione)
                                            <p class="text-sm text-cral-navy-soft truncate">{{ $documento->descrizione }}</p>
                                        @endif
                                    </div>
                                </div>
                                <a href="{{ route('documenti.download', $documento) }}" class="shrink-0 inline-flex items-center border-2 border-cral-navy bg-cral-navy px-5 py-2.5 font-grotesk text-xs font-bold tracking-[0.15em] uppercase text-white hover:bg-cral-red hover:border-cral-red transition">
                                    Scarica
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="mt-8">
                {{ $documenti->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
