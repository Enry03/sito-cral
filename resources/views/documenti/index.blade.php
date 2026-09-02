<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Documenti
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-100">
                @if($documenti->isEmpty())
                    <div class="p-12 text-center text-slate-500">
                        Nessun documento disponibile al momento.
                    </div>
                @else
                    <ul class="divide-y divide-slate-100">
                        @foreach($documenti as $documento)
                            <li class="flex items-center justify-between gap-4 p-5">
                                <div class="flex items-center gap-4 min-w-0">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-orange-50 text-orange-600">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-900 truncate">{{ $documento->titolo }}</p>
                                        @if($documento->descrizione)
                                            <p class="text-sm text-slate-500 truncate">{{ $documento->descrizione }}</p>
                                        @endif
                                    </div>
                                </div>
                                <a href="{{ route('documenti.download', $documento) }}" class="shrink-0 inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 transition">
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
