<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Ciao, {{ auth()->user()->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-100 p-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-slate-500">Numero tessera</p>
                    <p class="text-lg font-bold text-slate-900">{{ auth()->user()->numero_tessera ?? '—' }}</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                    Socio attivo
                </span>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <a href="{{ route('documenti.index') }}" class="group bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-100 p-6 hover:shadow-md hover:border-orange-200 transition">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-orange-50 text-orange-600 mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <h3 class="font-bold text-slate-900 group-hover:text-orange-600 transition">Documenti</h3>
                    <p class="mt-1 text-sm text-slate-500">Consulta i documenti caricati dall'associazione.</p>
                </a>

                <a href="{{ route('account.show') }}" class="group bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-100 p-6 hover:shadow-md hover:border-orange-200 transition">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-orange-50 text-orange-600 mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <h3 class="font-bold text-slate-900 group-hover:text-orange-600 transition">Il mio account</h3>
                    <p class="mt-1 text-sm text-slate-500">Visualizza i tuoi dati e la tua tessera associativa.</p>
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-100 p-6">
                <h3 class="font-bold text-slate-900 mb-2">Prossime attività</h3>
                <p class="text-sm text-slate-500 mb-4">Scopri cosa organizza il CRAL per i soci.</p>
                <a href="{{ route('attivita.index') }}" class="inline-flex items-center text-sm font-semibold text-orange-600 hover:text-orange-700">
                    Vai alle attività &rarr;
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
