<x-guest-layout title="Nuova password — CRAL">
    <h1 class="text-3xl font-bold tracking-[-0.03em]">Scegli una nuova password</h1>

    <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" value="Nuova password" />
            <x-password-input id="password" name="password" required autocomplete="new-password" />
            <p class="help">Almeno 10 caratteri, con maiuscole, minuscole e numeri.</p>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Conferma password" />
            <x-password-input id="password_confirmation" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <button type="submit" class="btn btn-primary w-full">Salva la nuova password</button>
    </form>
</x-guest-layout>
