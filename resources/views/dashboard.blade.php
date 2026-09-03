<x-app-layout>
    <x-slot name="header">
        <span class="font-grotesk text-xs font-bold tracking-[0.2em] uppercase text-cral-red">Area riservata</span>
        <h2 class="mt-1 font-display uppercase text-3xl tracking-wide">Ciao, {{ auth()->user()->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <div class="bg-white border-2 border-cral-navy p-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="font-grotesk text-[10px] font-bold tracking-[0.2em] uppercase text-cral-navy-soft">Numero tessera</p>
                    <p class="mt-1 font-grotesk font-bold text-lg tracking-widest">{{ auth()->user()->numero_tessera ?? '—' }}</p>
                </div>
                <span class="bg-cral-navy text-white text-[10px] font-bold tracking-[0.2em] uppercase px-3 py-1.5">
                    Socio attivo
                </span>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <a href="{{ route('documenti.index') }}" class="group bg-white border-2 border-cral-navy p-6 hover:stack-shadow-sm transition-shadow">
                    <div class="flex h-11 w-11 items-center justify-center bg-cral-navy text-white mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <h3 class="font-grotesk font-bold uppercase tracking-wide group-hover:text-cral-red transition">Documenti</h3>
                    <p class="mt-1 text-sm text-cral-navy-soft">Consulta i documenti caricati dall'associazione.</p>
                </a>

                <a href="{{ route('account.show') }}" class="group bg-white border-2 border-cral-navy p-6 hover:stack-shadow-sm transition-shadow">
                    <div class="flex h-11 w-11 items-center justify-center bg-cral-navy text-white mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <h3 class="font-grotesk font-bold uppercase tracking-wide group-hover:text-cral-red transition">Il mio account</h3>
                    <p class="mt-1 text-sm text-cral-navy-soft">Visualizza i tuoi dati e la tua tessera associativa.</p>
                </a>
            </div>

            <div class="bg-white border-2 border-cral-navy p-6">
                <h3 class="font-grotesk font-bold uppercase tracking-wide mb-2">Prossime attività</h3>
                <p class="text-sm text-cral-navy-soft mb-4">Scopri cosa organizza il CRAL per i soci.</p>
                <a href="{{ route('attivita.index') }}" class="inline-flex items-center font-grotesk text-xs font-bold tracking-[0.15em] uppercase text-cral-red hover:text-cral-navy transition">
                    Vai alle attività &rarr;
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
