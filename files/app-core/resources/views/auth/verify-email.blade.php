<x-guest-layout title="Verifica la tua email — CRAL">
    <h1 class="text-3xl font-bold tracking-[-0.03em]">Verifica la tua email</h1>
    <p class="mt-3 leading-relaxed text-cral-navy-soft">
        Grazie per l'iscrizione! Prima di iniziare, conferma il tuo indirizzo email cliccando sul link che ti abbiamo appena scritto.
        Se non l'hai ricevuto, te ne inviamo volentieri un altro.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="notice mt-6" role="status">Ti abbiamo inviato un nuovo link di verifica all'indirizzo che hai indicato.</div>
    @endif

    <div class="mt-8 flex flex-wrap items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary">Invia di nuovo l'email</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm font-semibold text-cral-navy-soft transition hover:text-cral-red">Esci</button>
        </form>
    </div>
</x-guest-layout>
