{{-- Avanzamento della richiesta di tessera: cosa è già fatto e cosa manca. --}}
@props(['utente'])

@php
    $passi = [
        ['Richiesta inviata', true],
        ['Pagamento della quota', (bool) $utente->quota_pagata],
        ['Approvazione di un amministratore', false],
        ['Tessera via email', false],
    ];

    $corrente = collect($passi)->search(fn ($passo) => ! $passo[1]);
@endphp

<ol {{ $attributes->merge(['class' => 'grid gap-4 sm:grid-cols-4']) }} aria-label="Avanzamento della richiesta">
    @foreach($passi as $i => [$etichetta, $fatto])
        <li class="flex items-center gap-3 sm:flex-col sm:items-start sm:gap-3" @if($i === $corrente) aria-current="step" @endif>
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full text-sm font-bold
                {{ $fatto ? 'bg-cral-red text-white' : ($i === $corrente ? 'bg-white text-cral-red ring-2 ring-cral-red' : 'bg-cral-navy-tint text-cral-navy-soft') }}">
                @if($fatto)
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                @else
                    {{ $i + 1 }}
                @endif
            </span>
            <span class="text-sm font-semibold leading-snug {{ $fatto || $i === $corrente ? 'text-cral-navy' : 'text-cral-navy-soft' }}">{{ $etichetta }}</span>
        </li>
    @endforeach
</ol>
