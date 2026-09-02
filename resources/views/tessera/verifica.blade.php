<x-public-layout title="Verifica tessera - CRAL">
    <section class="max-w-md mx-auto px-4 py-24 text-center">
        @if($socio && $socio->isAttivo())
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 mx-auto">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            </div>
            <h1 class="mt-6 text-xl font-extrabold text-slate-900">Tessera valida</h1>
            <p class="mt-2 text-slate-500">{{ $socio->name }} è un socio attivo del CRAL.</p>
            <p class="mt-1 text-sm text-slate-400 font-mono">Tessera n. {{ $socio->numero_tessera }}</p>
        @else
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-red-50 text-red-600 mx-auto">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </div>
            <h1 class="mt-6 text-xl font-extrabold text-slate-900">Tessera non valida</h1>
            <p class="mt-2 text-slate-500">Questo numero di tessera non risulta associato a un socio attivo.</p>
        @endif
    </section>
</x-public-layout>
