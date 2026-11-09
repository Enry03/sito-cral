<x-public-layout title="Il mio account — CRAL">
    <x-page-hero kicker="La mia area" title="Il mio account" subtitle="I tuoi dati di socio e il codice per verificare la tessera." />

    <div class="container-x grid max-w-5xl gap-6 pb-24 sm:pb-32 lg:grid-cols-[0.8fr_1.2fr]">

        {{-- Tessera: solo i dati. L'immagine della tessera arriva via email, non si mostra sul sito. --}}
        <section class="surface self-start p-6 sm:p-8" data-reveal>
            <span class="chip chip-red">Socio CRAL</span>
            <p class="mt-5 text-sm font-semibold text-cral-navy-soft">Numero tessera</p>
            <p class="mt-1 font-display text-3xl font-bold tracking-tight">{{ $user->numero_tessera ?? '—' }}</p>
            <p class="mt-4 text-sm leading-relaxed text-cral-navy-soft">
                La tua tessera personale ti è stata inviata via email al momento dell'approvazione.
                Se non la trovi, scrivi a <a class="font-semibold text-cral-red hover:underline" href="mailto:{{ config('mail.admin_address') }}">{{ config('mail.admin_address') }}</a>.
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
</x-public-layout>
