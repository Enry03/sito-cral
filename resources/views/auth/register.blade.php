<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iscriviti - CRAL</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-50">

    <div class="min-h-screen flex flex-col items-center py-10 px-4">
        <a href="{{ route('home') }}" class="flex items-center gap-3 mb-8">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white font-extrabold text-lg shadow-sm">CR</span>
            <span class="leading-tight">
                <span class="block font-extrabold text-lg text-slate-900">CRAL</span>
                <span class="block text-xs text-slate-500 -mt-0.5">Associazione dopolavoro</span>
            </span>
        </a>

        <div class="w-full max-w-2xl bg-white rounded-2xl shadow-sm border border-slate-100 p-8 sm:p-10">
            <h1 class="text-2xl font-extrabold text-slate-900">Iscriviti al CRAL</h1>
            <p class="mt-2 text-sm text-slate-500">
                Compila il modulo per richiedere l'iscrizione. Una volta approvata dai gestori, riceverai la tua
                tessera associativa e potrai accedere all'area riservata.
            </p>

            <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <x-input-label for="name" value="Nome e cognome" />
                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="telefono" value="Telefono" />
                        <x-text-input id="telefono" class="block mt-1 w-full" type="tel" name="telefono" :value="old('telefono')" required autocomplete="tel" />
                        <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <x-input-label for="data_nascita" value="Data di nascita" />
                        <x-text-input id="data_nascita" class="block mt-1 w-full" type="date" name="data_nascita" :value="old('data_nascita')" required />
                        <x-input-error :messages="$errors->get('data_nascita')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="codice_fiscale" value="Codice fiscale" />
                        <x-text-input id="codice_fiscale" class="block mt-1 w-full uppercase" type="text" name="codice_fiscale" :value="old('codice_fiscale')" maxlength="16" required />
                        <x-input-error :messages="$errors->get('codice_fiscale')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="indirizzo" value="Indirizzo di residenza" />
                    <x-text-input id="indirizzo" class="block mt-1 w-full" type="text" name="indirizzo" :value="old('indirizzo')" required autocomplete="street-address" />
                    <x-input-error :messages="$errors->get('indirizzo')" class="mt-2" />
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <x-input-label for="password" value="Password" />
                        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="password_confirmation" value="Conferma password" />
                        <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4">
                    <a class="text-sm text-slate-500 hover:text-slate-700 underline" href="{{ route('login') }}">
                        Hai già un account? Accedi
                    </a>

                    <button type="submit" class="inline-flex items-center rounded-lg bg-orange-600 px-6 py-3 text-sm font-semibold text-white hover:bg-orange-700 transition">
                        Invia richiesta di iscrizione
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
