@php
    $utente = auth()->user();
    $stato = $utente->stato_iscrizione;
    $richiedeTessera = $utente->ruolo === 'socio';
@endphp

<x-guest-layout title="La tua richiesta — CRAL" :wide="$stato === 'in_attesa' && $richiedeTessera">
    @if(session('ok_pagamento'))
        <p class="notice mb-6" role="status">{{ session('ok_pagamento') }}</p>
    @endif

    @if($stato === 'in_attesa')
        <span class="chip chip-soft">Richiesta in attesa</span>
        <h1 class="mt-4 text-3xl font-bold leading-tight tracking-[-0.03em] sm:text-4xl">
            {{ $richiedeTessera ? 'Quasi socio: manca poco' : 'La tua iscrizione è in fase di approvazione' }}
        </h1>
        <p class="mt-4 leading-relaxed text-cral-navy-soft">
            Ciao {{ $utente->name }}, abbiamo ricevuto la tua richiesta.
            @if($richiedeTessera)
                Appena un amministratore l'avrà approvata riceverai una email con la tua tessera e potrai accedere all'area riservata.
            @else
                Un amministratore la esaminerà a breve: riceverai una email di conferma e potrai accedere all'area riservata e consultare la libreria.
            @endif
        </p>

        @if($richiedeTessera)
            <x-richiesta-stato :utente="$utente" class="mt-8" />
            <x-tessera-pagamento :utente="$utente" class="mt-8 !bg-white !shadow-none ring-1 ring-cral-navy/10" />
        @endif
    @elseif($stato === 'rifiutato')
        <span class="chip chip-navy">Richiesta non accolta</span>
        <h1 class="mt-4 text-3xl font-bold leading-tight tracking-[-0.03em]">Non è stato possibile approvare la richiesta</h1>
        <p class="mt-4 leading-relaxed text-cral-navy-soft">
            Ciao {{ $utente->name }}, la tua richiesta non è stata accolta.
            Per saperne di più scrivi a <a href="mailto:{{ config('mail.admin_address') }}" class="font-semibold text-cral-red hover:underline">{{ config('mail.admin_address') }}</a>.
        </p>
    @else
        <span class="chip chip-navy">Account {{ $stato === 'scaduto' ? 'scaduto' : 'sospeso' }}</span>
        <h1 class="mt-4 text-3xl font-bold leading-tight tracking-[-0.03em]">Il tuo accesso non è attivo</h1>
        <p class="mt-4 leading-relaxed text-cral-navy-soft">
            Ciao {{ $utente->name }}, il tuo account risulta {{ $stato === 'scaduto' ? 'scaduto' : 'sospeso' }}.
            Per riattivarlo scrivi a <a href="mailto:{{ config('mail.admin_address') }}" class="font-semibold text-cral-red hover:underline">{{ config('mail.admin_address') }}</a>.
        </p>
    @endif

    <div class="mt-8 flex flex-wrap items-center justify-between gap-4 border-t border-cral-navy/10 pt-6">
        <a href="{{ route('home') }}" class="text-sm font-semibold text-cral-navy-soft transition hover:text-cral-red">&larr; Torna al sito</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-ghost btn-sm">Esci</button>
        </form>
    </div>
</x-guest-layout>
