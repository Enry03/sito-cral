<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 bg-white">

    <div x-data="{ mobileOpen: false }" class="relative">
        <header class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-slate-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-18 py-3">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white font-extrabold text-lg shadow-sm">
                            CR
                        </span>
                        <span class="leading-tight">
                            <span class="block font-extrabold text-lg text-slate-900">CRAL</span>
                            <span class="block text-xs text-slate-500 -mt-0.5">Associazione dopolavoro</span>
                        </span>
                    </a>

                    <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                        <a href="{{ route('home') }}" class="hover:text-orange-600 transition {{ request()->routeIs('home') ? 'text-orange-600' : '' }}">Home</a>
                        <a href="{{ route('attivita.index') }}" class="hover:text-orange-600 transition {{ request()->routeIs('attivita.*') ? 'text-orange-600' : '' }}">Attività</a>
                        <a href="{{ route('foto.index') }}" class="hover:text-orange-600 transition {{ request()->routeIs('foto.*') ? 'text-orange-600' : '' }}">Foto</a>
                        <a href="{{ route('register') }}" class="hover:text-orange-600 transition {{ request()->routeIs('register') ? 'text-orange-600' : '' }}">Iscriviti</a>
                    </nav>

                    <div class="hidden md:flex items-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 transition">
                                Area riservata
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 hover:text-orange-600 transition">Accedi</a>
                            <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700 transition shadow-sm">
                                Iscriviti ora
                            </a>
                        @endauth
                    </div>

                    <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 -mr-2 text-slate-600" aria-label="Apri menu">
                        <svg x-show="!mobileOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        <svg x-show="mobileOpen" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            <div x-show="mobileOpen" x-cloak class="md:hidden border-t border-slate-100 bg-white">
                <div class="px-4 py-4 space-y-3 text-sm font-semibold text-slate-700">
                    <a href="{{ route('home') }}" class="block">Home</a>
                    <a href="{{ route('attivita.index') }}" class="block">Attività</a>
                    <a href="{{ route('foto.index') }}" class="block">Foto</a>
                    <a href="{{ route('register') }}" class="block">Iscriviti</a>
                    <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-lg bg-slate-900 px-4 py-2 text-center text-white">Area riservata</a>
                        @else
                            <a href="{{ route('login') }}" class="text-center">Accedi</a>
                            <a href="{{ route('register') }}" class="rounded-lg bg-orange-600 px-4 py-2 text-center text-white">Iscriviti ora</a>
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer class="bg-slate-900 text-slate-300 mt-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 text-white font-extrabold text-sm">CR</span>
                        <span class="font-bold text-white">CRAL</span>
                    </div>
                    <p class="text-sm text-slate-400">L'associazione del dopolavoro aziendale: attività, eventi e vantaggi per tutti i soci.</p>
                </div>
                <div>
                    <h3 class="text-white font-semibold text-sm mb-3">Esplora</h3>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><a href="{{ route('attivita.index') }}" class="hover:text-white transition">Attività</a></li>
                        <li><a href="{{ route('foto.index') }}" class="hover:text-white transition">Foto</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition">Iscriviti</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-semibold text-sm mb-3">Soci</h3>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Accedi</a></li>
                        <li><a href="{{ route('dashboard') }}" class="hover:text-white transition">Area riservata</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-semibold text-sm mb-3">Contatti</h3>
                    <p class="text-sm text-slate-400">info@[dominio-associazione].it</p>
                </div>
            </div>
            <div class="border-t border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-xs text-slate-500">
                    &copy; {{ date('Y') }} CRAL. Tutti i diritti riservati.
                </div>
            </div>
        </footer>
    </div>

</body>
</html>
