<!DOCTYPE html>
<html lang="it" data-scrolled="false" data-menu="closed">
<head>
    @include('layouts.partials.head')
</head>
<body class="min-h-screen bg-white font-sans text-cral-navy antialiased">
    <a href="#contenuto" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[70] focus:rounded-full focus:bg-white focus:px-5 focus:py-3 focus:font-semibold focus:shadow-lift">Vai al contenuto</a>

    @php
        $utente = auth()->user();

        $navItems = [
            ['href' => route('home'), 'label' => 'Home', 'active' => request()->routeIs('home')],
            ['href' => route('attivita.index'), 'label' => 'Attività', 'active' => request()->routeIs('attivita.*')],
            ['href' => route('foto.index'), 'label' => 'Foto', 'active' => request()->routeIs('foto.*')],
            ['href' => route('convenzioni.index'), 'label' => 'Convenzioni', 'active' => request()->routeIs('convenzioni.*')],
        ];

        // La libreria è visibile solo a chi ha un account approvato.
        if ($utente && $utente->haAccessoSito()) {
            $navItems[] = ['href' => route('libreria.index'), 'label' => 'Libreria', 'active' => request()->routeIs('libreria.*')];
        }

        $menuUtente = [];

        if ($utente) {
            if ($utente->haAccessoSito()) {
                $menuUtente[] = ['href' => route('dashboard'), 'label' => 'La mia area'];

                if ($utente->isSocioCral()) {
                    $menuUtente[] = ['href' => route('account.show'), 'label' => 'Il mio account'];
                    $menuUtente[] = ['href' => route('libreria.prestiti'), 'label' => 'I miei prestiti'];
                    $menuUtente[] = ['href' => route('documenti.index'), 'label' => 'Documenti'];
                }

                $menuUtente[] = ['href' => route('profile.edit'), 'label' => 'Profilo e password'];
            } else {
                $menuUtente[] = ['href' => route('attesa-approvazione'), 'label' => 'Stato della richiesta'];
            }

            if ($utente->isAdmin()) {
                $menuUtente[] = ['href' => url('/admin'), 'label' => 'Pannello di gestione'];
            }
        }
    @endphp

    {{-- ------------------------------------------------------------ header --}}
    <header class="fixed inset-x-0 top-0 z-[60] px-3 pt-3 sm:px-6 sm:pt-4">
        <div class="header-bar mx-auto flex max-w-6xl items-center justify-between gap-2 rounded-full bg-white/85 py-2 pl-3 pr-2 shadow-soft backdrop-blur-xl sm:pl-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="CRAL — pagina iniziale">
                <img src="{{ asset('images/logo-cral.png') }}" alt="" width="44" height="44" class="h-11 w-11 object-contain">
                <span class="hidden font-display text-lg font-bold leading-none tracking-tight sm:block">CRAL <span class="font-medium text-cral-navy-soft">Chieti</span></span>
            </a>

            <nav class="hidden items-center gap-1 md:flex" aria-label="Principale">
                @foreach($navItems as $item)
                    <a href="{{ $item['href'] }}" @if($item['active']) aria-current="page" @endif
                        class="rounded-full px-4 py-2 text-[0.95rem] font-semibold transition duration-300 {{ $item['active'] ? 'bg-cral-red-tint text-cral-red' : 'text-cral-navy/80 hover:bg-cral-navy/5 hover:text-cral-navy' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="flex items-center gap-2">
                @auth
                    <details data-dropdown class="group relative hidden md:block">
                        <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full bg-cral-navy-tint py-1.5 pl-1.5 pr-4 text-sm font-semibold transition hover:bg-cral-navy/10 [&::-webkit-details-marker]:hidden">
                            <span class="grid h-8 w-8 place-items-center rounded-full bg-cral-red text-sm font-bold text-white">{{ mb_strtoupper(mb_substr($utente->name, 0, 1)) }}</span>
                            <span class="max-w-[9rem] truncate">{{ \Illuminate\Support\Str::before($utente->name, ' ') }}</span>
                            <svg class="h-4 w-4 transition duration-300 group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                        </summary>
                        <div class="absolute right-0 mt-3 w-64 origin-top-right animate-fade-up rounded-3xl bg-white p-2 shadow-lift">
                            <p class="truncate px-4 pb-2 pt-3 text-xs font-semibold text-cral-navy-soft">{{ $utente->email }}</p>
                            @foreach($menuUtente as $voce)
                                <a href="{{ $voce['href'] }}" class="block rounded-2xl px-4 py-2.5 text-[0.95rem] font-semibold transition hover:bg-cral-red-tint hover:text-cral-red">{{ $voce['label'] }}</a>
                            @endforeach
                            <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-cral-navy/10 pt-1">
                                @csrf
                                <button type="submit" class="block w-full rounded-2xl px-4 py-2.5 text-left text-[0.95rem] font-semibold text-cral-navy-soft transition hover:bg-cral-navy/5 hover:text-cral-navy">Esci</button>
                            </form>
                        </div>
                    </details>
                @else
                    <a href="{{ route('login') }}" class="hidden rounded-full px-4 py-2 text-[0.95rem] font-semibold text-cral-navy/80 transition hover:bg-cral-navy/5 hover:text-cral-navy md:block">Accedi</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm hidden md:inline-flex">Iscriviti</a>
                @endauth

                <button type="button" data-menu-toggle aria-expanded="false" aria-controls="menu-mobile" aria-label="Apri o chiudi il menu"
                    class="relative grid h-11 w-11 place-items-center rounded-full bg-cral-navy text-white transition active:scale-95 md:hidden [[data-menu=open]_&]:bg-cral-red">
                    <span class="sr-only">Menu</span>
                    <svg class="h-5 w-5 [[data-menu=open]_&]:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M4 8h16M4 16h16"/></svg>
                    <svg class="hidden h-5 w-5 [[data-menu=open]_&]:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>
        </div>
    </header>

    {{-- Menu da telefono --}}
    <div id="menu-mobile" class="mobile-menu navy-bg fixed inset-0 z-[55] overflow-y-auto md:hidden" role="dialog" aria-modal="true" aria-label="Menu">
        <div class="flex min-h-full flex-col px-7 pb-10 pt-28">
            <ul class="space-y-1">
                @foreach($navItems as $i => $item)
                    <li style="--i: {{ $i }}">
                        <a href="{{ $item['href'] }}" class="block py-2 font-display text-4xl font-bold tracking-tight {{ $item['active'] ? 'text-cral-red-glow' : 'text-white' }}">{{ $item['label'] }}</a>
                    </li>
                @endforeach
            </ul>

            <ul class="mt-auto space-y-3 pt-10">
                @auth
                    @foreach($menuUtente as $i => $voce)
                        <li style="--i: {{ count($navItems) + $i }}">
                            <a href="{{ $voce['href'] }}" class="block text-lg font-semibold text-white/80">{{ $voce['label'] }}</a>
                        </li>
                    @endforeach
                    <li style="--i: {{ count($navItems) + count($menuUtente) }}">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-ghost-light mt-3 w-full">Esci</button>
                        </form>
                    </li>
                @else
                    <li style="--i: {{ count($navItems) }}"><a href="{{ route('register') }}" class="btn btn-white w-full">Iscriviti al CRAL</a></li>
                    <li style="--i: {{ count($navItems) + 1 }}"><a href="{{ route('login') }}" class="btn btn-ghost-light w-full">Accedi</a></li>
                @endauth
            </ul>
        </div>
    </div>

    <main id="contenuto">
        {{ $slot }}
    </main>

    {{-- ------------------------------------------------------------ footer --}}
    <footer class="relative mt-0 bg-cral-navy-deep text-white">
        <x-wave variant="2" class="absolute inset-x-0 bottom-full -mb-px h-8 text-cral-navy-deep sm:h-14" />
        <div class="container-x grid gap-12 pb-10 pt-16 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
            <div>
                <img src="{{ asset('images/logo-cral-white.png') }}" alt="CRAL" width="64" height="64" class="h-16 w-16 object-contain" loading="lazy">
                <p class="mt-5 max-w-xs text-[0.95rem] leading-relaxed text-white/70">Il luogo di lavoro può essere molto divertente e stimolante: scopri cosa organizza il CRAL per i colleghi.</p>
            </div>
            <div>
                <h2 class="font-display text-base font-bold text-white">Esplora</h2>
                <ul class="mt-4 space-y-3 text-[0.95rem] text-white/70">
                    <li><a href="{{ route('attivita.index') }}" class="transition hover:text-white">Attività</a></li>
                    <li><a href="{{ route('foto.index') }}" class="transition hover:text-white">Foto</a></li>
                    <li><a href="{{ route('convenzioni.index') }}" class="transition hover:text-white">Convenzioni</a></li>
                    <li><a href="{{ route('register') }}" class="transition hover:text-white">Iscriviti</a></li>
                </ul>
            </div>
            <div>
                <h2 class="font-display text-base font-bold text-white">Soci</h2>
                <ul class="mt-4 space-y-3 text-[0.95rem] text-white/70">
                    <li><a href="{{ route('login') }}" class="transition hover:text-white">Accedi</a></li>
                    <li><a href="{{ route('dashboard') }}" class="transition hover:text-white">Area riservata</a></li>
                </ul>
            </div>
            <div>
                <h2 class="font-display text-base font-bold text-white">Contatti</h2>
                <p class="mt-4 text-[0.95rem] text-white/70"><a href="mailto:info@cralchietildo.it" class="transition hover:text-white">info@cralchietildo.it</a></p>
            </div>
        </div>
        <div class="container-x flex flex-wrap items-center justify-between gap-3 border-t border-white/10 py-6 text-sm text-white/50">
            <span>&copy; {{ date('Y') }} CRAL — Tutti i diritti riservati</span>
            <a href="{{ route('privacy') }}" class="transition hover:text-white">Privacy Policy</a>
        </div>
    </footer>

    @include('layouts.partials.motion')
</body>
</html>
