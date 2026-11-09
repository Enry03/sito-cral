<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#E4002B">
<title>{{ $title ?? config('app.name') }}</title>
@isset($description)
    <meta name="description" content="{{ $description }}">
@endisset
<link rel="icon" href="{{ asset('favicon.ico') }}">

{{-- Se JavaScript funziona la pagina può animarsi; altrimenti resta tutto visibile. --}}
<script>document.documentElement.classList.add('js');</script>

{{-- I caratteri sono sul sito stesso: si caricano subito, senza servizi esterni. --}}
<link rel="preload" href="{{ asset('fonts/bricolage-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="{{ asset('fonts/figtree-latin.woff2') }}" as="font" type="font/woff2" crossorigin>

@vite(['resources/css/app.css', 'resources/js/app.js'])
