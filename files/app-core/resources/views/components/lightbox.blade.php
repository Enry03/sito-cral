{{--
    Finestra per ingrandire le foto, con frecce, tastiera e scorrimento col dito.
    Va dentro un elemento con x-data="galleria". Le foto sono i pulsanti con
    l'attributo data-foto (data-src per l'immagine grande, data-didascalia per il testo).
--}}
<div x-show="aperta" x-cloak x-transition.opacity.duration.300ms
    @keydown.escape.window="chiudi()" @keydown.arrow-right.window="aperta && avanti()" @keydown.arrow-left.window="aperta && indietro()"
    @touchstart.passive="inizioTocco = $event.changedTouches[0].clientX"
    @touchend.passive="finisciTocco($event.changedTouches[0].clientX)"
    class="fixed inset-0 z-[80] flex flex-col bg-cral-navy-deep/95 backdrop-blur-md" style="display: none;" role="dialog" aria-modal="true" aria-label="Foto ingrandita">

    <div class="flex items-center justify-between px-4 py-4 text-white sm:px-8">
        <p class="text-sm font-semibold text-white/70" x-text="(indice + 1) + ' di ' + foto.length"></p>
        <button type="button" @click="chiudi()" class="grid h-11 w-11 place-items-center rounded-full bg-white/10 transition hover:bg-white/25" aria-label="Chiudi">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
    </div>

    <div class="relative flex min-h-0 flex-1 items-center justify-center px-2 sm:px-20" @click.self="chiudi()">
        <button type="button" @click="indietro()" x-show="foto.length > 1" class="absolute left-2 top-1/2 z-10 grid h-12 w-12 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-white transition hover:bg-white/25 sm:left-6" aria-label="Foto precedente">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>
        </button>

        <img :src="corrente()?.src" :alt="corrente()?.didascalia || ''" :key="indice"
            class="max-h-full max-w-full rounded-2xl object-contain shadow-lift transition duration-500" draggable="false">

        <button type="button" @click="avanti()" x-show="foto.length > 1" class="absolute right-2 top-1/2 z-10 grid h-12 w-12 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-white transition hover:bg-white/25 sm:right-6" aria-label="Foto successiva">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
        </button>
    </div>

    <p x-show="corrente()?.didascalia" x-text="corrente()?.didascalia" class="px-6 py-5 text-center text-white"></p>
    <div x-show="! corrente()?.didascalia" class="h-5"></div>
</div>
