<x-public-layout title="Verifica tessera - CRAL">
    <section class="max-w-md mx-auto px-4 py-24 text-center">
        <div class="border-2 border-cral-navy bg-white stack-shadow p-8">
            @if($socio && $socio->isAttivo())
                <span class="flex h-16 w-16 mx-auto items-center justify-center bg-cral-navy text-white">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </span>
                <span class="mt-5 inline-block bg-cral-navy text-white text-[10px] font-bold tracking-[0.2em] uppercase px-3 py-1.5">Tessera valida</span>
                <h1 class="mt-4 font-display uppercase text-2xl tracking-wide">{{ $socio->name }}</h1>
                <p class="mt-2 text-sm text-cral-navy-soft">è un socio attivo del CRAL.</p>
                <p class="mt-3 font-grotesk text-xs font-bold tracking-widest text-cral-navy-soft">Tessera n. {{ $socio->numero_tessera }}</p>
            @else
                <span class="flex h-16 w-16 mx-auto items-center justify-center bg-cral-red text-white">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </span>
                <span class="mt-5 inline-block bg-cral-red text-white text-[10px] font-bold tracking-[0.2em] uppercase px-3 py-1.5">Tessera non valida</span>
                <h1 class="mt-4 font-display uppercase text-2xl tracking-wide">Non trovata</h1>
                <p class="mt-2 text-sm text-cral-navy-soft">Questo numero di tessera non risulta associato a un socio attivo.</p>
            @endif
        </div>
    </section>
</x-public-layout>
