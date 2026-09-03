<!--
THESIS: membership here is a real, validated credential, not a mailing-list
signup — so the site borrows the thing an office worker already trusts as
"official": the jet-age airline coupon book, not a soft SaaS gradient hero.
OWN-WORLD: cream coupon-stock ground, carrier red for stamps/actions/dates,
navy ink for structure and rules. Anton caps for display, Archivo for UI/body.
Hairline borders, punch-hole card notches, dashed tear lines, hard offset
shadows — no rounded corners, no blurred glows, no gradients.
STORY: a visitor reads the "manifest" of what CRAL offers, sees activities as
ticket legs, understands iscrizione is a real boarding process ending in a
validated tessera — and acts.
FIRST VIEWPORT: ticket-wallet hero — a letterhead-style top bar, a giant
condensed headline, a red "boarding" CTA + navy outline secondary, a manifest
of upcoming activity legs alongside.
FORM: own direction (index 3, seed 2e692ca2) fused with dealt challenger
"vernacular-ephemera-jet-age-ticket-wallet" — won on audience identification
and product clarity.
-->
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=anton|archivo:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-grotesk antialiased text-cral-navy bg-cral-cream">

    <div x-data="{ mobileOpen: false }" class="relative">
        <header class="sticky top-0 z-50 bg-cral-cream border-b-2 border-cral-navy">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center bg-cral-navy text-white font-display text-xl tracking-wide ring-2 ring-cral-red ring-offset-2 ring-offset-cral-cream">
                            CR
                        </span>
                        <span class="leading-none">
                            <span class="block font-display text-2xl tracking-wide">CRAL</span>
                            <span class="block text-[10px] font-semibold tracking-[0.2em] text-cral-navy-soft uppercase -mt-0.5">Associazione dopolavoro</span>
                        </span>
                    </a>

                    <nav class="hidden md:flex items-center gap-1 text-xs font-bold tracking-[0.15em] uppercase">
                        @php
                            $navItems = [
                                ['href' => route('home'), 'label' => 'Home', 'active' => request()->routeIs('home')],
                                ['href' => route('attivita.index'), 'label' => 'Attività', 'active' => request()->routeIs('attivita.*')],
                                ['href' => route('foto.index'), 'label' => 'Foto', 'active' => request()->routeIs('foto.*')],
                                ['href' => route('register'), 'label' => 'Iscriviti', 'active' => request()->routeIs('register')],
                            ];
                        @endphp
                        @foreach($navItems as $item)
                            <a href="{{ $item['href'] }}" class="px-4 py-2 border-b-2 {{ $item['active'] ? 'border-cral-red text-cral-red' : 'border-transparent hover:border-cral-navy' }} transition">
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </nav>

                    <div class="hidden md:flex items-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center border-2 border-cral-navy bg-cral-navy px-5 py-2.5 text-xs font-bold tracking-[0.15em] uppercase text-white hover:bg-cral-navy/90 transition">
                                Area riservata
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-xs font-bold tracking-[0.15em] uppercase hover:text-cral-red transition">Accedi</a>
                            <a href="{{ route('register') }}" class="inline-flex items-center border-2 border-cral-navy bg-cral-red px-5 py-2.5 text-xs font-bold tracking-[0.15em] uppercase text-white hover:bg-cral-red/90 transition">
                                Iscriviti ora
                            </a>
                        @endauth
                    </div>

                    <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 -mr-2 border-2 border-cral-navy" aria-label="Apri menu">
                        <svg x-show="!mobileOpen" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        <svg x-show="mobileOpen" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            <div x-show="mobileOpen" x-cloak class="md:hidden border-t-2 border-cral-navy bg-cral-cream">
                <div class="px-4 py-4 space-y-1 text-sm font-bold tracking-widest uppercase">
                    @foreach($navItems as $item)
                        <a href="{{ $item['href'] }}" class="block py-2 {{ $item['active'] ? 'text-cral-red' : '' }}">{{ $item['label'] }}</a>
                    @endforeach
                    <div class="pt-3 mt-3 border-t-2 border-cral-navy flex flex-col gap-2">
                        @auth
                            <a href="{{ route('dashboard') }}" class="border-2 border-cral-navy bg-cral-navy px-4 py-2.5 text-center text-white">Area riservata</a>
                        @else
                            <a href="{{ route('login') }}" class="text-center py-1">Accedi</a>
                            <a href="{{ route('register') }}" class="border-2 border-cral-navy bg-cral-red px-4 py-2.5 text-center text-white">Iscriviti ora</a>
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer class="bg-cral-navy text-white border-t-4 border-cral-red mt-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-9 w-9 items-center justify-center bg-white text-cral-navy font-display text-base">CR</span>
                        <span class="font-display text-xl tracking-wide">CRAL</span>
                    </div>
                    <p class="text-sm text-white/70 leading-relaxed">L'associazione del dopolavoro aziendale: attività, eventi e vantaggi per tutti i soci.</p>
                </div>
                <div>
                    <h3 class="font-grotesk text-xs font-bold tracking-[0.2em] uppercase text-cral-red mb-4">/// Esplora</h3>
                    <ul class="space-y-2.5 text-sm text-white/80">
                        <li><a href="{{ route('attivita.index') }}" class="hover:text-white transition">Attività</a></li>
                        <li><a href="{{ route('foto.index') }}" class="hover:text-white transition">Foto</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition">Iscriviti</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-grotesk text-xs font-bold tracking-[0.2em] uppercase text-cral-red mb-4">/// Soci</h3>
                    <ul class="space-y-2.5 text-sm text-white/80">
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Accedi</a></li>
                        <li><a href="{{ route('dashboard') }}" class="hover:text-white transition">Area riservata</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-grotesk text-xs font-bold tracking-[0.2em] uppercase text-cral-red mb-4">/// Contatti</h3>
                    <p class="text-sm text-white/80">info@[dominio-associazione].it</p>
                </div>
            </div>
            <div class="border-t border-white/15">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 text-[11px] tracking-widest uppercase text-white/50">
                    &copy; {{ date('Y') }} CRAL — Tutti i diritti riservati
                </div>
            </div>
        </footer>
    </div>

</body>
</html>
