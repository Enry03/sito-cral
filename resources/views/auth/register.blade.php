<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iscriviti - CRAL</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=anton|archivo:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-grotesk antialiased text-cral-navy bg-cral-cream">

    <div class="min-h-screen flex flex-col items-center py-10 px-4">
        <a href="{{ route('home') }}" class="flex items-center gap-3 mb-8">
            <span class="flex h-11 w-11 items-center justify-center bg-cral-navy text-white font-display text-xl tracking-wide ring-2 ring-cral-red ring-offset-2 ring-offset-cral-cream">CR</span>
            <span class="leading-none">
                <span class="block font-display text-2xl tracking-wide">CRAL</span>
                <span class="block text-[10px] font-semibold tracking-[0.2em] text-cral-navy-soft uppercase -mt-0.5">Associazione dopolavoro</span>
            </span>
        </a>

        <div class="w-full max-w-2xl border-2 border-cral-navy bg-white stack-shadow">
            <div class="flex items-center justify-between px-8 py-4 border-b-2 border-cral-navy bg-cral-navy text-white">
                <span class="font-grotesk text-xs font-bold tracking-[0.2em] uppercase">/// Modulo di iscrizione</span>
                <span class="font-grotesk text-[10px] font-bold tracking-widest uppercase text-white/60">Nuovo socio</span>
            </div>

            <div class="p-8 sm:p-10">
                <h1 class="font-display uppercase text-3xl sm:text-4xl tracking-wide">Iscriviti al CRAL</h1>
                <p class="mt-3 text-sm text-cral-navy-soft leading-relaxed">
                    Compila il modulo per richiedere l'iscrizione. Una volta approvata dai gestori, riceverai la tua
                    tessera associativa e potrai accedere all'area riservata.
                </p>

                <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
                    @csrf

                    <div>
                        <label for="name" class="block font-grotesk text-[10px] font-bold tracking-[0.2em] uppercase text-cral-navy-soft mb-1.5">Nome e cognome</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                            class="block w-full border-2 border-cral-navy px-4 py-3 font-grotesk font-semibold focus:outline-none focus:ring-2 focus:ring-cral-red focus:border-cral-red">
                        @error('name') <p class="mt-1.5 text-xs font-semibold text-cral-red">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label for="email" class="block font-grotesk text-[10px] font-bold tracking-[0.2em] uppercase text-cral-navy-soft mb-1.5">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                                class="block w-full border-2 border-cral-navy px-4 py-3 font-grotesk font-semibold focus:outline-none focus:ring-2 focus:ring-cral-red focus:border-cral-red">
                            @error('email') <p class="mt-1.5 text-xs font-semibold text-cral-red">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="telefono" class="block font-grotesk text-[10px] font-bold tracking-[0.2em] uppercase text-cral-navy-soft mb-1.5">Telefono</label>
                            <input id="telefono" type="tel" name="telefono" value="{{ old('telefono') }}" required autocomplete="tel"
                                class="block w-full border-2 border-cral-navy px-4 py-3 font-grotesk font-semibold focus:outline-none focus:ring-2 focus:ring-cral-red focus:border-cral-red">
                            @error('telefono') <p class="mt-1.5 text-xs font-semibold text-cral-red">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label for="data_nascita" class="block font-grotesk text-[10px] font-bold tracking-[0.2em] uppercase text-cral-navy-soft mb-1.5">Data di nascita</label>
                            <input id="data_nascita" type="date" name="data_nascita" value="{{ old('data_nascita') }}" required
                                class="block w-full border-2 border-cral-navy px-4 py-3 font-grotesk font-semibold focus:outline-none focus:ring-2 focus:ring-cral-red focus:border-cral-red">
                            @error('data_nascita') <p class="mt-1.5 text-xs font-semibold text-cral-red">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="codice_fiscale" class="block font-grotesk text-[10px] font-bold tracking-[0.2em] uppercase text-cral-navy-soft mb-1.5">Codice fiscale</label>
                            <input id="codice_fiscale" type="text" name="codice_fiscale" value="{{ old('codice_fiscale') }}" maxlength="16" required
                                class="block w-full border-2 border-cral-navy px-4 py-3 font-grotesk font-semibold uppercase focus:outline-none focus:ring-2 focus:ring-cral-red focus:border-cral-red">
                            @error('codice_fiscale') <p class="mt-1.5 text-xs font-semibold text-cral-red">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="indirizzo" class="block font-grotesk text-[10px] font-bold tracking-[0.2em] uppercase text-cral-navy-soft mb-1.5">Indirizzo di residenza</label>
                        <input id="indirizzo" type="text" name="indirizzo" value="{{ old('indirizzo') }}" required autocomplete="street-address"
                            class="block w-full border-2 border-cral-navy px-4 py-3 font-grotesk font-semibold focus:outline-none focus:ring-2 focus:ring-cral-red focus:border-cral-red">
                        @error('indirizzo') <p class="mt-1.5 text-xs font-semibold text-cral-red">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label for="password" class="block font-grotesk text-[10px] font-bold tracking-[0.2em] uppercase text-cral-navy-soft mb-1.5">Password</label>
                            <input id="password" type="password" name="password" required autocomplete="new-password"
                                class="block w-full border-2 border-cral-navy px-4 py-3 font-grotesk font-semibold focus:outline-none focus:ring-2 focus:ring-cral-red focus:border-cral-red">
                            @error('password') <p class="mt-1.5 text-xs font-semibold text-cral-red">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block font-grotesk text-[10px] font-bold tracking-[0.2em] uppercase text-cral-navy-soft mb-1.5">Conferma password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                                class="block w-full border-2 border-cral-navy px-4 py-3 font-grotesk font-semibold focus:outline-none focus:ring-2 focus:ring-cral-red focus:border-cral-red">
                            @error('password_confirmation') <p class="mt-1.5 text-xs font-semibold text-cral-red">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="border-t-2 border-dashed border-cral-navy/30 pt-6 flex items-center justify-between gap-4">
                        <a class="text-xs font-bold tracking-widest uppercase text-cral-navy-soft hover:text-cral-red transition" href="{{ route('login') }}">
                            Hai già un account?
                        </a>

                        <button type="submit" class="inline-flex items-center border-2 border-cral-navy bg-cral-red px-7 py-3.5 text-sm font-bold tracking-[0.15em] uppercase text-white stack-shadow-sm hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-none transition">
                            Invia richiesta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
