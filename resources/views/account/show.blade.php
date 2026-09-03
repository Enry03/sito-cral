<x-app-layout>
    <x-slot name="header">
        <span class="font-grotesk text-xs font-bold tracking-[0.2em] uppercase text-cral-red">Area riservata</span>
        <h2 class="mt-1 font-display uppercase text-3xl tracking-wide">Il mio account</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid gap-8 lg:grid-cols-3">

            <!-- Tessera -->
            <div class="lg:col-span-1">
                <div class="ticket-card border-2 border-cral-navy bg-cral-navy text-white p-6 stack-shadow-red">
                    <div class="flex items-center justify-between">
                        <span class="font-display text-lg tracking-wide">CRAL</span>
                        <span class="font-grotesk text-[10px] font-bold tracking-[0.2em] uppercase text-white/60">Tessera socio</span>
                    </div>

                    <div class="tear-line my-4 opacity-40"></div>

                    <p class="font-grotesk text-[10px] tracking-[0.2em] uppercase text-white/50">Intestatario</p>
                    <p class="font-display text-2xl uppercase tracking-wide mt-1 truncate">{{ $user->name }}</p>

                    <div class="mt-6 flex items-end justify-between gap-4">
                        <div class="min-w-0">
                            <p class="font-grotesk text-[10px] tracking-[0.2em] uppercase text-white/50">N. tessera</p>
                            <p class="font-grotesk font-bold tracking-widest">{{ $user->numero_tessera ?? 'In lavorazione' }}</p>
                        </div>
                        @if($qrDataUri)
                            <img src="{{ $qrDataUri }}" alt="QR tessera" class="h-16 w-16 shrink-0 bg-white p-1">
                        @endif
                    </div>
                </div>

                @unless($user->numero_tessera)
                    <p class="mt-4 text-sm text-cral-navy-soft">
                        Il numero di tessera verrà assegnato automaticamente non appena la tua iscrizione sarà approvata.
                    </p>
                @endunless
            </div>

            <!-- Dati -->
            <div class="lg:col-span-2 bg-white border-2 border-cral-navy p-6 sm:p-8">
                <span class="font-grotesk text-xs font-bold tracking-[0.2em] uppercase text-cral-red">I miei dati</span>

                <dl class="mt-6 grid sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
                    <div>
                        <dt class="font-grotesk text-[10px] font-bold tracking-[0.2em] uppercase text-cral-navy-soft">Nome e cognome</dt>
                        <dd class="mt-1 font-grotesk font-bold">{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="font-grotesk text-[10px] font-bold tracking-[0.2em] uppercase text-cral-navy-soft">Email</dt>
                        <dd class="mt-1 font-grotesk font-bold">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="font-grotesk text-[10px] font-bold tracking-[0.2em] uppercase text-cral-navy-soft">Telefono</dt>
                        <dd class="mt-1 font-grotesk font-bold">{{ $user->telefono ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-grotesk text-[10px] font-bold tracking-[0.2em] uppercase text-cral-navy-soft">Data di nascita</dt>
                        <dd class="mt-1 font-grotesk font-bold">{{ optional($user->data_nascita)->translatedFormat('d F Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-grotesk text-[10px] font-bold tracking-[0.2em] uppercase text-cral-navy-soft">Codice fiscale</dt>
                        <dd class="mt-1 font-grotesk font-bold">{{ $user->codice_fiscale ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-grotesk text-[10px] font-bold tracking-[0.2em] uppercase text-cral-navy-soft">Indirizzo</dt>
                        <dd class="mt-1 font-grotesk font-bold">{{ $user->indirizzo ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-grotesk text-[10px] font-bold tracking-[0.2em] uppercase text-cral-navy-soft">Scadenza quota</dt>
                        <dd class="mt-1 font-grotesk font-bold">{{ optional($user->data_scadenza_quota)->translatedFormat('d F Y') ?? '—' }}</dd>
                    </div>
                </dl>

                <div class="mt-8 pt-6 border-t-2 border-dashed border-cral-navy/30">
                    <a href="{{ route('profile.edit') }}" class="inline-flex items-center font-grotesk text-xs font-bold tracking-[0.15em] uppercase text-cral-red hover:text-cral-navy transition">
                        Modifica nome, email o password &rarr;
                    </a>
                    <p class="mt-2 text-xs text-cral-navy-soft">Per correggere gli altri dati anagrafici contatta gli amministratori del CRAL.</p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
