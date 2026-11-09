@php
    $utente = auth()->user();
@endphp

<x-public-layout title="La mia area — CRAL">
    <x-page-hero kicker="La mia area" :title="'Ciao, '.\Illuminate\Support\Str::before($utente->name, ' ')"
        :subtitle="$utente->isSocioCral() ? 'Tutto quello che puoi fare come socio CRAL.' : 'Ecco cosa puoi fare con il tuo account.'" />

    <div class="container-x max-w-4xl space-y-6 pb-24 sm:pb-32">
        @if(session('avviso'))
            <p class="notice notice-error" role="status" data-reveal>{{ session('avviso') }}</p>
        @endif

        {{-- Stato dell'iscrizione --}}
        @if($utente->isSocioCral())
            <section class="surface flex flex-wrap items-center justify-between gap-4 p-6 sm:p-7" data-reveal>
                <div>
                    <p class="text-sm font-semibold text-cral-navy-soft">Numero tessera</p>
                    <p class="mt-1 font-display text-2xl font-bold tracking-tight">{{ $utente->numero_tessera ?? '—' }}</p>
                </div>
                <span class="chip chip-red">{{ $utente->isAdmin() ? 'Amministratore' : 'Socio attivo' }}</span>
            </section>
        @else
            <section class="surface flex flex-wrap items-center justify-between gap-5 p-6 sm:p-7" data-reveal>
                <div class="max-w-xl">
                    <span class="chip chip-soft">Iscritto al sito</span>
                    <p class="mt-3 leading-relaxed text-cral-navy-soft">
                        Puoi consultare il catalogo della libreria. Con la tessera di socio vedi anche tutte le foto, ti iscrivi alle attività e prendi in prestito i libri.
                    </p>
                </div>
                @if($utente->richiede_tessera)
                    <a href="{{ route('socio.richiesta') }}" class="btn btn-ghost btn-sm">Vedi la richiesta di tessera</a>
                @else
                    <a href="{{ route('socio.richiesta') }}" class="btn btn-primary btn-sm">Diventa socio CRAL</a>
                @endif
            </section>
        @endif

        <div class="grid gap-4 sm:grid-cols-2" data-stagger="0.08">
            @php
                $voci = [
                    ['href' => route('libreria.index'), 'titolo' => 'Libreria', 'testo' => $utente->isSocioCral() ? 'Sfoglia il catalogo e prendi in prestito i libri.' : 'Sfoglia il catalogo dei libri del CRAL.', 'icona' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                    ['href' => route('attivita.index'), 'titolo' => 'Attività', 'testo' => $utente->isSocioCral() ? 'Scopri cosa organizza il CRAL e iscriviti.' : 'Scopri cosa organizza il CRAL per i soci.', 'icona' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ];

                if ($utente->isSocioCral()) {
                    $voci[] = ['href' => route('libreria.prestiti'), 'titolo' => 'I miei prestiti', 'testo' => 'Controlla i libri che hai in prestito e le scadenze.', 'icona' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'];
                    $voci[] = ['href' => route('documenti.index'), 'titolo' => 'Documenti', 'testo' => 'Consulta i documenti caricati dall\'associazione.', 'icona' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'];
                    $voci[] = ['href' => route('account.show'), 'titolo' => 'Il mio account', 'testo' => 'I tuoi dati e il codice di verifica della tessera.', 'icona' => 'M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z'];
                }

                $voci[] = ['href' => route('profile.edit'), 'titolo' => 'Profilo e password', 'testo' => 'Modifica nome, email o password.', 'icona' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'];
            @endphp

            @foreach($voci as $voce)
                <a href="{{ $voce['href'] }}" data-reveal class="group surface flex items-start gap-5 p-6 transition duration-500 ease-premium hover:-translate-y-1 hover:shadow-lift">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-cral-red-tint text-cral-red transition duration-500 group-hover:bg-cral-red group-hover:text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="{{ $voce['icona'] }}"/></svg>
                    </span>
                    <span>
                        <span class="block font-display text-lg font-bold tracking-tight">{{ $voce['titolo'] }}</span>
                        <span class="mt-1 block text-[0.95rem] leading-relaxed text-cral-navy-soft">{{ $voce['testo'] }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</x-public-layout>
