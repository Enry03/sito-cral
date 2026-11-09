<x-guest-layout title="Conferma la password — CRAL">
    <h1 class="text-3xl font-bold tracking-[-0.03em]">Conferma la password</h1>
    <p class="mt-3 leading-relaxed text-cral-navy-soft">Questa è un'area protetta: conferma la tua password per continuare.</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <x-input-label for="password" value="Password" />
            <x-password-input id="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <button type="submit" class="btn btn-primary w-full">Conferma</button>
    </form>
</x-guest-layout>
