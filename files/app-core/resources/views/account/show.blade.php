<x-public-layout title="Il mio account — CRAL">
    <x-page-hero kicker="La mia area" title="Il mio account" subtitle="La tua tessera, i tuoi dati di socio e il codice per verificarla." />

    <div class="container-x max-w-5xl pb-24 sm:pb-32">

        {{-- La tessera personale: compare qui dopo che l'amministratore ha approvato l'iscrizione e verificato il pagamento --}}
        <section class="mb-6" data-reveal aria-labelledby="titolo-tessera">
            <h2 id="titolo-tessera" class="sr-only">La tua tessera CRAL {{ $annoTessera }}</h2>

            @php
                $numeroSulla = \App\Services\TesseraCard::numeroBreve($user->numero_tessera);
            @endphp

            <div class="mx-auto max-w-xl">
                @if($tesseraImmagine)
                    <img src="{{ route('account.tessera') }}" alt="Tessera CRAL {{ $annoTessera }} intestata a {{ $user->name }}, numero {{ $numeroSulla }}"
                        width="1264" height="848" class="h-auto w-full rounded-[1.5rem] shadow-lift">
                @else
                    {{-- Il server non può disegnare il nome sull'immagine: tessera semplificata con gli stessi dati --}}
                    <div class="navy-bg relative isolate overflow-hidden rounded-[1.5rem] p-7 text-white shadow-lift sm:p-9">
                        <div class="pointer-events-none absolute -right-16 -top-16 -z-10 h-56 w-56 rounded-full bg-cral-red/50 blur-3xl" aria-hidden="true"></div>
                        <div class="flex items-start justify-between gap-4">
                            <p class="font-display text-3xl font-bold tracking-tight">CRAL <span class="text-white/70">{{ $annoTessera }}</span></p>
                            <p class="text-right text-sm font-semibold text-white/70">N.<span class="ml-1 font-display text-2xl font-bold text-white">{{ $numeroSulla }}</span></p>
                        </div>
                        <p class="mt-10 text-sm font-semibold text-white/60">Nominativo</p>
                        <p class="mt-1 break-words font-display text-2xl font-bold tracking-tight sm:text-3xl">{{ $user->name }}</p>
                    </div>
                @endif

                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-cral-navy-soft">Tessera CRAL: valida dal {{ $annoTessera }}.</p>
                    @if($tesseraImmagine)
                        <a href="{{ route('account.tessera', ['scarica' => 1]) }}" class="btn btn-ghost btn-sm">Scarica la tessera</a>
                    @endif
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
        <section class="surface self-start p-6 sm:p-8" data-reveal>
            <span class="chip chip-red">Socio CRAL</span>
            <p class="mt-5 text-sm font-semibold text-cral-navy-soft">Numero tessera</p>
            <p class="mt-1 font-display text-3xl font-bold tracking-tight">{{ $user->numero_tessera ?? '—' }}</p>
            <p class="mt-4 text-sm leading-relaxed text-cral-navy-soft">
                Questa è la tua tessera personale: te ne abbiamo mandato una copia anche via email.
                Se hai bisogno di aiuto scrivi a <a class="font-semibold text-cral-red hover:underline" href="mailto:{{ config('mail.admin_address') }}">{{ config('mail.admin_address') }}</a>.
            </p>

            @if($qrDataUri)
                <div class="mt-6 flex items-center gap-4 rounded-2xl bg-cral-navy-tint p-4">
                    <img src="{{ $qrDataUri }}" alt="Codice QR di verifica della tessera" width="88" height="88" class="h-[5.5rem] w-[5.5rem] shrink-0 rounded-xl bg-white p-1.5">
                    <p class="text-sm leading-relaxed text-cral-navy-soft">
                        <strong class="block text-cral-navy">Codice di verifica</strong>
                        Mostralo all'ingresso delle attività per confermare che la tessera è valida.
                    </p>
                </div>
            @endif
        </section>

        <section class="surface p-6 sm:p-8" data-reveal data-delay="0.1">
            <h2 class="text-2xl font-bold tracking-tight">I miei dati</h2>

            <dl class="mt-6 grid gap-x-6 gap-y-5 sm:grid-cols-2">
                @foreach([
                    'Nome e cognome' => $user->name,
                    'Email' => $user->email,
                    'Telefono' => $user->telefono ?? '—',
                    'Data di nascita' => optional($user->data_nascita)->translatedFormat('d F Y') ?? '—',
                    'Codice fiscale' => $user->codice_fiscale ?? '—',
                    'Indirizzo' => $user->indirizzo ?? '—',
                    'Scadenza quota' => optional($user->data_scadenza_quota)->translatedFormat('d F Y') ?? '—',
                ] as $etichetta => $valore)
                    <div>
                        <dt class="text-sm font-semibold text-cral-navy-soft">{{ $etichetta }}</dt>
                        <dd class="mt-1 break-words font-semibold">{{ $valore }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="mt-8 border-t border-cral-navy/10 pt-6">
                <a href="{{ route('profile.edit') }}" class="btn btn-ghost btn-sm">Modifica nome, email o password</a>
                <p class="mt-3 text-sm text-cral-navy-soft">Per correggere gli altri dati anagrafici contatta gli amministratori del CRAL.</p>
            </div>
        </section>
        </div>
    </div>
</x-public-layout>
