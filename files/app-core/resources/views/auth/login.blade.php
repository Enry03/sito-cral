<x-guest-layout title="Accedi — CRAL">
    <h1 class="text-4xl font-bold tracking-[-0.03em]">Bentornato</h1>
    <p class="mt-3 leading-relaxed text-cral-navy-soft">Accedi con la tua email per entrare nell'area riservata.</p>

    @if (session('status'))
        <div class="notice mt-6" role="status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="email" class="label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                class="field {{ $errors->has('email') ? 'is-invalid' : '' }}">
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <label for="password" class="label">Password</label>
            <x-password-input name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <label for="remember_me" class="flex items-center gap-2.5 text-sm font-semibold">
                <input id="remember_me" type="checkbox" name="remember" class="h-5 w-5 rounded-md border-cral-navy/30 text-cral-red focus:ring-cral-red/30">
                Ricordami
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-semibold text-cral-navy-soft transition hover:text-cral-red" href="{{ route('password.request') }}">Password dimenticata?</a>
            @endif
        </div>

        <button type="submit" class="btn btn-primary w-full">Accedi</button>
    </form>

    <p class="mt-8 border-t border-cral-navy/10 pt-6 text-center text-sm text-cral-navy-soft">
        Non hai ancora un account?
        <a href="{{ route('register') }}" class="font-semibold text-cral-red hover:underline">Iscriviti</a>
    </p>
</x-guest-layout>
