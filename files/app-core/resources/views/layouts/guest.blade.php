<!DOCTYPE html>
<html lang="it" data-scrolled="false" data-menu="closed">
<head>
    @include('layouts.partials.head')
</head>
<body class="bg-cral-red-deep font-sans text-cral-navy antialiased">
<div class="hero-bg relative isolate min-h-screen overflow-x-hidden">
    <div class="pointer-events-none absolute -right-32 -top-32 -z-10 h-[32rem] w-[32rem] animate-drift rounded-full bg-cral-red-glow/35 blur-3xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-40 -left-32 -z-10 h-[28rem] w-[28rem] animate-drift rounded-full bg-cral-navy-deep/70 blur-3xl [animation-delay:-7s]" aria-hidden="true"></div>

    <div class="flex min-h-screen flex-col items-center px-4 pb-10 pt-8 sm:pt-12">
        <a href="{{ route('home') }}" class="flex items-center gap-3 text-white" aria-label="CRAL — torna al sito" data-reveal="fade">
            <img src="{{ asset('images/logo-cral-white.png') }}" alt="" width="56" height="56" class="h-14 w-14 object-contain">
            <span class="font-display text-xl font-bold tracking-tight">CRAL <span class="font-medium text-white/70">Chieti</span></span>
        </a>

        <main class="mt-8 w-full {{ $wide ? 'max-w-2xl' : 'max-w-md' }}" data-reveal data-delay="0.1">
            <div class="rounded-[2rem] bg-white p-6 shadow-lift sm:p-10">
                {{ $slot }}
            </div>
        </main>

        <a href="{{ route('home') }}" class="mt-8 text-sm font-semibold text-white/80 transition hover:text-white">&larr; Torna al sito</a>
    </div>

</div>

    @include('layouts.partials.motion')
</body>
</html>
