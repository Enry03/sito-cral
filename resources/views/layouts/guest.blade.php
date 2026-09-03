<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=anton|archivo:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-grotesk text-cral-navy antialiased bg-cral-cream">
        <div class="min-h-screen flex flex-col items-center py-10 px-4">
            <a href="/" class="flex items-center gap-3 mb-8">
                <span class="flex h-11 w-11 items-center justify-center bg-cral-navy text-white font-display text-xl tracking-wide ring-2 ring-cral-red ring-offset-2 ring-offset-cral-cream">CR</span>
                <span class="leading-none">
                    <span class="block font-display text-2xl tracking-wide">CRAL</span>
                    <span class="block text-[10px] font-semibold tracking-[0.2em] text-cral-navy-soft uppercase -mt-0.5">Associazione dopolavoro</span>
                </span>
            </a>

            <div class="w-full sm:max-w-md border-2 border-cral-navy bg-white stack-shadow px-8 py-8">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
