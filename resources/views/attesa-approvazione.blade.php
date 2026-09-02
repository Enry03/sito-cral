<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>In attesa di approvazione - CRAL</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-50">

    <div class="min-h-screen flex flex-col items-center justify-center px-4 text-center">
        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-white font-extrabold text-2xl shadow-sm mb-6">
            CR
        </div>

        <div class="max-w-md">
            <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">
                Richiesta in attesa
            </span>
            <h1 class="mt-4 text-2xl font-extrabold text-slate-900">La tua iscrizione è in fase di approvazione</h1>
            <p class="mt-3 text-sm text-slate-500">
                Ciao {{ auth()->user()->name }}, abbiamo ricevuto la tua richiesta di iscrizione al CRAL.
                Un amministratore la esaminerà a breve: riceverai la tua tessera e potrai accedere
                all'area riservata non appena verrà approvata.
            </p>

            <div class="mt-8 flex items-center justify-center gap-4">
                <a href="{{ route('home') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Torna al sito</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition">
                        Esci
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
