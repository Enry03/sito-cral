<x-guest-layout title="Password dimenticata — CRAL">
    <h1 class="text-3xl font-bold tracking-[-0.03em]">Password dimenticata?</h1>
    <p class="mt-3 leading-relaxed text-cral-navy-soft">
        Nessun problema: scrivi la tua email e ti inviamo un link per scegliere una nuova password.
    </p>

    <x-auth-session-status class="mt-6" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <button type="submit" class="btn btn-primary w-full">Invia il link per cambiare password</button>
    </form>

    <p class="mt-8 text-center text-sm"><a href="{{ route('login') }}" class="font-semibold text-cral-navy-soft transition hover:text-cral-red">Torna all'accesso</a></p>
</x-guest-layout>
