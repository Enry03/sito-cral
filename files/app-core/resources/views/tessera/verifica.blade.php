<x-public-layout title="Verifica tessera — CRAL">
    <section class="hero-bg relative isolate flex min-h-[80vh] items-center overflow-hidden text-white">
        <div class="pointer-events-none absolute -right-24 -top-24 -z-10 h-[26rem] w-[26rem] animate-drift rounded-full bg-cral-red-glow/30 blur-3xl" aria-hidden="true"></div>

        <div class="container-x py-32">
            <div class="mx-auto max-w-md rounded-[2rem] bg-white p-8 text-center text-cral-navy shadow-lift sm:p-10" data-reveal="scale">
                @if($socio && $socio->isAttivo())
                    <span class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-cral-navy text-white">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7" /></svg>
                    </span>
                    <p class="mt-5"><span class="chip chip-navy">Tessera valida</span></p>
                    <h1 class="mt-4 text-3xl font-bold tracking-[-0.03em]">{{ $socio->name }}</h1>
                    <p class="mt-2 text-cral-navy-soft">è un socio attivo del CRAL.</p>
                    <p class="mt-4 text-sm font-semibold text-cral-navy-soft">Tessera n. {{ $socio->numero_tessera }}</p>
                @else
                    <span class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-cral-red text-white">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </span>
                    <p class="mt-5"><span class="chip chip-red">Tessera non valida</span></p>
                    <h1 class="mt-4 text-3xl font-bold tracking-[-0.03em]">Non trovata</h1>
                    <p class="mt-2 text-cral-navy-soft">Questo numero di tessera non risulta associato a un socio attivo.</p>
                @endif
            </div>
        </div>
    </section>
</x-public-layout>
