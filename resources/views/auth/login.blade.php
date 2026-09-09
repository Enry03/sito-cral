<x-guest-layout>
    <div class="flex items-center justify-between pb-5 mb-6 border-b-2 border-dashed border-cral-navy/30">
        <span class="font-grotesk text-xs font-bold tracking-[0.2em] uppercase text-cral-red">Accesso soci</span>
    </div>

    <h1 class="font-display uppercase text-3xl tracking-wide mb-6">Accedi</h1>

    @if (session('status'))
        <div class="mb-5 border-2 border-cral-navy bg-cral-cream px-4 py-3 text-sm font-semibold">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block font-grotesk text-[10px] font-bold tracking-[0.2em] uppercase text-cral-navy-soft mb-1.5">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                class="block w-full border-2 border-cral-navy px-4 py-3 font-grotesk font-semibold focus:outline-none focus:ring-2 focus:ring-cral-red focus:border-cral-red">
            @error('email') <p class="mt-1.5 text-xs font-semibold text-cral-red">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="block font-grotesk text-[10px] font-bold tracking-[0.2em] uppercase text-cral-navy-soft mb-1.5">Password</label>
            <x-password-input ticket name="password" required autocomplete="current-password" />
            @error('password') <p class="mt-1.5 text-xs font-semibold text-cral-red">{{ $message }}</p> @enderror
        </div>

        <label for="remember_me" class="flex items-center gap-2 text-sm font-semibold">
            <input id="remember_me" type="checkbox" class="border-2 border-cral-navy text-cral-red focus:ring-cral-red rounded-none" name="remember">
            Ricordami
        </label>

        <div class="border-t-2 border-dashed border-cral-navy/30 pt-6 flex items-center justify-between gap-4">
            @if (Route::has('password.request'))
                <a class="text-xs font-bold tracking-widest uppercase text-cral-navy-soft hover:text-cral-red transition" href="{{ route('password.request') }}">
                    Password dimenticata?
                </a>
            @endif

            <button type="submit" class="inline-flex items-center border-2 border-cral-navy bg-cral-red px-7 py-3.5 text-sm font-bold tracking-[0.15em] uppercase text-white stack-shadow-sm hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-none transition">
                Accedi
            </button>
        </div>
    </form>
</x-guest-layout>
