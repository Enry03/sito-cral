<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>In attesa di approvazione - CRAL</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=anton|archivo:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-grotesk antialiased text-cral-navy bg-cral-cream">

    <div class="min-h-screen flex flex-col items-center justify-center px-4 text-center">
        <div class="w-full max-w-md border-2 border-cral-navy bg-white stack-shadow p-8 sm:p-10">
            <span class="flex h-14 w-14 mx-auto items-center justify-center bg-cral-navy text-white font-display text-lg tracking-wide ring-2 ring-cral-red ring-offset-2 ring-offset-white">
                CR
            </span>

            <span class="mt-6 inline-block bg-cral-red text-white text-[10px] font-bold tracking-[0.2em] uppercase px-3 py-1.5">
                Richiesta in attesa
            </span>
            <h1 class="mt-4 font-display uppercase text-2xl sm:text-3xl tracking-wide">La tua iscrizione è in fase di approvazione</h1>
            <p class="mt-4 text-sm text-cral-navy-soft leading-relaxed">
                Ciao {{ auth()->user()->name }}, abbiamo ricevuto la tua richiesta di iscrizione al CRAL.
                Un amministratore la esaminerà a breve: riceverai la tua tessera e potrai accedere
                all'area riservata non appena verrà approvata.
            </p>

            <div class="mt-8 border-t-2 border-dashed border-cral-navy/30 pt-6 flex items-center justify-center gap-6">
                <a href="{{ route('home') }}" class="text-xs font-bold tracking-widest uppercase text-cral-navy-soft hover:text-cral-red transition">Torna al sito</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center border-2 border-cral-navy bg-cral-navy px-5 py-2.5 text-xs font-bold tracking-[0.15em] uppercase text-white hover:bg-cral-navy/90 transition">
                        Esci
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
