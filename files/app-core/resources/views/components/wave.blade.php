{{--
    Onda di raccordo tra due sezioni: il colore è quello della sezione che segue
    (si passa con una classe text-..., es. class="text-white") e l'altezza (es. h-10 sm:h-20). Con "flip" l'onda è
    capovolta (per le sezioni che iniziano con una curva verso l'alto).
--}}
@props(['flip' => false, 'variant' => 1])

@php
    $percorsi = [
        1 => 'M0,64 C180,116 380,8 640,44 C900,80 1120,118 1440,36 L1440,120 L0,120 Z',
        2 => 'M0,40 C240,100 460,110 720,66 C980,22 1200,20 1440,78 L1440,120 L0,120 Z',
    ];
@endphp

<svg viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true" focusable="false"
    {{ $attributes->merge(['class' => 'block w-full fill-current']) }}
    @if($flip) style="transform: scale(-1, -1)" @endif>
    <path d="{{ $percorsi[$variant] ?? $percorsi[1] }}"/>
</svg>
